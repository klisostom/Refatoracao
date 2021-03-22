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

        $enrichPerformance = function ($aPerformance) use (
            $playFor,
            $amountFor
        ) {
            $result = $aPerformance;
            $result['play'] = $playFor($result);
            $result['amount'] = $amountFor($result);

            return $result;
        };

        $statement = [];
        $statement['customer'] = $invoice['customer'];
        $statement['performances'] = array_map($enrichPerformance, $invoice['performances']);

        return $this->renderPlainText($statement, $plays);
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

        $volumeCreditsFor = function ($aPerformance) {
            $result = 0;
            $result += max($aPerformance['audience'] - 30, 0);

            if ('comedy' === $aPerformance['play']['type']) {
                $result += floor($aPerformance['audience'] / 5);
            }

            return $result;
        };

        $totalVolumeCredits = function () use ($data, $volumeCreditsFor) {
            $result = 0;

            foreach ($data['performances'] as $perf) {
                $result += $volumeCreditsFor($perf);
            }

            return $result;
        };

        $totalAmount = function () use ($data) {
            $result = 0;

            foreach ($data['performances'] as $perf) {
                $result += $perf['amount'];
            }

            return $result;
        };

        $result = "\nStatement for ".$data['customer']."\n";

        foreach ($data['performances'] as $perf) {
            // exibe a linha para esta requisição
            $result .= "    " .
                $perf['play']['name'].": " .
                $usd($perf['amount']) .
                " (" . $perf['audience'] . " seats)\n";
        }

        $result .= "Amount owed is " . $usd($totalAmount()) . "\n";
        $result .= "You earned " . $totalVolumeCredits() . " credits\n";
        return $result;
    }
}
