<?php

namespace App\Controllers\TamoAi;

use App\Models\TamoAiCashBoxMovementModel;
use App\Models\TamoAiEmailLogModel;
use App\Models\TamoAiEventFinancialModel;
use App\Models\TamoAiEventModel;
use App\Models\TamoAiPaymentModel;
use App\Models\TamoAiReceiptModel;

class ReportController extends TamoAiBaseController
{
    public function index(): string
    {
        $month = $this->month();
        $data = $this->monthlyData($month['year'], $month['month']);

        return $this->renderApp('tamoai/reports/index', [
            'title' => 'Relatórios | Grupo Tamo Aí',
            'year' => $month['year'],
            'month' => $month['month'],
            'data' => $data,
        ]);
    }

    public function exportMonthly()
    {
        $month = $this->month();
        $data = $this->monthlyData($month['year'], $month['month']);

        $rows = [['Data', 'Evento', 'Local', 'Cachê', 'Recebido', 'Despesas', 'Freelancers', 'Caixa', 'Valor dividido', 'Valor por integrante', 'Status', 'Observação']];
        foreach ($data['events'] as $event) {
            $financial = $data['financials'][$event['id']] ?? [];
            $rows[] = [
                date('d/m/Y', strtotime($event['event_date'])),
                $event['client'],
                trim($event['address'] . ' ' . ($event['neighborhood'] ?? '')),
                number_format((float) ($financial['contracted_amount'] ?? $event['total_fee']), 2, ',', '.'),
                number_format((float) ($financial['received_amount'] ?? 0), 2, ',', '.'),
                number_format((float) ($financial['total_expenses'] ?? 0), 2, ',', '.'),
                number_format((float) ($financial['total_freelancers'] ?? 0), 2, ',', '.'),
                number_format((float) ($financial['group_cash_amount'] ?? 0), 2, ',', '.'),
                number_format((float) ($financial['amount_to_split'] ?? 0), 2, ',', '.'),
                number_format((float) ($financial['amount_per_member'] ?? 0), 2, ',', '.'),
                $financial['received_status'] ?? $event['payment_status'],
                $financial['notes'] ?? '',
            ];
        }

        $this->logAction('relatorio_exportado', 'reports', null, 'Relatório mensal exportado.', null, $month);

        return $this->csvResponse('relatorio-tamo-ai-' . $month['year'] . '-' . $month['month'] . '.csv', $rows);
    }

    public function printMonthly(): string
    {
        $month = $this->month();

        return view('tamoai/reports/print', [
            'title' => 'Relatório mensal',
            'year' => $month['year'],
            'month' => $month['month'],
            'data' => $this->monthlyData($month['year'], $month['month']),
        ]);
    }

    private function monthlyData(string $year, string $month): array
    {
        $start = $year . '-' . $month . '-01';
        $end = date('Y-m-t', strtotime($start));
        $events = (new TamoAiEventModel())->where('event_date >=', $start)->where('event_date <=', $end)->orderBy('event_date', 'ASC')->findAll();
        $ids = array_column($events, 'id');
        $financials = [];
        $payments = [];
        $receipts = [];

        if ($ids !== [] && $this->tableReady('tamo_ai_event_financials')) {
            foreach ((new TamoAiEventFinancialModel())->whereIn('event_id', $ids)->findAll() as $financial) {
                $financials[$financial['event_id']] = $financial;
            }
        }

        if ($ids !== [] && $this->tableReady('tamo_ai_payments')) {
            $payments = (new TamoAiPaymentModel())->whereIn('event_id', $ids)->findAll();
        }

        if ($ids !== [] && $this->tableReady('tamo_ai_receipts')) {
            $receipts = (new TamoAiReceiptModel())->whereIn('event_id', $ids)->findAll();
        }

        $cashEntries = $this->tableReady('tamo_ai_cash_box_movements')
            ? (new TamoAiCashBoxMovementModel())->where('movement_date >=', $start)->where('movement_date <=', $end)->findAll()
            : [];

        $stats = [
            'event_count' => count($events),
            'contracted' => 0,
            'received' => 0,
            'pending' => 0,
            'expenses' => 0,
            'freelancers' => 0,
            'split' => 0,
            'cash' => 0,
            'cash_balance' => 0,
            'payments_total' => array_sum(array_map(static fn ($payment) => (float) $payment['paid_amount'], $payments)),
            'receipts_count' => count($receipts),
        ];

        foreach ($events as $event) {
            $financial = $financials[$event['id']] ?? [];
            $contracted = (float) ($financial['contracted_amount'] ?? $event['total_fee']);
            $received = (float) ($financial['received_amount'] ?? 0);
            $stats['contracted'] += $contracted;
            $stats['received'] += $received;
            $stats['pending'] += max($contracted - $received, 0);
            $stats['expenses'] += (float) ($financial['total_expenses'] ?? 0);
            $stats['freelancers'] += (float) ($financial['total_freelancers'] ?? 0);
            $stats['split'] += (float) ($financial['amount_to_split'] ?? 0);
            $stats['cash'] += (float) ($financial['group_cash_amount'] ?? 0);
        }

        foreach ($cashEntries as $entry) {
            $stats['cash_balance'] += $entry['type'] === 'saida' ? -(float) $entry['amount'] : (float) $entry['amount'];
        }

        return [
            'events' => $events,
            'financials' => $financials,
            'payments' => $payments,
            'receipts' => $receipts,
            'cashEntries' => $cashEntries,
            'stats' => $stats,
        ];
    }

    private function month(): array
    {
        $year = (string) ($this->request->getGet('ano') ?: date('Y'));
        $month = (string) ($this->request->getGet('mes') ?: date('m'));
        if (preg_match('/^(\d{4})-(\d{2})$/', $month, $matches)) {
            $year = $matches[1];
            $month = $matches[2];
        }

        return [
            'year' => preg_match('/^\d{4}$/', $year) ? $year : date('Y'),
            'month' => preg_match('/^\d{2}$/', $month) ? $month : date('m'),
        ];
    }

    private function csvResponse(string $filename, array $rows)
    {
        $handle = fopen('php://temp', 'rb+');
        foreach ($rows as $row) {
            fputcsv($handle, $row, ';');
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody("\xEF\xBB\xBF" . $csv);
    }
}
