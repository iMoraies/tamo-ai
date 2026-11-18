<?php

namespace App\Controllers\TamoAi;

use App\Models\TamoAiEmailLogModel;
use App\Models\TamoAiEmailRecipientModel;
use App\Models\TamoAiEventFinancialModel;
use App\Models\TamoAiPaymentModel;
use App\Models\TamoAiReceiptModel;

class EmailController extends TamoAiBaseController
{
    private array $types = ['integrante', 'freelancer', 'contratante', 'contador', 'administrador', 'outro'];

    public function index(): string
    {
        $this->ensureFinance();

        return $this->renderApp('tamoai/emails/index', [
            'title' => 'E-mails | Grupo Tamo Aí',
            'recipients' => $this->tableReady('tamo_ai_email_recipients') ? (new TamoAiEmailRecipientModel())->orderBy('status', 'ASC')->orderBy('name', 'ASC')->findAll() : [],
            'logs' => $this->tableReady('tamo_ai_email_logs') ? (new TamoAiEmailLogModel())->orderBy('created_at', 'DESC')->findAll(40) : [],
            'types' => $this->types,
            'tableReady' => $this->tableReady('tamo_ai_email_recipients'),
        ]);
    }

    public function store()
    {
        $this->ensureFinance();
        $payload = $this->recipientPayload();
        if ($payload['name'] === '' || ! filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'Informe nome e e-mail válido.');
        }

        (new TamoAiEmailRecipientModel())->insert($payload);

        return redirect()->to('/tamo-ai/emails')->with('success', 'Destinatário cadastrado.');
    }

    public function delete(int $id)
    {
        $this->ensureFinance();
        (new TamoAiEmailRecipientModel())->delete($id);

        return redirect()->to('/tamo-ai/emails')->with('success', 'Destinatário removido.');
    }

    public function eventForm(int $eventId): string
    {
        $this->ensureFinance();
        $event = $this->findEvent($eventId);

        return $this->renderApp('tamoai/emails/send_event', [
            'title' => 'Enviar fechamento | Grupo Tamo Aí',
            'event' => $event,
            'recipients' => (new TamoAiEmailRecipientModel())->where('status', 'ativo')->orderBy('name', 'ASC')->findAll(),
            'financial' => (new TamoAiEventFinancialModel())->where('event_id', $eventId)->first(),
            'payments' => (new TamoAiPaymentModel())->where('event_id', $eventId)->orderBy('name', 'ASC')->findAll(),
            'receipts' => (new TamoAiReceiptModel())->where('event_id', $eventId)->orderBy('payment_date', 'ASC')->findAll(),
        ]);
    }

    public function sendEvent(int $eventId)
    {
        $this->ensureFinance();
        $event = $this->findEvent($eventId);
        $recipients = $this->selectedRecipients();
        if ($recipients === []) {
            return redirect()->back()->with('error', 'Selecione ou digite pelo menos um destinatário.');
        }

        $financial = (new TamoAiEventFinancialModel())->where('event_id', $eventId)->first() ?: [];
        $payments = (new TamoAiPaymentModel())->where('event_id', $eventId)->orderBy('name', 'ASC')->findAll();
        $receipts = (new TamoAiReceiptModel())->where('event_id', $eventId)->orderBy('payment_date', 'ASC')->findAll();
        $subject = 'Fechamento financeiro - Grupo Tamo Aí - ' . $event['client'] . ' - ' . date('d/m/Y', strtotime($event['event_date']));
        $message = view('tamoai/emails/templates/closing', [
            'event' => $event,
            'financial' => $financial,
            'payments' => $payments,
            'receipts' => $receipts,
            'mode' => (string) $this->request->getPost('send_mode'),
        ]);

        $status = 'enviado';
        $error = null;

        try {
            $email = service('email');
            $smtpHost = (string) env('TAMOAI_SMTP_HOST', '');
            if ($smtpHost !== '') {
                $email->initialize([
                    'protocol' => 'smtp',
                    'SMTPHost' => $smtpHost,
                    'SMTPUser' => env('TAMOAI_SMTP_USER'),
                    'SMTPPass' => env('TAMOAI_SMTP_PASS'),
                    'SMTPPort' => (int) env('TAMOAI_SMTP_PORT', 587),
                    'SMTPCrypto' => env('TAMOAI_SMTP_CRYPTO', 'tls'),
                    'mailType' => 'html',
                    'charset' => 'UTF-8',
                ]);
            }

            $email->setFrom((string) env('TAMOAI_EMAIL_FROM', 'financeiro@madebytheux.com'), (string) env('TAMOAI_EMAIL_FROM_NAME', 'Grupo Tamo Aí'));
            $email->setTo($recipients);
            $email->setSubject($subject);
            $email->setMessage($message);

            if (! $email->send(false)) {
                $status = 'erro';
                $error = $email->printDebugger(['headers']);
            }
        } catch (\Throwable $exception) {
            $status = 'erro';
            $error = $exception->getMessage();
        }

        (new TamoAiEmailLogModel())->insert([
            'event_id' => $eventId,
            'subject' => $subject,
            'recipients_json' => json_encode($recipients, JSON_UNESCAPED_UNICODE),
            'message' => $message,
            'attachments_json' => null,
            'status' => $status,
            'error_message' => $error,
            'sent_by' => session('tamo_ai_user_id'),
            'sent_at' => date('Y-m-d H:i:s'),
        ]);

        $this->logAction('email_fechamento_enviado', 'emails', $eventId, 'Envio de fechamento: ' . $status, null, ['recipients' => $recipients]);

        return redirect()->to('/tamo-ai/eventos/' . $eventId)->with($status === 'enviado' ? 'success' : 'error', $status === 'enviado' ? 'Fechamento enviado por e-mail.' : 'Não foi possível enviar o e-mail. O log foi salvo.');
    }

    private function recipientPayload(): array
    {
        $type = (string) $this->request->getPost('type');
        if (! in_array($type, $this->types, true)) {
            $type = 'outro';
        }

        return [
            'name' => trim((string) $this->request->getPost('name')),
            'email' => trim((string) $this->request->getPost('email')),
            'type' => $type,
            'status' => $this->request->getPost('status') === 'inativo' ? 'inativo' : 'ativo',
            'notes' => trim((string) $this->request->getPost('notes')),
        ];
    }

    private function selectedRecipients(): array
    {
        $emails = [];
        $ids = array_map('intval', (array) $this->request->getPost('recipient_ids'));
        if ($ids !== []) {
            foreach ((new TamoAiEmailRecipientModel())->whereIn('id', $ids)->findAll() as $recipient) {
                if (filter_var($recipient['email'], FILTER_VALIDATE_EMAIL)) {
                    $emails[] = $recipient['email'];
                }
            }
        }

        foreach (preg_split('/[\s,;]+/', (string) $this->request->getPost('manual_emails')) ?: [] as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[] = $email;
            }
        }

        return array_values(array_unique($emails));
    }
}
