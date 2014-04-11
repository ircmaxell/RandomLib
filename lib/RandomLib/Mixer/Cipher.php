<?php
/**
 * The Cipher high strength mixer class
 *
 * This class implements a mixer based upon the recommendations in RFC 4086
 * section 5.2
 *
 * PHP version 5.3
 *
 * @see        http://tools.ietf.org/html/rfc4086#section-5.2
 * @category   PHPCryptLib
 * @package    Random
 * @subpackage Mixer
 * @author     Anthony Ferrara <ircmaxell@ircmaxell.com>
 * @copyright  2011 The Authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    Build @@version@@
 */

namespace RandomLib\Mixer;

use \SecurityLib\Strength;

/**
 * The Cipher based high strength mixer class
 *
 * This class implements a mixer based upon the recommendations in RFC 4086
 * section 5.2
 *
 * @see        http://tools.ietf.org/html/rfc4086#section-5.2
 * @category   PHPCryptLib
 * @package    Random
 * @subpackage Mixer
 * @author     Anthony Ferrara <ircmaxell@ircmaxell.com>
 */
class Cipher extends \RandomLib\AbstractMixer {
    const CIPHER = "rijndael-128";
    const MODE = "ecb";

    /**
     * Build the cipher mixer
     *
     * @return void
     * @throws RuntimeException If the class is used without mcrypt support
     */
    public function __construct() {
        if (!static::test()) {
            throw new \RuntimeException("Attempting to load an unsupported mixer");
        }
    }

    /**
     * Return an instance of Strength indicating the strength of the source
     *
     * @return Strength An instance of one of the strength classes
     */
    public static function getStrength() {
        return new Strength(Strength::HIGH);
    }

    /**
     * Test to see if the mixer is available
     *
     * @return boolean If the mixer is available on the system
     */
    public static function test() {
        return extension_loaded("mcrypt") && function_exists("mcrypt_generic_init");
    }

    /**
     * Get the block size (the size of the individual blocks used for the mixing)
     *
     * @return int The block size
     */
    protected function getPartSize() {
        return mcrypt_get_block_size(static::CIPHER, static::MODE);
    }

    /**
     * Mix 2 parts together using one method
     *
     * This encrypts one of the parts using a single run of Rijndael-128
     * ECB is fine here, since we're not cascading state and both the key
     * and value are unknown, so neither are being protected. We are instead
     * leveraging the confusion and diffusion properties of Rijndael-128
     *
     * @param string $part1 The first part to mix
     * @param string $part2 The second part to mix
     *
     * @return string The mixed data
     */
    protected function mixParts1($part1, $part2) {
        return mcrypt_encrypt(static::CIPHER, $part1, $part2, static::MODE);
    }

    /**
     * Mix 2 parts together using another different method
     *
     * @param string $part1 The first part to mix
     * @param string $part2 The second part to mix
     *
     *
     * @see self::mixParts1
     * @return string The mixed data
     */
    protected function mixParts2($part1, $part2) {
        return mcrypt_decrypt(static::CIPHER, $part2, $part1, static::MODE);
    }

}
