Sham - mocking library for PHP 5.3
==================================

Sham is a mocking library for PHP 5.3 and up. The idea behind Sham is to
allow everything to be done to a Sham instance. Sham records everything done
to it, you can then later investigate what happened. Thus, you can use Sham for
both mocking and stubbing.

The goal is to record everything that is possible. This includes:

* Method calls
* __invoke calls
* property access
* array access
* method call overloading
* property access overloading
* iteration
* serialization

A Sham implements:

* ArrayAccess
* Iterator

Create a mock either by mocking an existing class, or by instantiating
`Sham_Mock` directly:
    
    $mock = Sham::create('My_Class'); // $mock instanceof My_Class => true
    $mock = new Sham_Mock();

By default method calls return new `Sham_Mock` objects, but you can set the return value:

    $mock->foo->returns('return value');

You can also tell methods to throw:
    
    $mock->foo->throws();
    $mock->foo->throws('Exception');
    $mock->foo->throws(new Exception());

For exceptionally complex cases you can write a stub implementation:

    $mock->foo->does(function($x, $y) { return $x + $y; });

Now call some methods:
    
    $mock->foo();
    $mock->bar('param 1');

Once your test action has been run, you can inspect the mock to see if
your code interacted with it correctly.

    // make a few calls
    $mock->xoo('param 1');
    $mock->xoo('param 1', 'param 2');

    // Sham_CallList::calls is an array of Call objects
    count($mock->calls('xoo')->calls); // 2

    $mock->calls('xoo', 'param 1', 'param 2')->once(); // true

    // use Sham::any() to match any parameter value
    $mock->calls('xoo', Sham::any(), 'param 2')->once(); // true

Methods can also be stubbed to do different things given different parameters:

    $mock->foo->given('zero', Sham::any())->returns(0);
    $mock->foo->given(Sham::any(), 0)->throws();

    $mock->foo('zero', 3); // 0
    $mock->foo('one', 0);  // exception
    $mock->foo('zero', 0); // exception (later stubs get priority)

## Array and property access

You can also use `Sham_Mock` as a value object. It records all `ArrayAccess` and
`__get`/`__set` method calls.

    // first load it with some data
    $mock->shamSetData(array(1, 'key' => 2));

    $mock[0]; // 1
    $mock->key; // 2

    $mock->some = 'value';
    $mock['some']; // value
    
    $mock['other'] = 'foo';
    $mock->other; // 'foo'

    $mock->calls('offsetGet', 0)->once();           // true
    $mock->calls('__get', 'key')->once();           // true
    $mock->calls('__set', 'some', 'value')->once(); // true
    $mock->calls('offsetSet', 'other', 'foo');      // true
    $mock->calls('__get', 'other')->once();         // true

## Iteration

You can also iterate over the data. All of the calls implemented by `Iterator`
will be recorded.
