<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class TamoAiAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session('tamo_ai_user_id')) {
            return null;
        }

        return redirect()->to('/tamo-ai/entrar')->with('error', 'Entre para acessar a agenda do grupo.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
