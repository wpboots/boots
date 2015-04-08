Boots - A Powerful WordPress Framework
=============

[Boots](https://wpboots.com/) is a WordPress framework that was missing to develop plugins and themes much easier and faster than ever before.

Features
---

**Modular** - Boots uses a fully modular approach (called extensions) so you choose the [extensions](https://wpboots.com/extensions) you need in your projects.

**Extendable** - Boots was built with extendability in mind. It is ridiculously easy to [extend](https://wpboots.com/extend) the framework with your own extensions.

**Composer** - We leverage [composer](https://getcomposer.org/) for development, so installing, managing and updating the extensions are a breeze.

Documentation
---

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->

 **Table of Contents**

- [Introduction](#introduction)
- [Requirements](#requirements)
  - [Development environment - Developers](#development-environment---developers)
  - [Production environment - End users](#production-environment---end-users)
- [Download](#download)
  - [Download via Composer](#download-via-composer)
  - [Download via Github](#download-via-github)
- [Usage](#usage)
- [Extensions](#extensions)
  - [Installing an extension](#installing-an-extension)
  - [Behind the scenes](#behind-the-scenes)
    - [Post installation](#post-installation)
    - [Wrapup](#wrapup)
  - [Browse extensions](#browse-extensions)
- [License](#license)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

## Introduction

At the core, boots is equipped with an api that copes only with the bootstrapping of the extensions it empowers.

Assume a manager being directed by his/her boss to accomplish different goals. Well, as a manager what will you do once you receive those orders? Pass those orders over to your workers and have them work on it, right?

If you understand the relationship in the example given above, then this is what you need to know.

**Boss** - That's you, the developer.

**Manager** - That's the boots api.

**Worker** - That's the boots extensions.

You now know the flow of working with boots, thats great! Lets dive more into the api (the manager).

## Requirements

### Development environment - Developers
- [PHP](http://php.net/) 5.3.2+
- [WordPress](http://wordpress.org/) 3.5+
- [Composer](https://getcomposer.org/)

### Production environment - End users
**Minimum**
- [PHP](http://php.net/) 5.2.17+
- [WordPress](http://wordpress.org/) 3.5+

**Recommended**
- [PHP](http://php.net/) 5.3+
- [WordPress](http://wordpress.org/) Latest version

## Download

Boots can be downloaded either via Github or via Composer.

### Download via Composer

This is the preferred way of downloading Boots. After installing [composer](https://getcomposer.org/) in your system, open up the terminal, cd into your plugin/theme directory and run the following command (without the $ sign).

```terminal
$ composer create-project boots/boots boots --no-install
```

The above command will download the latest version of the framework into the root of your plugin/theme directory within a folder named boots.

### Download via Github

You can optionally download boots from the  [Github Repo](https://github.com/wpboots/boots/archive/master.zip). Unzip it, rename it to `boots` and move it to the root of your plugin/theme directory.

**Note** - This is not the preferred way of downloading the framework because you still need to install any of the available [extensions](http://wpboots.com/extensions/) via composer.

## Usage

For demonstration purposes, we will hook things up as a plugin. The same would go for a theme `functions.php` as well.

Create a new folder in your wordpress plugin directory, we will name it `wpboots`, create an `index.php` file, open up your favorite code editor and walk along.

***

We first reference the boots framework api:

```php
<?php  # /wp-content/plugins/wpboots/index.php

/*
Plugin Name: WP Boots
Plugin URI: http://wpboots.com
Description: An introductory plugin looking at the boots framework.
Version: 1.0.0
Author: Author Name
Author URI: #
*/

require_once 'boots/api.php';
```

***

Then we need to create an array with the app settings.
```php
<?php # /wp-content/plugins/wpboots/index.php

// ... code left out for brevity ...

$WPBootsSettings = array(
    'ABSPATH'     => __FILE__,
    'APP_ID'      => 'my_plugin_id',
    'APP_NICK'    => 'My Plugin',
    'APP_VERSION' => '1.0',
    'APP_MODE'    => 'dev',          // optional, defaults to 'live'
    'APP_ICON'    => 'path_to_icon', // optional
    'APP_LOGO'    => 'path_to_logo', // optional
);
```
**ABSPATH** should remain as is. This basically will hold the path to the current file.

**APP_ID** is the unique identifier of your plugin.

**APP_NICK** refers to a pretty name of your plugin (usually your plugin name).

**APP_VERSION** will hold the version of your plugin you are working on.

**APP_MODE** is optional and defaults to `'live'` but should be set as `‘dev’` when you are developing. Change it to `‘live’` or remove the field completely before you release your plugin.

**APP_ICON** and **APP_LOGO** are optional and (if used) should contain the relative image file uri to the respective images and have dimensions of 20px/20px and 64px/64px respectively.

***

Next, we instantiate the boots api.

```php

<?php # /wp-content/plugins/wpboots/index.php

// ... code left out for brevity ...

$WPBoots = new Boots('plugin', $WPBootsSettings);
```

We pass on `‘plugin’` (or `‘theme’` if using in a theme) as the application type.
The settings array is passed by reference and gets updated with all the application settings (plus some useful extra suff).

**Note** - You must pass the settings array as a variable instead of a bare array as it will be used as a reference.

***

To see the extra stuff it has received, make a print out.

```php

<?php # /wp-content/plugins/wpboots/index.php

// ... code left out for brevity ...

print_r($WPBootsSettings);
```

***

To summarize, here is the complete code snippet.

```php
<?php  # /wp-content/plugins/wpboots/index.php

/*
Plugin Name: WP Boots
Plugin URI: http://wpboots.com
Description: An introductory plugin looking at the boots framework.
Version: 1.0.0
Author: Author Name
Author URI: #
*/

require_once 'boots/api.php';

$WPBootsSettings = array(
    'ABSPATH'     => __FILE__,
    'APP_ID'      => 'wp_boots',
    'APP_NICK'    => 'WP Boots',
    'APP_VERSION' => '1.0.0',
    'APP_MODE'    => 'dev',          // optional, defaults to 'live'
    'APP_ICON'    => 'path_to_icon', // optional
    'APP_LOGO'    => 'path_to_logo', // optional
);
$WPBoots = new Boots('plugin', $WPBootsSettings);
```

## Extensions

Extensions

### Installing an extension

Installing an extension

### Behind the scenes

Behind the scenes

#### Post installation

Post installation

#### Wrapup

Wrapup

### Browse extensions

Browse extensions

## License

Boots framework is open-sourced and is licensed under the [GPLv2](http://www.gnu.org/licenses/gpl-2.0.html) license.
