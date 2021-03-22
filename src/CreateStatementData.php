<?php

namespace App;

use Exception;
use App\Calculator\ComedyCalculator;
use App\Calculator\TragedyCalculator;
use App\Calculator\PerformanceCalculator;

require __DIR__ . '/../src/Calculator/PerformanceCalculator.php';
require __DIR__ . '/../src/Calculator/TragedyCalculator.php';
require __DIR__ . '/../src/Calculator/ComedyCalculator.php';

class CreateStatementData
{
    public function createStatementData(array $invoice, array $plays): array
    {
        $playFor = function ($aPerformance) use ($plays) {
            return $plays[$aPerformance['playID']];
        };

        $createPerformanceCalculator = function ($aPerformance, $aPlay) {
            return match($aPlay['type']) {
                'tragedy' => new TragedyCalculator($aPerformance, $aPlay),
                'comedy' => new ComedyCalculator($aPerformance, $aPlay),
                default => throw new Exception('Unknow type: ' . $aPlay['type']),
            };
        };

        $enrichPerformance = function ($aPerformance) use (
            $playFor,
            $createPerformanceCalculator,
        ) {
            $calculator = $createPerformanceCalculator($aPerformance, $playFor($aPerformance));

            $result = $aPerformance;
            $result['play'] = $calculator->getPlay();
            $result['amount'] = $calculator->getAmount();
            $result['volumeCredits'] = $calculator->getVolumeCredits();

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
