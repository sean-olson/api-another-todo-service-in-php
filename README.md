# YATA: Yet Another ToDo API
A simple REST API to support a simple ToDo application -- the *Hello World* of frontend frameworks. The YATA API is written in PHP and depends on 
Apache and MySQL.

## Setting Up YATA:

#### 1. Download the Project 
Clone or download the GitHub repo to your computer.

#### 2. Load the Code Files into Server Environment
Implementations may differ significantly, but to get up and running quickly just copy the contents of the `/src` 
folder into the `DocumentRoot`, as configured in your instance of Apache (see the `httpd.conf` file).

#### 3. Setup the Database
The API depends on the MySQL database to function.  Setup is a simple 
three-step process.

1. Create a database for your ToDo project, named *db_todo*. 

2. Create the database table and view by running the two scripts in the `SQL/` directory of the project folder in your 
MySQL SQL window.  Execute the scripts in order.

    - `1_tbl_todo_items.sql` will create a database table, *tbl_todo_items* where the API stores the ToDo items
    - `2_vw_todo_items.sql` will create a database view, *vw_todo_ietms*  that provides a filtered view of the 
     of the *tbl_todo_items* eliminating the deleted todo items.  

3. Set the DB credentials in the `todo.ini` file.

    Database connectivity and authentication requires the setting of three properties in the *todo.ini* file.
    ````
    db_name = 'db_todo'
    user_name =  'YOUR_USER_NAME_HERE'
    password = 'YOUR_PASSWORD_HERE'
   ````
  
The file is located in the root of the project file.  For security, it's expected that this file will 
sit in the same directory as Apache's root document folder.  If the relative location of the `todo.ini` 
changes in relationship to the code files, the relative path statement used to parse the file will need 
to be updated. (see the `getDbConnection()` method in `src/v1/controllers/db_controller.php`)   


#### 4. Review the .htaccess File 
The rewrite rules in the `.htaccess` file allow use of standard API routes in your application,
rather than the *less-pretty* FQDN with query-string parameters.  If this is more than you want to deal 
with, just know that's what the `.htaccess` file does, and it needs to be inside the v1 folder, alongside 
the directories for `controllers` and `models`.

#### 5. The `tbl_todo_items` Entity

  - `todo_item_id` bigint(20) **NOT NULL**,
  - `todo_item_name` varchar(255) **NOT NULL**,
  - `todo_item_description` text DEFAULT NULL,
  - `todo_item_due_date` datetime DEFAULT NULL,
  - `todo_item_is_completed` enum('Y','N') **NOT NULL**,
  - `is_deleted` tinyint(1) DEFAULT 1

## Using YATA:
YATA includes a set of routes that support full CRUD operations against the `tbl_todo_items` entity, 
providing a platform for a functional ToDo application.

###### TODO Item Schema
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
- **`/v1/todo`** : requires a to-do item in JSON format -- `name` and `is_completed` are required.

###### PUT Method
- **`/v1/todo/:id`** : requires a to-do item in JSON format with updated fields only. 

###### DELETE Method
- **`/v1/todo/:id`** 
