<?php

namespace App;

use Exception;
use \NumberFormatter;

class Statement
{

    public function __construct(
        private $numberFormatter = null,
        public int $totalAmount = 0,
        public int $volumeCredits = 0,
        public string $result = '',
    ) {
        $this->numberFormatter = new NumberFormatter(
            'en_US',
            NumberFormatter::CURRENCY,
        );
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

        $volumeCreditsFor = function ($perf) use ($playFor) {
            $volumeCreditsTemp = 0;
            $volumeCreditsTemp += max($perf['audience'] - 30, 0);

            if ('comedy' === $playFor($perf)['type']) {
                $volumeCreditsTemp += floor($perf['audience'] / 5);
            }

            return $volumeCreditsTemp;
        };

        foreach ($invoice['performances'] as $perf) {
            $this->volumeCredits += $volumeCreditsFor($perf);

            // exibe a linha para esta requisição
            $this->result .= "    " .
                $playFor($perf)['name'].": " .
                $this->numberFormatter->format($amountFor($perf) / 100) .
                " (" .
                $perf['audience'] .
                " seats)\n";

            $this->totalAmount += $amountFor($perf);
        }

        $this->result .= "Amount owed is " . $this->numberFormatter->format(($this->totalAmount/100)) . "\n";
        $this->result .= "You earned " . $this->volumeCredits . " credits\n";
        return $this->result;
    }
}
