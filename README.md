Mocker - mocking library for PHP 5.3
====================================

Mocker is a mocking library for PHP 5.3 and up. The idea behind Mocker is to
allow everything to be done to a Mocker instance. Mocker records everything done
to it, you can then later expect what happened. Thus, you can use Mocker for
both mocking and stubbing.

A Mocker implements everything and can be passed anywhere. The goal is to record
everything that is possible in PHP. This includes:

* Method calls (done)
* static calls
* constuctor calls
* __invoke calls (done)
* property access
* array access (done)
* method overloading (done)
* property overloading (done)
* static overloading
* iteration
* serialization

A Mocker implements:

* Countable
* ArrayAccess

etc.

Author: Antti Holvikari <anttih@gmail.com>
