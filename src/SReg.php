<?php

namespace gorriecoe\sreg;

use SilverStripe\ORM\DataExtension;

/**
 * @package silverstripe-sreg
 */
class SReg extends DataExtension
{
    public function sreg($string)
    {
        $string = $this->sregValue($this->sregTokenizer($string));
        if (trim($string) != '') {
            return $string;
        }
    }

    /**
     * Simple syntax tokenizer that allows you to use .ss template like variables in a string
     * e.g 'Lorem ipsum {$Relation.Title}'
     * @param string $string
     * @return string
     */
    private function sregTokenizer($value = '')
    {
        return preg_replace_callback(
            '/\{\$([\w\d\s.|]*)\}/',
            function ($matches)
            {
                return $this->sregValue($matches[1]);
            },
            $value
        );
    }

    /**
     * Splits string by |, loops each segment and returns the first non-null value
     * @param string $value
     * @param boolean $allowNoMatch
     * @return string|null
     */
    private function sregValue($value = '')
    {
        $owner = $this->owner;
        $values = explode('|', $value);
        $count = count($values);
        foreach ($values as $key => $value) {
            if ($value == '' || $value == 'null') {
                return null;
            } elseif ($relValue = $owner->relField($value)) {
                if (is_object($relValue) && method_exists($relValue, 'AbsoluteLink')) {
                    if ($link = $relValue->AbsoluteLink()) {
                        return $link;
                    } else {
                        continue;
                    }
                } else {
                    return $relValue;
                }
            } elseif ($key + 1 == $count && !$owner->hasMethod($value) ) {
                return $value;
            }
        }
    }
}
