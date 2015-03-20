<?php
namespace RandomLib;

class UtilTest extends \PHPUnit_Framework_TestCase {

    public function testSafeStrlen() {
        $this->assertEquals(Util::safeStrlen("\x03\x3f"), 2);
        ini_set('mbstring.func_overload', 7);
        $this->assertEquals(Util::safeStrlen("\x03\x3f"), 2);
        ini_set('mbstring.func_overload', 0);
    }

    public function testSafeSubstr() {
        $a = "abcdefg\x03\x3fhijk";
        $b = "\x03\x3f";
        $this->assertEquals(Util::safeSubstr($a, 7, 2), $b);
        
        ini_set('mbstring.func_overload', 7);
        $a = "abcdefg\x03\x3fhijk";
        $b = "\x03\x3f";
        $this->assertEquals(Util::safeSubstr($a, 7, 2), $b);
        ini_set('mbstring.func_overload', 0);
    }
}
