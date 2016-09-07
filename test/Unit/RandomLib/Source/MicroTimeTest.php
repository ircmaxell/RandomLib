<?php

namespace RandomLib\Source;

use SecurityLib\Strength;

class MicroTimeTest extends AbstractSourceTest {

    protected static function getExpectedStrength() {
        return new Strength(Strength::VERYLOW);
    }

    /** 
     * Test the initialization of the static counter (!== 0)
     */
    public function testCounterNotNull() {
        $class = static::getTestedClass();
        $rand = new $class;
        $reflection_class = new \ReflectionClass($class);
        $static = $reflection_class->getStaticProperties();
        $this->assertTrue($static['counter'] !== 0);
    }

}
