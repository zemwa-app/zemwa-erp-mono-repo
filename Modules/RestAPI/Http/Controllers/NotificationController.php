<?php

namespace Modules\RestAPI\Http\Controllers;

use Carbon\Carbon;
use Froiden\RestAPI\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Lang;

class NotificationController extends ApiBaseController
{
    public function index()
    {
        $user = api_user();

        $limit = (int) request('limit', 20);
        $offset = (int) request('offset', 0);

        // Strict query to avoid any duplicates or scope issues
        $query = \Illuminate\Notifications\DatabaseNotification::query()
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', \App\Models\User::class)
            ->whereNull('read_at')
            ->orderByDesc('created_at');

        if ($offset > 0) {
            $query->skip($offset);
        }

        $notifications = $query->take($limit)->get();

        $data = $notifications->filter(function ($n) {
            $payload = is_array($n->data) ? $n->data : (json_decode($n->data, true) ?: []);
            // Skip daily_goal_reminder notifications
            return ($payload['type'] ?? '') !== 'daily_goal_reminder';
        })->map(function ($n) {
            $payload = is_array($n->data) ? $n->data : (json_decode($n->data, true) ?: []);

            $actorId = $payload['user_id']
                ?? $payload['sender_id']
                ?? $payload['from_id']
                ?? $payload['by_id']
                ?? null;
            $actor = $actorId ? \App\Models\User::find($actorId) : null;

            // Get notification type class name
            $notificationType = class_basename($n->type);
            $typeSlug = Str::snake($notificationType);

            // Get title based on notification type (matching web interface)
            $title = $this->getNotificationTitle($typeSlug, $payload);

            // Get message based on notification type (matching web interface)
            $message = $this->getNotificationMessage($typeSlug, $payload);

            $type = $typeSlug;

            $eventId = $payload['id']
                ?? $payload['event_id']
                ?? $payload['task_id']
                ?? $payload['leave_id']
                ?? $payload['salary_slip_id']
                ?? $payload['entity_id']
                ?? $payload['model_id']
                ?? null;

            return [
                'id' => $n->id,
                'user' => [
                    'id' => $actor?->id,
                    'name' => $actor?->name ?? ($payload['user_name'] ?? null),
                    'image_url' => $actor?->image_url,
                ],
                'title' => $title,
                'message' => $message,
                'time' => Carbon::parse($n->created_at)->timezone(company()->timezone)->diffForHumans(),
                'type' => $type,
                'event_id' => $eventId,
                'read_at' => $n->read_at,
                'created_at' => $n->created_at,
            ];
        })->values();

        return ApiResponse::make(null, [
            'total' => (clone $query)->count(),
            'count' => $data->count(),
            'records' => $data,
        ]);
    }

    public function markAsRead($id)
    {
        $user = api_user();

        $notification = $user->notifications()->where('id', $id)->firstOrFail();
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return ApiResponse::make(__('messages.notificationRead'), [
            'id' => $notification->id,
            'read_at' => $notification->read_at,
        ]);
    }

    public function markAllAsRead()
    {
        $user = api_user();
        $user->unreadNotifications()->update(['read_at' => now()]);

        return ApiResponse::make(__('messages.notificationRead'), [
            'unread' => 0,
        ]);
    }

    /**
     * Get notification title based on type (matching web interface)
     */
    private function getNotificationTitle($typeSlug, $payload)
    {
        $notificationMappings = [
            'new_notice' => 'email.newNotice.subject',
            'notice_update' => 'email.newNotice.subject',
            'new_task' => 'email.newTask.subject',
            'task_updated' => 'email.taskUpdate.subject',
            'task_status_updated' => 'email.taskUpdate.statusUpdated',
            'task_mention' => 'email.newTask.mentionTask',
            'task_note' => 'email.taskNote.subject',
            'task_note_mention' => 'email.taskNote.mentionNote',
            'leave_application' => 'email.leave.applied',
            'leave_status_approve' => 'email.leave.approve',
            'leave_status_reject' => 'email.leave.reject',
            'leaves' => 'email.leaves.subject',
            'new_leave_request' => 'email.leaves.subject',
            'new_expense_admin' => 'email.newExpense.subject',
            'new_expense_member' => 'email.newExpense.subject',
            'new_chat' => 'email.newChat.subject',
            'new_mention_chat' => 'email.newChat.subject',
            'new_discussion' => 'email.newDiscussion.subject',
            'new_discussion_reply' => 'email.newDiscussionReply.subject',
            'new_discussion_mention' => 'email.newDiscussion.mentionDiscussion',
            'new_project_member' => 'email.newProjectMember.subject',
            'new_ticket' => 'email.newTicket.subject',
            'new_ticket_reply' => 'email.newTicketReply.subject',
            'new_ticket_requester' => 'email.newTicketRequester.subject',
            'ticket_agent' => 'email.ticketAgent.subject',
            'new_user' => 'email.newUser.subject',
            'new_customer' => 'email.newCustomer.subject',
            'new_holiday' => 'email.newHoliday.subject',
            'new_estimate_request' => 'email.newEstimateRequest.subject',
            'estimate_declined' => 'email.estimateDeclined.subject',
            'invoice_payment_received' => 'email.invoicePaymentReceived.subject',
            'new_rating' => 'email.newRating.subject',
            'new_appreciation' => 'email.newAppreciation.subject',
            'new_follow_up' => 'email.newFollowUp.subject',
            'new_follow_up_deal' => 'email.newFollowUpDeal.subject',
            'new_follow_up_attendees' => 'email.newFollowUpAttendees.subject',
            'auto_follow_up_reminder' => 'email.autoFollowUpReminder.subject',
            'new_lead_created' => 'email.lead.subject',
            'lead_agent_assigned' => 'clan.leadAgent.subject',
            'deal_stage_updated' => 'email.dealStageUpdated.subject',
            'contract_signed' => 'email.contractSigned.subject',
            'proposal_signed' => 'email.proposalSigned.subject',
            'new_order' => 'email.newOrder.subject',
            'order_updated' => 'email.orderUpdated.subject',
            'new_product_purchase_request' => 'email.newProductPurchaseRequest.subject',
            'time_tracker_reminder' => 'email.trackerReminder.subject',
            'shift_scheduled' => 'email.shiftScheduled.subject',
            'shift_change_request' => 'email.shiftChangeRequest.subject',
            'shift_change_status' => 'email.shiftChangeStatus.subject',
            'request_regularise' => 'email.requestRegularise.subject',
            'request_regularisation_accept' => 'email.requestRegularisationAccept.subject',
            'request_regularisation_reject' => 'email.requestRegularisationReject.subject',
            'promotion_added' => 'email.promotionAdded.subject',
            'promotion_updated' => 'email.promotionUpdated.subject',
            'birthday_reminder' => 'email.birthdayReminder.subject',
            'cold_task' => 'email.coldTask.subject',
            'task_approval' => 'email.taskApproval.subject',
            'task_completed' => 'email.taskComplete.subject',
            'task_watchers' => 'email.taskWatchers.subject',
            'task_dependent_notification' => 'email.taskDependentNotification.subject',
            'sub_task_created' => 'email.subTaskCreated.subject',
            'sub_task_completed' => 'email.subTaskCompleted.subject',
            'sub_task_assignee_added' => 'email.subTaskAssigneeAdded.subject',
            'task_comment' => 'email.taskComment.subject',
            'task_comment_mention' => 'email.taskCommentMention.subject',
            'project_member_mention' => 'email.projectMemberMention.subject',
            'project_note_mention' => 'email.projectNoteMention.subject',
            'project_rating' => 'email.projectRating.subject',
            'new_project_status' => 'email.newProjectStatus.subject',
            'expense_recurring_status' => 'email.expenseRecurringStatus.subject',
            'expense_status' => 'email.expenseStatus.subject',
            'expense_recurring_member' => 'email.expenseRecurringMember.subject',
            'event_invite' => 'email.eventInvite.subject',
            'event_host_invite' => 'email.eventHostInvite.subject',
            'event_reminder' => 'email.eventReminder.subject',
            'event_minutes_of_meeting' => 'email.eventMinutesOfMeeting.subject',
            'event_host_minutes_of_meeting' => 'email.eventHostMinutesOfMeeting.subject',
            'event_status_note' => 'email.eventStatusNote.subject',
            'gdpr_notification' => 'email.gdprNotification.subject',
            'gdpr_immediate_notification' => 'email.gdprImmediateNotification.subject',
            'new_user_via_link' => 'email.newUserViaLink.subject',
            'new_user_slack' => 'email.newUserSlack.subject',
            'multiple_leave_application' => 'email.multipleLeaveApplication.subject',
            'leave_status_update' => 'email.leaveStatusUpdate.subject'
        ];

        if (isset($notificationMappings[$typeSlug])) {
            return $this->translateNotificationTitle($notificationMappings[$typeSlug], $payload, $typeSlug);
        }

        // Fallback for unmapped notification types
        return $this->fallbackNotificationTitle($payload, $typeSlug);
    }

    /**
     * Get notification message based on type (matching web interface)
     */
    private function getNotificationMessage($typeSlug, $payload)
    {
        $messageMappings = [
            'new_notice' => 'heading',
            'notice_update' => 'heading',
            'new_task' => 'heading',
            'task_updated' => 'heading',
            'task_status_updated' => 'heading',
            'task_mention' => 'heading',
            'task_note' => 'heading',
            'task_note_mention' => 'heading',
            'leave_application' => 'leave_date',
            'leaves' => 'user.name',
            'new_leave_request' => 'user.name',
            'leave_status_approve' => 'heading',
            'leave_status_reject' => 'leave_date',
            'new_expense_admin' => 'item_name',
            'new_expense_member' => 'item_name',
            'new_chat' => 'user_name',
            'new_mention_chat' => 'user_name',
            'new_discussion' => 'heading',
            'new_discussion_reply' => 'heading',
            'new_discussion_mention' => 'heading',
            'new_project_member' => 'heading',
            'new_ticket' => 'subject',
            'new_ticket_reply' => 'subject',
            'new_ticket_requester' => 'subject',
            'ticket_agent' => 'subject',
            'new_user' => 'name',
            'new_customer' => 'name',
            'new_holiday' => 'date',
            'new_estimate_request' => 'heading',
            'estimate_declined' => 'heading',
            'invoice_payment_received' => 'heading',
            'new_rating' => 'heading',
            'new_appreciation' => 'heading',
            'new_follow_up' => 'heading',
            'new_follow_up_deal' => 'heading',
            'new_follow_up_attendees' => 'heading',
            'auto_follow_up_reminder' => 'heading',
            'new_lead_created' => 'heading',
            'lead_agent_assigned' => 'heading',
            'deal_stage_updated' => 'heading',
            'contract_signed' => 'heading',
            'proposal_signed' => 'heading',
            'new_order' => 'heading',
            'order_updated' => 'heading',
            'new_product_purchase_request' => 'heading',
            'time_tracker_reminder' => 'id',
            'shift_scheduled' => 'heading',
            'shift_change_request' => 'heading',
            'shift_change_status' => 'heading',
            'request_regularise' => 'heading',
            'request_regularisation_accept' => 'heading',
            'request_regularisation_reject' => 'heading',
            'promotion_added' => 'heading',
            'promotion_updated' => 'heading',
            'birthday_reminder' => 'name',
            'cold_task' => 'heading',
            'task_approval' => 'heading',
            'task_completed' => 'heading',
            'task_watchers' => 'heading',
            'task_dependent_notification' => 'heading',
            'sub_task_created' => 'heading',
            'sub_task_completed' => 'heading',
            'sub_task_assignee_added' => 'heading',
            'task_comment' => 'heading',
            'task_comment_mention' => 'heading',
            'project_member_mention' => 'heading',
            'project_note_mention' => 'heading',
            'project_rating' => 'heading',
            'new_project_status' => 'heading',
            'expense_recurring_status' => 'heading',
            'expense_status' => 'heading',
            'expense_recurring_member' => 'heading',
            'event_invite' => 'heading',
            'event_host_invite' => 'heading',
            'event_reminder' => 'heading',
            'event_minutes_of_meeting' => 'heading',
            'event_host_minutes_of_meeting' => 'heading',
            'event_status_note' => 'heading',
            'gdpr_notification' => 'heading',
            'gdpr_immediate_notification' => 'heading',
            'new_user_via_link' => 'name',
            'new_user_slack' => 'name',
            'multiple_leave_application' => 'heading',
            'leave_status_update' => 'heading'
        ];

        if (isset($messageMappings[$typeSlug])) {
            $messageSource = $messageMappings[$typeSlug];
            
            // Handle special cases for message formatting
            if ($messageSource === 'user.name') {
                $name = $payload['user']['name'] ?? $payload['user_name'] ?? null;
                if ($name) return (string)$name;
            } elseif ($messageSource === 'leave_date' && isset($payload['leave_date'])) {
                return \Carbon\Carbon::parse($payload['leave_date'])->translatedFormat(company()->date_format);
            } elseif ($messageSource === 'id' && isset($payload['id'])) {
                return '#' . $payload['id'];
            } elseif (isset($payload[$messageSource])) {
                return $payload[$messageSource];
            }
        }

        // Fallback for unmapped notification types
        return $payload['heading'] ?? $payload['subject'] ?? $payload['name'] ?? $payload['body'] ?? $payload['message'] ?? $payload['text'] ?? $payload['description'] ?? '';
    }

    private function translateNotificationTitle(string $translationKey, array $payload, string $typeSlug): string
    {
        if (Lang::has($translationKey)) {
            return __($translationKey);
        }

        return $this->fallbackNotificationTitle($payload, $typeSlug);
    }

    private function fallbackNotificationTitle(array $payload, string $typeSlug): string
    {
        return $payload['title']
            ?? $payload['heading']
            ?? $payload['subject']
            ?? Str::headline($typeSlug);
    }

}

