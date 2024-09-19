<?php

namespace App\Service;

class Calculator
{
    public function cToF(float|array $celsius): float|array
    {
        if (is_array($celsius)) {
            $result = [];
            foreach ($celsius as $c) {
                $result[] = $this->cToF($c);
            }
            return $result;
        }
        return $celsius * 9 / 5 + 32;
    }
}

