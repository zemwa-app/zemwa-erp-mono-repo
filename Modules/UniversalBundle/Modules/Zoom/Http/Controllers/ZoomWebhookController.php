<?php

namespace Modules\Zoom\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Scopes\CompanyScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Modules\Zoom\Entities\ZoomMeeting;
use Modules\Zoom\Entities\ZoomSetting;
use Modules\Zoom\Events\MeetingInviteEvent;

class ZoomWebhookController extends Controller
{

    public function index($companyHash = null)
    {
        $response = $this->webhookPayload();
        $event = $response['event'] ?? null;

        Log::info('Zoom Webhook Response', $response);

        switch ($event) {

        case 'meeting.started':
            $this->meetingStarted($response);
            break;

        case 'meeting.ended':
            $this->meetingEnded($response);
            break;

        case 'meeting.deleted':
            $this->meetingDeleted($response);
            break;

        case 'meeting.created':
            $this->meetingCreated($response);
            break;

        case 'meeting.updated':
            $this->meetingUpdated($response);
            break;

        case 'endpoint.url_validation':
            return $this->validateEndpointUrl($response, $companyHash);

        default:
            //
            break;
        }

        return response('Webhook Handled');
    }

    protected function validateEndpointUrl($response, $companyHash)
    {
        $company_id = Company::select('id')->where('hash', $companyHash)->first();

        $secret = ZoomSetting::where('company_id', $company_id->id)->first();

        $token = hash_hmac('sha256', $response['payload']['plainToken'], $secret->secret_token);

        $plain_token = $response['payload']['plainToken'];

        return response(['plainToken' => $plain_token, 'encryptedToken' => $token]);

    }

    /**
     * @return array<string, mixed>
     */
    protected function webhookPayload(): array
    {
        if (request()->isJson()) {
            $data = request()->json()->all();

            if (is_array($data) && $data !== []) {
                return $data;
            }
        }

        $raw = request()->getContent();

        if ($raw !== '' && $raw !== '0') {
            $decoded = json_decode($raw, true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return request()->all();
    }

    /**
     * @param  array<string, mixed>  $response
     * @return array<string, mixed>|null
     */
    protected function meetingPayloadObject(array $response): ?array
    {
        $object = data_get($response, 'payload.object');

        return is_array($object) ? $object : null;
    }

    protected function normalizeZoomMeetingId(mixed $id): string
    {
        if ($id === null || $id === '') {
            return '';
        }

        return (string) $id;
    }

    /**
     * Map Zoom occurrence status (meeting.updated) to app enum:
     * waiting = not yet started, live = in progress, canceled, finished.
     */
    protected function statusFromOccurrenceZoom(?string $zoomOccurrenceStatus): ?string
    {
        return match ($zoomOccurrenceStatus) {
            'deleted' => 'canceled',
            'available' => 'waiting',
            default => null,
        };
    }

    /**
     * Resolve DB row for Zoom meeting.started / meeting.ended.
     * Avoids whereDate(UTC vs local) mismatches for recurring rows sharing the same Zoom meeting_id.
     *
     * @param  array<string, mixed>  $response
     */
    protected function resolveMeetingFromWebhook(array $response): ?ZoomMeeting
    {
        $object = $this->meetingPayloadObject($response);

        if ($object === null) {
            return null;
        }

        $zoomMeetingId = $this->normalizeZoomMeetingId($object['id'] ?? null);

        if ($zoomMeetingId === '') {
            return null;
        }

        $candidates = ZoomMeeting::withoutGlobalScope(CompanyScope::class)
            ->with('company')
            ->where('meeting_id', $zoomMeetingId)
            ->orderBy('start_date_time')
            ->orderBy('id')
            ->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        if ($candidates->count() === 1) {
            return $candidates->first();
        }

        $occurrenceId = data_get($object, 'occurrence_id');

        if ($occurrenceId !== null && $occurrenceId !== '') {
            $byOccurrence = $candidates->firstWhere('occurrence_id', (string) $occurrenceId);

            if ($byOccurrence) {
                return $byOccurrence;
            }
        }

        $startRaw = $object['start_time'] ?? null;

        if ($startRaw === null || $startRaw === '') {
            return $candidates->first();
        }

        try {
            $zoomStartUtc = Carbon::parse($startRaw)->utc();
        } catch (\Throwable) {
            return $candidates->first();
        }

        $best = null;
        $bestDiff = PHP_INT_MAX;

        foreach ($candidates as $meeting) {
            $companyTz = $meeting->company?->timezone ?: config('app.timezone');

            try {
                $dbStartUtc = Carbon::parse($meeting->start_date_time, $companyTz)->utc();
            } catch (\Throwable) {
                continue;
            }

            $diff = abs($dbStartUtc->diffInSeconds($zoomStartUtc));

            if ($diff < $bestDiff) {
                $bestDiff = $diff;
                $best = $meeting;
            }
        }

        return $best ?? $candidates->first();
    }

    protected function meetingStarted($response)
    {
        $meeting = $this->resolveMeetingFromWebhook($response);

        if ($meeting) {
            $meeting->status = 'live';
            $meeting->save();
        }
    }

    protected function meetingEnded($response)
    {
        $meeting = $this->resolveMeetingFromWebhook($response);

        if ($meeting) {
            $meeting->status = 'finished';
            $meeting->save();
        }
    }

    protected function meetingDeleted($response)
    {
        $object = $this->meetingPayloadObject($response);

        if ($object === null) {
            return;
        }

        $zoomMeetingId = $this->normalizeZoomMeetingId($object['id'] ?? null);

        if ($zoomMeetingId === '') {
            return;
        }

        $baseQuery = ZoomMeeting::withoutGlobalScope(CompanyScope::class)
            ->where('meeting_id', $zoomMeetingId);

        $operation = data_get($response, 'payload.operation');

        if ($operation === 'all') {
            $baseQuery->update(['status' => 'canceled']);

            return;
        }

        $occurrences = data_get($object, 'occurrences');

        if (is_array($occurrences) && $occurrences !== []) {
            foreach ($occurrences as $occ) {
                $occurrenceId = data_get($occ, 'occurrence_id');

                if ($occurrenceId !== null && $occurrenceId !== '') {
                    ZoomMeeting::withoutGlobalScope(CompanyScope::class)
                        ->where('meeting_id', $zoomMeetingId)
                        ->where('occurrence_id', (string) $occurrenceId)
                        ->update(['status' => 'canceled']);
                }
            }

            return;
        }

        $baseQuery->update(['status' => 'canceled']);
    }

    protected function meetingCreated($response)
    {
        $object = $this->meetingPayloadObject($response);

        if ($object === null) {
            return;
        }

        $zoomMeetingId = $this->normalizeZoomMeetingId($object['id'] ?? null);

        if ($zoomMeetingId === '') {
            return;
        }

        $meeting = ZoomMeeting::withoutGlobalScope(CompanyScope::class)
            ->with('attendees', 'company')
            ->where('meeting_id', $zoomMeetingId)
            ->first();

        if (! is_null($meeting) && $meeting->repeat == 1) {
            $occurrences = data_get($object, 'occurrences');

            if (! is_array($occurrences)) {
                return;
            }

            foreach ($occurrences as $key => $value) {

                if ($key == 0) {
                    $meeting->occurrence_id = $value['occurrence_id'];
                    $meeting->start_date_time = Carbon::parse($value['start_time'])->timezone($meeting->company->timezone)->toDateTimeString();
                    $meeting->end_date_time = Carbon::parse($value['start_time'])->timezone($meeting->company->timezone)->addMinutes($value['duration'])->toDateTimeString();
                    $meeting->status = 'waiting';
                    $meeting->save();
                    event(new MeetingInviteEvent($meeting, $meeting->attendees));

                }
                else {
                    $occurrence = $meeting->replicate()->fill(
                        [
                            'occurrence_id' => $value['occurrence_id'],
                            'occurrence_order' => $key + 1,
                            'start_date_time' => Carbon::parse($value['start_time'])->timezone($meeting->company->timezone)->toDateTimeString(),
                            'end_date_time' => Carbon::parse($value['start_time'])->timezone($meeting->company->timezone)->addMinutes($value['duration'])->toDateTimeString(),
                            'status' => 'waiting',
                        ]
                    );

                    $occurrence->save();
                    $attendees = $meeting->attendees->pluck('id')->toArray();
                    $occurrence->attendees()->sync($attendees);
                }
            }
        }
    }

    protected function meetingUpdated($response)
    {
        $object = $this->meetingPayloadObject($response);

        if ($object === null) {
            return;
        }

        $zoomMeetingId = $this->normalizeZoomMeetingId($object['id'] ?? null);

        if ($zoomMeetingId === '') {
            return;
        }

        $meetings = ZoomMeeting::withoutGlobalScope(CompanyScope::class)
            ->with('company')
            ->where('meeting_id', $zoomMeetingId)
            ->orderBy('id')
            ->get();

        if ($meetings->count() > 1) {

            $occurrences = data_get($object, 'occurrences');

            if (! is_array($occurrences)) {
                return;
            }

            foreach ($occurrences as $occPayload) {
                $occurrenceId = data_get($occPayload, 'occurrence_id');

                if ($occurrenceId === null || $occurrenceId === '') {
                    continue;
                }

                $meeting = $meetings->firstWhere('occurrence_id', (string) $occurrenceId);

                if (! $meeting) {
                    continue;
                }

                $updates = [];

                if (isset($occPayload['start_time'])) {
                    $tz = $meeting->company?->timezone ?: config('app.timezone');
                    $duration = (int) ($occPayload['duration'] ?? 0);

                    if ($duration < 1) {
                        $duration = max(
                            1,
                            (int) Carbon::parse($meeting->start_date_time)->diffInMinutes(Carbon::parse($meeting->end_date_time))
                        );
                    }

                    $updates['start_date_time'] = Carbon::parse($occPayload['start_time'])->timezone($tz)->toDateTimeString();
                    $updates['end_date_time'] = Carbon::parse($occPayload['start_time'])->timezone($tz)->addMinutes($duration)->toDateTimeString();
                }

                $mapped = $this->statusFromOccurrenceZoom(isset($occPayload['status']) ? (string) $occPayload['status'] : null);

                if ($mapped !== null) {
                    $updates['status'] = $mapped;
                }

                if ($updates === []) {
                    continue;
                }

                ZoomMeeting::withoutGlobalScope(CompanyScope::class)
                    ->where('occurrence_id', (string) $occurrenceId)
                    ->update($updates);
            }

            return;
        }

        $meeting = $meetings->first();

        if (! isset($object['start_time']) || ! $meeting) {
            return;
        }

        $tz = $meeting->company?->timezone ?: config('app.timezone');
        $startTime = Carbon::parse($object['start_time'])->timezone($tz);
        $duration = (int) ($object['duration'] ?? 0);

        $updates = [
            'start_date_time' => $startTime->toDateTimeString(),
            'end_date_time' => $startTime->copy()->addMinutes($duration)->toDateTimeString(),
        ];

        if (! in_array($meeting->status, ['live', 'finished', 'canceled'], true) && $startTime->isFuture()) {
            $updates['status'] = 'waiting';
        }

        ZoomMeeting::withoutGlobalScope(CompanyScope::class)
            ->where('meeting_id', $zoomMeetingId)
            ->update($updates);
    }

    public function getWebhook()
    {
        return response()->json(['message' => 'This URL should not be accessed directly. Only POST requests are allowed.']);
    }

}
