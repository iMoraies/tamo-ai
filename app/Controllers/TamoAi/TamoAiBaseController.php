<?php

namespace App\Controllers\TamoAi;

use App\Controllers\BaseController;
use App\Models\TamoAiActivityLogModel;
use App\Models\TamoAiEventModel;
use CodeIgniter\Exceptions\PageNotFoundException;

abstract class TamoAiBaseController extends BaseController
{
    protected function renderApp(string $view, array $data = []): string
    {
        $data['content'] = view($view, $data);

        return view('tamoai/layout', $data);
    }

    protected function role(): string
    {
        return (string) session('tamo_ai_role');
    }

    protected function canManageFinance(): bool
    {
        return in_array($this->role(), ['admin', 'financeiro'], true);
    }

    protected function ensureFinance(): void
    {
        if (! $this->canManageFinance()) {
            throw PageNotFoundException::forPageNotFound('Página não encontrada.');
        }
    }

    protected function ensureAdmin(): void
    {
        if ($this->role() !== 'admin') {
            throw PageNotFoundException::forPageNotFound('Página não encontrada.');
        }
    }

    protected function findEvent(int $eventId): array
    {
        $event = (new TamoAiEventModel())->find($eventId);
        if (! $event) {
            throw PageNotFoundException::forPageNotFound('Evento não encontrado.');
        }

        return $event;
    }

    protected function tableReady(string $table): bool
    {
        try {
            return db_connect()->tableExists($table);
        } catch (\Throwable) {
            return false;
        }
    }

    protected function moneyToFloat(string $value): float
    {
        $value = trim($value);
        if ($value === '') {
            return 0.0;
        }

        $value = preg_replace('/[^\d,.-]/', '', $value) ?? '';
        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
        }

        return (float) str_replace(',', '.', $value);
    }

    protected function parseDate(?string $value, ?string $fallback = null): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return $fallback;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $matches)) {
            return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }

        $time = strtotime($value);

        return $time ? date('Y-m-d', $time) : $fallback;
    }

    protected function logAction(string $action, string $module, ?int $relatedId, string $description, ?array $oldData = null, ?array $newData = null): void
    {
        if (! $this->tableReady('tamo_ai_activity_logs')) {
            return;
        }

        try {
            (new TamoAiActivityLogModel())->insert([
                'user_id' => session('tamo_ai_user_id'),
                'action' => $action,
                'module' => $module,
                'related_id' => $relatedId,
                'description' => $description,
                'old_data' => $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
                'new_data' => $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => substr((string) $this->request->getUserAgent(), 0, 500),
            ]);
        } catch (\Throwable) {
            // Logging must never block the finance workflow.
        }
    }
}
