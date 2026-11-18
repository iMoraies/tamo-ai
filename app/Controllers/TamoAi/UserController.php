<?php

namespace App\Controllers\TamoAi;

use App\Controllers\BaseController;
use App\Models\TamoAiUserModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class UserController extends BaseController
{
    private array $pixTypes = [
        'cpf' => 'CPF',
        'email' => 'E-mail',
        'telefone' => 'Telefone',
        'aleatoria' => 'Chave aleatória',
    ];

    private array $instruments = [
        'Voz',
        'Violão',
        'Cavaco',
        'Banjo',
        'Pandeiro',
        'Tantan',
        'Reco-reco',
        'Surdo',
        'Bateria',
        'Percussão',
        'Produção',
        'Outro',
    ];

    public function index(): string
    {
        $this->ensureAdmin();

        return $this->renderApp('tamoai/users/index', [
            'title' => 'Usuários | Grupo Tamo Aí',
            'users' => (new TamoAiUserModel())->orderBy('created_at', 'DESC')->findAll(),
        ]);
    }

    public function members(): string
    {
        return $this->renderApp('tamoai/users/members', [
            'title' => 'Integrantes | Grupo Tamo Aí',
            'users' => (new TamoAiUserModel())
                ->where('active', 1)
                ->orderBy('name', 'ASC')
                ->findAll(),
            'pixTypes' => $this->pixTypes,
        ]);
    }

    public function profile(): string
    {
        $user = (new TamoAiUserModel())->find((int) session('tamo_ai_user_id'));
        if (! $user) {
            return redirect()->to('/tamo-ai/sair')->with('error', 'Entre novamente para atualizar seu perfil.');
        }

        return $this->renderApp('tamoai/users/profile', [
            'title' => 'Meu perfil | Grupo Tamo Aí',
            'user' => $user,
            'pixTypes' => $this->pixTypes,
            'instruments' => $this->instruments,
        ]);
    }

    public function updateProfile()
    {
        $userId = (int) session('tamo_ai_user_id');
        $model = new TamoAiUserModel();
        $payload = $this->profilePayload();
        $password = (string) $this->request->getPost('password');

        if ($password !== '') {
            $payload['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $model->update($userId, $payload);

        session()->set([
            'tamo_ai_name' => $payload['name'],
            'tamo_ai_email' => $payload['email'],
        ]);

        return redirect()->to('/tamo-ai/perfil')->with('success', 'Perfil atualizado.');
    }

    public function create(): string
    {
        $this->ensureAdmin();

        return $this->form('Novo usuário', [], '/tamo-ai/usuarios/salvar');
    }

    public function store()
    {
        $this->ensureAdmin();
        (new TamoAiUserModel())->insert($this->adminPayload(true));

        return redirect()->to('/tamo-ai/usuarios')->with('success', 'Usuário criado.');
    }

    public function edit(int $id): string
    {
        $this->ensureAdmin();
        $user = (new TamoAiUserModel())->find($id);
        if (! $user) {
            throw PageNotFoundException::forPageNotFound('Usuário não encontrado.');
        }

        return $this->form('Editar usuário', $user, '/tamo-ai/usuarios/atualizar/' . $id);
    }

    public function update(int $id)
    {
        $this->ensureAdmin();
        (new TamoAiUserModel())->update($id, $this->adminPayload(false));

        return redirect()->to('/tamo-ai/usuarios')->with('success', 'Usuário atualizado.');
    }

    public function delete(int $id)
    {
        $this->ensureAdmin();

        if ((int) session('tamo_ai_user_id') === $id) {
            return redirect()->back()->with('error', 'Você não pode excluir o usuário logado.');
        }

        (new TamoAiUserModel())->delete($id);

        return redirect()->to('/tamo-ai/usuarios')->with('success', 'Usuário removido.');
    }

    private function form(string $title, array $user, string $action): string
    {
        return $this->renderApp('tamoai/users/form', [
            'title' => $title,
            'user' => $user,
            'action' => $action,
            'pixTypes' => $this->pixTypes,
            'instruments' => $this->instruments,
        ]);
    }

    private function adminPayload(bool $requiredPassword): array
    {
        $payload = $this->profilePayload();
        $role = (string) $this->request->getPost('role');
        $payload['role'] = in_array($role, ['admin', 'financeiro', 'member'], true) ? $role : 'member';
        $payload['active'] = $this->request->getPost('active') ? 1 : 0;

        $password = (string) $this->request->getPost('password');
        if ($password !== '' || $requiredPassword) {
            $payload['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        return $payload;
    }

    private function profilePayload(): array
    {
        $pixType = (string) $this->request->getPost('pix_key_type');
        if (! array_key_exists($pixType, $this->pixTypes)) {
            $pixType = '';
        }

        return [
            'name' => trim((string) $this->request->getPost('name')),
            'email' => trim((string) $this->request->getPost('email')),
            'phone' => trim((string) $this->request->getPost('phone')),
            'main_instrument' => trim((string) $this->request->getPost('main_instrument')),
            'pix_key' => trim((string) $this->request->getPost('pix_key')),
            'pix_key_type' => $pixType,
        ];
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
