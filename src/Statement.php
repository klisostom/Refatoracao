<?php

namespace App;

use Exception;
use \NumberFormatter;

class Statement
{

    public function __construct(
        public int $totalAmount = 0,
        public int $volumeCredits = 0,
        public string $result = '',
    ) {}

    protected function format($aNumber)
    {
        $result = new NumberFormatter(
            'en_US',
            NumberFormatter::CURRENCY,
        );

        return $result->format($aNumber);
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

        foreach ($invoice['performances'] as $perf) {
            $this->volumeCredits += $volumeCreditsFor($perf);

            // exibe a linha para esta requisição
            $this->result .= "    " .
                $playFor($perf)['name'].": " .
                $this->format($amountFor($perf) / 100) .
                " (" . $perf['audience'] . " seats)\n";

            $this->totalAmount += $amountFor($perf);
        }

        $this->result .= "Amount owed is " . $this->format(($this->totalAmount/100)) . "\n";
        $this->result .= "You earned " . $this->volumeCredits . " credits\n";
        return $this->result;
    }
}
