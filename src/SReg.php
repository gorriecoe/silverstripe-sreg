<?php

namespace gorriecoe\sreg;

use SilverStripe\ORM\DataExtension;
use SilverStripe\Core\ClassInfo;

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
        foreach ($values as $key => $fieldPath) {
            if ($fieldPath == '' || $fieldPath == 'null') {
                return null;
            } elseif ($relValue = $this->sregTraverse($fieldPath)) {
                if (is_object($relValue) && method_exists($relValue, 'AbsoluteLink')) {
                    if ($link = $relValue->AbsoluteLink()) {
                        return $link;
                    } else {
                        continue;
                    }
                } else {
                    return $relValue;
                }
            } elseif ($key + 1 == $count && !$owner->hasMethod($fieldPath) ) {
                return $fieldPath;
            }
        }
    }

    private function sregTraverse($fieldPath)
    {
        $owner = $this->owner;
        $component = $owner;
        // Parse all relations
        foreach (explode('.', $fieldPath) as $relation) {
            if (!$component) {
                return null;
            }

            preg_match('/([\w\d]*)(\(([\w\d\s,\'"]*)\))?/', $relation, $matches);

            if (isset($matches[1])) {
                $relation = $matches[1];
            }
            // Inspect relation type
            if (ClassInfo::hasMethod($component, $relation)) {
                if (isset($matches[3])) {
                    $component = call_user_func_array(
                        [$component, $relation],
                        explode(',', $matches[3])
                    );
                } else {
                    $component = $component->{$relation}();
                }
            } elseif (
                $component instanceof Relation ||
                $component instanceof DataList
            ) {
                // $relation could either be a field (aggregate), or another  drelation
                $singleton = DataObject::singleton($component->dataClass());
                $component = $singleton->dbObject($relation) ?: $component->relation($relation);
            } elseif (
                $component instanceof DataObject &&
                ($dbObject = $component->dbObject($relation))
            ) {
                $component = $dbObject;
            } elseif (ClassInfo::hasMethod($component, 'obj')) {
                $component = $component->obj($relation);
                if (ClassInfo::hasMethod($component, 'Plain')) {
                    $component = $component->Plain();
                }
            }
        }
        return $component;
    }
}
