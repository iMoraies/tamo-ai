<?php

namespace App\Controllers\TamoAi;

use App\Models\TamoAiEventModel;
use App\Models\TamoAiFreelancerModel;
use App\Models\TamoAiReceiptModel;
use App\Models\TamoAiUserModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class ReceiptController extends TamoAiBaseController
{
    private array $types = [
        'cache_recebido' => 'Cachê recebido',
        'pagamento_integrante' => 'Pagamento de integrante',
        'pagamento_freelancer' => 'Pagamento de freelancer',
        'despesa_grupo' => 'Despesa do grupo',
        'transporte' => 'Transporte',
        'equipamento' => 'Equipamento',
        'ensaio' => 'Ensaio',
        'marketing' => 'Marketing',
        'alimentacao' => 'Alimentação',
        'outros' => 'Outros',
    ];

    private array $categories = [
        'entrada' => 'Entrada',
        'pagamento' => 'Pagamento',
        'despesa' => 'Despesa',
        'caixa' => 'Caixa',
        'outro' => 'Outro',
    ];

    private array $paymentMethods = ['Pix', 'Dinheiro', 'Transferência', 'Cartão', 'Outro'];
    private array $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'webp'];
    private array $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];

    public function index(): string
    {
        if (! $this->tableReady('tamo_ai_receipts')) {
            return $this->renderApp('tamoai/receipts/index', [
                'title' => 'Comprovantes | Grupo Tamo Aí',
                'tableReady' => false,
                'receipts' => [],
            ]);
        }

        $model = new TamoAiReceiptModel();
        $filters = $this->filters();

        if ($filters['event_id'] > 0) {
            $model->where('event_id', $filters['event_id']);
        }
        if ($filters['month'] !== '') {
            $model->where('payment_date >=', $filters['year'] . '-' . $filters['month'] . '-01')
                ->where('payment_date <=', date('Y-m-t', strtotime($filters['year'] . '-' . $filters['month'] . '-01')));
        }
        if ($filters['type'] !== '') {
            $model->where('type', $filters['type']);
        }
        if ($filters['category'] !== '') {
            $model->where('category', $filters['category']);
        }
        if ($filters['payment_method'] !== '') {
            $model->where('payment_method', $filters['payment_method']);
        }
        if ($filters['person'] !== '') {
            $model->groupStart()
                ->like('paid_by', $filters['person'])
                ->orLike('received_by', $filters['person'])
                ->groupEnd();
        }
        if ($filters['min'] > 0) {
            $model->where('amount >=', $filters['min']);
        }
        if ($filters['max'] > 0) {
            $model->where('amount <=', $filters['max']);
        }

        $receipts = $this->withEventNames($model->orderBy('payment_date', 'DESC')->orderBy('created_at', 'DESC')->findAll(120));

        return $this->renderApp('tamoai/receipts/index', [
            'title' => 'Comprovantes | Grupo Tamo Aí',
            'tableReady' => true,
            'receipts' => $receipts,
            'events' => $this->events(),
            'types' => $this->types,
            'categories' => $this->categories,
            'paymentMethods' => $this->paymentMethods,
            'filters' => $filters,
            'canManageFinance' => $this->canManageFinance(),
        ]);
    }

    public function create(?int $eventId = null): string
    {
        $this->ensureFinance();

        return $this->form('Novo comprovante', [], '/tamo-ai/comprovantes/salvar', $eventId ? (int) $eventId : null);
    }

    public function edit(int $id): string
    {
        $this->ensureFinance();
        $receipt = (new TamoAiReceiptModel())->find($id);
        if (! $receipt) {
            throw PageNotFoundException::forPageNotFound('Comprovante não encontrado.');
        }

        return $this->form('Editar comprovante', $receipt, '/tamo-ai/comprovantes/atualizar/' . $id, (int) $receipt['event_id']);
    }

    public function store()
    {
        $this->ensureFinance();
        $payload = $this->payload();
        $fileData = $this->storeFile();
        if (! $fileData['ok']) {
            return redirect()->back()->withInput()->with('error', $fileData['error']);
        }

        $payload += $fileData['data'];
        $payload['uploaded_by'] = session('tamo_ai_user_id');

        $model = new TamoAiReceiptModel();
        $model->insert($payload);
        $id = (int) $model->getInsertID();
        $this->logAction('comprovante_anexado', 'receipts', $id, 'Comprovante anexado.', null, $payload);

        $target = $payload['event_id'] ? '/tamo-ai/eventos/' . $payload['event_id'] : '/tamo-ai/comprovantes';

        return redirect()->to($target)->with('success', 'Comprovante anexado.');
    }

    public function update(int $id)
    {
        $this->ensureFinance();
        $model = new TamoAiReceiptModel();
        $receipt = $model->find($id);
        if (! $receipt) {
            throw PageNotFoundException::forPageNotFound('Comprovante não encontrado.');
        }

        $payload = $this->payload();
        $upload = $this->request->getFile('receipt_file');
        if ($upload && $upload->isValid()) {
            $fileData = $this->storeFile(false);
            if (! $fileData['ok']) {
                return redirect()->back()->withInput()->with('error', $fileData['error']);
            }
            $payload += $fileData['data'];
        }

        $model->update($id, $payload);
        $this->logAction('comprovante_editado', 'receipts', $id, 'Comprovante editado.', $receipt, $payload);

        $target = $payload['event_id'] ? '/tamo-ai/eventos/' . $payload['event_id'] : '/tamo-ai/comprovantes';

        return redirect()->to($target)->with('success', 'Comprovante atualizado.');
    }

    public function download(int $id)
    {
        $receipt = (new TamoAiReceiptModel())->find($id);
        if (! $receipt) {
            throw PageNotFoundException::forPageNotFound('Comprovante não encontrado.');
        }

        $path = WRITEPATH . 'uploads/' . ltrim((string) $receipt['file_path'], '/\\');
        if (! is_file($path)) {
            throw PageNotFoundException::forPageNotFound('Arquivo não encontrado.');
        }

        return $this->response->download($path, null)->setFileName((string) ($receipt['file_original_name'] ?: $receipt['file_name']));
    }

    public function delete(int $id)
    {
        $this->ensureFinance();

        $model = new TamoAiReceiptModel();
        $receipt = $model->find($id);
        if (! $receipt) {
            return redirect()->to('/tamo-ai/comprovantes')->with('error', 'Comprovante não encontrado.');
        }

        $model->delete($id);
        $this->logAction('comprovante_removido', 'receipts', $id, 'Comprovante removido.', $receipt, null);

        return redirect()->back()->with('success', 'Comprovante removido.');
    }

    private function form(string $title, array $receipt, string $action, ?int $eventId): string
    {
        return $this->renderApp('tamoai/receipts/form', [
            'title' => $title,
            'receipt' => $receipt,
            'action' => $action,
            'eventId' => $eventId,
            'events' => $this->events(),
            'members' => (new TamoAiUserModel())->where('active', 1)->orderBy('name', 'ASC')->findAll(),
            'freelancers' => $this->tableReady('tamo_ai_freelancers') ? (new TamoAiFreelancerModel())->where('status', 'ativo')->orderBy('name', 'ASC')->findAll() : [],
            'types' => $this->types,
            'categories' => $this->categories,
            'paymentMethods' => $this->paymentMethods,
        ]);
    }

    private function payload(): array
    {
        $type = (string) $this->request->getPost('type');
        $category = (string) $this->request->getPost('category');
        $paymentMethod = trim((string) $this->request->getPost('payment_method'));

        if (! array_key_exists($type, $this->types)) {
            $type = 'outros';
        }
        if (! array_key_exists($category, $this->categories)) {
            $category = 'outro';
        }
        if (! in_array($paymentMethod, $this->paymentMethods, true)) {
            $paymentMethod = 'Outro';
        }

        return [
            'event_id' => (int) $this->request->getPost('event_id') ?: null,
            'payment_id' => (int) $this->request->getPost('payment_id') ?: null,
            'cash_entry_id' => (int) $this->request->getPost('cash_entry_id') ?: null,
            'title' => trim((string) $this->request->getPost('title')),
            'type' => $type,
            'category' => $category,
            'amount' => $this->moneyToFloat((string) $this->request->getPost('amount')),
            'payment_date' => $this->parseDate((string) $this->request->getPost('payment_date'), date('Y-m-d')),
            'payment_method' => $paymentMethod,
            'paid_by' => trim((string) $this->request->getPost('paid_by')),
            'received_by' => trim((string) $this->request->getPost('received_by')),
            'member_id' => (int) $this->request->getPost('member_id') ?: null,
            'freelancer_id' => (int) $this->request->getPost('freelancer_id') ?: null,
            'notes' => trim((string) $this->request->getPost('notes')),
        ];
    }

    private function storeFile(bool $required = true): array
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

    private function events(): array
    {
        return (new TamoAiEventModel())->orderBy('event_date', 'DESC')->findAll(160);
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
            'event_id' => (int) $this->request->getGet('evento'),
            'month' => preg_match('/^\d{2}$/', $month) ? $month : '',
            'year' => preg_match('/^\d{4}$/', $year) ? $year : date('Y'),
            'type' => trim((string) $this->request->getGet('tipo')),
            'category' => trim((string) $this->request->getGet('categoria')),
            'person' => trim((string) $this->request->getGet('pessoa')),
            'payment_method' => trim((string) $this->request->getGet('forma')),
            'min' => $this->moneyToFloat((string) $this->request->getGet('min')),
            'max' => $this->moneyToFloat((string) $this->request->getGet('max')),
        ];
    }

    private function withEventNames(array $receipts): array
    {
        if ($receipts === []) {
            return [];
        }

        $events = [];
        $eventIds = array_values(array_filter(array_unique(array_column($receipts, 'event_id'))));
        if ($eventIds !== []) {
            foreach ((new TamoAiEventModel())->whereIn('id', $eventIds)->findAll() as $event) {
                $events[$event['id']] = $event;
            }
        }

        foreach ($receipts as &$receipt) {
            $receipt['event'] = $events[$receipt['event_id']] ?? null;
        }

        return $receipts;
    }
}
