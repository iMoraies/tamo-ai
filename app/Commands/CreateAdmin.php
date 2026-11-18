<?php

namespace App\Commands;

use App\Models\TamoAiUserModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateAdmin extends BaseCommand
{
    protected $group = 'Tamo Aí';
    protected $name = 'tamoai:admin';
    protected $description = 'Cria uma conta de administrador do Tamo Aí.';

    public function run(array $params)
    {
        $name = CLI::prompt('Nome', null, 'required|max_length[120]');
        $email = CLI::prompt('E-mail', null, 'required|valid_email|max_length[190]');
        $model = new TamoAiUserModel();
        if ($model->where('email', $email)->first()) {
            CLI::error('Já existe uma conta com esse e-mail.');
            return EXIT_ERROR;
        }

        $password = CLI::prompt('Senha', null, 'required');
        $id = $model->insert([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'admin',
            'active' => 1,
        ]);
        if ($id === false) {
            CLI::error('Confira os dados e a estrutura do banco.');
            return EXIT_ERROR;
        }

        CLI::write('Administrador criado. Acesse /tamo-ai/entrar.', 'green');
    }
}
