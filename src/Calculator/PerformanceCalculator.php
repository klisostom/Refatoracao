<?php

namespace App\Calculator;

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
}
