<?php
/**
 * The Random Number Generator Class
 *
 * Use this factory to generate cryptographic quality random numbers (strings)
 *
 * PHP version 5.3
 *
 * @category   PHPPasswordLib
 * @package    Random
 * @author     Anthony Ferrara <ircmaxell@ircmaxell.com>
 * @author     Timo Hamina
 * @copyright  2011 The Authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    Build @@version@@
 */

namespace RandomLib;

use SecurityLib\BaseConverter;

/**
 * The Random Number Generator Class
 *
 * Use this factory to generate cryptographic quality random numbers (strings)
 *
 * @category   PHPPasswordLib
 * @package    Random
 * @author     Anthony Ferrara <ircmaxell@ircmaxell.com>
 * @author     Timo Hamina
 */
class Generator {

    /**
     * @const UPPER_CASE Flag for upper-case letters
     */
    const UPPER_CASE = 1;

    /**
     * @const LOWER_CASE Flag for loser-case letters
     */
    const LOWER_CASE = 2;

    /**
     * @const DIGITS Flag for digits
     */
    const DIGITS = 4;

    /**
     * @const UPPER_HEX Flag for upper-case hexadecimal symbols
     */
    const UPPER_HEX = 8;

    /**
     * @const LOWER_HEX Flag for lower-case hexidecimal symbols
     */
    const LOWER_HEX = 16;

    /**
     * @const BASE64 Flag for base64 symbols
     */
    const BASE64 = 32;

    /**
     * @const EASY_TO_READ Flag for upper/lower-case and digits but without "B8G6I1l|0OQDS5Z2"
     */
    const EASY_TO_READ = 64;

    /**
     * @const SYMBOLS Flag for additional symbols accessible via the keyboard
     */
    const SYMBOLS = 128;

    /**
     * @const BRACKETS Flag for brackets
     */
    const BRACKETS = 256;

    /**
     * @const PUNCT Flag for punctuation marks
     */
    const PUNCT = 512;

    /**
     * @const Flag for higher ANSI characters (symbols #127 to #255)
     */
    const HIGH = 1024;

    /**
     * @var Mixer The mixing strategy to use for this generator instance
     */
    protected $mixer = null;

    /**
     * @var array An array of random number sources to use for this generator
     */
    protected $sources = array();

    /**
     * @var charArrays Holds the different character sets by Flag
     */
    private $charArrays = array(
        self::UPPER_CASE => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        self::LOWER_CASE => 'abcdefghijklmnopqrstuvwxyz',
        self::DIGITS => '0123456789',
        self::SYMBOLS => '!"#$%&\'()* +,-./:;<=>?@[\]^_`{|}~',
        self::BRACKETS => '()[]{}<>',
        self::PUNCT => ',.;:',
        self::HIGH => ':)'
    );

    /**
     * @var charArrays Holds the different character sets by Flag
     */
    private $ambiguous = 'B8G6I1l|0OQDS5Z2';

    /**
     * Build a new instance of the generator
     *
     * @param array $sources An array of random data sources to use
     * @param Mixer $mixer   The mixing strategy to use for this generator
     */
    public function __construct(array $sources, Mixer $mixer) {
        foreach ($sources as $source) {
            $this->addSource($source);
        }
        $this->mixer = $mixer;
				
        $this->charArrays[self::UPPER_HEX] = $this->charArrays[self::DIGITS] . 'ABCDEF';
        $this->charArrays[self::LOWER_HEX] = $this->charArrays[self::DIGITS] . 'abcdef';
        $this->charArrays[self::EASY_TO_READ] = $this->charArrays[self::UPPER_CASE] .
            $this->charArrays[self::LOWER_CASE] . $this->charArrays[self::DIGITS];
        $this->charArrays[self::BASE64] = $this->charArrays[self::EASY_TO_READ] . '+/';
        $this->charArrays[self::HIGH] = array_reduce(
            range(127, 255),
            function ($result, $item) {
                return $result .= chr($item);
            },
            ''
        );
    }

    /**
     * Add a random number source to the generator
     *
     * @param Source $source The random number source to add
     *
     * @return Generator $this The current generator instance
     */
    public function addSource(Source $source) {
        $this->sources[] = $source;
        return $this;
    }

    /**
     * Generate a random number (string) of the requested size
     *
     * @param int $size The size of the requested random number
     *
     * @return string The generated random number (string)
     */
    public function generate($size) {
        $seeds = array();
        foreach ($this->sources as $source) {
            $seeds[] = $source->generate($size);
        }
        return $this->mixer->mix($seeds);
    }

    /**
     * Generate a random integer with the given range
     *
     * @param int $min The lower bound of the range to generate
     * @param int $max The upper bound of the range to generate
     *
     * @return int The generated random number within the range
     */
    public function generateInt($min = 0, $max = PHP_INT_MAX) {
        $tmp   = (int) max($max, $min);
        $min   = (int) min($max, $min);
        $max   = $tmp;
        $range = $max - $min;
        if ($range == 0) {
            return $max;
        } elseif ($range > PHP_INT_MAX || is_float($range)) {
            /**
             * This works, because PHP will auto-convert it to a float at this point,
             * But on 64 bit systems, the float won't have enough precision to
             * actually store the difference, so we need to check if it's a float
             * and hence auto-converted...
             */
            throw new \RangeException(
                'The supplied range is too great to generate'
            );
        }

        $bits  = $this->countBits($range) + 1;
        $bytes = (int) max(ceil($bits / 8), 1);
        $mask  = (int) (pow(2, $bits) - 1);
        /**
         * The mask is a better way of dropping unused bits.  Basically what it does
         * is to set all the bits in the mask to 1 that we may need.  Since the max
         * range is PHP_INT_MAX, we will never need negative numbers (which would
         * have the MSB set on the max int possible to generate).  Therefore we
         * can just mask that away.  Since pow returns a float, we need to cast
         * it back to an int so the mask will work.
         *
         * On a 64 bit platform, that means that PHP_INT_MAX is 2^63 - 1.  Which
         * is also the mask if 63 bits are needed (by the log(range, 2) call).
         * So if the computed result is negative (meaning the 64th bit is set), the
         * mask will correct that.
         *
         * This turns out to be slightly better than the shift as we don't need to
         * worry about "fixing" negative values.
         */
        do {
            $test   = $this->generate($bytes);
            $result = hexdec(bin2hex($test)) & $mask;
        } while ($result > $range);
        return $result + $min;
    }

    /**
     * Generate a random string of specified length.
     *
     * This uses the supplied character list for generating the new result
     * string.
     *
     * @param int    $length     The length of the generated string
     * @param mixed  $characters String: An optional list of characters to use
     *                           Integer: Character flags
     *
     * @return string The generated random string
     */
    public function generateString($length, $characters = '') {
        if (is_int($characters)) {
            $tmp_char = '';
            foreach ($this->charArrays as $flag => $chars) {
                if ($characters & $flag) {
                    $tmp_char .= $chars;
                }
            }

            $tmp_char_len = strlen($tmp_char);
            $tmp_char_array = array();
            for ($i=0; $i < $tmp_char_len; $i++) {
                if ($characters & self::EASY_TO_READ AND
                    strpos($this->ambiguous, $i) !== false) continue;
                $tmp_char_array[$tmp_char[$i]] = null;
            }

            $characters = implode('', array_keys($tmp_char_array));
        } elseif ($length == 0 || strlen($characters) == 1) {
            return '';
        }	elseif (empty($characters)) {
            // Default to base 64
            $characters = $this->charArrays[self::BASE64];
        }
        // determine how many bytes to generate
        // This is basically doing floor(log(strlen($characters)))
        // But it's fixed to work properly for all numbers
        $len   = strlen($characters);
        $bytes = ceil($length * ($this->countBits($len) + 1) / 8);

        // determine mask for valid characters
        $mask   = 255 - (255 % $len);
        $result = '';
        do {
            $rand = $this->generate($bytes);
            for ($i = 0; $i < $bytes; $i++) {
                if (ord($rand[$i]) > $mask) {
                    continue;
                }
                $result .= $characters[ord($rand[$i]) % $len];
            }
        } while (strlen($result) < $length);
        // We may over-generate, since we always use the entire buffer
        return substr($result, 0, $length);
    }

    /**
     * Get the Mixer used for this instance
     *
     * @return Mixer the current mixer
     */
    public function getMixer() {
        return $this->mixer;
    }

    /**
     * Get the Sources used for this instance
     *
     * @return Source[] the current mixer
     */
    public function getSources() {
        return $this->sources;
    }

    /**
     * Count the minimum number of bits to represent the provided number
     *
     * This is basically floor(log($number, 2))
     * But avoids float precision issues
     *
     * @param int $number The number to count
     *
     * @return int The number of bits
     */
    protected function countBits($number) {
        $log2 = 0;
        while ($number >>= 1) {
            $log2++;
        }
        return $log2;
    }

}
