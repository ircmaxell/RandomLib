<?php

/*
 * The RandomLib library for securely generating random numbers and strings in PHP
 *
 * @author     Anthony Ferrara <ircmaxell@ircmaxell.com>
 * @copyright  2011 The Authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    Build @@version@@
 */
namespace RandomLib\Mixer;

use SecurityLib\Strength;

class XorMixerTest extends \PHPUnit_Framework_TestCase
{
    public static function provideMix()
    {
        $data = array(
            array(array(), ''),
            array(array('1', '1'), '00'),
            array(array('a'), '61'),
            array(array('a', 'b'), '03'),
            array(array('aa', 'ba'), '0300'),
            array(array('ab', 'bb'), '0300'),
            array(array('aa', 'bb'), '0303'),
            array(array('aa', 'bb', 'cc'), '6060'),
            array(array('aabbcc', 'bbccdd', 'ccddee'), '606065656262'),
        );

        return $data;
    }

    public function testConstructWithoutArgument()
    {
        $xorMixer = new XorMixer();
        $this->assertTrue($xorMixer instanceof \RandomLib\Mixer);
    }

    public function testGetStrength()
    {
        $strength = new Strength(Strength::VERYLOW);
        $actual = XorMixer::getStrength();
        $this->assertEquals($actual, $strength);
    }

    public function testTest()
    {
        $actual = XorMixer::test();
        $this->assertTrue($actual);
    }

    /**
     * @dataProvider provideMix
     */
    public function testMix($parts, $result)
    {
        $mixer = new XorMixer();
        $actual = $mixer->mix($parts);
        $this->assertSame($result, bin2hex($actual));
    }
}
