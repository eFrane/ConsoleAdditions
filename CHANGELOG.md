# Changelog

## UNRELEASED

* [**BC BREAK**] Removed string argument parsing from Batch shell actions
* [**BC BREAK**] Upgrade Dependencies to Symfony 6 (fixes #34)
* [**BC BREAK**] Drop Support for PHP 7.1, 7.2 and 7.3
  * **WARNING** Support for PHP 7.4 will be dropped very soon

## 0.6.2

* Many internal code style changes to increase the PHPStan Level
* Improved documentation
* Extended support range for symfony/console and symfony/process to 5.x

## 0.6.0

* Fix output verbosities
* Drop PHP 7.0
* Add version guard around php 7.1 failing assertion
* Fix packagist link in readme
* Merge branch 'dependabot/composer/phpunit/phpunit-tw-6.4or-tw-8.0'
* Fix phpunit 8 deprecations
* Add MessageAction
* Move Batch class into it's namespace
* Remove input requirement from InstanceCommandAction
* Move application dependency out of action constructor
* refactor Batch to use Actions
* Increase required symfony/console version to 3.4
* Move tests to subdir
* Update phpunit/phpunit requirement from ^6.4 to ^6.4 || ^8.0
