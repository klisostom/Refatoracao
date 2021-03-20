<?php

namespace Tests;

use App\Statement;
use PHPUnit\Framework\TestCase;

require __DIR__ . '/../src/Statement.php';

class StatementTest extends TestCase
{

    public function testStatement()
    {
        // Cenário
        $plays = [
            'hamlet' => ['name' => 'Hamlet', 'type' => 'tragedy'],
            'as-like' => ['name' => 'As You Like It', 'type' => 'comedy'],
            'othello' => ['name' => 'Othello', 'type' => 'tragedy'],
        ];

        $invoice = [
            'customer' => 'BigCo',
            'performances' => [
                [
                    'playID' => 'hamlet',
                    'audience' => 55,
                ],
                [
                    'playID' => 'as-like',
                    'audience' => 35,
                ],
                [
                    'playID' => 'othello',
                    'audience' => 40,
                ],
            ]
        ];

        // Ação
        $statement = new Statement();
        $actual = $statement->statement($invoice, $plays);

        // Asserts
$expected = '
Statement for BigCo
    Hamlet: $650.00 (55 seats)
    As You Like It: $580.00 (35 seats)
    Othello: $500.00 (40 seats)
Amount owed is $1,730.00
You earned 47 credits
';

        $this->assertIsString($actual);
        $this->assertEquals(strlen($expected), strlen($actual));
        $this->assertEquals(preg_split('/\r\n|\r|\n/', $expected), preg_split('/\r\n|\r|\n/', $actual));
    }
}
