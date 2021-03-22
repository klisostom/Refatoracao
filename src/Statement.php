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

    public function usd($aNumber)
    {
        $result = new NumberFormatter(
            'en_US',
            NumberFormatter::CURRENCY,
        );

        return $result->format($aNumber / 100);
    }

    public function statement(array $invoice, array $plays)
    {
        return $this->renderPlainText($this->statementData->createStatementData($invoice, $plays));
    }

    public function renderPlainText(array $data): string
    {
        $result = "\nStatement for ".$data['customer']."\n";

        foreach ($data['performances'] as $perf) {
            // exibe a linha para esta requisição
            $result .= "    " .
                $perf['play']['name'].": " .
                $this->usd($perf['amount']) .
                " (" . $perf['audience'] . " seats)\n";
        }

        $result .= "Amount owed is " . $this->usd($data['totalAmount']) . "\n";
        $result .= "You earned " . $data['totalVolumeCredits'] . " credits\n";
        return $result;
    }

    public function htmlStatement(array $invoice, array $plays)
    {
        return $this->renderHtml($this->statementData->createStatementData($invoice, $plays));
    }

    public function renderHtml(array $data)
    {
        $result = "<h1>Statement for " . $data['customer'] . "</h1><br>";
        $result .= "<table><br>";
        $result .= "<tr><th>play</th><th>seats</th><th>cost</th></tr>";

        foreach ($data['performances'] as $perf) {
            $result .= " <tr><td>" . $perf['play']['name'] . "</td><td>" . $perf['audience'] . "</td>";
            $result .= "<td>" . $this->usd($perf['amount']) . "</td></tr><br>";
        }

        $result .= "</table>";
        $result .= "Amount owed is " . $this->usd($data['totalAmount']) . "\n";
        $result .= "You earned " . $data['totalVolumeCredits'] . " credits\n";
        return $result;
    }
}
