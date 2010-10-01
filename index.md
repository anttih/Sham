---
layout: default
---

Sham - test stub and spy for PHP 5.3
====================================

Sham is test stub and spy (test double) for PHP 5.3 and up. The idea
behind Sham is to allow everything to be done to a Sham instance.
Sham records every interaction you have with it, you can then later
investigate what happened. It does not self-verify, use your testing
framework for asserting.

## Manual

<ul>
    <li><a href="#intro">Introduction</a></li>
    <li><a href="#stub">Stubbing</a></li>
    <li><a href="#assert">Filter and assert</a></li>
    <li>
        <ul>
            <li><a href="#calls">Method calls</a></li>
            <li><a href="#return">Return values</a></li>
            <li><a href="#params">Stubbing by parameters</a></li>
            <li><a href="#exceptions">Exceptions</a></li>
            <li><a href="#sidefx">Side effects</a></li>
        </ul>
    </li>
    <li><a href="#assert">__invoke</a></li>
    <li><a href="#assert">Data objects</a></li>
    <li>
        <ul>
            <li><a href="#properties">Property access calls</a></li>
            <li><a href="#arrayaccess">ArrayAccess</a></li>
            <li><a href="#iteration">Iteration</a></li>
        </ul>
    </li>
    <li><a href="#api">API</a></li>
</ul>

# <a name="intro" href="#intro">Introduction</a>

Sham is a mocking library, but to use a more correct term, it's a test stub or
a test spy. It uses the record then assert paradigm, which is more suitable for
Behavior Driven Development, where the test setup usually performs the test act
and the tests themselves only assert. Sham does not self-verify how the SUT
(system under test) interacted with it, you have to make the assertions
yourself. This makes Sham a very small and simple library since it does not
have to integrate with different testing libraries.

This is the basic stubbing workflow with Sham:

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
    $stub->calls('__call')->never();  // true, no __call call is ever recorded


## Return values

Every call returns a `Sham_Mock` instance by default. You can, however, set a
return value:

    $stub->method->returns('foo');
    $stub->method(); // 'foo'

Calls will keep returning the same value. Any subsequent calls to `method()`
will now return 'foo'.

## Stubbing based on parameters

You can also stub a method to return a certain value given specific parameters.

    $stub->method->given('foo')->returns('bar');
    $stub->method('foo'); // 'bar'
    
    // fallback to default return value when params don't match
    $stub->method(); // Sham_Mock

You can call `given()` multiple times. These will be added to a stack where the
top most calls get priority:

    $stub->method->given('zero', Sham::any())->returns('first');
    $stub->method->given(Sham::any(), 0)->returns('second');

    $stub->method('zero', 3); // 'first'
    $stub->method('one', 0);  // 'second'
    $stub->method('zero', 0); // 'second' (later stubs get priority)

The `given()->...` pattern also applies to exceptions and side effects. That
is, you can replace the `returns()` call with `throws()` or `does()`. More on
these next.

## Throwing exceptions

To make a call throw an exception on invokation, use the `throws()` method.
The first parameter tells it which exception to throw. You can give it a name
of an exception as a string, or an instance of an exception class. If not given
anything, it will throw a `Sham_Exception`. All of the examples below will
set `method()` to throw a `Sham_Exception` when invoked:

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

Return values for `__invoke` calls can be set just like for method calls.
Either:

    $stub->__invoke->returns('return value');
    $stub(); // 'return value'

or using a convenience method `returns()` on the stub itself:

    $stub->returns('return value');
    $stub(); // 'return value'


# Sham as value object

Sham objects can act as value or entity objects. All property access,
array access and iteration is recorded. The data it operates on is set using
`shamSetData()` or by setting the properties and array indexes directly.
Property access, array access and iteration all operate on the same data.

    $stub->shamSetData(array(
        'key' => 'value',
    ));

    $stub->key   // 'value'
    $stub['key'] // 'value'

## Property access and property overloading

Properties can be set and retrieved, and it works just like you'd expect.
Under the hood all the calls get recorded. This behavior can be used when
testing code that uses the Entity or Active Record pattern:

    $record = new Sham_Mock();

    $record->name = 'Antti';
    $record->save();

    $record->calls('__set', 'name', 'Antti')->once(); // true
    $record->calls('save')->once();                   // true

    // ditto.
    $record->name // 'Antti'
    $stub->calls('__get', 'name')->once(); // true

## <a name="arrayaccess" href="#arrayaccess">ArrayAccess</a>

Sham implements the `ArrayAccess` interface and records all of those calls.

    // retrieve with array access
    $stub['key'] // 'value'
    $stub->calls('offsetGet', 'key')->once(); // true

    // set offset
    $stub['other'] = 'value';
    $stub->calls('offsetSet', 'other', 'value')->once(); // true


## <a name="iteration" href="#iteration">Iteration</a>

You can iterate over the data
You can also iterate over the data set with Sham_Mock::shamSetData().
All of the calls implemented by `Iterator` will be recorded.

# <a name="api" href="#api">API</a>

