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
        $count = 0;
        $parsedString = preg_replace_callback(
            '/\{\$([\w\d\s.|]*)\}/',
            function ($matches)
            {
                return $this->sregValue($matches[1]);
            },
            $string,
            -1,
            $count
        );
        if (!$count && !preg_match('/ /', $string)) {
            $parsedString = $this->sregValue($string);
        }
        if (trim($parsedString) != '') {
            return $parsedString;
        }
    }

    /**
     * Splits string by |, loops each segment and returns the first non-null value
     * @param string $string
     * @return string|null
     */
    private function sregValue($value='')
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
    }
}
