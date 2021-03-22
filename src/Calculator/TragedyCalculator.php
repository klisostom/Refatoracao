<?php

namespace App\Calculator;

class TragedyCalculator extends PerformanceCalculator
{

    public function getAmount()
    {
        $result = 40000;

        if ($this->aPerformance['audience'] > 30) {
           $result += 1000 * ($this->aPerformance['audience'] - 30);
        }

        return $result;
    }
}
