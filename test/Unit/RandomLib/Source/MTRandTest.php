<?php

namespace RandomLib\Source;

use SecurityLib\Strength;

class MTRandTest extends AbstractSourceTest {

    protected static function getExpectedStrength() {
        if (defined('S_ALL')) {
            return new Strength(Strength::MEDIUM);
        } else {
            return new Strength(Strength::LOW);
        }
    }

}
