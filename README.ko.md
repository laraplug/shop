[![Latest Stable Version](https://poser.pugx.org/laraplug/shop/v/stable.svg?format=flat-square)](https://github.com/laraplug/shop/releases)
[![Software License](https://poser.pugx.org/laraplug/shop/license.svg?format=flat-square)](LICENSE)
[![Daily Downloads](https://poser.pugx.org/laraplug/shop/d/daily.svg?format=flat-square)](https://packagist.org/packages/laraplug/shop)
[![Monthly Downloads](https://poser.pugx.org/laraplug/shop/d/monthly.svg?format=flat-square)](https://packagist.org/packages/laraplug/shop)
[![Total Downloads](https://poser.pugx.org/laraplug/shop/d/total.svg?format=flat-square)](https://packagist.org/packages/laraplug/shop)
[![PHP7 Compatible](https://img.shields.io/badge/php-7-green.svg?style=flat-square)](https://packagist.org/packages/laraplug/shop)

### 현재 이 모듈은 활발하게 개발이 진행되고 있습니다.
### 도움이 필요합니다! 영문번역은 완전하지 않을수 있습니다. 어떤 PR이든 환영입니다 :)

# Laraplug Shop

**Laraplug Shop** 은 유연하고, 커스터마이징이 쉬운 쇼핑몰입니다. [AsgardCMS](https://github.com/AsgardCms/Platform) 플랫폼을 기반으로 제작되었습니다.

아래 모듈들이 함께 설치됩니다  
[laraplug/product-module](https://github.com/laraplug/product-module)  
[laraplug/attribute-module](https://github.com/laraplug/attribute-module)  
[laraplug/cart-module](https://github.com/laraplug/cart-module)  
[laraplug/order-module](https://github.com/laraplug/order-module)  
[laraplug/theme-module](https://github.com/laraplug/theme-module) (Deprecated)

## Table Of Contents

- [설치](#설치)
- [사용법](#사용법)
    - [상품모델 생성](#상품모델-생성)
    - [상품모델 속성추가](#상품모델-속성추가)
    - [상품모델 등록](#상품모델-등록)
- [Laraplug 소개](#laraplug-소개)
- [기여하기](#기여하기)


## 설치

1. 컴포저를 이용하여 패키지를 설치합니다:
    ```shell
    composer require laraplug/shop
    ```

2. [AsgardCMS](https://github.com/AsgardCms/Platform)의 모듈 마이그레이션 커맨드를 이용하여 DB를 생성합니다:
    ```shell
    php artisan module:migrate Attribute
    php artisan module:migrate Product
    php artisan module:migrate Shop
    ```

3. [AsgardCMS](https://github.com/AsgardCms/Platform)의 모듈 퍼블리싱 커맨드를 이용하여 Assets(js,css,font...etc)를 생성합니다:
    ```shell
    php artisan module:publish Attribute
    php artisan module:publish Product
    php artisan module:publish Shop
    ```

4. 끝!

## 사용법

### 상품모델 생성

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

### 상품모델 속성추가

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

#### 사용가능한 SystemAttributes 파라미터

**type** : String of input type (list below)
 - `input` : input[type=text]
 - `textarea` : teaxarea
 - `radio` : input[type=radio]
 - `checkbox` : input[type=checkbox]
 - `select` : select
 - `multiselect` : select[multiple]

**options** : Array of option keys

**has_translatable_values** : boolean

### 상품모델 등록

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

### Laraplug 소개

LaraPlug is a opensource project to build e-commerce solution on top of AsgardCMS.


## 기여하기

We welcome any pull-requests or issues :)
