<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Get metric stats unit tests
 *
 * @package     tool_nla
 * @category    phpunit
 * @copyright   2017 Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use tool_nla\analyze\analyze;

class tool_nla_get_stats_testcase extends advanced_testcase {

    /**
     * Test get_stats method.
     */
    public function test_get_stats_min() {
        $metric = [7, 8, 9, 1, 2, 2, 3, 3, 3, 4, 5, 6];
        $analyzer = new analyze();
        list(
                $minimum,
                $maximum,
                $mean,
                $median,
                $lowerq,
                $upperq,
                $interquartilerange
             ) = $analyzer->get_stats($metric);

             $this->assertEquals(1, $minimum);
    }

    /**
     * Test get_stats method.
     */
    public function test_get_stats_max() {
        $metric = [7, 8, 9, 1, 2, 2, 3, 3, 3, 4, 5, 6];
        $analyzer = new analyze();
        list(
                $minimum,
                $maximum,
                $mean,
                $median,
                $lowerq,
                $upperq,
                $interquartilerange
                ) = $analyzer->get_stats($metric);

                $this->assertEquals(9, $maximum);
    }

    /**
     * Test get_stats method.
     */
    public function test_get_stats_mean() {
        $metric = [7, 8, 9, 1, 2, 2, 3, 3, 3, 4, 5, 6];
        $analyzer = new analyze();
        list(
                $minimum,
                $maximum,
                $mean,
                $median,
                $lowerq,
                $upperq,
                $interquartilerange
                ) = $analyzer->get_stats($metric);

                $this->assertEquals(4.417, $mean);
    }

    /**
     * Test get_stats method.
     */
    public function test_get_stats_median_odd() {
        $metric = [4, 3, 1, 2, 5];
        $analyzer = new analyze();
        list(
                $minimum,
                $maximum,
                $mean,
                $median,
                $lowerq,
                $upperq,
                $interquartilerange
                ) = $analyzer->get_stats($metric);

                $this->assertEquals(3, $median);
    }

    /**
     * Test get_stats method.
     */
    public function test_get_stats_median_even() {
        $metric = [4, 3, 1, 2, 5, 3];
        $analyzer = new analyze();
        list(
                $minimum,
                $maximum,
                $mean,
                $median,
                $lowerq,
                $upperq,
                $interquartilerange
                ) = $analyzer->get_stats($metric);

                $this->assertEquals(3, $median);
    }

    /**
     * Test get_stats method.
     */
    public function test_get_stats_lowerq_even() {
        $metric = [18, 20, 23, 20, 23, 27, 24, 23, 29];
        $analyzer = new analyze();
        list(
                $minimum,
                $maximum,
                $mean,
                $median,
                $lowerq,
                $upperq,
                $interquartilerange
                ) = $analyzer->get_stats($metric);

                $this->assertEquals(20, $lowerq);
    }

    /**
     * Test get_stats method.
     */
    public function test_get_stats_lowerq_odd() {
        $metric = [11, 4, 6, 8, 3, 10, 8, 10, 4, 12, 31];
        $analyzer = new analyze();
        list(
                $minimum,
                $maximum,
                $mean,
                $median,
                $lowerq,
                $upperq,
                $interquartilerange
                ) = $analyzer->get_stats($metric);

                $this->assertEquals(4, $lowerq);
    }

    /**
     * Test get_stats method.
     */
    public function test_get_stats_upperq_even() {
        $metric = [18, 20, 23, 20, 23, 27, 24, 23, 29];
        $analyzer = new analyze();
        list(
                $minimum,
                $maximum,
                $mean,
                $median,
                $lowerq,
                $upperq,
                $interquartilerange
                ) = $analyzer->get_stats($metric);

                $this->assertEquals(25.5, $upperq);
    }

    /**
     * Test get_stats method.
     */
    public function test_get_stats_upperq_odd() {
        $metric = [11, 4, 6, 8, 3, 10, 8, 10, 4, 12, 31];
        $analyzer = new analyze();
        list(
                $minimum,
                $maximum,
                $mean,
                $median,
                $lowerq,
                $upperq,
                $interquartilerange
                ) = $analyzer->get_stats($metric);

                $this->assertEquals(11, $upperq);
    }

    /**
     * Test get_stats method.
     */
    public function test_get_stats_interq_even() {
        $metric = [18, 20, 23, 20, 23, 27, 24, 23, 29];
        $analyzer = new analyze();
        list(
                $minimum,
                $maximum,
                $mean,
                $median,
                $lowerq,
                $upperq,
                $interquartilerange
                ) = $analyzer->get_stats($metric);

                $this->assertEquals(5.5, $interquartilerange);
    }

    /**
     * Test get_stats method.
     */
    public function test_get_stats_interq_odd() {
        $metric = [11, 4, 6, 8, 3, 10, 8, 10, 4, 12, 31];
        $analyzer = new analyze();
        list(
                $minimum,
                $maximum,
                $mean,
                $median,
                $lowerq,
                $upperq,
                $interquartilerange
                ) = $analyzer->get_stats($metric);

                $this->assertEquals(7, $interquartilerange);
    }
}
