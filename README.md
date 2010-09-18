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
