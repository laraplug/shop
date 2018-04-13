[![Latest Stable Version](https://poser.pugx.org/laraplug/shop/v/stable.svg?format=flat-square)](https://github.com/laraplug/shop/releases)
[![Software License](https://poser.pugx.org/laraplug/shop/license.svg?format=flat-square)](LICENSE)
[![Daily Downloads](https://poser.pugx.org/laraplug/shop/d/daily.svg?format=flat-square)](https://packagist.org/packages/laraplug/shop)
[![Monthly Downloads](https://poser.pugx.org/laraplug/shop/d/monthly.svg?format=flat-square)](https://packagist.org/packages/laraplug/shop)
[![Total Downloads](https://poser.pugx.org/laraplug/shop/d/total.svg?format=flat-square)](https://packagist.org/packages/laraplug/shop)
[![PHP7 Compatible](https://img.shields.io/badge/php-7-green.svg?style=flat-square)](https://packagist.org/packages/laraplug/shop)

# Laraplug Shop

**Laraplug Shop** is a flexible, extendable e-commerce module, built on top of [AsgardCMS](https://github.com/AsgardCms/Platform) platform.

Integrated with [laraplug/product-module](https://github.com/laraplug/product-module)
and [laraplug/attribute-module](https://github.com/laraplug/attribute-module)

## Table Of Contents

- [Installation](#installation)
- [Usage](#usage)
    - [Extend Product Model](#extend-product-model)
    - [Add EAV to Produt model](#add-eav-to-product-model)
    - [Register Your Product](#register-your-product)
- [About Laraplug](#about-laraplug)
- [Contributing](#contributing)


## Installation

1. Install the package via composer:
    ```shell
    composer require laraplug/shop
    ```

2. Execute migrations via [AsgardCMS](https://github.com/AsgardCms/Platform)'s module command:
    ```shell
    php artisan module:migrate Attribute
    php artisan module:migrate Product
    php artisan module:migrate Shop
    ```

3. Execute publish via [AsgardCMS](https://github.com/AsgardCms/Platform)'s module command:
    ```shell
    php artisan module:publish Attribute
    php artisan module:publish Product
    php artisan module:publish Shop
    ```

4. Done!

## Usage

### Extend Product Model

To create your own `Book` Product Eloquent model on `BookStore` module, just extend the `\Modules\Product\Entities\Product` model like this:

```php
use Modules\Product\Entities\Product;

class Book extends Product
{
    // Override entityNamespace to identify your Model on database
    protected static $entityNamespace = 'bookstore/book';

    // Override this method to convert Namespace into Human-Readable name
    public function getEntityName()
    {
        return trans('bookstore::books.title.books');
    }

}
```

### Add EAV to Product model

Add `$systemAttributes` to utilize [laraplug/attribute-module](https://github.com/laraplug/attribute-module) on code-level like this:

```php
use Modules\Product\Entities\Product;

class Book extends Product
{
    ...

    // Set systemAttributes to define EAV attributes
    protected $systemAttributes = [
        'isbn' => [
            'type' => 'input'
        ],
        'media' => [
            'type' => 'checkbox',
            'options' => [
                'audio-cd',
                'audio-book',
                'e-book',
            ]
        ]
    ];
}
```

#### Available SystemAttributes Parameters

**type** : String of input type (list below)
 - `input` : input[type=text]
 - `textarea` : teaxarea
 - `radio` : input[type=radio]
 - `checkbox` : input[type=checkbox]
 - `select` : select
 - `multiselect` : select[multiple]

**options** : Array of option keys

**has_translatable_values** : boolean

### Register Your Product

You can register your Entity using `ProductManager` like this:

```php
use Modules\Product\Repositories\ProductManager;
use Modules\BookStore\Products\Book;

class BookStoreServiceProvider extends ServiceProvider
{
    ...

    public function boot()
    {
        ...

        // Register Book
        $this->app[ProductManager::class]->registerEntity(new Book());

        ...
    }
}
```

### About Laraplug

LaraPlug is a opensource project to build e-commerce solution on top of AsgardCMS.


## Contributing

We welcome any pull-requests or issues :)
