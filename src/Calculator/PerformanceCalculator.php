<?php

namespace App\Calculator;

use Error;
use Exception;

class PerformanceCalculator
{
    public function __construct(
        protected $aPerformance,
        protected $aPlay,
    ) {
        # code...
    }

    public function getPlay()
    {
        return $this->aPlay;
    }

    public function getAmount()
    {
        throw new Error('Subclass responsibility');
    }

    public function getVolumeCredits()
    {
        return max($this->aPerformance['audience'] - 30, 0);
    }
}
