<h1 align="center">Laravel Lang Generator</h1>

<img src="https://i.imgur.com/SALwcKf.png" alt="Laravel Lang Generator">
<p align="center">
<a href="https://travis-ci.org/glebsky/laravel-lang-generator"><img src="https://app.travis-ci.com/Glebsky/laravel-lang-generator.svg?branch=main" alt="Build Status"></a>
<a href="https://styleci.io/repos/440089612"><img src="https://github.styleci.io/repos/440089612/shield?style=flat" alt="StyleCI"></a>
<a href="https://packagist.org/packages/glebsky/laravel-lang-generator"><img src="https://badgen.net/github/release/glebsky/laravel-lang-generator" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/glebsky/laravel-lang-generator"><img src="http://poser.pugx.org/glebsky/laravel-lang-generator/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/glebsky/laravel-lang-generator"><img src="http://poser.pugx.org/glebsky/laravel-lang-generator/v/unstable" alt="Stable"></a>
<a href="https://packagist.org/packages/glebsky/laravel-lang-generator"><img src="http://poser.pugx.org/glebsky/laravel-lang-generator/license" alt="License"></a>
<a href="https://packagist.org/packages/glebsky/laravel-lang-generator"><img src="https://badgen.net/packagist/php/glebsky/laravel-lang-generator" alt="PHP Version"></a>
<br>
<br>
Searches for multilingual phrases in a Laravel project and automatically generates language files for you. You can search for new translation keys, delete unused keys, and quickly generate new language files.
</p>

---

## Installation

You can start the installation through the <b>composer</b> using the command.

```
composer require glebsky/laravel-lang-generator
```

## Configuration
To create configuration file of this package you can use command:

```
php artisan vendor:publish --tag=config
```
It will create configuration file in `app/config` with name `lang-generator`

### About configuration

<b>file_type</b>: is responsible for the type of the generated file. It is possible to generate both a json and an php array files. Posible values: `array` , `json`

---

<b>file_name</b>: is responsible for the name of the generated files. By default it is `lang`.

---

<b>languages</b>:  is responsible for the generated languages and accepts an array. Language folders with the specified data will be created. By default it just `en`.

---

## Usage

### Main Command

This command starts searching for translation keys in the `resource/views` and `app` folders according to the basic settings.
Existing keys will not be removed, only new ones will be added.

```
php artisan lang:generate
```
it will create new language files with found translation keys.
By default name of lang file is `lang`

![title](https://i.imgur.com/hvDrlVO.jpeg)

### Parameters

In addition, the command accepts several parameters that allow you to flexibly manage the package.
```
php artisan lang:generate lang:generate --type= --name= --langs= --sync --clear --path=
```
### About parameters 

`--type=` or `-T`:

is responsible for the type of the generated file. It is possible to generate both a json and an php array files. Posible values: `array` , `json`. <br>Example: `php artisan lang:generate --type=json`

---

`--name=` or `-N`:

is responsible for the name of the generated files. By default it is `lang`. 

Example: `php artisan lang:generate --name="pagination"`

---

`--langs=` or `-L`:

is responsible for the generated languages and accepts an array. Language folders with the specified data will be created. By default it just `en`. <br>Example: `php artisan lang:generate --langs="en" --langs="es`

---

`--sync` or `-S`:

If you specify this flag, then all unused already existing translation keys will be deleted. <br>Example: `php artisan lang:generate --sync`

---

`--clear` or `-C`:

If you specify this flag, existing language files are removed and new ones are created. All existing translations will be removed.

`NOTE! That NOT all language files are deleted, but only with the name specified in the settings.`

Example: `php artisan lang:generate --clear`

## Notes
`lang:generate` will update your language files by writing them completely, meaning that any comments or special styling will be removed, so I recommend you backup your files.
