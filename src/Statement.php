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
        $playFor = function ($aPerformance) use ($plays) {
            return $plays[$aPerformance['playID']];
        };

        $amountFor = function ($aPerformance, $play) use ($playFor)
        {
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

        $this->result = "\nStatement for ".$invoice['customer']."\n";

        foreach ($invoice['performances'] as $perf) {
            $thisAmount = $amountFor($perf, $playFor($perf));

            // soma créditos por volume
            $this->volumeCredits += max($perf['audience'] - 30, 0);
            //soma um crédito extra para cada dez espectadores de comédia
            if ('comedy' === $playFor($perf)['type']) {
                $this->volumeCredits += floor($perf['audience'] / 5);
            }

            // exibe a linha para esta requisição
            $this->result .= "    " .
                $playFor($perf)['name'].": " .
                $this->numberFormatter->format($thisAmount / 100) .
                " (" .
                $perf['audience'] .
                " seats)\n";

            $this->totalAmount += $thisAmount;
        }

        $this->result .= "Amount owed is " . $this->numberFormatter->format(($this->totalAmount/100)) . "\n";
        $this->result .= "You earned " . $this->volumeCredits . " credits\n";
        return $this->result;
    }
}
