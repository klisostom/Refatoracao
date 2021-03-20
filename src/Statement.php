<?php

namespace App;

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

    public function amountFor($perf, $play)
    {
        $thisAmount = 0;

        switch ($play['type']) {
            case 'tragedy':
                $thisAmount = 40000;
                if ($perf['audience'] > 30) {
                    $thisAmount += 1000 * ($perf['audience'] - 30);
                }
                break;
            case 'comedy':
                $thisAmount = 30000;
                if ($perf['audience'] > 20) {
                    $thisAmount += 10000 + 500 * ($perf['audience'] - 20);
                }
                $thisAmount += 300 * $perf['audience'];
                break;
            default:
                throw new Exception('Unknow type: ' . $play['type'], 1);
        };

        return $thisAmount;
    }

    public function statement($invoice, array $plays): string
    {
        $this->result = "\nStatement for ".$invoice['customer']."\n";

        foreach ($invoice['performances'] as $perf) {
            $play = $plays[$perf['playID']];
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
