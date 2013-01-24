TheMissingModel
===============

A small, chainable model class for CodeIgniter, loosely inspired by jQuery

Requires CodeIgniter 2.1+


Getting Started
---------------

- Add MY_Model.php to your "application/core" folder (create it if it does not exist).

- Create your models objects as subclasses of MY_Model, like so: ```class User extends MY_Model```

- See the [CI User Guide](http://ellislab.com/codeigniter/user-guide/general/creating_libraries.html) for more details.


Usage
-----

This class turns every model object into a container for data, similar to the way that jQuery is a container for DOM objects. Data is stored in an internal array ($Obj->data). This data can be worked with as though it were multiple items, or a single item; if acting on a single item, such as calling $Obj->value(), the object acts only on the first item.

Each model is intended to be tied to a specific table. In your model subclass, set this value with the name of that table: ```public $table = 'my_table';```. Though each model is meant to represent one table, you don't have to make a model for every table, and you can still do JOINs as part of your queries (see below). Or, if you don't wish to tie your model to a table (for instance, when using an [Entity-Attribute-Value](http://en.wikipedia.org/wiki/Entity%E2%80%93attribute%E2%80%93value_model) schema), you can leave the $table attribute blank, in which case the ->query(), ->save(), and ->insert() methods will not function.

Most actions return a copy of the object itself, allowing actions to be chained on the same object, similarly to jQuery or CodeIgniter's ActiveRecord. It is encouraged to follow this convention on your own Model classes as well (i.e., ending most methods with ```return $this;```). Success and failure should be determined based on the internal $Obj->errors array, and/or the presence of data.

One last tidbit: This class assumes you use "id" as a primary key. If you use a different primary key, be sure to change this value in your subclassed model, or pass in the desired value to the relevant function(s).


Details
-------

The class itself is short and sweet, and you can treat the code as documentation. But for convenience, here are the included attributes and methods:

### Attributes ###

**$table**: Full name of primary database table.

**$data**: Internal data storage.

**$errors**: Internal array of errors. Error strings are recommended, but can be numbers, arrays, or objects if desired.

**$validation**: An optional array for CodeIgniter's [Form Validation](http://ellislab.com/codeigniter/user-guide/libraries/form_validation.html) functionality. Allows you to keep validation rules in the model instead of the controller.


### Methods ###

**sql("SELECT * FROM my_table")**

Populates internal data with results of SQL query.


**query(array('field_name' => 'some_value'), 'field1, field2')**

Simple WHERE queries, and optional SELECT.

- - -

**getAll()**

Return array of all internal data.


**getOne()**

Return a single item and increment counter. Useful for looping.


**get(5)**

Get a specific item if present. Internal data is numerically indexed, starting at zero.


**getIndexed()**

Retrieve all data, indexed by primary key or the supplied field


**getKeyValue('some_field')**

Create key-value pairs from internal data. Useful for populating <option> elements. Defaults to array('id' => 'name').


**rewind()**

Reset the internal counter for getOne().

- - -

**indexArray()**

Utility function. Does the same thing as getIndexed(), but on any array.


**keyValue()**
Utility function. Does the same thing as getKeyValue(), but on any array.

- - -

**value('some_field')**

Get the desired field for the current item in the array (usually the first).


**setValue('some_field', 'a value')**

Set the desired field for the current item in the array (usually the first).

- - -

**save()**

Save all internal data to database. Intelligently UPDATEs or INSERTs based upon the presence of a primary key in the data.


**insert(array('some_field' => 'a value'))**

INSERT supplied array into database, and load into internal data, including the resultant primary key.

- - -

**validate()**

Wrapper for CodeIgniter's Form Validation run() function. Uses rules set in the $validation attribute.


**error("Something happened")**

Adds an error to the internal error stack.


**success()**

Returns true if there are no internal errors.


**clearErrors()**

Deletes all internal errors.


**clear()**

Deletes all internal data.


### Simple JOINs ###

If you pass an additional array to the ->query() function, it will attempt to LEFT JOIN using the supplied keys and values, like so:

```$Obj->query(array('food' => 'tacos'), array('other_table', 'other_table.linking_id = main_table.id'), '*')```


### Complex Data Relationships ###

The simple way to use this class is for small wrappers for specific tables. However, you can also use it for complex multi-table relationships, by creating your own functions for specific queries, and/or over-riding the standard functions such as ->query() and ->save().


### Saving Multi-Table Data ###

If you are retrieving fields other than your primary table, whether via JOINs, custom queries, or SQL functions such as SUM(), you will need to specify which fields to update when using the ->save() function, like so:

```$Obj->save(array('some_field', 'some_other_field'));```

By default, ->save() will attempt to save all fields it finds, which works great with single-table data, but otherwise will throw an error.


### One Last Thing ###

You can choose on a per-model basis whether or not to use this model class, if so desired. To use CodeIgniter's vanilla model, just use ```class User extends CI_Model``` just like normal.


Example
-------

Coming Soon!