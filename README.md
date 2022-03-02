A Trait to handle Translations in Laravel
===
This is a Laravel package containing a trait for translatable Eloquent models. This package follows
the approach to have only a single table to maintain all the translations.

This approach may not be perfect for every use case as the table can grow really big. But compared to all the
other packages this approach is the most flexible as it lets you make models and its attributes translatable
without extra configuration.

Alternatives to this package are following packages:

1. [Spatie/laravel-translatable](https://github.com/spatie/laravel-translatable) saves the translatable
attributes as jsons
2. [dimsav/laravel-translatable](https://github.com/dimsav/laravel-translatable) expects a new table for
every new model that has translatable attributes

## Requirements

This package require a minimum Laravel version of `5.8` and PHP in version `7.1`

Installation
---
You can install the package via composer:

```bash
composer require said/laravel-translatable
```

Now you can use this Trait on any Eloquent Model of your project.

Usage
---

To make your Eloquent Model translatable just add the Said\Translatable\Traits\Translatable` Trait to your model.
Then add a public attribute `$translatable_columns` as an array containing all the attributes that should be translatable.

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Said\Translatable\Traits\Translatable;

class MyModel extends Model
{
    use Translatable;
    protected $translatable_columns=[
        'name'
    ];
    protected $fillable=[
        'price'
    ];
}
```

Methods
---

The simplest version of getting an translation is to simply get the property. This will return the value of
the property in the current language

```php
// assuming $myModel is an instace of MyModel class defined above
// and the translations are set
echo $myModel->name; // returns 'Product'
App::setLocale('fr');
echo $myModel->name; // returns 'Produit'
```

You can also use

```php
$myModel->in('fr')->translate('name'); // returns 'Produit'
```

### Getting the translated model
*soon*

### Translating an Model

You can translate a model using ...
*soon*
Changelog
---
Check [CHANGELOG](CHANGELOG.md) for the changelog

Testing
---
*soon*
    
Contributing
---
*soon*

Security
---
*soon*

About Said Ibrahim
---
*soon*.

License
---
The MIT License (MIT).
