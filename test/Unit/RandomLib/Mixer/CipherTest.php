<?php

namespace RandomLib\Mixer;

use SecurityLib\Strength;

class CipherTest extends \PHPUnit_Framework_TestCase {

    public static function provideMix() {
        $data = array(
            array(array(), ''),
            array(array('1', '1'), 'f9'),
            array(array('a'), '61'),
            array(array('a', 'b'), '70'),
            array(array('aa', 'ba'), 'b1b6'),
            array(array('ab', 'bb'), '40d2'),
            array(array('aa', 'bb'), 'ebb3'),
            array(array('aa', 'bb', 'cc'), '08da'),
            array(array('aabbcc', 'bbccdd', 'ccddee'), '7bf55cd5e9c9'),
        );
        return $data;
    }

    protected function setup() {
        if (!extension_loaded("mcrypt")) {
            $this->markTestSkipped("The mcrypt extension is not available");
        }
    }

    public function testConstructWithoutArgument() {
        $cipher = new Cipher;
        $this->assertTrue($cipher instanceof \RandomLib\Mixer);
    }

    public function testGetStrength() {
        $strength = new Strength(Strength::HIGH);
        $actual = Cipher::getStrength();
        $this->assertEquals($actual, $strength);
    }

    public function testTest() {
        $actual = Cipher::test();
        $this->assertTrue($actual);
    }

    /**
     * @dataProvider provideMix
     */
    public function testMix($parts, $result) {
        $mixer = new Cipher();
        $actual = $mixer->mix($parts);
        $this->assertEquals($result, bin2hex($actual));
    }


}
