<?php
/**
 * FuzzyNumber
 *
 * Author:  Chukwuemeka Nwobodo (jcnwobodo@gmail.com)
 * Date:    11/4/2016
 * Time:    6:20 PM
 **/

namespace NcJoes\FuzzyNumber;

class FuzzyNumber implements \Serializable, \JsonSerializable
{
    private $l;
    private $m;
    private $u;

    public function __construct(array $array)
    {
        if (self::checkIfTriple($array)) {
            $this->l = $array[0];
            $this->m = $array[1];
            $this->u = $array[2];

            return $this;
        }

        throw  new self::$E("Not a triple: ".print_r($array));
    }

    public function __set($name, $value)
    {
        throw new self::$E("Can not mutate a FuzzyNumber, create a new object instead");
    }

    public function __toString()
    {
        return json_encode($this->triple());
    }

    public function serialize()
    {
        return $this->__toString();
    }

    public function unserialize($serialized)
    {
        $phpJsonObject = json_decode($serialized);
        $this->l = $phpJsonObject[0];
        $this->m = $phpJsonObject[1];
        $this->u = $phpJsonObject[2];
    }

    function jsonSerialize()
    {
        return $this->triple();
    }

    public function L() { return $this->l; }

    public function M() { return $this->m; }

    public function U() { return $this->u; }

    public function triple() { return [$this->l, $this->m, $this->u]; }

    public function isTFN()
    {
        return self::checkIfTFN($this->triple());
    }

    public function add(self $fuzzyNumber)
    {
        return self::sum($this, $fuzzyNumber);
    }

    public function subtract(self $fuzzyNumber)
    {
        return self::diff($this, $fuzzyNumber);
    }

    public function multiply(self $fuzzyNumber)
    {
        return self::product($this, $fuzzyNumber);
    }

    public function divide(self $fuzzyNumber)
    {
        return self::divider($this, $fuzzyNumber);
    }

    public function reciprocal($dp = 3)
    {
        return self::invert($this, $dp);
    }

    public function defuzzify($dp = 3)
    {
        $sum = array_sum($this->triple());

        return $sum ? round($sum / 3, $dp) : $sum;
    }

    public static function sum(self $fzn1, self $fzn2)
    {
        return new self([
            $fzn1->L() + $fzn2->L(),
            $fzn1->M() + $fzn2->M(),
            $fzn1->U() + $fzn2->U()
        ]);
    }

    public static function diff(self $fzn1, self $fzn2)
    {
        return new self([
            $fzn1->L() - $fzn2->L(),
            $fzn1->M() - $fzn2->M(),
            $fzn1->U() - $fzn2->U()
        ]);
    }

    public static function product(self $fzn1, self $fzn2)
    {
        return new self([
            $fzn1->L() * $fzn2->L(),
            $fzn1->M() * $fzn2->M(),
            $fzn1->U() * $fzn2->U()
        ]);
    }

    public static function divider(self $fzn1, self $fzn2)
    {
        return new self([
            $fzn1->L() / $fzn2->L(),
            $fzn1->M() / $fzn2->M(),
            $fzn1->U() / $fzn2->U()
        ]);
    }

    public static function invert(self $fuzzyNumber, $dp)
    {
        return new self([
            round(1 / $fuzzyNumber->U(), $dp),
            round(1 / $fuzzyNumber->M(), $dp),
            round(1 / $fuzzyNumber->L(), $dp)
        ]);
    }

    public static function geometricMean(array $fuzzyNumbers, $dp = 3)
    {
        if (self::checkIfMassActionable($fuzzyNumbers)) {
            return new self([
                self::GM(self::getL($fuzzyNumbers), $dp),
                self::GM(self::getM($fuzzyNumbers), $dp),
                self::GM(self::getU($fuzzyNumbers), $dp)
            ]);
        }

        throw new self::$E("Array -{fuzzyNumbers}- must contain 2 or more FuzzyNumbers only \n".print_r($fuzzyNumbers, true));
    }

    public static function addMany(array $fuzzyNumbers)
    {
        return self::massAction($fuzzyNumbers, 'sum');
    }

    public static function multiplyMany(array $fuzzyNumbers)
    {
        return self::massAction($fuzzyNumbers, 'product');
    }

    public static function getL(array $fuzzyNumbers)
    {
        return self::getKey($fuzzyNumbers, 'L');
    }

    public static function getM(array $fuzzyNumbers)
    {
        return self::getKey($fuzzyNumbers, 'M');
    }

    public static function getU(array $fuzzyNumbers)
    {
        return self::getKey($fuzzyNumbers, 'U');
    }

    public static function checkIfTriple(array $array)
    {
        return (isset($array[0]) and isset($array[1]) and isset($array[2]) and sizeof($array) === 3);
    }

    public static function checkIfTFN(array $arr)
    {
        return self::checkIfTriple($arr) and ($arr[0] !== $arr[1] and $arr[0] !== $arr[2] and $arr[1] !== $arr[2]);
    }

    protected static function checkIfMassActionable(array& $fuzzyNumbers, $min = 2)
    {
        if (count($fuzzyNumbers) >= $min - 1) {
            foreach ($fuzzyNumbers as $key => $item) {
                if (is_array($item) and self::checkIfTriple($item)) {
                    $fuzzyNumbers[ $key ] = new self($item);
                    continue;
                }
                if (is_object($item)) {
                    $class = get_class($item);
                    if (in_array(self::class, class_parents($class)) or $class === self::class)
                        continue;
                }

                return false;
            }

            return true;
        }

        return false;
    }

    protected static function getKey(array $fuzzyNumbers, $K)
    {
        $R = [];
        if (self::checkIfMassActionable($fuzzyNumbers, 2)) {
            foreach ($fuzzyNumbers as $fuzzyNumber) {
                array_push($R, $fuzzyNumber->$K());
            }
        }

        return $R;
    }

    protected static function GM(array $array, $dp)
    {
        $mul = $array[0];
        foreach ($array as $i => $n) {
            $mul = $i === 0 ? $n : $mul * $n;
        }

        return round(pow($mul, 1 / count($array)), $dp);
    }

    protected static function massAction(array $fuzzyNumbers, $method)
    {
        if (self::checkIfMassActionable($fuzzyNumbers)) {
            $keys = array_keys($fuzzyNumbers);
            $result = $fuzzyNumbers[ $keys[0] ];
            foreach ($fuzzyNumbers as $fuzzyNumber) {
                $result = self::$method($result, $fuzzyNumber);
            }

            return $result;
        }

        throw new self::$E("Array -{fuzzyNumbers}- must contain 2 or more FuzzyNumbers");
    }

    public static $E = \InvalidArgumentException::class;
}
