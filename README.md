# FuzzyNumber 
### A PHP implementation of Fuzzy number as data-type

Supported operations on Fuzzy numbers include:

*   Addition
*   Subtraction
*   Multiplication
*   Division
*   Inversion (reciprocals)
*   Geometric Mean
*   ...lots more

## How to Install?

You can install this package using any of these 2 methods:

* Install via Composer (ncjoes/fuzzy-number on Packagist);
* Use the Git repository (https://github.com/ncjoes/fuzzy-number).

Then, require the vendor/autoload.php file to enable the autoloading mechanism provided by Composer.
Otherwise, your application won't be able to find the classes of this Symfony component.

## How to Use?

Here are some examples.

```php
<?php
// if you are using composer, just use this
use NcJoes\FuzzyNumber\FuzzyNumber;

$fn1 = new FuzzyNumber([1.25, 1.88, 2.22]);
$fn2 = new FuzzyNumber([0.333, 0.815, 1.212]);

$fn3 = $fn1->add($fn2); //
$fn4 = FuzzyNumber::sum($fn1, $fn2);// ==$fn3


$fn5 = $fn3->subtract($fn2); // ==$fn1
$fn4 = FuzzyNumber::diff($fn3, $fn2);// ==$fn1

//etc
?>
```

### License
The FuzzyNumber package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

### Feedback & Contribute

Notify me of any issues, bugs, or improvements. Thanks :+1:
