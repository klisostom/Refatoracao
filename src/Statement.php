<?php

namespace App;

use Exception;
use \NumberFormatter;

class Statement
{
    private $numberFormatter;

    public function __construct(
        public int $totalAmount = 0,
        public int $volumeCredits = 0,
        public string $result = '',
    ) {
        $this->numberFormatter = new NumberFormatter(
            'en_US',
            NumberFormatter::CURRENCY,
        );
    }

    public function amountFor($aPerformance, $play)
    {
        $result = 0;

        switch ($play['type']) {
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
                throw new Exception('Unknow type: ' . $play['type'], 1);
        };

        return $result;
    }

    public function statement(array $invoice, array $plays): string
    {
        $playFor = function ($aPerformance) use ($plays) {
            return $plays[$aPerformance['playID']];
        };

        $this->result = "\nStatement for ".$invoice['customer']."\n";

        foreach ($invoice['performances'] as $perf) {
            $play = $playFor($perf);
            $thisAmount = $this->amountFor($perf, $play);

            // soma créditos por volume
            $this->volumeCredits += max($perf['audience'] - 30, 0);
            //soma um crédito extra para cada dez espectadores de comédia
            if ('comedy' === $play['type']) {
                $this->volumeCredits += floor($perf['audience'] / 5);
            }

            // exibe a linha para esta requisição
            $this->result .= "    " .
                $play['name'].": " .
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
