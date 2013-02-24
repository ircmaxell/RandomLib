<?php
/**
 * The MTRand Random Number Source
 *
 * This source generates low strength random numbers by using the internal
 * mt_rand() function.  By itself it is quite weak.  However when combined with
 * other sources it does provide significant benefit.
 *
 * PHP version 5.3
 *
 * @category   PHPCryptLib
 * @package    Random
 * @subpackage Source
 * @author     Anthony Ferrara <ircmaxell@ircmaxell.com>
 * @copyright  2011 The Authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    Build @@version@@
 */

namespace RandomLib\Source;

use SecurityLib\Strength;

/**
 * The ExecTime Random Number Source
 *
 * This source generates low strength random numbers by measuring the execution
 * time to perform a sequence of SHA1 computations seeded by mt_rand().
 *
 * @category   PHPCryptLib
 * @package    Random
 * @subpackage Source
 * @author     Anthony Ferrara <ircmaxell@ircmaxell.com>
 * @codeCoverageIgnore
 */
class ExecTime implements \RandomLib\Source {

    /**
     * Return an instance of Strength indicating the strength of the source
     *
     * @return Strength An instance of one of the strength classes
     */
    public static function getStrength() {
        return new Strength(Strength::LOW);
    }

    /**
     * Generate a random string of the specified size
     *
     * @param int $size The size of the requested random string
     *
     * @return string A string of the requested size
     */
    public function generate($size) {
        $result = '';
        $entropy = '';
        $msec_per_round = 400;
        $bits_per_round = 2;
        $total = $size;
        $bytes = 0;
        $hash_length = 20;
        $rounds = 0;
        while (strlen($result) < $size) {
            $bytes = ($total > $hash_length)? $hash_length : $total;
            $total -= $bytes;
            for ($i=1; $i < 3; $i++) {
                $t1 = microtime(true);
                $seed = mt_rand();
                for ($j=1; $j < 50; $j++) { 
                    $seed = sha1($seed);
                }
                $t2 = microtime(true);
                $entropy .= $t1 . $t2;
            }
            $rounds = (int) ($msec_per_round * 50 / (int) (($t2 - $t1) * 1000000));
            $iter = $bytes * (int) (ceil(8 / $bits_per_round));
            for ($i = 0; $i < $iter; $i ++)
            {
                $t1 = microtime();
                $seed = sha1(mt_rand());
                for ($j = 0; $j < $rounds; $j++)
                {
                   $seed = sha1($seed);
                }
                $t2 = microtime();
                $entropy .= $t1 . $t2;
            }
            $result .= sha1($entropy, true);
        }
        return substr($result, 0, $size);
    }

}
