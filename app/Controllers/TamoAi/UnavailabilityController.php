<?php

namespace App\Controllers\TamoAi;

use App\Models\TamoAiUnavailabilityModel;
use App\Models\TamoAiUserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;

class UnavailabilityController extends TamoAiBaseController
{
    public function index(): string
    {
        $tableReady = $this->tableReady('tamo_ai_unavailabilities');
        $month = $this->selectedMonth();
        $monthStart = $month->format('Y-m-01');
        $monthEnd = $month->modify('last day of this month')->format('Y-m-d');
        $users = $this->activeUsers();
        $items = [];

        if ($tableReady) {
            $items = (new TamoAiUnavailabilityModel())
                ->select('tamo_ai_unavailabilities.*, tamo_ai_users.name AS user_name, tamo_ai_users.main_instrument')
                ->join('tamo_ai_users', 'tamo_ai_users.id = tamo_ai_unavailabilities.user_id', 'left')
                ->where('start_date <=', $monthEnd)
                ->where('end_date >=', $monthStart)
                ->orderBy('start_date', 'ASC')
                ->orderBy('tamo_ai_users.name', 'ASC')
                ->findAll();
        }

        return $this->renderApp('tamoai/unavailabilities/index', [
            'title' => 'Indisponibilidades | Grupo Tamo Aí',
            'tableReady' => $tableReady,
            'users' => $users,
            'items' => $items,
            'calendar' => $this->calendar($month, $items),
            'monthLabel' => $this->monthLabel($month),
            'currentMonth' => $month->format('Y-m'),
            'prevMonth' => $month->modify('-1 month')->format('Y-m'),
            'nextMonth' => $month->modify('+1 month')->format('Y-m'),
            'calendarDirection' => $this->calendarDirection(),
            'canManageAll' => $this->canManageFinance(),
            'currentUserId' => (int) session('tamo_ai_user_id'),
        ]);
    }

    public function store()
    {
        if (! $this->tableReady('tamo_ai_unavailabilities')) {
            return redirect()->back()->with('error', 'A tabela de indisponibilidades ainda não foi importada.');
        }

        $payload = $this->payload();
        if ($payload['user_id'] <= 0 || $payload['start_date'] === null || $payload['end_date'] === null) {
            return redirect()->back()->withInput()->with('error', 'Informe integrante, data inicial e data final.');
        }
        if ($payload['end_date'] < $payload['start_date']) {
            return redirect()->back()->withInput()->with('error', 'A data final não pode ser anterior à data inicial.');
        }

        $payload['created_by'] = session('tamo_ai_user_id');
        (new TamoAiUnavailabilityModel())->insert($payload);
        $this->logAction('indisponibilidade_criada', 'unavailabilities', null, 'Indisponibilidade cadastrada.', null, $payload);

        return redirect()->to('/tamo-ai/indisponibilidades?mes=' . substr($payload['start_date'], 0, 7))->with('success', 'Indisponibilidade cadastrada.');
    }

    public function edit(int $id): string
    {
        $item = $this->findUnavailability($id);
        $this->ensureCanChange($item);

        return $this->renderApp('tamoai/unavailabilities/form', [
            'title' => 'Editar indisponibilidade',
            'item' => $item,
            'users' => $this->activeUsers(),
            'action' => '/tamo-ai/indisponibilidades/atualizar/' . $id,
            'canManageAll' => $this->canManageFinance(),
        ]);
    }

    public function update(int $id)
    {
        $model = new TamoAiUnavailabilityModel();
        $old = $this->findUnavailability($id);
        $this->ensureCanChange($old);

        $payload = $this->payload((int) $old['user_id']);
        if ($payload['start_date'] === null || $payload['end_date'] === null) {
            return redirect()->back()->withInput()->with('error', 'Informe data inicial e data final.');
        }
        if ($payload['end_date'] < $payload['start_date']) {
            return redirect()->back()->withInput()->with('error', 'A data final não pode ser anterior à data inicial.');
        }

        $payload['updated_by'] = session('tamo_ai_user_id');
        $model->update($id, $payload);
        $this->logAction('indisponibilidade_atualizada', 'unavailabilities', $id, 'Indisponibilidade atualizada.', $old, $payload);

        return redirect()->to('/tamo-ai/indisponibilidades?mes=' . substr($payload['start_date'], 0, 7))->with('success', 'Indisponibilidade atualizada.');
    }

    public function delete(int $id)
    {
        $model = new TamoAiUnavailabilityModel();
        $item = $this->findUnavailability($id);
        $this->ensureCanChange($item);

        $model->delete($id);
        $this->logAction('indisponibilidade_removida', 'unavailabilities', $id, 'Indisponibilidade removida.', $item, null);

        return redirect()->back()->with('success', 'Indisponibilidade removida.');
    }

    private function payload(?int $fallbackUserId = null): array
    {
        $userId = $this->canManageFinance()
            ? (int) $this->request->getPost('user_id')
            : (int) ($fallbackUserId ?: session('tamo_ai_user_id'));

        return [
            'user_id' => $userId,
            'start_date' => $this->parseDate((string) $this->request->getPost('start_date')),
            'end_date' => $this->parseDate((string) $this->request->getPost('end_date')),
            'reason' => trim((string) $this->request->getPost('reason')),
            'notes' => trim((string) $this->request->getPost('notes')),
        ];
    }

    private function findUnavailability(int $id): array
    {
        $item = (new TamoAiUnavailabilityModel())->find($id);
        if (! $item) {
            throw PageNotFoundException::forPageNotFound('Indisponibilidade não encontrada.');
        }

        return $item;
    }

    private function ensureCanChange(array $item): void
    {
        if ($this->canManageFinance()) {
            return;
        }

        if ((int) ($item['user_id'] ?? 0) !== (int) session('tamo_ai_user_id')) {
            throw PageNotFoundException::forPageNotFound('Página não encontrada.');
        }
    }

    private function activeUsers(): array
    {
        return (new TamoAiUserModel())
            ->where('active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    private function selectedMonth(): DateTimeImmutable
    {
        $month = trim((string) $this->request->getGet('mes'));
        if (preg_match('/^\d{4}-\d{2}$/', $month)) {
            return new DateTimeImmutable($month . '-01');
        }

        return new DateTimeImmutable(date('Y-m-01'));
    }

    private function calendarDirection(): string
    {
        $direction = trim((string) $this->request->getGet('direcao'));

        return in_array($direction, ['anterior', 'proximo'], true) ? $direction : '';
    }

    private function calendar(DateTimeImmutable $month, array $items): array
    {
        $monthStart = $month->modify('first day of this month');
        $monthEnd = $month->modify('last day of this month');
        $gridStart = $monthStart->modify('-' . $monthStart->format('w') . ' days');
        $gridEnd = $monthEnd->modify('+' . (6 - (int) $monthEnd->format('w')) . ' days');
        $itemsByDate = [];

        foreach ($items as $item) {
            $start = new DateTimeImmutable((string) $item['start_date']);
            $end = new DateTimeImmutable((string) $item['end_date']);
            $rangeStart = $start < $monthStart ? $monthStart : $start;
            $rangeEnd = $end > $monthEnd ? $monthEnd : $end;

            foreach (new DatePeriod($rangeStart, new DateInterval('P1D'), $rangeEnd->modify('+1 day')) as $day) {
                $itemsByDate[$day->format('Y-m-d')][] = $item;
            }
        }

        $weeks = [];
        $week = [];
        foreach (new DatePeriod($gridStart, new DateInterval('P1D'), $gridEnd->modify('+1 day')) as $day) {
            $key = $day->format('Y-m-d');
            $week[] = [
                'date' => $key,
                'number' => $day->format('j'),
                'inMonth' => $day->format('m') === $month->format('m'),
                'today' => $key === date('Y-m-d'),
                'items' => $itemsByDate[$key] ?? [],
            ];

            if (count($week) === 7) {
                $weeks[] = $week;
                $week = [];
            }
        }

        return $weeks;
    }

    private function monthLabel(DateTimeImmutable $month): string
    {
        $months = [
            '01' => 'Janeiro',
            '02' => 'Fevereiro',
            '03' => 'Março',
            '04' => 'Abril',
            '05' => 'Maio',
            '06' => 'Junho',
            '07' => 'Julho',
            '08' => 'Agosto',
            '09' => 'Setembro',
            '10' => 'Outubro',
            '11' => 'Novembro',
            '12' => 'Dezembro',
        ];

        return ($months[$month->format('m')] ?? $month->format('m')) . ' de ' . $month->format('Y');
    }
}
