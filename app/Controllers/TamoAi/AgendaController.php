<?php

namespace App\Controllers\TamoAi;

use App\Controllers\BaseController;
use App\Models\TamoAiCashBoxMovementModel;
use App\Models\TamoAiEventFreelancerModel;
use App\Models\TamoAiEventModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class AgendaController extends BaseController
{
    private array $statuses = [
        'finalizado' => 'Finalizado',
        'a-confirmar' => 'A confirmar',
        'confirmado' => 'Confirmado',
        'cancelado' => 'Cancelado',
        'ultimo-do-mes' => 'Último do mês',
    ];

    private array $paymentStatuses = [
        'sim' => 'Sim',
        'nao' => 'Não',
        'atrasado' => 'Atrasado',
        'aguardando' => 'Aguardando pagamento',
    ];

    private array $dayColors = [
        'Branco',
        'Bege/Marrom',
        'Preto',
        'Verde',
        'Azul',
        'Vermelho',
        'Amarelo',
        'Laranja',
        'Roxo',
        'Rosa',
        'Cinza',
        'Marrom',
    ];

    private array $sketchModels = ['Palco', 'Roda de samba'];
    private array $soundOptions = ['Som da casa', 'Cardoso', 'Rick Valentim', 'Adriano DJ Som', 'Cleyson', 'Sandro'];
    private array $guitarPlayers = ['Leandro', 'Rafael', 'Jay', 'Rick', 'Cardoso', 'Sem violão'];
    private array $drummers = ['Wesley', 'Cesar', 'Sem bateria'];
    private array $freelancerInstruments = ['Violão', 'Bateria', 'Som', 'Percussão', 'Voz', 'Outro'];
    private array $setOptions = [1, 2, 3, 4, 5];
    private array $intervalOptions = [5, 10, 15, 20, 25, 30, 35, 40, 45];
    private array $freelancerNames = [
        'Leandro',
        'Rafael',
        'Jay',
        'Rick',
        'Cardoso',
        'Wesley',
        'Cesar',
        'Rick Valentim',
        'Adriano DJ Som',
        'Cleyson',
        'Sandro',
    ];

    public function index(): string
    {
        $model = new TamoAiEventModel();
        $statusParam = $this->request->getGet('status');
        $status = $statusParam === null ? 'confirmado' : trim((string) $statusParam);
        $month = trim((string) $this->request->getGet('mes'));
        $search = trim((string) $this->request->getGet('busca'));

        if ($status !== '' && array_key_exists($status, $this->statuses)) {
            $model->where('status', $status);
        }

        if (preg_match('/^\d{4}-\d{2}$/', $month)) {
            $model->where('event_date >=', $month . '-01')
                ->where('event_date <=', date('Y-m-t', strtotime($month . '-01')));
        }

        if ($search !== '') {
            $model->groupStart()
                ->like('client', $search)
                ->orLike('address', $search)
                ->orLike('neighborhood', $search)
                ->orLike('notes', $search)
                ->orLike('viola_player', $search)
                ->orLike('drummer', $search)
                ->groupEnd();
        }

        $events = $model->orderBy('event_date', 'ASC')
            ->orderBy('start_time', 'ASC')
            ->findAll(80);

        return $this->renderApp('tamoai/agenda/index', [
            'title' => 'Agenda | Grupo Tamo Aí',
            'events' => $this->enrichEvents($events),
            'status' => $status,
            'month' => $month,
            'search' => $search,
            'stats' => $this->stats(),
            'statuses' => $this->statuses,
            'paymentStatuses' => $this->paymentStatuses,
        ]);
    }

    public function create(): string
    {
        $this->ensureAdmin();

        return $this->form('Novo evento', [], '/tamo-ai/eventos/salvar');
    }

    public function store()
    {
        $this->ensureAdmin();

        $model = new TamoAiEventModel();
        $model->insert($this->payload());
        $this->saveFreelancers((int) $model->getInsertID());

        return redirect()->to('/tamo-ai')->with('success', 'Evento adicionado.');
    }

    public function edit(int $id): string
    {
        $this->ensureAdmin();

        $event = (new TamoAiEventModel())->find($id);
        if (! $event) {
            throw PageNotFoundException::forPageNotFound('Evento não encontrado.');
        }

        $event['freelancers'] = (new TamoAiEventFreelancerModel())
            ->where('event_id', $id)
            ->orderBy('id', 'ASC')
            ->findAll();

        return $this->form('Editar evento', $event, '/tamo-ai/eventos/atualizar/' . $id);
    }

    public function update(int $id)
    {
        $this->ensureAdmin();

        (new TamoAiEventModel())->update($id, $this->payload());
        $this->saveFreelancers($id);

        return redirect()->to('/tamo-ai')->with('success', 'Evento atualizado.');
    }

    public function status(int $id)
    {
        $this->ensureAdmin();

        $model = new TamoAiEventModel();
        $event = $model->find($id);
        if (! $event) {
            throw PageNotFoundException::forPageNotFound('Evento não encontrado.');
        }

        $model->update($id, [
            'status' => $this->normalizeStatus((string) $this->request->getPost('status')),
            'updated_by' => session('tamo_ai_user_id'),
        ]);

        return redirect()->back()->with('success', 'Status do evento atualizado.');
    }

    public function delete(int $id)
    {
        $this->ensureAdmin();

        (new TamoAiEventFreelancerModel())->where('event_id', $id)->delete();
        (new TamoAiEventModel())->delete($id);

        return redirect()->to('/tamo-ai')->with('success', 'Evento removido.');
    }

    private function form(string $title, array $event, string $action): string
    {
        return $this->renderApp('tamoai/agenda/form', [
            'title' => $title,
            'event' => $event,
            'action' => $action,
            'statuses' => $this->statuses,
            'paymentStatuses' => $this->paymentStatuses,
            'dayColors' => $this->dayColors,
            'sketchModels' => $this->sketchModels,
            'soundOptions' => $this->soundOptions,
            'guitarPlayers' => $this->guitarPlayers,
            'drummers' => $this->drummers,
            'freelancerInstruments' => $this->freelancerInstruments,
            'setOptions' => $this->setOptions,
            'intervalOptions' => $this->intervalOptions,
            'freelancerNames' => $this->freelancerNames,
        ]);
    }

    private function payload(): array
    {
        $date = (string) $this->request->getPost('event_date');
        $paymentStatus = (string) $this->request->getPost('payment_status');
        if (! array_key_exists($paymentStatus, $this->paymentStatuses)) {
            $paymentStatus = 'nao';
        }

        return [
            'status' => $this->normalizeStatus((string) $this->request->getPost('status')),
            'event_date' => $date,
            'weekday' => $this->weekday($date),
            'client' => trim((string) $this->request->getPost('client')),
            'address' => trim((string) $this->request->getPost('address')),
            'neighborhood' => trim((string) $this->request->getPost('neighborhood')),
            'start_time' => (string) $this->request->getPost('start_time'),
            'set_count' => (int) $this->request->getPost('set_count'),
            'interval_minutes' => (int) $this->request->getPost('interval_minutes'),
            'total_fee' => $this->moneyToFloat((string) $this->request->getPost('total_fee')),
            'payment_done' => $paymentStatus === 'sim' ? 1 : 0,
            'payment_status' => $paymentStatus,
            'split_condition' => '',
            'expected_members' => max(0, (int) $this->request->getPost('expected_members')),
            'day_color' => trim((string) $this->request->getPost('day_color')),
            'sketch_model' => trim((string) $this->request->getPost('sketch_model')),
            'sound_contract' => trim((string) $this->request->getPost('sound_contract')),
            'viola_player' => trim((string) $this->request->getPost('viola_player')),
            'drummer' => trim((string) $this->request->getPost('drummer')),
            'notes' => trim((string) $this->request->getPost('notes')),
            'created_by' => session('tamo_ai_user_id'),
            'updated_by' => session('tamo_ai_user_id'),
        ];
    }

    private function saveFreelancers(int $eventId): void
    {
        $model = new TamoAiEventFreelancerModel();
        $model->where('event_id', $eventId)->delete();

        $names = (array) $this->request->getPost('freelancer_name');
        $instruments = (array) $this->request->getPost('freelancer_instrument');
        $fees = (array) $this->request->getPost('freelancer_fee');

        for ($index = 0; $index < 3; $index++) {
            $name = trim((string) ($names[$index] ?? ''));
            $instrument = trim((string) ($instruments[$index] ?? ''));
            $fee = $this->moneyToFloat((string) ($fees[$index] ?? ''));

            if ($name === '' && $instrument === '' && $fee <= 0) {
                continue;
            }

            $model->insert([
                'event_id' => $eventId,
                'name' => $name,
                'instrument' => $instrument,
                'fee' => $fee,
            ]);
        }
    }

    private function stats(): array
    {
        $model = new TamoAiEventModel();
        $month = date('Y-m');
        $monthEvents = $model->where('event_date >=', $month . '-01')
            ->where('event_date <=', date('Y-m-t'))
            ->findAll();
        $cashBox = $this->cashFromEvents() + $this->cashFromManualMovements();

        return [
            'month_count' => count($monthEvents),
            'confirmed' => count(array_filter($monthEvents, static fn ($event) => $event['status'] === 'confirmado')),
            'cash_box' => $cashBox,
            'total_fee' => array_sum(array_map(static fn ($event) => (float) $event['total_fee'], $monthEvents)),
        ];
    }

    private function cashFromEvents(): float
    {
        $cashEvents = (new TamoAiEventModel())
            ->where('status', 'ultimo-do-mes')
            ->findAll();

        if ($cashEvents === []) {
            return 0.0;
        }

        $cashIds = array_column($cashEvents, 'id');
        $freelancersByEvent = [];
        $freelancers = (new TamoAiEventFreelancerModel())->whereIn('event_id', $cashIds)->findAll();

        foreach ($freelancers as $freelancer) {
            $freelancersByEvent[$freelancer['event_id']][] = $freelancer;
        }

        $cashBox = 0.0;
        foreach ($cashEvents as $event) {
            $freelancerTotal = array_sum(array_map(
                static fn ($freelancer) => (float) $freelancer['fee'],
                $freelancersByEvent[$event['id']] ?? []
            ));
            $cashBox += max((float) $event['total_fee'] - $freelancerTotal, 0);
        }

        return $cashBox;
    }

    private function cashFromManualMovements(): float
    {
        if (! db_connect()->tableExists('tamo_ai_cash_box_movements')) {
            return 0.0;
        }

        $total = 0.0;
        $movements = (new TamoAiCashBoxMovementModel())->findAll();

        foreach ($movements as $movement) {
            $amount = (float) $movement['amount'];
            $total += $movement['type'] === 'saida' ? -$amount : $amount;
        }

        return $total;
    }

    private function weekday(string $date): string
    {
        if ($date === '') {
            return '';
        }

        $days = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];

        return $days[(int) date('w', strtotime($date))] ?? '';
    }

    private function enrichEvents(array $events): array
    {
        if ($events === []) {
            return [];
        }

        $ids = array_column($events, 'id');
        $freelancers = (new TamoAiEventFreelancerModel())->whereIn('event_id', $ids)->findAll();

        $freelancersByEvent = [];
        foreach ($freelancers as $freelancer) {
            $freelancersByEvent[$freelancer['event_id']][] = $freelancer;
        }

        foreach ($events as &$event) {
            $eventFreelancers = $freelancersByEvent[$event['id']] ?? [];
            $splitMembers = max((int) ($event['expected_members'] ?? 0), 0);
            $freelancerTotal = array_sum(array_map(static fn ($item) => (float) $item['fee'], $eventFreelancers));
            $netTotal = max((float) $event['total_fee'] - $freelancerTotal, 0);
            $isCashBoxEvent = $event['status'] === 'ultimo-do-mes';

            $event['freelancers'] = $eventFreelancers;
            $event['division'] = [
                'freelancers_total' => $freelancerTotal,
                'net_total' => $isCashBoxEvent ? 0 : $netTotal,
                'cash_box' => $isCashBoxEvent ? $netTotal : 0,
                'per_member' => (! $isCashBoxEvent && $splitMembers > 0) ? $netTotal / $splitMembers : 0,
                'split_members' => $splitMembers,
            ];
            $event['status_label'] = $this->statuses[$event['status']] ?? ucfirst((string) $event['status']);
            $event['payment_label'] = $this->paymentStatuses[$event['payment_status'] ?? 'nao'] ?? 'Não';
        }

        return $events;
    }

    private function moneyToFloat(string $value): float
    {
        return (float) str_replace(',', '.', preg_replace('/[^\d,.-]/', '', $value));
    }

    private function normalizeStatus(string $status): string
    {
        return array_key_exists($status, $this->statuses) ? $status : 'confirmado';
    }

    private function ensureAdmin(): void
    {
        if (session('tamo_ai_role') !== 'admin') {
            throw PageNotFoundException::forPageNotFound('Página não encontrada.');
        }
    }

    private function renderApp(string $view, array $data = []): string
    {
        $data['content'] = view($view, $data);

        return view('tamoai/layout', $data);
    }
}
