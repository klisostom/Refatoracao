<?php

namespace App;

use Exception;
use \NumberFormatter;

class Statement
{

    public function __construct()
    {
        //
    }

    public function statement(array $invoice, array $plays): string
    {
        $playFor = function ($aPerformance) use ($plays) {
            return $plays[$aPerformance['playID']];
        };

        $amountFor = function ($aPerformance) {
            $result = 0;

            switch ($aPerformance['play']['type']) {
                case 'tragedy':
                    $result = 40000;
                    if ($aPerformance['audience'] > 30) {
                        $result += 1000 * ($aPerformance['audience'] - 30);
                    }
                    break;
                case 'comedy':
                    $result = 30000;
                    if ($aPerformance['audience'] > 20) {
                        $result += 10000 + 500 * ($aPerformance['audience'] - 20);
                    }
                    $result += 300 * $aPerformance['audience'];
                    break;
                default:
                    throw new Exception('Unknow type: ' . $aPerformance['play']['type'], 1);
            };

            return $result;
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
            $result = $aPerformance;
            $result['play'] = $playFor($result);
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

        $statementData = [];
        $statementData['customer'] = $invoice['customer'];
        $statementData['performances'] = array_map($enrichPerformance, $invoice['performances']);
        $statementData['totalAmount'] = $totalAmount($statementData);
        $statementData['totalVolumeCredits'] = $totalVolumeCredits($statementData);

        return $this->renderPlainText($statementData, $plays);
    }

    public function renderPlainText(array $data): string
    {
        $usd = function ($aNumber) {
            $result = new NumberFormatter(
                'en_US',
                NumberFormatter::CURRENCY,
            );

            return $result->format($aNumber / 100);
        };

        $result = "\nStatement for ".$data['customer']."\n";

        foreach ($data['performances'] as $perf) {
            // exibe a linha para esta requisição
            $result .= "    " .
                $perf['play']['name'].": " .
                $usd($perf['amount']) .
                " (" . $perf['audience'] . " seats)\n";
        }

        $result .= "Amount owed is " . $usd($data['totalAmount']) . "\n";
        $result .= "You earned " . $data['totalVolumeCredits'] . " credits\n";
        return $result;
    }
}
