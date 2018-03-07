# PHP Code Coverage Verifier

[![License](https://poser.pugx.org/tomzx/php-code-coverage-verifier/license.svg)](https://packagist.org/packages/tomzx/php-code-coverage-verifier)
[![Latest Stable Version](https://poser.pugx.org/tomzx/php-code-coverage-verifier/v/stable.svg)](https://packagist.org/packages/tomzx/php-code-coverage-verifier)
[![Latest Unstable Version](https://poser.pugx.org/tomzx/php-code-coverage-verifier/v/unstable.svg)](https://packagist.org/packages/tomzx/php-code-coverage-verifier)
[![Build Status](https://img.shields.io/travis/tomzx/php-code-coverage-verifier.svg)](https://travis-ci.org/tomzx/php-code-coverage-verifier)
[![Code Quality](https://img.shields.io/scrutinizer/g/tomzx/php-code-coverage-verifier.svg)](https://scrutinizer-ci.com/g/tomzx/php-code-coverage-verifier/code-structure)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/tomzx/php-code-coverage-verifier.svg)](https://scrutinizer-ci.com/g/tomzx/php-code-coverage-verifier)
[![Total Downloads](https://img.shields.io/packagist/dt/tomzx/php-code-coverage-verifier.svg)](https://packagist.org/packages/tomzx/php-code-coverage-verifier)

**PHP Code Coverage Verifier** allows you to determine if a change you've just done to your PHP code is being tested/covered by your tests. It uses a clover-xml report from a phpunit run (or any other code coverage suite that outputs to clover-xml format) and a unified diff containing your changes. **PHP Code Coverage Verifier** will then generate a list of the covered and not covered changes.

## Notice

The code is currently in a very crude state. It was done as a proof of concept and will improve if it proves useful to others.

## How to use

**PHP Code Coverage Verifier** *ships* as a Composer package. You can include it in your project's composer.json and use in a console.

Before you run the command line, be sure to run your tests with phpunit (or other) and generate the clover xml file that will be used here. For instance, one can generate the clover-xml file with phpunit using `phpunit --coverage-clover=my-clover.xml`.

Then you need to generate the diff of your changes. Here we assume you will generate those using either `svn diff` or `git diff` (users of TortoiseGIT/TortoiseSVN can use "Create patch...").

(Example: `git diff --no-prefix > my-diff.patch`)

```
php vendor/bin/php-code-coverage-verifier verify --help
Usage:
 verify [--display-not-covered-range[="..."]] clover-xml diff-file

Arguments:
 clover-xml                   Path to the clover-xml file
 diff-file                    Path to the diff-file

Options:
 --display-not-covered-range  Will display which line aren't covered (default: false)
 --help (-h)                  Display this help message.
 --quiet (-q)                 Do not output any message.
 --verbose (-v)               Increase verbosity of messages.
 --version (-V)               Display this application version.
 --ansi                       Force ANSI output.
 --no-ansi                    Disable ANSI output.
 --no-interaction (-n)        Do not ask any interactive question.
```

## Example of (current) output

```
php vendor/bin/php-code-coverage-verifier verify my-clover.xml my-diff.patch
Using clover-xml file: my-clover.xml
With diff file: my-diff.patch

Covered:
controller/admin/stocks.php line 15 - 21
controller/admin/stocks.php line 91 - 97
controller/search.php line 26 - 32
controller/search.php line 376 - 384
model/user.php line 34 - 41
model/user.php line 44 - 51

Not covered:
controller/account.php line 39 - 45
controller/admin/stocks.php line 27 - 33
controller/search.php line 36 - 42
controller/search.php line 187 - 193
model/user.php line 533 - 540

Ignored:
application/composer.json

Coverage: 40 covered (56.338%), 31 not covered (43.662%)
```

## Roadmap

* More output formatters
    * XML
    * HTML
    * Text (better)

## License

The code is licensed under the [MIT license](http://choosealicense.com/licenses/mit/). See [LICENSE](LICENSE).