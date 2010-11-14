---
layout: default
---

Sham - test stub and spy for PHP
====================================

Sham is test stub and spy (test double) for PHP 5.3 and up. Sham records every
interaction you have with it, you can then later investigate what happened. It
does not self-verify, use your testing framework for asserting.

## Download and Install

Download the latest version [here](http://github.com/anttih/Sham/dowload).

## Manual

<div id="toc">
<ul>
    <li><a href="#intro">Introduction</a></li>
    <li><a href="#stubbing">Stubbing</a></li>
    <li>
        <ul>
            <li><a href="#return">Return values</a></li>
            <li><a href="#params">Stubbing by parameters</a></li>
            <li><a href="#exceptions">Exceptions</a></li>
            <li><a href="#sidefx">Side effects</a></li>
        </ul>
    </li>
    <li><a href="#recording">Recording</a></li>
    <li>
        <ul>
            <li><a href="#calls">Method calls</a></li>
            <li><a href="#invoke">__invoke</a></li>
            <li><a href="#serialize">Serializing (__sleep/__wakeup)</a></li>
        </ul>
    </li>
    <li><a href="#filter">Filtering calls (asserting)</a></li>
    <li><a href="#data">Data objects</a></li>
    <li>
        <ul>
            <li><a href="#properties">Property access</a></li>
            <li><a href="#arrayaccess">ArrayAccess</a></li>
            <li><a href="#iteration">Iteration</a></li>
        </ul>
    </li>
    <li><a href="#api">API</a></li>
    <li><a href="#license">License</a></li>
</ul>
</div>

# <a name="intro" href="#intro">Introduction</a>

Sham is a mocking library, but to use a more correct term, it's a test stub
and spy. It uses the record-then-assert paradigm, which is more suitable for
Behavior Driven Development, where the tests themselves only assert. Sham does
not self-verify, you have to make the assertions yourself using your test
framework (there are plans to make it assert).

There is no need to set expectations beforehand, just inject a Sham in place of
a real object and let it record.

# <a name="stubbing" href="#stubbing">Stubbing</a>

You can create a stub by instantiating the `sham\Stub` class directly:

    $stub = new \sham\Stub();

However, if the object you are trying to stub must be an instance of a certain
class, use the static method `sham\Sham::create()`:

    $stub = \sham\Sham::create('My\Class');

`$stub` is now an instance of `My\Class` and will pass any `instanceof` or
typhint checks. What the `create` method does is, it takes the source of the
`sham\Stub` class as string, augments it to be an instance of `My\Class` and
`eval()`s that code. It implements all the neccessary methods to adhere to any
abstract classes in the class hierarchy.

You can also stub interfaces. Just pass in a name of an interface. You get back
an object which implements that interface.


## <a name="return" href="#return">Return values</a>

Every call returns a `\sham\Stub` instance by default. You can, however, set a
return value:

    $stub->method->returns('foo');
    $stub->method(); // 'foo'

Calls will keep returning the same value. Any subsequent calls to `method()`
will now return 'foo'.

## <a name="params" href="#params">Stubbing by parameters</a>

You can also stub a method to return a certain value given specific parameters.

    $stub->method->given('foo')->returns('bar');
    $stub->method('foo'); // 'bar'
    
    // fallback to default return value when params don't match
    $stub->method(); // \sham\Stub

You can call `given()` multiple times. These will be added to a stack where the
top most calls get priority:

    $stub->method->given('zero', sham\Sham::any())->returns('first');
    $stub->method->given(sham\Sham::any(), 0)->returns('second');

    $stub->method('zero', 3); // 'first'
    $stub->method('one', 0);  // 'second'
    $stub->method('zero', 0); // 'second' (later stubs get priority)

The `given()->...` pattern also applies to exceptions and side effects. That
is, you can replace the `returns()` call with `throws()` or `does()`. More on
these next.

## <a name="exceptions" href="#exceptions">Throwing exceptions</a>

To make a call throw an exception on invokation, use the `throws()` method.
The first parameter tells it which exception to throw. You can give it a name
of an exception as a string, or an instance of an exception class. If not given
anything, it will throw a `sham\Exception`. All of the examples below will
set `method()` to throw a `sham\Exception` when invoked:

    $stub->method->throws('sham\Exception');
    $stub->method->throws(new \sham\Exception());
    $stub->method->throws();

## <a name="sidefx" href="#sidefx">Side effects</a>

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

# <a name="recording" href="#recording">Recording</a>

## <a name="calls" href="#calls">Method calls</a>

Sham does not distinguish between normal and overloaded method calls. You
filter both with just the called method's name. For example, if you call a
method `overload()`, which would be an overloaded method in the real
implementation:

    $stub->overload();

    $stub->got('overload')->once(); // true
    $stub->got('__call')->never();  // true, no __call call is ever recorded

## <a name="invoke" href="#invoke">__invoke</a>

A `sham\Stub` instance can be invoked. A call with name `__invoke` is recorded:

    $stub();
    $stub->got('__invoke')->once(); // true

    $stub('foo');
    $stub->got('__invoke', 'foo')->once(); // true

Return values for `__invoke` calls can be set just like for method calls.
Either:

    $stub->__invoke->returns('return value');
    $stub(); // 'return value'

or using a convenience method `returns()` on the stub itself:

    $stub->returns('return value');
    $stub(); // 'return value'

## <a name="serialize" href="#serialize">Serializing</a>

Stubs can be serialized and unserialized. Sham records both `__sleep` and
`__wakeup`.

    $stub = new \sham\Stub();
    $waken = unserialize(serialize($stub));

    $waken->got('__sleep')->once(); // true
    $waken->got('__wakeup')->once(); // true

Stubbed method return values and exceptions are preserved but side effects are
not. This is because they are implemented with anonymous functions and PHP
can't serialize those.

    $stub->method->given('something')->returns('return value');
    $waken = unserialize(serialize($stub));

    $waken->method('something'); // 'return value'

# <a name="filter" href="#filter">Filtering calls (asserting)</a>

To investigate your stub objects you use the `got()` method. It filters the
calls by given criteria and returns a call list object (`sham\CallList`). The
call list object has some helpful methods you can use when asserting. These
methods don't throw exceptions. Use your test runner for actual asserting.

To check if `foo()` was called on `$stub` you would do this:

    $stub = new \sham\Stub();
    $stub->foo();

    $stub->got('foo')->once(); // true

To check if `foo()` was called once with 'first' as the only parameter:

    $stub->got('foo', 'first')->once();

To check if `foo()` was called with anything as the first param and `bar` as
the second param:

    $stub->got('foo', sham\Sham::any(), 'bar')->once();

The special `sham\Sham::any()` call returns a matcher object which matches anything.
This is useful when you are writing a test which only needs to test a certain
parameter and ignore the others.


# <a name="data" href="#data">Data objects</a>

Sham objects can act as value or entity objects. All property access,
array access and iteration is recorded. The data it operates on is set using
`shamSetData()` or by setting the properties and array indexes directly.
Property access, array access and iteration all operate on the same data.

    $stub->shamSetData(array(
        'key' => 'value',
    ));

    $stub->key   // 'value'
    $stub['key'] // 'value'

## <a name="properties" href="#properties">Properties</a>

Properties can be set and retrieved, and it works just like you'd expect.
Under the hood all the calls get recorded. This is useful when you are
stubbing out an entity or an Active Record object:

    $record = new \sham\Stub();

    $record->name = 'Antti';
    $record->save();

    $record->got('__set', 'name', 'Antti')->once(); // true
    $record->got('save')->once();                   // true

    // ditto.
    $record->name // 'Antti'
    $stub->got('__get', 'name')->once(); // true

If you call `isset()` on a non-existent property, and `__isset()` call will be
recorded.

    isset($stub->invalid); // false
    $stub->got('__isset', 'invalid')->once(); // true

If you unset a property, the property will be unset and a `__unset` call will
be recorded.

    $stub->prop = 'value';
    unset($stub->prop);
    $stub->got('__unset', 'prop')->once(); // true

## <a name="arrayaccess" href="#arrayaccess">ArrayAccess</a>

Sham implements the `ArrayAccess` interface and records all of those calls.

    // retrieve with array access
    $stub['key'] // 'value'
    $stub->got('offsetGet', 'key')->once(); // true

    // set offset
    $stub['other'] = 'value';
    $stub->got('offsetSet', 'other', 'value')->once(); // true


## <a name="iteration" href="#iteration">Iteration</a>

You can iterate over the data you've set with `\sham\Stub::shamSetData()`. 
All of the calls implemented by `Iterator` will be recorded.

# <a name="api" href="#api">API</a>

** `sham\Sham`**

Methods:

* `any()` - Returns a matcher which matches any value. Used to indicate a
  parameter that we don't want to test right now. Shorthand for `new
  \sham\matcher\Any()`.

* `create(string $spec)` - Build a new stub based on the `$spec` class. Returns
  an object which is an instance of the spec.

**`sham\Stub`**:

Methods:

* `shamSetData()` - set the data for `__get`/`__set` and `ArrayAccess`.

* `got()` - a proxy for `sham\CallList::filter()`.


**`sham\CallList`**:

Methods:

* `filter([$name [, $... ]])` - Filters calls by name and parameters. Returns a
new call list with the matched calls.

* `first()` - Returns the first `sham\Call` object in the list.

* `times($count)` - Checks if there are exactly `$count` amount of calls in the
  list. Returns a boolean.

* `once()` - Checks if there is exactly one call in the list. Returns a boolean.

* `never()` - Checks if there are no calls in the list. Returns a boolean.
  `true` if there are no calls.


**`sham\Call`**:

Properties:

* `return_value` - The value the call returned.

* `params` - An array of parameters the call was made with.

# <a name="license" href="#license">License</a>

Sham is licensed under the BSD license.

Copyright (c) 2010, Antti Holvikari  
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this
list of conditions and the following disclaimer.

* Redistributions in binary form must reproduce the above copyright notice, this
list of conditions and the following disclaimer in the documentation and/or
other materials provided with the distribution.

* Neither the name of the Sham nor the names of its contributors may be
used to endorse or promote products derived from this software without specific
prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

