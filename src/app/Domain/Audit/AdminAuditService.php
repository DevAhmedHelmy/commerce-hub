<?php

declare(strict_types=1);

namespace App\Domain\Audit;

use App\Models\AdminAuditLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Single, consistent mechanism for recording critical admin actions (prompt 39 §9). Captures
 * the actor, a neutral action code, the affected entity, only the audited old/new fields, and
 * safe request metadata, then writes one immutable {@see AdminAuditLog}. Sensitive fields are
 * redacted centrally (§8). Callers invoke this AFTER a successful mutation so a rolled-back
 * change never produces a false-success audit (§10).
 */
final class AdminAuditService
{
    /**
     * Field name fragments that must never be persisted in an audit payload (§8). Matched
     * case-insensitively as substrings so `password`, `password_hash`, `otp_code`, `api_token`,
     * etc. are all redacted.
     */
    private const SENSITIVE_FRAGMENTS = ['password', 'secret', 'token', 'otp', 'code_hash', 'api_key', 'credential'];

    private const REDACTED = '[redacted]';

    /**
     * @param  array<string,mixed>  $oldValues
     * @param  array<string,mixed>  $newValues
     * @param  array<string,mixed>  $metadata
     */
    public function record(
        string $action,
        ?Model $auditable = null,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = [],
    ): AdminAuditLog {
        return AdminAuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $this->redact($oldValues) ?: null,
            'new_values' => $this->redact($newValues) ?: null,
            'metadata' => $metadata ?: null,
            'ip_address' => $this->requestIp(),
            'user_agent' => $this->requestUserAgent(),
        ]);
    }

    /**
     * Old/new maps limited to the given audited fields that actually changed on a saved model.
     *
     * @param  list<string>  $fields
     * @return array{0: array<string,mixed>, 1: array<string,mixed>}
     */
    public function changes(Model $model, array $fields): array
    {
        $old = [];
        $new = [];

        foreach ($fields as $field) {
            if ($model->wasChanged($field)) {
                $old[$field] = $model->getOriginal($field);
                $new[$field] = $model->getAttribute($field);
            }
        }

        return [$old, $new];
    }

    /**
     * A snapshot of the given fields' current values (for create/delete events).
     *
     * @param  list<string>  $fields
     * @return array<string,mixed>
     */
    public function snapshot(Model $model, array $fields): array
    {
        $data = [];

        foreach ($fields as $field) {
            $data[$field] = $model->getAttribute($field);
        }

        return $data;
    }

    /**
     * @param  array<string,mixed>  $values
     * @return array<string,mixed>
     */
    private function redact(array $values): array
    {
        foreach ($values as $key => $value) {
            $lower = strtolower((string) $key);

            foreach (self::SENSITIVE_FRAGMENTS as $fragment) {
                if (str_contains($lower, $fragment)) {
                    $values[$key] = self::REDACTED;
                    break;
                }
            }
        }

        return $values;
    }

    private function requestIp(): ?string
    {
        if (app()->runningInConsole()) {
            return null;
        }

        return request()?->ip();
    }

    private function requestUserAgent(): ?string
    {
        if (app()->runningInConsole()) {
            return null;
        }

        $agent = request()?->userAgent();

        return $agent !== null ? substr($agent, 0, 1000) : null;
    }
}
