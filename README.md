# php2gpc
**[Прочитать на русском](README.ru.md)**

# Purpose

Console tool that can convert specially formed PHP-code to GPC-script used for programming Cronusmax

# Installation
```bash
composer global require inside/php2gpc
```
<sub>(+ check that you have filled system variable with path to directory of bin composer)</sub>

# Using
```bash
php2gpc index.php script.gpc
```

# Coding principles

## Events
Cronusmax can handle two events: init и main.
To do this, it's enough to declare functions with these names. Declared arguments will be ignored.

## Combo
To declare combos you need to declare a variable and assign an anonymous function to it.
The functions from documentation should be used, but the combo name must be given as a string.

## Functions
You need to declare a function.
You can call it by writing call('funcname') or you can do it just like in PHP.

## Declaration of variables and constants
The variable can be declared anywhere. After converstion it will be declared in the beginning of the script.
The constants are declared in default PHP way (define, const).

# TODO

## Generated combos
To make it possible to declare arguments for combos. For example:
```php
$sidestep = function($direction) {/* some stuff with $direction */}

combo_run('sidestep', PS4_UP);
combo_run('sidestep', PS4_DOWN);
```
&rarr;
```gpc
combo sitestep_PS4_UP {/* some stuff with PS4_UP */}
combo sitestep_PS4_DOWN {/* some stuff with PS4_DOWN */}

combo_run(sidestep_PS4_UP);
combo_run(sidestep_PS4_DOWN);
```
