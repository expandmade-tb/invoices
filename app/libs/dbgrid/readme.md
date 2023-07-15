# DbCRUD for SQLite / mySQL

A simple Database CRUD

# Properties

- [Methods](#methods)
- [Controller](#controller)
- [Example](#example)

## date_fmt
```PHP
    public string $date_fmt = 'd-m-Y';
```
Defines the output format for dates.
___
## form_back
```PHP
    public string $form_back = 'back';
```
The form button text for *back*.
___
## form_delete
```PHP
    public string $form_delete = 'delete';
```
The form button text for *delete*.
___
## form_save
```PHP
    public string $form_save = 'save';
```
The form button text for *save*.
___
## grid_add
```PHP
    public string $grid_add = '<a class="btn btn-primary" href="[:script_name]/add" role="button">Add</a>';    
```
The grid button for *add*.
___
## grid_delete
```PHP
    public string $grid_delete = '<a class="btn btn-danger btn-sm" href="[:script_name]/delete/[:identifier]" role="button">Delete</a>';
```
The grid button for *delete*.
___
## grid_edit
```PHP
    public string $grid_edit = '<a class="btn btn-primary btn-sm" href="[:script_name]/edit/[:identifier]" role="button">Edit</a>';
```
The grid button for *edidt*
___
## grid_search
```PHP
    public string $grid_search = '<form class="d-flex method="post"> <input class="form-control" type="search" name="search" value="[:search]" placeholder="Search" aria-label="Search"><a href="[:script_name]/clear" style="margin: 0 10px 0 -20px; display: inline-block;" title="Clear Search">x</a> <input type="submit" name="search_submit" value="Search" id="search_submit" class="btn btn-primary"/> </form>';
```
The grid form and button for *search*
___
## grid_show
```PHP
    public string $grid_show = '<a class="btn btn-primary btn-sm" href="[:script_name]/show/[:identifier]" role="button">Show</a>';
```
The grid button for *show*
___
## grid_sql
```PHP
    public string $grid_sql = '';
```
Sql statement for grid selection
___
## limit
```PHP
    public int $limit = 100;
```
Limits the number of rows for one page.
___
## show_titles
```PHP
    public bool $show_titles = true;
```
Show the grid titles.
___
## time_fmt
```PHP
    public string $time_fmt = 'h:i:s';
```
The output format for time.
___
## use_sessions
```PHP
    public bool $use_sessions = false;
```
Use sessions or cookie (default).

# Methods

- [Properties](#properties)
- [Controller](#controller)
- [Example](#example)

## __construct
```PHP
    public function __construct(DBTable $table)
```

Constructor method of the *DbCrud* class
___
## addFields
```PHP
    public function addFields (string $fields)
```

Fields to be used on the Form when adding data.
___
## callbackDelete
```PHP
    public function callbackDelete (callable $callback)
```

A function to be called when a row has be deleted. This callback overwrites the standard delete method !
___
## callbackInsert
```PHP
    public function callbackInsert (callable $callback)
```

A function to be called when a row has to be inserted, This callback overwrites the standard insert method !
___
## callbackUpdate
```PHP
    public function callbackUpdate(callable $callback)
```

A function to be called when a row has to be updated, This callback overwrites the standard update method !
___
## clear
```PHP
    public function clear()
```

Clears the grids search value and executes a new select.
___
## delete
```PHP
    public function delete($id) : bool
```

Deletes the *id* of the current grid table.
___
## editFields
```PHP
    public function editFields (string $fields)
```

Fields to be used on the Form when editing data.
___
## encode_identifier
```PHP
    public function encode_identifier (bool $encode = true)
```

Encodes the *id* of a row when used in the URI.
___
## fieldOnChange
```PHP
    public function fieldOnChange(string $field, string $js_function)
```

Set a javascript function which should be called on change for the given field.
___
## fieldPlaceholder
```PHP
    public function fieldPlaceholder (string $field, string $placeholder)
```

Set the placeholder for the given field.
___
## fieldTitles
```PHP
    public function fieldTitles (string $fields, string $titles='')
```

Set the field titles for the given fields.
___
## fieldType
```PHP
    public function fieldType (string $field, string $type, string $valuelist='')
```

Field type are automatically detected. Using this method will overwrite the field type found. Currently the following field types are supported:

+ checkbox
+ datalist
+ date
+ datetime
+ integer
+ numeric
+ select
+ text
+ textarea
___
## fields
```PHP
    public function fields (string $fields)
```

Calls the following methods:

+ addFields
+ editFields
+ readFields
+ gridFields
+ fieldTitles
___
## form
```PHP
    public function form(string $action, $id='')
```

The controller should call this method when a row has to be edited, added or shown. The parameter *action* can either be:

+ add
+ edit
+ show
___
## formatField
```PHP
    public function formatField(string $field, callable $callable)
```

Set the callback function to format a field. whenever a this field has to be shown in the grid or the form, this function will be called.
___
## grid
```PHP
    public function grid (int $page=1)
```

The controller should call this method when the grid has to be shown.
___
## gridFields
```PHP
    public function gridFields (string $fields)
```

Set the fields which should be shown in the grid.
___
## linkedTable
```PHP
    public function linkedTable(string $controller, string $button_value, string $method='index')
```

Adds an additional buttom to the form and links it to the depending table controller and method.
___
## model
```PHP
    public function model() : DBTable
```

Returns the current table model used in the grid.
___
## readFields
```PHP
    public function readFields (string $fields)
```

Sets the field shown in the form when action button *show* has been selected.
___
## readonlyFields
```PHP
    public function readonlyFields (string $fields)
```

Sets all readonly fields in the form.
___
## requiredFields
```PHP
    public function requiredFields (string $fields)
```

Sets all the required fields in the form. This is automatically set for all required constraints of the table.
___
## searchFields
```PHP
    public function searchFields (string $fields)
```

Set all fields which can be searched/filtered.
___
## setContstraints
```PHP
    public function setContstraints(string $field, string $depending_table, string $depending_field)
```

Checks a deletion constraint. In the form the delete button will be disabled if the constraint is not fullfilled. In the following example the parent table is "Products". The depending table is "OrderDetails". The form will check, if on deletion of a "Products" row, there are still depding "OrderDetails" rows. 

```PHP
    $this->crud->setContstraints('ProductID', 'OrderDetails', 'ProductID');
```
___
## setRelation
```PHP
    public function setRelation(string $field, string $relatedField, string $relatedTable)
```

Set a relation 1-n database relation. This will automatically create a dropdown list to the field and show the actual value of the field and not just a primary key to the list. If a table is quite large, see *setSearchRelation*.
___
## setRule
```PHP
    public function setRule(string $field, callable $callback)
```

Add a validation rule to the given field.
___
## setSearchRelation
```PHP
    public function setSearchRelation(string $field, string $relatedTable, string $relatedField) {
```

Set a relation 1-n database relation. This will automatically create a live search to the field and show the actual value of the field and not just a primary key.
___

# Controller

- [Properties](#properties)
- [Methods](#methods)
- [Example](#example)

## Public Methods to be implemented

All methods are requested if they are needed. The only mandatory method is *grid*:

```PHP
    public function grid(int $page) {
        $this->data['dbgrid'] = $this->crud->grid($page);
        $this->view('Crud', $this->data);
    }
```

Other methods have to be implemented if you allow to *add*, *edit*, *delete* and *search* columns:

```PHP
   public function add() { 
        $this->data['dbgrid'] = $this->crud->form('add');
        $this->view('Crud', $this->data);
    }

    public function edit($id) {
        $this->data['dbgrid'] = $this->crud->form('edit', $id);
        $this->view('Crud', $this->data);
    }

    public function delete($id) {
        $this->crud->delete($id);
        $this->data['dbgrid'] = $this->crud->grid();
        $this->view('Crud', $this->data);
    }

    public function clear() {
        $this->crud->clear();
        $this->data['dbgrid'] = $this->crud->grid();
        $this->view('Crud', $this->data);
    }
```
___

## Callback Methods

### Callback on deletion of a row
Set:
```PHP
    $this->crud->callbackDelete([$this, 'example_row_delete']);
```
Format:
```PHP
    public function example_row_delete($did) {
        // code to delete the data
    }
```
___

### Callback on formatting a value
Set:
```PHP
    $this->crud->formatField('example_field', [$this, 'example_field_formatting']);
```
Format:
```PHP
    public function example_field_formatting(string $field, string $source, string $value) : string {
        // code to format the field
    }
```
___

### Callback on inserting a row
Set:
```PHP
    $this->crud->callbackInsert([$this, 'example_row_insert']);
```
Format:
```PHP
    public function example_row_insert($data) {
        // code to insert the data
    }
```
___

### Callback on field validation
Set:
```PHP
    $this->crud->rule('example_field', [$this, 'example_field_validate']);
```
Format:
```PHP
    public function example_field_validate($value, $field) : string {
        // code to validate the field
    }
```
___
### Callback on updating a row

Set:
```PHP
    $this->crud->callbackUpdate([$this, 'example_row_update']);
```

Format:
```PHP
    public function example_row_update($id, $data) {
        // code to update the data
    }
```
___

# Example

- [Properties](#properties)
- [Methods](#methods)
- [Controller](#controller)

```PHP
<?php

namespace controller;

use dbgrid\DbCrud;
use helper\Helper;
use Menu\MenuBar;
use models\track_model;

class CrudTrack extends BaseController {
    private DbCrud $crud;

    function __construct() {
        $this->data['css_files'] = [
            STYLESHEET.'/styles.css',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css'            
        ];
        $this->data['js_files'] = [
            "https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js",
            JAVASCRIPT.'/livesearch.js'
        ];
        
        $this->data['icon'] = IMAGES.Helper::env('app_image');
        $this->data['title'] = Helper::env('app_title', 'Remote Tables');
        $this->data['menu'] = MenuBar::factory()->get();
        $this->crud = new DbCrud(new track_model());

        // crud customization 
        $this->crud->grid_show = '';
        $this->crud->grid_sql = 
        'select
            Track.Name as TrackName,
            Album.Title as AlbumTitle, 
            MediaType.Name as MediaTypeName,
            Genre.Name as GenreName,
            Composer,
            Milliseconds,
            UnitPrice,
            Title
        from Track 
            left join Album on Track.AlbumId = Album.AlbumId 
            left join MediaType on Track.MediaTypeId = MediaType.MediaTypeId
            left join Genre on Track.GenreId = Genre.GenreId';
    
        $this->crud->gridFields('TrackName,AlbumTitle,MediaTypeName,GenreName,Milliseconds,UnitPrice,Composer,Title');

        $this->crud->fieldTitles('TrackName,AlbumTitle,MediaTypeName,GenreName,Milliseconds,UnitPrice,Composer,Title',
                                 'Track,Album,MediaType,Genre,Milliseconds,Unit Price,Composer,Title');

        $this->crud->searchFields('TrackName,MediaTypeName,Composer,Title');

        $this->crud->setRelation('GenreId', 'Name', 'Genre');
        $this->crud->setRelation('MediaTypeId', 'Name', 'MediaType');
        $this->crud->setSearchRelation('AlbumId', 'Album', 'Title');
    }

    public function index () {
       $this->grid(1);
    }

    public function add() {
        $this->data['dbgrid'] = $this->crud->form('add');
        $this->view('Crud', $this->data);
    }

    public function edit($id) {
        $this->data['dbgrid'] = $this->crud->form('edit', $id);
        $this->view('Crud', $this->data);
    }

    public function delete($id) {
        $this->crud->delete($id);
        $this->data['dbgrid'] = $this->crud->grid();
        $this->view('Crud', $this->data);
    }

    public function grid(int $page) {
        $this->data['dbgrid'] = $this->crud->grid($page);
        $this->view('Crud', $this->data);
    }

    public function clear() {
        $this->crud->clear();
        $this->data['dbgrid'] = $this->crud->grid();
        $this->view('Crud', $this->data);
    }
}```