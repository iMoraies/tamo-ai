<?php

namespace App\Controllers\TamoAi;

use App\Models\TamoAiActivityLogModel;

class LogController extends TamoAiBaseController
{
    public function index(): string
    {
        $this->ensureAdmin();

        return $this->renderApp('tamoai/logs/index', [
            'title' => 'Logs | Grupo Tamo Aí',
            'logs' => $this->tableReady('tamo_ai_activity_logs') ? (new TamoAiActivityLogModel())->orderBy('created_at', 'DESC')->findAll(160) : [],
            'tableReady' => $this->tableReady('tamo_ai_activity_logs'),
        ]);
    }
}
