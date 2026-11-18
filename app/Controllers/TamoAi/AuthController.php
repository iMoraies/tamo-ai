<?php

namespace App\Controllers\TamoAi;

use App\Controllers\BaseController;
use App\Models\TamoAiUserModel;

class AuthController extends BaseController
{
    public function login()
    {
        if (session('tamo_ai_user_id')) {
            return redirect()->to('/tamo-ai');
        }

        return view('tamoai/login', [
            'title' => 'Agenda | Grupo Tamo Aí',
        ]);
    }

    public function attempt()
    {
        $email = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');
        $userModel = new TamoAiUserModel();
        $user = $userModel->where('email', $email)->where('active', 1)->first();

        if (! $user || ! password_verify($password, $user['password_hash'])) {
            return redirect()->back()->with('error', 'E-mail ou senha inválidos.')->withInput();
        }

        session()->regenerate(true);
        session()->set([
            'tamo_ai_user_id' => $user['id'],
            'tamo_ai_name' => $user['name'],
            'tamo_ai_email' => $user['email'],
            'tamo_ai_role' => $user['role'],
        ]);

        $userModel->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        return redirect()->to('/tamo-ai')->with('success', 'Agenda liberada.');
    }

    public function logout()
    {
        session()->remove(['tamo_ai_user_id', 'tamo_ai_name', 'tamo_ai_email', 'tamo_ai_role']);

        return redirect()->to('/tamo-ai/entrar')->with('success', 'Você saiu da agenda.');
    }
}
