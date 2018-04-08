<?php

namespace gorriecoe\sreg;

use SilverStripe\ORM\DataExtension;

/**
 * @package silverstripe-sreg
 */
class SReg extends DataExtension
{
    /**
     * Simple syntax tokenizer that allows you to use .ss template like variables in a string
     * e.g 'Lorem ipsum {$Relation.Title}'
     * @param string $string
     * @return string|null
     */
    public function sreg($string)
    {
        $owner = $this->owner;
        $string = $this->sregValue(
            preg_replace_callback(
                '/\{\$([\w\d\s.|]*)\}/',
                function ($matches)
                {
                    return $this->sregValue($matches[1]);
                },
                $string
            ),
            true
        );
        if (trim($string) != '') {
            return $string;
        }
    }

    /**
     * Splits string by |, loops each segment and returns the first non-null value
     * @param string $value
     * @param boolean $allowNoMatch
     * @return string|null
     */
    private function sregValue($value = '', $allowNoMatch = false)
    {
        $owner = $this->owner;
        $values = explode('|', $value);
        $count = count($values);
        foreach ($values as $key => $value) {
            if ($relValue = $owner->relField($value)) {
                return $relValue;
            } elseif (
                $value != 'null' &&
                $count > 1 &&
                $key + 1 == $count &&
                !$owner->hasMethod($value)
            ) {
                return $value;
            }
        }
        if ($allowNoMatch) {
            return $value;
        }
    }
}
