<?php

namespace App\Calculator;

use Exception;

class PerformanceCalculator
{
    public function __construct(
        public $aPerformance,
        public $aPlay,
    ) {
        # code...
    }

    public function getPlay()
    {
        return $this->aPlay;
    }

    public function getAmount()
    {
        $result = 0;

        switch ($this->aPlay['type']) {
            case 'tragedy':
                $result = 40000;
                if ($this->aPerformance['audience'] > 30) {
                    $result += 1000 * ($this->aPerformance['audience'] - 30);
                }
                break;
            case 'comedy':
                $result = 30000;
                if ($this->aPerformance['audience'] > 20) {
                    $result += 10000 + 500 * ($this->aPerformance['audience'] - 20);
                }
                $result += 300 * $this->aPerformance['audience'];
                break;
            default:
                throw new Exception('Unknow type: ' . $this->aPlay['type'], 1);
        };

        return $result;
    }
}
