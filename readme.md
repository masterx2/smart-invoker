SmartInvoker
=============

[![Build Status](https://travis-ci.org/bzick/smart-invoker.svg)](https://travis-ci.org/bzick/smart-invoker) [![Coverage Status](https://coveralls.io/repos/bzick/smart-invoker/badge.svg)](https://coveralls.io/r/bzick/smart-invoker)

Smart Invoker allows to call functions, methods and objects with validation of arguments and type casting. 
This is similar to [call_user_func](http://php.net/call_user_func) and [call_user_func_array](http://php.net/call_user_func_array), but more smarter.
For example calculate hypotenuse using method:

```php
class Math {
	/**
	 * @param float $leg1 (unsigned) first cathetus of triangle
	 * @param float $leg2 (unsigned) second cathetus of triangle
	 * @return float
	 */
    public static function hypotenuse($leg1, $leg2, $round = 2) {
        // ...
    }
}
```
all arguments we gets from associative (or plain) array (e.g. `$_GET`).

If use classic way with `call_user_func`:

```php
if(isset($_GET['leg1'])) {
    $leg1 = floatval($_GET['leg1']);
    if($leg1 < 0) {
        throw new LogicException("leg1 should be greater than 0");
    }
} else {
    throw new RuntimeException("No leg1 parameter");
}
if(isset($_GET['leg2'])) {
    $leg2 = floatval($_GET['leg2']);
    if($leg2 < 0) {
        throw new LogicException("leg2 should be greater than 0");
    }
} else {
    throw new RuntimeException("No leg2 parameter");
}

return call_user_func('Math::hypotenuse', $leg1, $leg2);
```

But if use `SmartInvoker` we get one line of code: 

```php
return SmartInvoker::call('Math::hypotenuse', $_GET);
```

SmartInvoker find all arguments from associative (or plain) array `$_GET`, change type, validate and invoke method.

## Method call


```php
/**
 * Method description
 * @param int $arg1 some integer value
 * @param string $arg2 some string value
 * @param float[] $arg3 array of floating point values
 * @return bool method result
 **/
public function doSomething($arg1, $arg2, array $arg3 = array()) {
    // ...
}
```

Перед вызовом метода система проверит на наличие всех аргументов в запросе и приведет к нужному типу, согласно описанию метода.
Бывает недостаточно простого приведения типа, может потребоваться привинтивная проверка самих данных.
В этом случае в системе есть набор готовых проверок. Все проверки указываются так же в doc-блоке в скобках, через запятую сразу после названия аргумента:

```php
/**
 * Method description
 * @param int $arg1 (value 0..100) некий числовой аргумент, значения которого находится между 0 и 100 включительно
 * @param string $arg2 (keyword, length < 100) некий стоковый аргумент, длина которого меньше 100 символов и состоит из `a-z0-9-_`
 * @param float[] $arg3 (count 0..10) массив чисел с плавующей точкой, массив может содержать от 0 до 10 элементов
 **/
public function doSomethingAction($arg1, $arg2, array $arg3) {
    // ...
}
```

List of verifications (class `SmartInvoker\Verify`):

* `unsigned` - value is unsigned __integer__
* `positive` - __integer__ value __greater than zero__
* `negative` -  __integer__ value __less than zero__
* `smalltext` - __string__ value less than or equal to __256 bytes__
* `text` - __string__ value less than or equal to __64KiB__.
* `largetext` - __string__ value less than or equal to __2MiB__.
* `date [FORMAT]` - string value is a __date__. `FORMAT` - the [format](http://php.net/manual/datetime.createfromformat.php#refsect1-datetime.createfromformat-parameters) that the passed in string should be in. `FORMAT` - optional parameter, by default function [strtotime](http://docs.php.net/strtotime) parse string.
* `length RANGE` - specifies the __maximum number of bytes__ allowed in the __string__ value. Maybe range `length 1..6`, equality `length = 6` or inequality `length <=100`.
* `value RANGE` - числовое значение имеет ограничения по значению. Интервал задается промежуток значений `1..6` так и не равеством `<=100`. Например, `value <100`, `value 6..20`
* `count RANGE` - массив занчений имеет ограничения по количеству элеменотов. Интервал задается промежуток значений `1..6` так и не равеством `<=100`. Например, `count <100`, `count 6..20`
* `file` - checks that the given __string__ is __existing regular file__.
* `dir` - checks that the given __string__ is __existing directory__.
* `email [extended]` - __string__ value is __email address__. Set parameter `extended` that would allow the email format `James Bond <agent007@dev.null>`.
* `domain` - checks that the given __string__ is __domain__.
* `custom CALLBACK` - значение будет передано в указанную функцию для проверки
* `url` -  __string__ value is __URL__
* `ip` - __string__ value is __IP address__
* `hex` - __hex string__, like md5 or sha1.
* `like PATTERN` - match __string__ value against a __[glob](https://en.wikipedia.org/wiki/Glob_%28programming%29) pattern__. Example: `like *.tgz`
* `mask PATTERN` - строковое значение удовлетворяет маске символов. Например, `mask a-z0-9_`
* `regexp PATTERN` - строковое значение удовлетворяет регулярному выражению. Например, `regexp /^[a-z0-9_]+$/si`
* `variants SOURCE` - значение является одним из вариантом из списка SOURCE. SOURCE может быть как обычным перечислением через пробел возможных значений параметра так и коллбеком, который возвращает массив допустимых параметров. Например, `variants: one two three four`
* `options CALLBACK` - ассоциативный массив возможных значений. Необходимое значение ищется в ключе массива.
* `is [SOMETHING]` - более метка чем проверка, сама проверка ничего не делает и всегда возвращает `true`. Однако значение можно использовать для правильной отрисовки контрола.

Если указанная проверка не существует в `SmartInvoker\Verify` то для валидации аргумента будет вызван метод текущего контроллера с суффиксом `Validator`.

```php
/**
 * Method description
 * @param int $author (user active)
 **/
public function addPost($author) {
    // ...
}

public function userValidator($user_id, $type) {
    // check $user_id
}
```

Система так же воспринимает закачиваемые файлы, в этом случае тип аргумента должен быть `splFileInfo`:

```php
/**
 * Описание метода
 * @param splFileInfo $file файлы, которые будут закачины на сервер
 **/
public function doSomethingAction(splFileInfo $file) {
    // ...
}
```

## Create object

## Cache

## RPC example

