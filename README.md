Sham - mocking library for PHP 5.3
==================================

Sham is a mocking library for PHP 5.3 and up. The idea behind Sham is to
allow everything to be done to a Sham instance. Sham records everything done
to it, you can then later expect what happened. Thus, you can use Sham for
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

Create a mock either by mocking an existing class, or by instantiating Sham_Mock directly:
    
    $mock = Sham::create('My_Class'); // $mock instanceof My_Class => true
    $mock = new Sham_Mock();

Call some methods:
    
    $mock->foo();
    $mock->bar('param 1');

By default method calls return new Sham_Mock objects, but you can set the return value:

    $mock->foo->returns('return value');

You can also tell methods to throw:
    
    $mock->foo->throw();
    $mock->foo->throws('Exception');
    $mock->foo->throws(new Exception());

Once your test action has been run, you can inspect the mock to see if
your code interacted with it correctly.

    // make a few calls
    $mock->foo('param 1');
    $mock->foo('param 1', 'param 2');

    // Sham_CallList::calls is an array of Call objects
    count($mock->calls('foo')->calls); // 2

    $mock->calls('foo', 'param 1', 'param 2')->once(); // true

    // use Sham::ANY to match any parameter value
    $mock->calls('foo', Sham::ANY, 'param 2')->once(); // true

## Array and property access

You can also use Sham_Mock as a value object. It records all ArrayAccess and
__get/__set method calls.

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
    $mock->calls('__get')->first()->returns();      // 2
    $mock->calls('__set', 'some', 'value')->once(); // true
    $mock->calls('offsetSet', 'other', 'foo');      // true
    $mock->calls('__get', 'other')->once();         // true

## Iteration

You can also iterate over the data. All of the calls implemented by Iterator
will be recorded.
