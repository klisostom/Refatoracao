<?php

namespace App;

use \NumberFormatter;
use App\CreateStatementData;

require __DIR__ . '/../src/CreateStatementData.php';

class Statement
{
    protected CreateStatementData $statementData;

    public function __construct()
    {
        $this->statementData = new CreateStatementData();
    }

    public function statement(array $invoice, array $plays)
    {
        return $this->renderPlainText($this->statementData->createStatementData($invoice, $plays));
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
