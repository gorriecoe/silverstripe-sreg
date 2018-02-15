# Silverstripe sreg
Simple syntax parser that allows you to use .ss template like variables in a dataobjects string

## Installation
Composer is the recommended way of installing SilverStripe modules.
```
composer require gorriecoe/silverstripe-sreg
```

## Requirements

- silverstripe/framework ^4.0

## Maintainers

- [Gorrie Coe](https://github.com/gorriecoe)

## Usage

```php
class MyObject extends DataObject
{
    private static $has_one = [
        'Relation' => 'SomeObject',
        'AFallBack' => 'SomeObject',
    ];

    public function SomeFunction()
    {
        return 'Some text';
    }

    public function getTitle()
    {
        return $this->sreg('Lorem ipsum {$Relation.Title|AFallBack.Title|Fall back text} {$SomeFunction}');
    }
}

```
