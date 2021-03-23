<?php

namespace App\Calculator;

class ComedyCalculator extends PerformanceCalculator
{
    public function getAmount()
    {
        $result = 30000;

        if ($this->aPerformance['audience'] > 20) {
            $result += 10000 + 500 * ($this->aPerformance['audience'] - 20);
        }

        $result += 300 * $this->aPerformance['audience'];
        return $result;
    }

    public function getVolumeCredits() {
        return parent::getVolumeCredits() + floor($this->aPerformance['audience'] / 5);
    }
}
