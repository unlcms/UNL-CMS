-- SUMMARY --

This module implements a simple PSR-4 class autoloader for Drupal modules and
profiles. Take a look at the following examples for classes and where PSR-4
tries to find their declaration.

  Drupal\your_module\YourClass -> your_module/lib/YourClass.php
  Drupal\your_module\SomeInterface -> your_module/lib/SomeInterface.php
  Drupal\your_module\Namespace\Class -> your_module/lib/Namespace/Class.php
  Drupal\your_profile\SomeClass -> profiles/your_profile/lib/SomeClass.php

-- REQUIREMENTS --

* Requires PHP >= 5.3.0 for namespaces to work.

-- INSTALLATION --

* Install as usual, see https://drupal.org/node/895323 for further information.
* Works as soon as the module is enabled without further configuration.

-- FAQ --

Q: Why is it called psr0 if it implements PSR-4?

A: At the time of it's writing PSR-0 was the standard used by Drupal 8, but it
   soon changed to PSR-4 after that. Module short-names can only be set once.
   So I'm stuck with psr0.
