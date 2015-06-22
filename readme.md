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

* `unsigned` - число должно быть положительным, включая ноль
* `positive` - число должно быть строго больше нуля
* `negative` - число должно быть строго меньше нуля
* `smalltext` - текстовое значение не более 256 симовлов длиной
* `text` - текстовое значение не более 64KiB симовлов длиной
* `largetext` - текстовое значение не более 2MiB симовлов длиной
* `date [FORMAT]` - значение должно быть датой. `FORMAT` - шаблон ожидаемой даты, шаблон должен быть в формате [strftime](http://php.net/manual/en/function.strftime.php). Если шаблон не указан для проверки даты используется [strtotime](http://www.php.net/manual/en/function.strtotime.php). Например, `date`, `date %Y-%m-%d`
* `length RANGE` - строковое значение имеет ограничения по количеству символов. Интервал задается промежуток значений `1..6` так и не равеством `<=100`. Например, `length <100`, `length 6..20`
* `value RANGE` - числовое значение имеет ограничения по значению. Интервал задается промежуток значений `1..6` так и не равеством `<=100`. Например, `value <100`, `value 6..20`
* `count RANGE` - массив занчений имеет ограничения по количеству элеменотов. Интервал задается промежуток значений `1..6` так и не равеством `<=100`. Например, `count <100`, `count 6..20`
* `file` - строковое значение является регулярным файлом
* `dir` - строковое значение является директорией
* `email [extended]` - строковое значение является электронным адресом. Если включен параметр `extended` формат email может быть в виде `Vasya Pupkin <pupkin@dev.null> `
* `domain` - строковое значение является доменом
* `custom CALLBACK` - значение будет передано в указанную функцию для проверки
* `url` - строковое значение является интернет-адресом
* `ip` - строковое значение является IP адресом
* `hex` - строковое значение является HEX выражением, like md5 or sha1.
* `like PATTERN` - строковое значение удовлетворяет шаблону формата [sscanf](http://php.net/manual/en/function.sscanf.php). Например, `like %d-%d-%d %d:%d:%d.%[a-z].$d`
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

