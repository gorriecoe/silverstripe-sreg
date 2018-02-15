<?php

namespace gorriecoe\sreg;

use SilverStripe\ORM\DataExtension;

/**
 * @package silverstripe-sreg
 */
class SReg extends DataExtension
{
    /**
     * Simple syntax parser that allows you to use .ss template like variables in a string
     * e.g 'Lorem ipsum {$Relation.Title}'
     * @param string $string
     * @return string|null
     */
    public function sreg($string)
    {
        $owner = $this->owner;
        $parsedString = preg_replace_callback(
            '/\{\$([\w\d\s|]*)\}/',
            function ($matches) use($owner)
            {
                $values = explode('|', $matches[1]);
                $count = count($values);
                foreach ($values as $key => $value) {
                    if ($relValue = $owner->relField($value)) {
                        return $relValue;
                    }
                    if ($key + 1 == $count && !$owner->hasMethod($value)) {
                        return $value;
                    }
                }

            },
            $string
        );
        if (trim($parsedString) != '') {
            return $parsedString;
        }
    }
}
