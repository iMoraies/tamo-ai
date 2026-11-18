<?php

namespace App\Controllers\TamoAi;

use App\Models\TamoAiEventFinancialModel;
use App\Models\TamoAiEventFreelancerModel;
use App\Models\TamoAiPaymentModel;
use App\Models\TamoAiReceiptModel;
use App\Models\TamoAiUserModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class FinanceController extends TamoAiBaseController
{
    private array $receivedStatuses = [
        'pendente' => 'Pendente',
        'parcial' => 'Parcial',
        'recebido' => 'Recebido',
    ];

    private array $paymentStatuses = [
        'pendente' => 'Pendente',
        'parcial' => 'Parcial',
        'pago' => 'Pago',
    ];

    private array $paymentMethods = ['Pix', 'Dinheiro', 'Transferência', 'Cartão', 'Outro'];

    public function event(int $eventId): string
    {
        $event = $this->findEvent($eventId);

        if (! $this->tableReady('tamo_ai_event_financials')) {
            return $this->renderApp('tamoai/events/show', [
                'title' => 'Financeiro | Grupo Tamo Aí',
                'event' => $event,
                'tableReady' => false,
            ]);
        }

        $financial = $this->financialForEvent($event);
        $payments = (new TamoAiPaymentModel())->where('event_id', $eventId)->orderBy('person_type', 'ASC')->orderBy('name', 'ASC')->findAll();
        $receipts = (new TamoAiReceiptModel())->where('event_id', $eventId)->orderBy('payment_date', 'DESC')->orderBy('created_at', 'DESC')->findAll();
        $freelancers = (new TamoAiEventFreelancerModel())->where('event_id', $eventId)->orderBy('id', 'ASC')->findAll();
        $members = (new TamoAiUserModel())->where('active', 1)->orderBy('name', 'ASC')->findAll();

        return $this->renderApp('tamoai/events/show', [
            'title' => 'Financeiro | Grupo Tamo Aí',
            'event' => $event,
            'tableReady' => true,
            'financial' => $financial,
            'payments' => $payments,
            'receipts' => $receipts,
            'freelancers' => $freelancers,
            'members' => $members,
            'receivedStatuses' => $this->receivedStatuses,
            'paymentStatuses' => $this->paymentStatuses,
            'paymentMethods' => $this->paymentMethods,
            'canManageFinance' => $this->canManageFinance(),
            'summary' => $this->eventSummary($event, $financial, $payments, $receipts),
        ]);
    }

    public function updateFinancial(int $eventId)
    {
        $this->ensureFinance();
        $event = $this->findEvent($eventId);
        $model = new TamoAiEventFinancialModel();
        $current = $model->where('event_id', $eventId)->first();

        $payload = $this->financialPayload($event);

        if ($current) {
            $model->update((int) $current['id'], $payload);
        } else {
            $payload['event_id'] = $eventId;
            $model->insert($payload);
        }

        $this->logAction('financeiro_atualizado', 'event_financials', $eventId, 'Fechamento financeiro atualizado.', $current ?: null, $payload);

        return redirect()->to('/tamo-ai/eventos/' . $eventId)->with('success', 'Fechamento financeiro atualizado.');
    }

    public function storePayment(int $eventId)
    {
        $this->ensureFinance();
        $event = $this->findEvent($eventId);
        $payload = $this->paymentPayload($eventId);

        if ($payload['name'] === '' || (float) $payload['expected_amount'] <= 0 && (float) $payload['paid_amount'] <= 0) {
            return redirect()->back()->with('error', 'Informe nome e valor do pagamento.');
        }

        (new TamoAiPaymentModel())->insert($payload);
        $this->logAction('pagamento_criado', 'payments', $eventId, 'Pagamento criado para ' . $payload['name'] . '.', null, $payload);

        return redirect()->to('/tamo-ai/eventos/' . $event['id'])->with('success', 'Pagamento adicionado.');
    }

    public function updatePayment(int $eventId, int $paymentId)
    {
        $this->ensureFinance();
        $this->findEvent($eventId);

        $model = new TamoAiPaymentModel();
        $payment = $model->where('event_id', $eventId)->find($paymentId);
        if (! $payment) {
            throw PageNotFoundException::forPageNotFound('Pagamento não encontrado.');
        }

        $payload = $this->paymentPayload($eventId);
        $model->update($paymentId, $payload);
        $this->logAction('pagamento_atualizado', 'payments', $paymentId, 'Pagamento atualizado.', $payment, $payload);

        return redirect()->to('/tamo-ai/eventos/' . $eventId)->with('success', 'Pagamento atualizado.');
    }

    public function markPaymentPaid(int $eventId, int $paymentId)
    {
        $this->ensureFinance();
        $this->findEvent($eventId);

        $model = new TamoAiPaymentModel();
        $payment = $model->where('event_id', $eventId)->find($paymentId);
        if (! $payment) {
            throw PageNotFoundException::forPageNotFound('Pagamento não encontrado.');
        }

        $payload = [
            'paid_amount' => (float) $payment['expected_amount'],
            'status' => 'pago',
            'payment_method' => trim((string) ($this->request->getPost('payment_method') ?: $payment['payment_method'] ?: 'Pix')),
            'paid_at' => $this->parseDate((string) $this->request->getPost('paid_at'), date('Y-m-d')),
        ];

        $model->update($paymentId, $payload);
        $this->logAction('pagamento_pago', 'payments', $paymentId, 'Pagamento marcado como pago.', $payment, $payload);

        return redirect()->to('/tamo-ai/eventos/' . $eventId)->with('success', 'Pagamento marcado como pago.');
    }

    public function deletePayment(int $eventId, int $paymentId)
    {
        $this->ensureFinance();
        $this->findEvent($eventId);

        $model = new TamoAiPaymentModel();
        $payment = $model->where('event_id', $eventId)->find($paymentId);
        if ($payment) {
            $model->delete($paymentId);
            $this->logAction('pagamento_removido', 'payments', $paymentId, 'Pagamento removido.', $payment, null);
        }

        return redirect()->to('/tamo-ai/eventos/' . $eventId)->with('success', 'Pagamento removido.');
    }

    public function printClosing(int $eventId): string
    {
        $event = $this->findEvent($eventId);
        $financial = $this->financialForEvent($event);
        $payments = (new TamoAiPaymentModel())->where('event_id', $eventId)->orderBy('name', 'ASC')->findAll();
        $receipts = (new TamoAiReceiptModel())->where('event_id', $eventId)->orderBy('payment_date', 'ASC')->findAll();

        return view('tamoai/events/print', [
            'title' => 'Fechamento financeiro',
            'event' => $event,
            'financial' => $financial,
            'payments' => $payments,
            'receipts' => $receipts,
            'summary' => $this->eventSummary($event, $financial, $payments, $receipts),
        ]);
    }

    private function financialForEvent(array $event): array
    {
        $financial = (new TamoAiEventFinancialModel())->where('event_id', $event['id'])->first();

        return $financial ?: [
            'event_id' => $event['id'],
            'contracted_amount' => (float) ($event['total_fee'] ?? 0),
            'received_amount' => ((string) ($event['payment_status'] ?? '') === 'sim') ? (float) ($event['total_fee'] ?? 0) : 0,
            'received_status' => ((string) ($event['payment_status'] ?? '') === 'sim') ? 'recebido' : 'pendente',
            'received_date' => null,
            'received_method' => '',
            'total_expenses' => 0,
            'total_freelancers' => $this->freelancersTotal((int) $event['id']),
            'group_cash_amount' => $event['status'] === 'ultimo-do-mes' ? max((float) ($event['total_fee'] ?? 0) - $this->freelancersTotal((int) $event['id']), 0) : 0,
            'amount_to_split' => 0,
            'split_members_count' => (int) ($event['expected_members'] ?? 0),
            'amount_per_member' => 0,
            'manual_adjustment' => 0,
            'notes' => '',
            'closed_at' => null,
            'closed_by' => null,
        ];
    }

    private function financialPayload(array $event): array
    {
        $receivedStatus = (string) $this->request->getPost('received_status');
        if (! array_key_exists($receivedStatus, $this->receivedStatuses)) {
            $receivedStatus = 'pendente';
        }

        $contracted = $this->moneyToFloat((string) $this->request->getPost('contracted_amount'));
        $received = $this->moneyToFloat((string) $this->request->getPost('received_amount'));
        $expenses = $this->moneyToFloat((string) $this->request->getPost('total_expenses'));
        $freelancers = $this->moneyToFloat((string) $this->request->getPost('total_freelancers'));
        $cash = $this->moneyToFloat((string) $this->request->getPost('group_cash_amount'));
        $manualAdjustment = $this->moneyToFloat((string) $this->request->getPost('manual_adjustment'));
        $splitMembers = max(0, (int) $this->request->getPost('split_members_count'));

        $calculatedToSplit = max($received - $expenses - $freelancers - $cash + $manualAdjustment, 0);
        $postedToSplit = trim((string) $this->request->getPost('amount_to_split'));
        $amountToSplit = $postedToSplit === '' ? $calculatedToSplit : $this->moneyToFloat($postedToSplit);
        $postedPerMember = trim((string) $this->request->getPost('amount_per_member'));
        $amountPerMember = $postedPerMember === '' && $splitMembers > 0 ? $amountToSplit / $splitMembers : $this->moneyToFloat($postedPerMember);

        return [
            'contracted_amount' => $contracted > 0 ? $contracted : (float) ($event['total_fee'] ?? 0),
            'received_amount' => $received,
            'received_status' => $receivedStatus,
            'received_date' => $this->parseDate((string) $this->request->getPost('received_date')),
            'received_method' => trim((string) $this->request->getPost('received_method')),
            'total_expenses' => $expenses,
            'total_freelancers' => $freelancers,
            'group_cash_amount' => $cash,
            'amount_to_split' => $amountToSplit,
            'split_members_count' => $splitMembers,
            'amount_per_member' => $amountPerMember,
            'manual_adjustment' => $manualAdjustment,
            'notes' => trim((string) $this->request->getPost('notes')),
            'closed_at' => $this->request->getPost('close_financial') ? date('Y-m-d H:i:s') : null,
            'closed_by' => $this->request->getPost('close_financial') ? session('tamo_ai_user_id') : null,
        ];
    }

    private function paymentPayload(int $eventId): array
    {
        $personType = (string) $this->request->getPost('person_type');
        if (! in_array($personType, ['integrante', 'freelancer', 'outro'], true)) {
            $personType = 'outro';
        }

        $status = (string) $this->request->getPost('status');
        if (! array_key_exists($status, $this->paymentStatuses)) {
            $status = 'pendente';
        }

        return [
            'event_id' => $eventId,
            'person_type' => $personType,
            'member_id' => $personType === 'integrante' ? (int) $this->request->getPost('member_id') ?: null : null,
            'freelancer_id' => $personType === 'freelancer' ? (int) $this->request->getPost('freelancer_id') ?: null : null,
            'name' => trim((string) $this->request->getPost('name')),
            'role' => trim((string) $this->request->getPost('role')),
            'expected_amount' => $this->moneyToFloat((string) $this->request->getPost('expected_amount')),
            'paid_amount' => $this->moneyToFloat((string) $this->request->getPost('paid_amount')),
            'status' => $status,
            'payment_method' => trim((string) $this->request->getPost('payment_method')),
            'pix_key' => trim((string) $this->request->getPost('pix_key')),
            'paid_at' => $this->parseDate((string) $this->request->getPost('paid_at')),
            'receipt_id' => (int) $this->request->getPost('receipt_id') ?: null,
            'notes' => trim((string) $this->request->getPost('notes')),
        ];
    }

    private function eventSummary(array $event, array $financial, array $payments, array $receipts): array
    {
        $paidTotal = array_sum(array_map(static fn ($payment) => (float) $payment['paid_amount'], $payments));
        $pendingPayments = array_filter($payments, static fn ($payment) => $payment['status'] !== 'pago');
        $receiptTotal = array_sum(array_map(static fn ($receipt) => (float) $receipt['amount'], $receipts));
        $income = (float) ($financial['received_amount'] ?? 0);
        $out = (float) ($financial['total_expenses'] ?? 0) + (float) ($financial['total_freelancers'] ?? 0) + (float) ($financial['group_cash_amount'] ?? 0);

        return [
            'income' => $income,
            'out' => $out,
            'final_balance' => $income - $out,
            'paid_total' => $paidTotal,
            'pending_payments' => count($pendingPayments),
            'receipt_count' => count($receipts),
            'receipt_total' => $receiptTotal,
            'financial_status' => $this->financialStatus($event, $financial, $payments, $receipts),
        ];
    }

    private function financialStatus(array $event, array $financial, array $payments, array $receipts): string
    {
        if (! empty($financial['closed_at']) && count(array_filter($payments, static fn ($payment) => $payment['status'] !== 'pago')) === 0) {
            return 'Fechado';
        }

        if ((float) ($financial['received_amount'] ?? 0) <= 0 || $receipts === []) {
            return 'Pendente';
        }

        return 'Parcial';
    }

    private function freelancersTotal(int $eventId): float
    {
        $freelancers = (new TamoAiEventFreelancerModel())->where('event_id', $eventId)->findAll();

        return array_sum(array_map(static fn ($item) => (float) $item['fee'], $freelancers));
    }
}
