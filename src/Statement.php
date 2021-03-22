<?php

namespace App;

use Exception;
use \NumberFormatter;

class Statement
{

    public function __construct(
        public int $totalAmount = 0,
        public string $result = '',
    ) {}

    protected function usd($aNumber)
    {
        $result = new NumberFormatter(
            'en_US',
            NumberFormatter::CURRENCY,
        );

        return $result->format($aNumber / 100);
    }

    public function statement(array $invoice, array $plays): string
    {
        $this->result = "\nStatement for ".$invoice['customer']."\n";

        $playFor = function ($aPerformance) use ($plays) {
            return $plays[$aPerformance['playID']];
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

        $totalVolumeCredits = function () use ($invoice, $volumeCreditsFor) {
            $volumeCredits = 0;

            foreach ($invoice['performances'] as $perf) {
                $volumeCredits += $volumeCreditsFor($perf);
            }

            return $volumeCredits;
        };

        foreach ($invoice['performances'] as $perf) {
            // exibe a linha para esta requisição
            $this->result .= "    " .
                $playFor($perf)['name'].": " .
                $this->usd($amountFor($perf)) .
                " (" . $perf['audience'] . " seats)\n";

            $this->totalAmount += $amountFor($perf);
        }

        $this->result .= "Amount owed is " . $this->usd($this->totalAmount) . "\n";
        $this->result .= "You earned " . $totalVolumeCredits() . " credits\n";
        return $this->result;
    }
}
