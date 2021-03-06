open qoob framework
-------------------
the qoob is a semi-RESTful php api framework designed to simplify and expedite the process of creating dynamic web applications.

```text
                           MM.                                                   
                           MNNNN.                                                
                           MM...NMN.                                             
                           MN......ONN                                           
                           MN.......ZN                                           
                           MN.......ZN                                           
                           MNZ......ZN         MMMMMMM  MMMMMMM  MMMMMMM  MMMMM  
                         ,ND."MND...ON         MM   MM  MM   MM  MM  MMM  MM   MM
                      ,NNN..NN. "MNDON         MM   MM  MM   MM  MM       MM   MM
                   ,NNN.......DNN. "ON         MMMMMMM  MMMMMMM  MMMMMMM  MM   MM
                 NNM..............NNM                   MM                       
             ,N N. "NN7........ONN" ,N                  MM                MM     
          ,NNNN NNNN."ONN...ZNN" ,NMON                  MM                MM     
       ,NNI..ON NO...NN. "NN" ,NN...MN                                    MM     
     NN......ON NZ......NN INN......MN         MMMMMMM  MMMMMMM  MMMMMMM  MMMMMMM
     N.......ON NZ.......N $N.......MN         MM   MM  MM   MM  MM   MM  MM   MM
     N.......ZN N$.......N $N.......MN         MM   MM  MM   MM  MM   MM  MM   MM
     N.......MN NN.......N $N.......MN         MMMMMMM  MMMMMMM  MMMMMMM  MMMMMMM
     N....NN$"    "NN....N $N....NN",ON.            MM                           
     N.NNN"          "N..N $N..N" ,MN...MN.         MM                           
     NI"                "N $N" ,NM........"MM.      MM                           
                             MM...............MM                                 
                              "MMM.........MNM"                                  
                                 "MMN...NMM"                                     
                                    "NMM"  
```
##DOCS (wip)
 - [version](#version)
 - [requirements](#requirements)
 - [getting started](#getting-started)
 - [config](#config)
 - [loading classes](#loading-classes)
 - [routing](#routing)
 - [templates](#templates)
 - [databases](#databases)

##version
this is actually version 2.x of the open qoob framework and is a complete rewrite from the ground up. if you're looking for the legacy versions, they are archived here: 
 - Framework: http://github.com/xero/open_qoob_legacy/ 
 - CMS: http://github.com/xero/open_qoob_cms_legacy/

follow [@openqoob](http://twitter.com/openqoob) for updates via social media

##requirements
 - PHP 5.1.2
 - mySQL 5.0+
 - Apache mod_rewrite

##getting started
###database installation
install the `db.sql` file on your mysql server. add the connection information to the `qoob\app\config` file.

###framework init
to load the open qoob into memory in your index.php call:
```php5
$qoob = require('qoob/open_qoob.php');
```
to get your qoob reference in any other class call:
```php5
$qoob = qoob::open();
```

##config
INI files can be used to load configuration variables into the library. pass the location of the file to the `config` method to load it.
```php
$qoob->config('qoob/app/config.ini.php');
```
variables in the file will be added to the library with the CONFIG pseudo namespace.
```
somevar="something"
```
```
CONFIG.somevar
```
sections in INI files will become a second level namespace in CONFIG.SECTION.name syntax.
```
[general]
somevar="something"
```
```
CONFIG.GENERAL.somevar
```
###best practice
i suggest naming INI files with a .php extension and adding `;<?php exit(); __halt_compiler();` to the first line. since INI files are simply text files, anyone who knows (or guesses) their location will be able to read them. giving the file a php extension and adding the exit directive to the first line will keep the file’s contents hidden. 

here’s and example config.ini.php file:
```php
;<?php exit(); __halt_compiler();
debug=true

[general]
author="xero harrison"
copyright="creative commons attribution-shareAlike 3.0 unported"
keywords="qoob, open qoob, openqoob, framework, code, api"
description="the open qoob framework"
```

##loading classes
the qoob uses the new (php 5.1.2) `spl_autoload` methods for automatically loading classes. if your class exists within the qoob's file structure there is no need to call `include` or `require` before using it. this method is also namespace aware.

loading the benchmark class (located at /qoob/utils/benchmark.php):
```php5
$bench = new qoob\utils\benchmark;
$bench->mark('name');
```
it is also possible to load a class *into* the open qoob framework and have it be addressable via `$this->classname` internally or `$qoob->classname` externally. you accomplish with with the qoob `load` method, which is also namespace aware.
```php5
$this->load('qoob\utils\benchmark');
$this->benchmark->mark('name');
```
**note:** the load method will strip off the namespace and create a public variable from the name of the class only.

you can also autoload classes when in the config file is loaded. simply add the namespace and class as a value of the autoload config array.

here's an example of loading the benchmark and logz utility classes via the config file:
```php
[autoload]
benchmark="qoob\utils\benchmark"
logz="qoob\utils\logz"
```

##routing
the qoob has it's own routing system. routes map urls and requests to either anonymous functions or class methods.
###closure style callbacks 
when this route is requested the anonymous function will be called.
```php5
$qoob->route('GET /home', function() {
  echo '<h1>open qoob</h1><p>this is the home page.</p>';
});
```
###class method callbacks
when this route is requested an instance of the `some_class` will be created and its `some_method` will be called.
```php5
$qoob->route('GET /something', 'some_class->some_method');
```
you can also use static methods
```php5
$qoob->route('GET /static', 'some_class::static_method');
```
you can also use class namespaces.
```php5
$qoob->route('GET /something', 'name\space\some_class->some_method');
```

route patterns have two required parts and one optional one. 
 - __HTTP verb__ - `GET`, `HEAD`, `POST`, `PUT`, `PATCH`, `DELETE`, or `CONNECT`
 - __URI pattern__ - e.g. `/home`, `/user/42`, `/blog/page/9`, `/test/:arg`, etc
 - __request type__ _optional_ - `[SYNC]` or `[AJAX]`

you can create URI argument variables with `:name` syntax. the results of which are passed to your callback function at run time.
```php5
$qoob->route('GET /user/:id', function($args){
  echo "user id: ".$args['id'];
});
```
```php5
$qoob->route('POST /date/:month/:day/:year', function($args){
  echo "data for date: ".$args['month'].'//'.$args['day'].'//'.$args['year'];
});
```
you can also create valid routes for mulitple HTTP verbs (e.g. GET or POST)
```php5
$qoob->route('GET|POST /hello', function($args){
  echo "hello word!";
});
```
using the optional request type allows you to handle the same route in two different methods depending on the context. use either the SYNC or AJAX keyword wrapped in square brackets. if a request type is not supplied SYNC is assumed by default. say you have a method that needs to return data in HTML for a synchronous (SYNC) request and JSON for an asynchronous (AJAX) request:
```php5
$qoob->route('GET /info [SYNC]', 'request_types->sync');
```
```php5
$qoob->route('GET /info [AJAX]', 'request_types->ajax');
```
RESTful apps respond to request semantically. so depending on the http verb used (e.g. GET, HEAD, POST) variables will be retrieved from a different request method. the qoob handles this internally and gives you access to appropriate ones via an argument variable `$args` passed to the constructor of a callback function.
```php5
$qoob->route('POST /testPostVars', function($args){
  echo "these are from the $_POST superglobal: ".print_r($args, true);
});
$qoob->route('GET /testGetVars', function($args){
  echo "these are from the $_GET superglobal: ".print_r($args, true);
});
$qoob->route('DELETE /testDeleteVars', function($args){
  echo "these are from php://input: ".print_r($args, true);
});
```
if uri arguments are present for a route, the uri arguments will be recursivly merged with the request variables
```php5
$qoob->route('POST /testMultiVars/:one/:two/:three', function($args){
  echo "these are from the uri request and $_POST superglobal: ".print_r($args, true);
});
```  

##templates
the qoob uses it's own "mustache style" template engine. this engine is very simple compared to other [fully featured mustache implementations](http://mustache.github.com/).

###loading a template
load the 'stache' template engine. then call the stache->render function.

the function has two mandatory and one optional arguments:
- __filename__ [string] - template file name (minus .html extension) 
- __data__ [array] - name value pairs to replace in the template file
- __return__ [boolean] _optional_ - auto echo on false, return string on true (default = false)

here's an example:
```php5
    $qoob->load('qoob\core\view\stache');
    $qoob->stache->render(
      'templateFileName', 
      array(
        'title' => 'open qoob',
        'body'=> '<p>hello world!</p>',
        'year' => date('Y'),
        'author' => 'xero harrison'
      )
    );    
```
you can also use templates with no replacements
```php5
    $qoob->load('qoob\core\view\stache');
    $qoob->stache->render('templateFileName');    
```
###creating a template
creating stache templates are simple, you replace any dynamic value with a mustache variable! but templates are not limited to html. they could be emails, xml, excel, or any other type of text file.

here's an example:
```xml
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>{{&title}}</title>
  </head>
  <body>
    <header><h1>{{&title}}</h1></header>
    {{@body}}
    {{!this is a comment}}
    <p>{{optional}}</p>
    <footer>&copy; {{#year}} {{#author}}</footer>
  </body>
</html>
```

###mustache types
there are five types of mustaches that the qoob currently supports:
- __{{var_name}}__ - regular variable (escaped)
- __{{&unescaped}}__ - an unescaped variable (may contain html)
- __{{!ignored}}__ - a variable that will not be rendered
- __{{#required}}__ - required variables will throw exceptions if not set
- __{{@unescaped_required}}__ - unescaped required variable

**note:** any non-required variable will be replaced with an empty string if not set by the render function.

##databases
the qoob currently only supports mysql databases. while it's possible to use the mysql/mysqli adapter in any class, IMHO using models makes the most sence. to create a model simply create a class that extends one of the mysql classes: `\qoob\core\db\mysql` or `\qoob\core\db\mysqli`. add your connection variables in the class constructor then connect. after that all functions of the model will be ready to execute queries. 

###connecting to a db server
there are two methods necessary to connect to a mysql database, `init` and `connect`.

the `init` method takes four mandatory parameters and two optional ones.
- __db_host__ [string] - the database server host name
- __db_user__ [string] - the database users
- __db_pass__ [string] - the password for the database user
- __db_name__ [string] - the name of the default database to select
- __asciiOnly__ [boolean] _optional_ - true will allow only ascii characters, false will allow all printable characters (default = true)
- __keepAlive__ [boolean] _optional_ - true will not close the database on class destruction (default = false)

once the necessary variables are set with `init` simply call the `connect` function.

###sql queries
mysql queries are setup very much like qoob routes. variables in your sql statements are prefixed with a `:` (colon) and are written inline. you also pass an array of name value pairs to be sanitized and replaced in your sql statement.

here's an example:
```php5
    $result = $qoob->mysql->query(
      "SELECT * FROM  `code` LIMIT :limit, :offset;",
      array(
        'limit' => 0,
        'offset' => 30
      )
    );
    print_r($result);
```
if you want to insert data, set the 3rd parameter of the query function (results) to false. 
```php5
    $qoob->mysql->query(
      "INSERT INTO `code` (`code_id`, `key`, `val`) VALUES (NULL, ':key', ':val');",
      array(
        'key' => 'hello',
        'val' => 'world'
      ),
      false
    );
```
if you want the newly created ID from an insert statment, call the `insertID` function.
```php5
    $newID = $qoob->mysql->insertID();
```
if you want just a count of rows effected by your query set the 4th parameter to true. then call the `num_rows` function.
```php5
    $qoob->mysql->query(
      "SELECT * FROM  `code`;",
      array(),
      false, //dont return results
      true   //count rows
    );
    $count = $qoob->mysql->num_rows();
```

###auto-sanitization
there are currently three methods of sanitization applied to each variable. 
- __stripslashes__ - only called if magic quotes are enabled (legacy compatability)
- __asciiOnly__ - depending on the value of the `asciiOnly` class variable, all non-printable characters are removed. by default only ascii characters are allowed, if you need to support special/international characters (e.g. ñ, ½, etc) change the value to false.
- __mysql_real_escape_string__ - uses the db server's internal sql injection protection routines.
