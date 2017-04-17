<?php

/*
 * (c) Mark Badolato <mbadolato@gmail.com>
 *
 * This content is released under the {@link http://www.opensource.org/licenses/MIT MIT License.}
 */
 
namespace Bado\ScoreCalculator;

/**
 * Calculate a score based on a Wilson Confidence Interval
 *
 * Based on concepts discussed at @link http://www.evanmiller.org/how-not-to-sort-by-average-rating.html
 */
class Wilsoncalcu
{
    /**
     * Computed value for confidence (z)
     *
     * These values were computed using Ruby's Statistics2.pnormaldist function
     * 1.959964 = 95.0% confidence
     * 2.241403 = 97.5% confidence
     */
    const CONFIDENCE = 2.241403;

    public function getScore($positiveVotes, $totalVotes, $confidence = self::CONFIDENCE)
    {
        return $totalVotes ? $this->lowerBound($positiveVotes, $totalVotes, $confidence) : 0;
    }

    private function lowerBound($positiveVotes, $totalVotes, $confidence)
    {
        $phat           = 1.0 * $positiveVotes / $totalVotes;
        $numerator      = $this->calculationNumerator($totalVotes, $confidence, $phat);
        $denominator    = $this->calculationDenominator($totalVotes, $confidence);

        return $numerator / $denominator;
    }

    private function calculationDenominator($total, $z)
    {
        return 1 + $z * $z / $total;
    }

    private function calculationNumerator($total, $z, $phat)
    {
        return $phat + $z * $z / (2 * $total) - $z * sqrt(($phat * (1 - $phat) + $z * $z / (4 * $total)) / $total);
    }
}