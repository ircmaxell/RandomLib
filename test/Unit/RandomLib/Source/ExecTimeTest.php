<?php

namespace RandomLib\Source;

use SecurityLib\Strength;



class ExecTimeTest extends \PHPUnit_Framework_TestCase {

    public static function provideGenerate() {
        $data = array();
        for ($i = 0; $i < 10; $i += 5) {
            $not = $i > 0 ? str_repeat(chr(0), $i) : chr(0);
            $data[] = array($i, $not);
        }
        return $data;
    }

    /**
     */
    public function testGetStrength() {
        $strength = new Strength(Strength::LOW);
        $actual = ExecTime::getStrength();
        $this->assertEquals($actual, $strength);
    }

    /**
     * @dataProvider provideGenerate
     */
    public function testGenerate($length, $not) {
        $rand = new ExecTime;
        $stub = $rand->generate($length);
        $this->assertEquals($length, strlen($stub));
        $this->assertNotEquals($not, $stub);
    }

}
