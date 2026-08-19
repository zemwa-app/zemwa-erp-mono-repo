<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\DealEmailTemplate;

class DealEmailTemplateService
{
    public static function parseMergeFields(string $text, Deal $deal): string
    {
        $deal->loadMissing(['contact', 'leadStage', 'pipeline', 'leadAgent.user', 'currency']);

        $replacements = [
            '{contact_name}' => $deal->contact->client_name ?? '',
            '{contact_email}' => $deal->contact->client_email ?? '',
            '{contact_company}' => $deal->contact->company_name ?? '',
            '{deal_name}' => $deal->name ?? '',
            '{deal_value}' => $deal->value ? currency_format($deal->value, $deal->currency_id) : '',
            '{deal_stage}' => $deal->leadStage->name ?? '',
            '{deal_pipeline}' => $deal->pipeline->name ?? '',
            '{agent_name}' => $deal->leadAgent?->user?->name ?? '',
            '{company_name}' => company()->company_name ?? '',
            '{today_date}' => now()->timezone(company()->timezone)->translatedFormat(company()->date_format),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    public static function fetchForDeal(int $templateId, int $dealId): array
    {
        $template = DealEmailTemplate::findOrFail($templateId);
        $deal = Deal::with(['contact', 'leadStage', 'pipeline', 'leadAgent.user', 'currency'])->findOrFail($dealId);

        return [
            'subject' => self::parseMergeFields($template->subject, $deal),
            'body' => self::parseMergeFields($template->body, $deal),
        ];
    }
}
