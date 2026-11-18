<?php

namespace App\Controllers\TamoAi;

use App\Models\TamoAiMaterialModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class MaterialController extends TamoAiBaseController
{
    private array $statuses = [
        'pendente' => 'Pendente',
        'orcando' => 'Orçando',
        'aprovado' => 'Aprovado',
        'comprado' => 'Comprado',
        'cancelado' => 'Cancelado',
    ];

    private array $urgencies = [
        'normal' => 'Normal',
        'urgente' => 'Urgente',
        'baixa' => 'Baixa',
    ];

    private array $categories = [
        'instrumento' => 'Instrumento',
        'cabo' => 'Cabo / conexão',
        'audio' => 'Áudio / som',
        'manutencao' => 'Manutenção',
        'figurino' => 'Figurino',
        'divulgacao' => 'Divulgação',
        'outro' => 'Outro',
    ];

    public function index(): string
    {
        $filters = $this->filters();
        $materials = [];
        $summary = [
            'pending' => 0,
            'urgent' => 0,
            'approved' => 0,
            'estimated_total' => 0,
        ];

        if ($this->tableReady('tamo_ai_materials')) {
            $query = new TamoAiMaterialModel();

            if ($filters['status'] !== '') {
                $query->where('status', $filters['status']);
            }
            if ($filters['urgency'] !== '') {
                $query->where('urgency', $filters['urgency']);
            }
            if ($filters['search'] !== '') {
                $query->groupStart()
                    ->like('name', $filters['search'])
                    ->orLike('category', $filters['search'])
                    ->orLike('notes', $filters['search'])
                    ->groupEnd();
            }

            $materials = $query
                ->orderBy("FIELD(urgency, 'urgente', 'normal', 'baixa')", '', false)
                ->orderBy("FIELD(status, 'pendente', 'orcando', 'aprovado', 'comprado', 'cancelado')", '', false)
                ->orderBy('created_at', 'DESC')
                ->findAll();

            $all = (new TamoAiMaterialModel())->findAll();
            foreach ($all as $item) {
                if (($item['status'] ?? '') !== 'comprado' && ($item['status'] ?? '') !== 'cancelado') {
                    $summary['pending']++;
                    $summary['estimated_total'] += (float) ($item['estimated_price'] ?? 0);
                }
                if (($item['urgency'] ?? '') === 'urgente' && ($item['status'] ?? '') !== 'comprado' && ($item['status'] ?? '') !== 'cancelado') {
                    $summary['urgent']++;
                }
                if (($item['status'] ?? '') === 'aprovado') {
                    $summary['approved']++;
                }
            }
        }

        return $this->renderApp('tamoai/materials/index', [
            'title' => 'Materiais | Grupo Tamo Aí',
            'tableReady' => $this->tableReady('tamo_ai_materials'),
            'materials' => $materials,
            'summary' => $summary,
            'statuses' => $this->statuses,
            'urgencies' => $this->urgencies,
            'categories' => $this->categories,
            'filters' => $filters,
            'canManageFinance' => $this->canManageFinance(),
        ]);
    }

    public function create(): string
    {
        $this->ensureFinance();

        return $this->form('Novo material', [], '/tamo-ai/materiais/salvar');
    }

    public function store()
    {
        $this->ensureFinance();

        $payload = $this->payload();
        if ($payload['name'] === '') {
            return redirect()->back()->withInput()->with('error', 'Informe o nome do material.');
        }

        $payload['created_by'] = session('tamo_ai_user_id');
        (new TamoAiMaterialModel())->insert($payload);
        $this->logAction('material_criado', 'materials', null, 'Material cadastrado: ' . $payload['name'], null, $payload);

        return redirect()->to('/tamo-ai/materiais')->with('success', 'Material cadastrado.');
    }

    public function edit(int $id): string
    {
        $this->ensureFinance();
        $material = (new TamoAiMaterialModel())->find($id);
        if (! $material) {
            throw PageNotFoundException::forPageNotFound('Material não encontrado.');
        }

        return $this->form('Editar material', $material, '/tamo-ai/materiais/atualizar/' . $id);
    }

    public function update(int $id)
    {
        $this->ensureFinance();

        $model = new TamoAiMaterialModel();
        $old = $model->find($id);
        if (! $old) {
            throw PageNotFoundException::forPageNotFound('Material não encontrado.');
        }

        $payload = $this->payload();
        $payload['updated_by'] = session('tamo_ai_user_id');
        $model->update($id, $payload);
        $this->logAction('material_atualizado', 'materials', $id, 'Material atualizado.', $old, $payload);

        return redirect()->to('/tamo-ai/materiais')->with('success', 'Material atualizado.');
    }

    public function delete(int $id)
    {
        $this->ensureFinance();

        $model = new TamoAiMaterialModel();
        $old = $model->find($id);
        if ($old) {
            $model->delete($id);
            $this->logAction('material_removido', 'materials', $id, 'Material removido.', $old, null);
        }

        return redirect()->to('/tamo-ai/materiais')->with('success', 'Material removido.');
    }

    private function form(string $title, array $material, string $action): string
    {
        return $this->renderApp('tamoai/materials/form', [
            'title' => $title,
            'material' => $material,
            'action' => $action,
            'statuses' => $this->statuses,
            'urgencies' => $this->urgencies,
            'categories' => $this->categories,
        ]);
    }

    private function payload(): array
    {
        $status = (string) $this->request->getPost('status');
        if (! array_key_exists($status, $this->statuses)) {
            $status = 'pendente';
        }

        $urgency = (string) $this->request->getPost('urgency');
        if (! array_key_exists($urgency, $this->urgencies)) {
            $urgency = 'normal';
        }

        $category = (string) $this->request->getPost('category');
        if (! array_key_exists($category, $this->categories)) {
            $category = 'outro';
        }

        return [
            'name' => trim((string) $this->request->getPost('name')),
            'category' => $category,
            'estimated_price' => $this->moneyToFloat((string) $this->request->getPost('estimated_price')),
            'product_url' => trim((string) $this->request->getPost('product_url')),
            'status' => $status,
            'urgency' => $urgency,
            'priority' => max(0, (int) $this->request->getPost('priority')),
            'needed_until' => $this->parseDate((string) $this->request->getPost('needed_until')),
            'notes' => trim((string) $this->request->getPost('notes')),
        ];
    }

    private function filters(): array
    {
        $status = trim((string) $this->request->getGet('status'));
        $urgency = trim((string) $this->request->getGet('urgencia'));

        return [
            'status' => array_key_exists($status, $this->statuses) ? $status : '',
            'urgency' => array_key_exists($urgency, $this->urgencies) ? $urgency : '',
            'search' => trim((string) $this->request->getGet('busca')),
        ];
    }
}
