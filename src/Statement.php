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
        $enrichPerformance = function ($aPerformance) {
            return $aPerformance;
        };

        $statement = [];
        $statement['customer'] = $invoice['customer'];
        $statement['performances'] = array_map($enrichPerformance, $invoice['performances']);

        return $this->renderPlainText($statement, $plays);
    }

    public function renderPlainText(
        array $data,
        array $plays
    ): string {
        $playFor = function ($aPerformance) use ($plays) {
            return $plays[$aPerformance['playID']];
        };

        $usd = function ($aNumber) {
            $result = new NumberFormatter(
                'en_US',
                NumberFormatter::CURRENCY,
            );

            return $result->format($aNumber / 100);
        };

        $amountFor = function ($aPerformance) use ($playFor) {
            $result = 0;

            switch ($playFor($aPerformance)['type']) {
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
                    throw new Exception('Unknow type: ' . $playFor($aPerformance)['type'], 1);
            };

            return $result;
        };

        $volumeCreditsFor = function ($aPerformance) use ($playFor) {
            $result = 0;
            $result += max($aPerformance['audience'] - 30, 0);

            if ('comedy' === $playFor($aPerformance)['type']) {
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

        $totalAmount = function () use ($data, $amountFor) {
            $result = 0;

            foreach ($data['performances'] as $perf) {
                $result += $amountFor($perf);
            }

            return $result;
        };

        $result = "\nStatement for ".$data['customer']."\n";

        foreach ($data['performances'] as $perf) {
            // exibe a linha para esta requisição
            $result .= "    " .
                $playFor($perf)['name'].": " .
                $usd($amountFor($perf)) .
                " (" . $perf['audience'] . " seats)\n";
        }

        $result .= "Amount owed is " . $usd($totalAmount()) . "\n";
        $result .= "You earned " . $totalVolumeCredits() . " credits\n";
        return $result;
    }
}
