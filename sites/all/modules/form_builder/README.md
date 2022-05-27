[![Build Status (7.x-2.x)](https://travis-ci.org/moreonion/form_builder.svg?branch=7.x-2.x)](https://travis-ci.org/torotil/form_builder)

## Overview

This is a Drupal module that provides an interface for editing and configuring forms. It started out as a module to edit Drupal7 Form-API arrays but has been extended since then to edit [webforms](https://www.drupal.org/project/webform) and others. In theory it can manage every tree of configurable items.

### Features

* Edit forms by drag&droping form elements.
* Immediately get a preview of the form.

### Installation

Just install it like [any other drupal module](https://www.drupal.org/documentation/install/modules-themes/modules-7).

### Requirements

* [Options Element](https://www.drupal.org/project/options_element)
* [ctools](https://www.drupal.org/project/ctools)
* [psr0](https://www.drupal.org/project/psr0)
* _PHP 7.0_ or higher.

### Integrations / Modules based on form_builder

* [webform](https://www.drupal.org/project/webform) (≥4.0) - enable the form_builder_webform sub-module.


## Development

### Maintenance status

Only the 7.x-2.x branch is actively developed. Other branches are only minimally maintained and will receive security fixes only.

### Contributing

Apart from contributing code there is numerous other ways to contribute:

* Triage bugs: Try to confirm reported bugs. Provide step-by-step instructions to reproduce them preferably starting from a clean Drupal installation.
* [Review and test patches](https://www.drupal.org/patch/review): Does the patch really fix the issue? Does it have any unwanted side-effects?
* Re-roll patches against the latest dev-version if needed.
* Write tests.
* Add documentation.

If you want to help out feel free to use either the [Drupal issue queue](https://www.drupal.org/project/issues/form_builder) or pitch in on [github](https://github.com/moreonion/form_builder).

### Executing the tests

To execute the tests you need [phpunit](https://phpunit.de) and [upal](https://github.com/torotil/upal).

You also need a drupal installation that has form_builder enabled.
