<?php

namespace App\Controllers\TamoAi;

use App\Models\TamoAiCashBoxMovementModel;
use App\Models\TamoAiEventFreelancerModel;
use App\Models\TamoAiEventModel;
use App\Models\TamoAiReceiptModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class CashBoxController extends TamoAiBaseController
{
    private array $categories = [
        'cache' => 'Cachê',
        'reserva_grupo' => 'Reserva do grupo',
        'doacao' => 'Doação',
        'reembolso' => 'Reembolso',
        'adiantamento_integrantes' => 'Adiantamento p/ integrantes',
        'pagamento_integrante' => 'Pagamento de integrante',
        'pagamento_freelancer' => 'Pagamento de freelancer',
        'transporte' => 'Transporte',
        'equipamento' => 'Equipamento',
        'ensaio' => 'Ensaio',
        'marketing' => 'Marketing',
        'figurino' => 'Figurino',
        'alimentacao' => 'Alimentação',
        'manutencao' => 'Manutenção',
        'outro' => 'Outro',
    ];

    private array $paymentMethods = ['Pix', 'Dinheiro', 'Transferência', 'Cartão', 'Outro'];
    private array $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
    private array $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];

    public function index(): string
    {
        $filters = $this->filters();
        $summary = $this->summary($filters);
        $movements = [];

        if ($summary['table_ready']) {
            $movements = $this->movementQuery($filters)->findAll(100);
        }

        $receiptsById = $this->receiptsById($movements);
        $eventsById = $this->eventsById($movements);

        return $this->renderApp('tamoai/cashbox/index', [
            'title' => 'Caixa | Grupo Tamo Aí',
            'summary' => $summary,
            'movements' => $movements,
            'receiptsById' => $receiptsById,
            'eventsById' => $eventsById,
            'events' => (new TamoAiEventModel())->orderBy('event_date', 'DESC')->findAll(120),
            'categories' => $this->categories,
            'paymentMethods' => $this->paymentMethods,
            'filters' => $filters,
            'canManageFinance' => $this->canManageFinance(),
        ]);
    }

    public function store()
    {
        $this->ensureFinance();

        if (! $this->cashBoxTableReady()) {
            return redirect()->to('/tamo-ai/caixa')->with('error', 'Importe a tabela do caixa antes de lançar movimentos.');
        }

        $type = (string) $this->request->getPost('type');
        if (! in_array($type, ['entrada', 'saida'], true)) {
            $type = 'entrada';
        }

        $category = (string) $this->request->getPost('category');
        if (! array_key_exists($category, $this->categories)) {
            $category = 'outro';
        }

        $paymentMethod = (string) $this->request->getPost('payment_method');
        if (! in_array($paymentMethod, $this->paymentMethods, true)) {
            $paymentMethod = 'Outro';
        }

        $amount = $this->moneyToFloat((string) $this->request->getPost('amount'));
        if ($amount <= 0) {
            return redirect()->back()->with('error', 'Informe um valor maior que zero.');
        }

        $uploadedFile = $this->request->getFile('receipt_file');
        if ($uploadedFile && $uploadedFile->isValid() && ! $this->tableReady('tamo_ai_receipts')) {
            return redirect()->back()->withInput()->with('error', 'Importe a tabela de comprovantes antes de anexar documentos no caixa.');
        }

        $fileData = $this->storeReceiptFile(false);
        if (! $fileData['ok']) {
            return redirect()->back()->withInput()->with('error', $fileData['error']);
        }

        $description = trim((string) $this->request->getPost('description'));
        if ($description === '') {
            $description = $type === 'entrada' ? 'Entrada manual' : 'Saída manual';
        }

        $payload = [
            'event_id' => (int) $this->request->getPost('event_id') ?: null,
            'receipt_id' => (int) $this->request->getPost('receipt_id') ?: null,
            'type' => $type,
            'category' => $category,
            'description' => $description,
            'amount' => $amount,
            'movement_date' => $this->parseDate((string) $this->request->getPost('movement_date'), date('Y-m-d')),
            'payment_method' => $paymentMethod,
            'responsible_user_id' => session('tamo_ai_user_id'),
            'notes' => trim((string) $this->request->getPost('notes')),
            'created_by' => session('tamo_ai_user_id'),
        ];

        $model = new TamoAiCashBoxMovementModel();
        $model->insert($payload);
        $movementId = (int) $model->getInsertID();

        if ($fileData['data'] !== []) {
            if (! $this->tableReady('tamo_ai_receipts')) {
                return redirect()->to('/tamo-ai/caixa')->with('error', 'Movimento criado, mas a tabela de comprovantes ainda não existe para anexar o arquivo.');
            }

            $receiptPayload = [
                'event_id' => $payload['event_id'],
                'payment_id' => null,
                'cash_entry_id' => $movementId,
                'title' => 'Comprovante do caixa - ' . $description,
                'type' => $type === 'entrada' ? 'cache_recebido' : 'despesa_grupo',
                'category' => $type === 'entrada' ? 'entrada' : 'despesa',
                'amount' => $amount,
                'payment_date' => $payload['movement_date'],
                'payment_method' => $paymentMethod,
                'paid_by' => $type === 'saida' ? 'Grupo Tamo Aí' : '',
                'received_by' => $type === 'entrada' ? 'Grupo Tamo Aí' : '',
                'member_id' => null,
                'freelancer_id' => null,
                'notes' => $payload['notes'],
                'uploaded_by' => session('tamo_ai_user_id'),
            ] + $fileData['data'];

            $receiptModel = new TamoAiReceiptModel();
            $receiptModel->insert($receiptPayload);
            $receiptId = (int) $receiptModel->getInsertID();
            $model->update($movementId, ['receipt_id' => $receiptId]);
            $payload['receipt_id'] = $receiptId;
        }

        $this->logAction('caixa_movimento_criado', 'cash_box', $movementId, 'Movimento criado no caixa.', null, $payload);

        return redirect()->to('/tamo-ai/caixa')->with('success', 'Movimento lançado no caixa.');
    }

    public function edit(int $id): string
    {
        $this->ensureFinance();

        if (! $this->cashBoxTableReady()) {
            throw PageNotFoundException::forPageNotFound('Movimento não encontrado.');
        }

        $movement = (new TamoAiCashBoxMovementModel())->find($id);
        if (! $movement) {
            throw PageNotFoundException::forPageNotFound('Movimento não encontrado.');
        }

        $receipt = null;
        if (! empty($movement['receipt_id']) && $this->tableReady('tamo_ai_receipts')) {
            $receipt = (new TamoAiReceiptModel())->find((int) $movement['receipt_id']);
        }

        return $this->renderApp('tamoai/cashbox/form', [
            'title' => 'Editar movimento | Grupo Tamo Aí',
            'movement' => $movement,
            'receipt' => $receipt,
            'events' => (new TamoAiEventModel())->orderBy('event_date', 'DESC')->findAll(160),
            'categories' => $this->categories,
            'paymentMethods' => $this->paymentMethods,
        ]);
    }

    public function update(int $id)
    {
        $this->ensureFinance();

        if (! $this->cashBoxTableReady()) {
            return redirect()->to('/tamo-ai/caixa')->with('error', 'Importe a tabela do caixa antes de editar movimentos.');
        }

        $model = new TamoAiCashBoxMovementModel();
        $old = $model->find($id);
        if (! $old) {
            throw PageNotFoundException::forPageNotFound('Movimento não encontrado.');
        }

        $type = (string) $this->request->getPost('type');
        if (! in_array($type, ['entrada', 'saida'], true)) {
            $type = 'entrada';
        }

        $category = (string) $this->request->getPost('category');
        if (! array_key_exists($category, $this->categories)) {
            $category = 'outro';
        }

        $paymentMethod = (string) $this->request->getPost('payment_method');
        if (! in_array($paymentMethod, $this->paymentMethods, true)) {
            $paymentMethod = 'Outro';
        }

        $amount = $this->moneyToFloat((string) $this->request->getPost('amount'));
        if ($amount <= 0) {
            return redirect()->back()->withInput()->with('error', 'Informe um valor maior que zero.');
        }

        $description = trim((string) $this->request->getPost('description'));
        if ($description === '') {
            $description = $type === 'entrada' ? 'Entrada manual' : 'Saída manual';
        }

        $payload = [
            'event_id' => (int) $this->request->getPost('event_id') ?: null,
            'type' => $type,
            'category' => $category,
            'description' => $description,
            'amount' => $amount,
            'movement_date' => $this->parseDate((string) $this->request->getPost('movement_date'), date('Y-m-d')),
            'payment_method' => $paymentMethod,
            'notes' => trim((string) $this->request->getPost('notes')),
        ];

        $uploadedFile = $this->request->getFile('receipt_file');
        if ($uploadedFile && $uploadedFile->isValid() && ! $this->tableReady('tamo_ai_receipts')) {
            return redirect()->back()->withInput()->with('error', 'Importe a tabela de comprovantes antes de anexar documentos no caixa.');
        }

        $fileData = $this->storeReceiptFile(false);
        if (! $fileData['ok']) {
            return redirect()->back()->withInput()->with('error', $fileData['error']);
        }

        $receiptPayload = [
            'event_id' => $payload['event_id'],
            'payment_id' => null,
            'cash_entry_id' => $id,
            'title' => 'Comprovante do caixa - ' . $description,
            'type' => $type === 'entrada' ? 'cache_recebido' : 'despesa_grupo',
            'category' => $type === 'entrada' ? 'entrada' : 'despesa',
            'amount' => $amount,
            'payment_date' => $payload['movement_date'],
            'payment_method' => $paymentMethod,
            'paid_by' => $type === 'saida' ? 'Grupo Tamo Aí' : '',
            'received_by' => $type === 'entrada' ? 'Grupo Tamo Aí' : '',
            'member_id' => null,
            'freelancer_id' => null,
            'notes' => $payload['notes'],
            'uploaded_by' => session('tamo_ai_user_id'),
        ];

        if ($this->tableReady('tamo_ai_receipts')) {
            $receiptModel = new TamoAiReceiptModel();
            if ($fileData['data'] !== []) {
                $receiptModel->insert($receiptPayload + $fileData['data']);
                $payload['receipt_id'] = (int) $receiptModel->getInsertID();
            } elseif (! empty($old['receipt_id'])) {
                $receiptModel->update((int) $old['receipt_id'], $receiptPayload);
                $payload['receipt_id'] = (int) $old['receipt_id'];
            }
        }

        $model->update($id, $payload);
        $this->logAction('caixa_movimento_atualizado', 'cash_box', $id, 'Movimento atualizado no caixa.', $old, $payload);

        return redirect()->to('/tamo-ai/caixa')->with('success', 'Movimento atualizado.');
    }

    public function delete(int $id)
    {
        $this->ensureFinance();

        if ($this->cashBoxTableReady()) {
            $model = new TamoAiCashBoxMovementModel();
            $movement = $model->find($id);
            $model->delete($id);
            $this->logAction('caixa_movimento_removido', 'cash_box', $id, 'Movimento removido do caixa.', $movement ?: null, null);
        }

        return redirect()->to('/tamo-ai/caixa')->with('success', 'Movimento removido.');
    }

    public function exportCsv()
    {
        $this->ensureFinance();
        $filters = $this->filters();
        $movements = $this->movementQuery($filters)->findAll();

        $rows = [['Data', 'Tipo', 'Categoria', 'Descrição', 'Valor', 'Forma', 'Evento', 'Observação']];
        $events = [];
        if ($movements !== []) {
            foreach ((new TamoAiEventModel())->whereIn('id', array_filter(array_column($movements, 'event_id')))->findAll() as $event) {
                $events[$event['id']] = $event['client'] . ' - ' . date('d/m/Y', strtotime($event['event_date']));
            }
        }

        foreach ($movements as $movement) {
            $rows[] = [
                date('d/m/Y', strtotime($movement['movement_date'])),
                $movement['type'],
                $this->categories[$movement['category'] ?? 'outro'] ?? ($movement['category'] ?? ''),
                $movement['description'],
                number_format((float) $movement['amount'], 2, ',', '.'),
                $movement['payment_method'] ?? '',
                $events[$movement['event_id'] ?? 0] ?? '',
                $movement['notes'] ?? '',
            ];
        }

        return $this->csvResponse('caixa-tamo-ai-' . date('Ymd-His') . '.csv', $rows);
    }

    private function summary(array $filters): array
    {
        $eventCash = $this->cashFromEvents();
        $manualIn = 0.0;
        $manualOut = 0.0;
        $groupManualIn = 0.0;
        $groupManualOut = 0.0;
        $advanceIn = 0.0;
        $advanceOut = 0.0;
        $monthIn = 0.0;
        $monthOut = 0.0;
        $tableReady = $this->cashBoxTableReady();
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        if ($tableReady) {
            $movements = (new TamoAiCashBoxMovementModel())->findAll();
            foreach ($movements as $movement) {
                $isAdvance = ($movement['category'] ?? '') === 'adiantamento_integrantes';

                if ($movement['type'] === 'saida') {
                    $manualOut += (float) $movement['amount'];
                    if ($isAdvance) {
                        $advanceOut += (float) $movement['amount'];
                    } else {
                        $groupManualOut += (float) $movement['amount'];
                    }
                    if ($movement['movement_date'] >= $monthStart && $movement['movement_date'] <= $monthEnd) {
                        $monthOut += (float) $movement['amount'];
                    }
                    continue;
                }

                $manualIn += (float) $movement['amount'];
                if ($isAdvance) {
                    $advanceIn += (float) $movement['amount'];
                } else {
                    $groupManualIn += (float) $movement['amount'];
                }
                if ($movement['movement_date'] >= $monthStart && $movement['movement_date'] <= $monthEnd) {
                    $monthIn += (float) $movement['amount'];
                }
            }
        }

        return [
            'table_ready' => $tableReady,
            'event_cash' => $eventCash,
            'advance_balance' => $advanceIn - $advanceOut,
            'manual_in' => $manualIn,
            'manual_out' => $manualOut,
            'month_in' => $monthIn,
            'month_out' => $monthOut,
            'month_balance' => $monthIn - $monthOut,
            'total' => $eventCash + $groupManualIn - $groupManualOut,
        ];
    }

    private function movementQuery(array $filters): TamoAiCashBoxMovementModel
    {
        $model = new TamoAiCashBoxMovementModel();

        if ($filters['month'] !== '') {
            $model->where('movement_date >=', $filters['year'] . '-' . $filters['month'] . '-01')
                ->where('movement_date <=', date('Y-m-t', strtotime($filters['year'] . '-' . $filters['month'] . '-01')));
        }
        if ($filters['type'] !== '') {
            $model->where('type', $filters['type']);
        }
        if ($filters['category'] !== '') {
            $model->where('category', $filters['category']);
        }
        if ($filters['event_id'] > 0) {
            $model->where('event_id', $filters['event_id']);
        }
        if ($filters['payment_method'] !== '') {
            $model->where('payment_method', $filters['payment_method']);
        }

        return $model->orderBy('movement_date', 'DESC')->orderBy('created_at', 'DESC');
    }

    private function cashFromEvents(): float
    {
        $events = (new TamoAiEventModel())
            ->where('status', 'ultimo-do-mes')
            ->findAll();

        if ($events === []) {
            return 0.0;
        }

        $ids = array_column($events, 'id');
        $freelancersByEvent = [];
        $freelancers = (new TamoAiEventFreelancerModel())->whereIn('event_id', $ids)->findAll();

        foreach ($freelancers as $freelancer) {
            $freelancersByEvent[$freelancer['event_id']][] = $freelancer;
        }

        $total = 0.0;
        foreach ($events as $event) {
            $freelancerTotal = array_sum(array_map(
                static fn ($freelancer) => (float) $freelancer['fee'],
                $freelancersByEvent[$event['id']] ?? []
            ));
            $total += max((float) $event['total_fee'] - $freelancerTotal, 0);
        }

        return $total;
    }

    private function cashBoxTableReady(): bool
    {
        return db_connect()->tableExists('tamo_ai_cash_box_movements');
    }

    private function receiptsById(array $movements): array
    {
        $receiptIds = array_values(array_filter(array_column($movements, 'receipt_id')));
        if ($receiptIds === [] || ! $this->tableReady('tamo_ai_receipts')) {
            return [];
        }

        $receipts = [];
        foreach ((new TamoAiReceiptModel())->whereIn('id', $receiptIds)->findAll() as $receipt) {
            $receipts[$receipt['id']] = $receipt;
        }

        return $receipts;
    }

    private function eventsById(array $movements): array
    {
        $eventIds = array_values(array_filter(array_column($movements, 'event_id')));
        if ($eventIds === []) {
            return [];
        }

        $events = [];
        foreach ((new TamoAiEventModel())->whereIn('id', $eventIds)->findAll() as $event) {
            $events[$event['id']] = $event;
        }

        return $events;
    }

    private function storeReceiptFile(bool $required): array
    {
        $file = $this->request->getFile('receipt_file');
        if (! $file || ! $file->isValid()) {
            return $required ? ['ok' => false, 'error' => 'Anexe um arquivo de comprovante.'] : ['ok' => true, 'data' => []];
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            return ['ok' => false, 'error' => 'O arquivo precisa ter até 10MB.'];
        }

        $extension = strtolower($file->getClientExtension());
        $mime = (string) $file->getMimeType();
        if (! in_array($extension, $this->allowedExtensions, true) || ! in_array($mime, $this->allowedMimes, true)) {
            return ['ok' => false, 'error' => 'Tipo de arquivo não permitido. Use PDF, JPG, PNG ou WEBP.'];
        }

        $folder = 'tamo-ai/comprovantes/' . date('Y') . '/' . date('m');
        $target = WRITEPATH . 'uploads/' . $folder;
        if (! is_dir($target)) {
            mkdir($target, 0775, true);
        }

        $name = bin2hex(random_bytes(16)) . '.' . $extension;
        $file->move($target, $name);

        return [
            'ok' => true,
            'data' => [
                'file_original_name' => $file->getClientName(),
                'file_name' => $name,
                'file_path' => $folder . '/' . $name,
                'file_mime' => $mime,
                'file_size' => $file->getSize(),
            ],
        ];
    }

    private function filters(): array
    {
        $year = (string) ($this->request->getGet('ano') ?: date('Y'));
        $month = (string) ($this->request->getGet('mes') ?: '');
        if (preg_match('/^(\d{4})-(\d{2})$/', $month, $matches)) {
            $year = $matches[1];
            $month = $matches[2];
        }

        return [
            'month' => preg_match('/^\d{2}$/', $month) ? $month : '',
            'year' => preg_match('/^\d{4}$/', $year) ? $year : date('Y'),
            'type' => trim((string) $this->request->getGet('tipo')),
            'category' => trim((string) $this->request->getGet('categoria')),
            'event_id' => (int) $this->request->getGet('evento'),
            'payment_method' => trim((string) $this->request->getGet('forma')),
        ];
    }

    private function csvResponse(string $filename, array $rows)
    {
        $handle = fopen('php://temp', 'rb+');
        foreach ($rows as $row) {
            fputcsv($handle, $row, ';');
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody("\xEF\xBB\xBF" . $csv);
    }
}
