<?php

namespace App\Controllers\TamoAi;

use App\Models\TamoAiFreelancerModel;

class FreelancerController extends TamoAiBaseController
{
    private array $roles = ['Voz', 'Cavaco', 'Violão', 'Banjo', 'Pandeiro', 'Tantã', 'Rebolo', 'Surdo', 'Percussão', 'Produção', 'Som', 'Outro'];

    public function index(): string
    {
        $this->ensureFinance();

        return $this->renderApp('tamoai/freelancers/index', [
            'title' => 'Freelancers | Grupo Tamo Aí',
            'freelancers' => $this->tableReady('tamo_ai_freelancers') ? (new TamoAiFreelancerModel())->orderBy('status', 'ASC')->orderBy('name', 'ASC')->findAll() : [],
            'roles' => $this->roles,
            'tableReady' => $this->tableReady('tamo_ai_freelancers'),
        ]);
    }

    public function store()
    {
        $this->ensureFinance();
        $payload = $this->payload();
        if ($payload['name'] === '') {
            return redirect()->back()->with('error', 'Informe o nome do freelancer.');
        }

        (new TamoAiFreelancerModel())->insert($payload);
        $this->logAction('freelancer_criado', 'freelancers', null, 'Freelancer cadastrado: ' . $payload['name'], null, $payload);

        return redirect()->to('/tamo-ai/freelancers')->with('success', 'Freelancer cadastrado.');
    }

    public function update(int $id)
    {
        $this->ensureFinance();
        $model = new TamoAiFreelancerModel();
        $old = $model->find($id);
        $payload = $this->payload();
        $model->update($id, $payload);
        $this->logAction('freelancer_atualizado', 'freelancers', $id, 'Freelancer atualizado.', $old ?: null, $payload);

        return redirect()->to('/tamo-ai/freelancers')->with('success', 'Freelancer atualizado.');
    }

    public function delete(int $id)
    {
        $this->ensureFinance();
        $model = new TamoAiFreelancerModel();
        $old = $model->find($id);
        $model->delete($id);
        $this->logAction('freelancer_removido', 'freelancers', $id, 'Freelancer removido.', $old ?: null, null);

        return redirect()->to('/tamo-ai/freelancers')->with('success', 'Freelancer removido.');
    }

    private function payload(): array
    {
        $status = (string) $this->request->getPost('status');
        if (! in_array($status, ['ativo', 'inativo'], true)) {
            $status = 'ativo';
        }

        return [
            'name' => trim((string) $this->request->getPost('name')),
            'role' => trim((string) $this->request->getPost('role')),
            'email' => trim((string) $this->request->getPost('email')),
            'phone' => trim((string) $this->request->getPost('phone')),
            'pix_key' => trim((string) $this->request->getPost('pix_key')),
            'default_amount' => $this->moneyToFloat((string) $this->request->getPost('default_amount')),
            'status' => $status,
            'notes' => trim((string) $this->request->getPost('notes')),
        ];
    }
}
