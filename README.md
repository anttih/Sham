Sham - mocking library for PHP 5.3
==================================

Sham is a mocking library for PHP 5.3 and up. The idea behind Sham is to
allow everything to be done to a Sham instance. Sham records everything done
to it, you can then later expect what happened. Thus, you can use Sham for
both mocking and stubbing.

The goal is to record everything that is possible in PHP. This includes:

* Method calls
* __invoke calls
* property access
* array access
* method call overloading
* property access overloading
* iteration
* serialization

A Sham implements:

* Countable
* ArrayAccess

