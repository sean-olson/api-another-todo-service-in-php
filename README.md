# Yet Another To-Do API (YATA)
A simple REST API to support a To-Do web application -- the *Hello World* of frontend frameworks. YATA is written in PHP and depends on 
Apache and MySQL.

## Setting Up YATA:

#### 1. Download the Project 
Clone or download the GitHub [repo](https://github.com/sean-olson/YetAnotherTodoAPI.git) to your computer.

#### 2. Load the Code Files into Server Environment
Implementations may differ significantly, but to get up and running quickly just copy the contents of the `/src` 
folder into the `DocumentRoot` of your Apache Server, as configured in your instance (see the `httpd.conf` file).

#### 3. Setup the Database
The API depends on a MySQL database to function.  Setup is a simple three-step process.

1. Create a database for your To-Do project, named *db_todo*. 

2. Create the database table and view by running the two scripts in the `SQL/` directory of the project folder, running 
each script in your installed instance of MySQL.  Make sure to execute the scripts in order.

    - `1_tbl_todo_items.sql` will create a database table, *tbl_todo_items* where the API stores the to-do items
    - `2_vw_todo_items.sql` will create a database view, *vw_todo_ietms*  that provides a filtered view of 
     *tbl_todo_items*, eliminating deleted to-do items.  

3. Set the DB credentials in the `todo.ini` file.

    Database connectivity and authentication require setting three properties in the *todo.ini* file.
    ````
    db_name = 'db_todo'
    user_name =  'YOUR_USER_NAME_HERE'
    password = 'YOUR_PASSWORD_HERE'
   ````
  
The file is located in the root of the project.  For security reasons, it's expected that this file will 
sit in the same directory as Apache's `DocumentRoot` folder.  If the relative location of the `todo.ini` 
changes in relationship to the code files, the relative path statement used to parse the file will need 
to be updated. (see the `getDbConnection()` method in `src/v1/controllers/db_controller.php`)   


#### 4. Review the .htaccess File 
The rewrite rules in the `.htaccess` file allow use of standard API route names in your application,
rather than the *less-pretty* FQDN with query-string parameters.  The `.htaccess` file needs to be inside the v1 folder, 
alongside the directories for `controllers` and `models`.

#### 5. The `tbl_todo_items` Entity

  - `todo_item_id` bigint(20) **NOT NULL**,
  - `todo_item_name` varchar(255) **NOT NULL**,
  - `todo_item_description` text DEFAULT NULL,
  - `todo_item_due_date` datetime DEFAULT NULL,
  - `todo_item_is_completed` enum('Y','N') **NOT NULL**,
  - `is_deleted` tinyint(1) DEFAULT 1

## Using YATA:
YATA includes a set of routes that support full CRUD operations against the `tbl_todo_items` entity, 
providing a platform for a functional To-Do application.

###### The To-Do Item Schema
    {
        "id":{"type":"integer"},
        "name":{"type":"string", "required":true, "maximum":255},
        "description":{"type":"string"," required":false},
        "due_date":{"type":"string", "format":"date-time", "required":false},
        "is_completed": {"type":string", "enum":["Y", "N"], required":true} 
    }

###### GET Methods
- **`/v1/todo`** : returns an array of all to-do items.
- **`/v1/todo/:id`** : returns the to-do item for the given id (if it exists).
- **`/v1/todo/complete`** : returns an array of all the completed to-do items.
- **`/v1/todo/incomplete`** : returns an array of all the uncompleted to-do items.
- **`/v1/todo/page/:number`** : returns a page of to-do items to the client (if it exists).  Page size is fixed 
at 20 items. 

###### POST Method
- **`/v1/todo`** : requires a to-do item in JSON format, `name` and `is_completed` properties are required.

###### PUT Method
- **`/v1/todo/:id`** : requires a to-do item in JSON format with updated fields only. 

###### DELETE Method
- **`/v1/todo/:id`** 
