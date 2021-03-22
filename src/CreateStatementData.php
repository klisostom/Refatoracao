<?php

namespace App;

use Exception;
use App\Calculator\PerformanceCalculator;

require __DIR__ . '/../src/Calculator/PerformanceCalculator.php';

class CreateStatementData
{
    public function createStatementData(array $invoice, array $plays): array
    {
        $playFor = function ($aPerformance) use ($plays) {
            return $plays[$aPerformance['playID']];
        };

        $amountFor = function ($aPerformance) use ($playFor) {
            $result = new PerformanceCalculator($aPerformance, $playFor($aPerformance));
            return $result->getAmount();
        };

        $volumeCreditsFor = function ($aPerformance) {
            $result = 0;
            $result += max($aPerformance['audience'] - 30, 0);

            if ('comedy' === $aPerformance['play']['type']) {
                $result += floor($aPerformance['audience'] / 5);
            }

            return $result;
        };

        $enrichPerformance = function ($aPerformance) use (
            $playFor,
            $amountFor,
            $volumeCreditsFor,
        ) {
            $calculator = new PerformanceCalculator($aPerformance, $playFor($aPerformance));

            $result = $aPerformance;
            $result['play'] = $calculator->getPlay();
            $result['amount'] = $amountFor($result);
            $result['volumeCredits'] = $volumeCreditsFor($result);

            return $result;
        };

        $totalVolumeCredits = function ($data) {
            return array_reduce($data['performances'], fn ($total, $perf) => $total + $perf['volumeCredits'], 0);
        };

        $totalAmount = function ($data) {
            return array_reduce($data['performances'], fn ($total, $perf) => $total + $perf['amount'], 0);
        };

        $result = [];
        $result['customer'] = $invoice['customer'];
        $result['performances'] = array_map($enrichPerformance, $invoice['performances']);
        $result['totalAmount'] = $totalAmount($result);
        $result['totalVolumeCredits'] = $totalVolumeCredits($result);

        return $result;
    }
}
