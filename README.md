ARC: Ariadne Component Library 
========================= 

A flexible component library for PHP 5.3+ 
----------------------------------------- 

The Ariadne Component Library is a spinoff from the Ariadne Web 
Application Framework and Content Management System 
[ http://www.ariadne-cms.org/ ]

The PHP 5.3 version is very much a work in progress. It is by no
means complete yet.

It is designed to make it simpler to do many common web application
tasks in a way that is easy to extend and is standards compliant and
accessible by default. The components are loosely coupled, they can
generally be used seperately but can be combined to enable more
features. 

All components use dependency injection combined with default factory
methods, so the components are very flexible but easy to use.

It doesn't provide a MVC (Model View Controller) framework or URL 
router, that is left to other frameworks. Instead it provides the 
following components:

Generic components
------------------
- xml: generate, parse and traverse xml, native or through dom methods
- url: create and parse urls and query arguments
- path: parse paths, including relative paths, get parents, etc.
- http: simple http requests and generic access to user input
- tainting: taint variables as unsafe, with automatic filtering

(Web) application building blocks
---------------------------------
- events: dom-like event system
- cache: easy caching system, can be used as a proxy for other objects
- context: a context stack system with automatic inheritance
- config: a configuration/acquisition component
- template: a very simple substitution template language

Connectivity components
-----------------------
- atom
- rss

Coming soon
-----------
These are already in Ariadne, they just need to be refactored.
- html: generate, parse and traverse html, native or through dom methods
- html\form: easy form generation, validation and manipulation
- html\menu: simple menu builder component
- html\table: simple table builder component
- csv: easy csv generation and parsing
- connect\ftp: ftp client
- connect\oauth: openauth client
- connect\twitter: twitetr client
- connect\xmlrpc: easy xmlrpc client

TODO
----
- tests and more tests
- documentation - see [https://github.com/Ariadne-CMS/arc/wiki](the Wiki pages)
  which are far from complete, but you can edit them yourself!
- Refactor some static variables away - e.g. \arc\http::$tainting

What makes ARC useful/interesting/unique?
=========================================

Many people have written about PHP lately and specifically about how
bad it is. I agree and disagree. PHP is powerfull and flexible, but
it isn't consistent. 

Many parts of PHP have not been updated to use newer features to 
maximum effect. Some parts are downright incomprehensible. This
library of components is set up to remedy that for the most common
things we encountered while building Ariadne over the last 14-odd 
years.

The main design idea in ARC is that components should not depend on 
other components, unless absolutely necessary. They should be flexible 
enough to do what you need or be able to be extended easily. In 
addition they should be easy to use, predictable in API and behaviour
and cohere closely to expected PHP behaviour.

We did this using the following architectural guidelines:
- (constructor based) dependency injection.
  No need for complex injection containers or service locators, since
  ARC is not a framework. It does provice factory methods to ease and
  standardize the use of components.
- Limit static / global state.
  Static methods are limited to factory methods. In addition there are
  some static variables which are optional but make life easier for
  simple systems.
- Use Interfaces liberally, define them minimally.
  Wherever there are multiple implementations possible, components 
  define an interface and implement it. Wherever an existing interface
  can be usefully implemented in a component, do so. But interfaces
  themselves should contain only the minimal set of methods needed for
  such a component. It should _never_ define a constructor, since that
  would break constructor based dependency injection.
- Duck typing all the way
  PHP is a dynamic language, strict type checking is out the window. 
  So why not go completely in the opposite direction? Don't specify
  interfaces - or worse: classes - but accept all types of input. As
  long as they implement the needed methods and give access to the 
  requested properties, accept it as correct. If it walks like a duck, 
  smells like a duck and quacks like a duck, you can probably use it
  like a duck.
  The interface definitions are there to be used if you can, or to use
  as a reference specifying which methods and properties should be
  available.
- Be liberal in what you accept
  An extension of the duck typing principal, methods should accept a
  range of input parameters, if they make sense in usage. So wherever
  you would accept a single value, but could as well operate on an
  array of values, do so.
  If you accept string values, also accept objects with a __toString
  method. If you accept an array, also accept array-like objects.
- Be simple, concise and readable
  The code must be simple to read and understand, without the need for
  large docblocks. Because of the ducktyping rule the default defense
  of docblocks - that they assist the IDE in telling you what to type 
  where - is no longer valid. So the only comments in the code are 
  there to explain some non-obvious but unavoidable piece of code. All
  the rest should be easily understandable by reading the how-to-use
  documentation and the code.
- Be consistent in naming and usage.
  Each method and class is named to be consistent with similar methods
  or classes. Argument order is kept similar where possible.
