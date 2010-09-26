Sham - test stub and spy for PHP 5.3
====================================

Sham is test stub and spy (test double) for PHP 5.3 and up. The idea
behind Sham is to allow everything to be done to a Sham instance.
Sham records every interaction you have with it, you can then later
investigate what happened. It does not self-verify, use your testing
framework for asserting.

The goal is to record everything that is possible. This includes:

* Method calls
* __invoke calls
* property access
* array access
* method call overloading
* property access overloading
* iteration
* serialization

A Sham_Mock implements:

* ArrayAccess
* Iterator

# Introduction

Sham is a mocking library, but to use a more correct term, it's a test stub or
a test spy. It uses the record then assert paradigm, which is more suitable for
Behavior Driven Development, where the test setup usually performs the test act
and the tests themselves only assert. Sham does not self-verify how the SUT
(system under test) interacted with it, you have to make the assertions
yourself. This makes Sham a very small and simple library since it does not
have to integrate with different testing libraries.

This is the basic workflow with using Sham:

1. Create a stub by either passing a class name to the static method
   `Sham::create()` or by instantiating `Sham_Mock` directly.

2. Inject the stub to your System Under Test.

3. Perform the action that exercises the code you want to test.

4. Assert that your code interacted with the stub the way it was supposed to.

The goal of Sham is to record every interaction you have with it. By default,
every call returns another `Sham_Mock` instance.

Stubbing with Sham is really easy because you don't have to set any
expectations in your test setup, just pass in a Sham object.

# Stubbing

You can create a stub by instantiating the `Sham_Mock' class directly:

    $stub = new Sham_Mock();

However, if the object you are trying to stub must be an instance of a certain class,
use the static method `Sham::create()`:

    $stub = Sham::create('My_Class');

`$stub` is now an instance of `My_Class` and will pass any `instanceof` or
typhint checks. What the `create` method does is, it takes the source of the
`Sham_Mock` class as string, augments it to be an instance of `My_Class` and
`eval()`s that code. It implements all the neccessary methods to adhere to any
abstract classes in the class hierarchy.

You can also stub interfaces. Just pass in a name of an interface. You get back
an object which implements that interface.

# Filter and assert

After you have exercised your code with stubs injected in them, you can
investigate the stubbed objects. The heart of this is the `calls()` method.
It filters the object given some criteria and returns a call list object
(`Sham_CallList`). The call list object has some helpful methods you can use
when asserting. To check if `foo()` was called on `$stub` you would do this:

    $stub = new Sham_Mock();
    $stub->foo();

    $stub->calls('foo')->once(); // true

To check if `foo()` was called once with 'first' as the only parameter:

    $stub->calls('foo', 'first')->once();

To check if `foo()` was called with anything as the first param and `bar` as
the second param:

    $stub->calls('foo', Sham::any(), 'bar')->once();

The special `Sham::any()` call returns a matcher object which matches anything.
This is useful when you are writing a test which only needs to test a certain
parameter and ignore the others.

## Method calls and method overloading

Sham does not distinguish between normal and overloaded method calls. You
filter both with just the called method's name. For example, if you call a
method `overload()`, which would be an overloaded method in the real
implementation:

    $stub->overload();

    $stub->calls('overload')->once(); // true
    $stub->calls('__call')->never();  // true, no __call is ever recorded


## Return values

Every call returns a `Sham_Mock` instance by default. You can however set a
return value:

    $stub->method->returns('foo');
    $stub->method(); // 'foo'

## Throwing exceptions

To make a call throw an exception on invokation, use the `throws()` method.
The first parameter tells it which exception to throw. You can give it a name
of an exception as a string, or an instance of an exception class. If not given
anything, it will throw a `Sham_Exception`. All of the below examples will
set `method` to throw a `Sham_Exception` once invoked:

    $stub->method->throws('Sham_Exception');
    $stub->method->throws(new Sham_Exception());
    $stub->method->throws();

## Side effects

You can also make methods run code when they are invoked. These side effects can
be added with the `does()` method. It accepts an anonymous function as it's
only param.

    $stub->method->does(function () {
        bar();
        return 'foo';
    });

    $stub->method(); // calls bar and returns 'foo'

Think hard before you use this. It's very likely that you need to refactor your
code before ever needing to use this.

## __invoke

A Sham instance can be invoked. A call with name `__invoke` is recorded:

    $stub();
    $stub->calls('__invoke')->once(); // true

    $stub('foo');
    $stub->calls('__invoke', 'foo')->once(); // true

Return values can be set just like for method calls. Either:

    $stub->__invoke->returns('return value');
    $stub(); // 'return value'

or simply just:

    $stub->returns('return value');
    $stub(); // 'return value'


# Sham as value object

Sham objects can act as value or entity objects. All property access,
array access and iteration is recorded. The data it operates on is set using
`shamSetData()`.

    $stub->shamSetData(array(
        'key' => 'value',
    ));

    $stub->key   // 'value'
    $stub['key'] // 'value'

## Property access and property overloading

## ArrayAccess

## Iteration


# Filtering

# API

Create a stub either by stubbing an existing class, or by instantiating
`Sham_Mock` directly:
    
    $stub = Sham::create('My_Class'); // $stub instanceof My_Class => true
    $stub = new Sham_Mock();

By default method calls return new `Sham_Mock` objects, but you can set the return value:

    $stub->foo->returns('return value');

You can also tell methods to throw:
    
    $stub->foo->throws();
    $stub->foo->throws('Exception');
    $stub->foo->throws(new Exception());

For exceptionally complex cases you can write a stub implementation:

    $stub->foo->does(function($x, $y) { return $x + $y; });

Now call some methods:
    
    $stub->foo();
    $stub->bar('param 1');

Once your test act has been run, you can inspect your test double to see if
your code interacted with it correctly.

    // make a few calls
    $stub->xoo('param 1');
    $stub->xoo('param 1', 'param 2');

    // Sham_CallList::calls is an array of Call objects
    count($stub->calls('xoo')->calls); // 2

    $stub->calls('xoo', 'param 1', 'param 2')->once(); // true

    // use Sham::any() to match any parameter value
    $stub->calls('xoo', Sham::any(), 'param 2')->once(); // true

Methods can also be stubbed to do different things given different parameters:

    $stub->foo->given('zero', Sham::any())->returns(0);
    $stub->foo->given(Sham::any(), 0)->throws();

    $stub->foo('zero', 3); // 0
    $stub->foo('one', 0);  // exception
    $stub->foo('zero', 0); // exception (later stubs get priority)

## Array and property access

You can also use `Sham_Mock` as a value object. It records all `ArrayAccess` and
`__get`/`__set` method calls.

    // first load it with some data
    $stub->shamSetData(array(1, 'key' => 2));

    $stub[0]; // 1
    $stub->key; // 2

    $stub->some = 'value';
    $stub['some']; // value
    
    $stub['other'] = 'foo';
    $stub->other; // 'foo'

    $stub->calls('offsetGet', 0)->once();           // true
    $stub->calls('__get', 'key')->once();           // true
    $stub->calls('__set', 'some', 'value')->once(); // true
    $stub->calls('offsetSet', 'other', 'foo');      // true
    $stub->calls('__get', 'other')->once();         // true

## Iteration

You can also iterate over the data set with Sham_Mock::shamSetData().
All of the calls implemented by `Iterator` will be recorded.
