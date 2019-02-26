
# FormBuilder - A Laravel package

## The Business Case

This package reduces time and cost of application development by providing solutions to the following requirements: 

 - The business process involves filling out many, long, complicated forms.
 - Form "ownership" is passed between different user roles.
 - Forms move through "states" during their life-cycle with different fields becoming editable by different user roles. 
 - The questions on the forms may change slightly at a future date, but the answers recorded against them must remain true to what the question was at the time the form was filled out. 
 - In the case where a few questions on a form have changed, it must be possible to produce reports containing both versions.


## The Developer Case

FormBuilder aims to reduce complexity and repetition for the developer by providing the following features:

 - The ability to define long complicated forms as a JSON structure and letting the application markup the HTML. 
 - The ability to choose CSS class names applied to elements in markup.
 - Artisan commands to create the required database migrations, models, controllers and to create new form schemas.

## Accessibility Case

Although ultimate responsibility for making an interface accessible lies with the developer, FormBuilder does not stand in the way. Current standards have been followed when defining how the forms are markedup but provisions to override all parts of the system are in place should an edge case need to be implemented.  


# Form "states"

Each form can have multiple states and each field in the form can be marked-up in 1 of 4 ways in each state. 

The possible values are: 

 - editable - _visible and editable_
 - readonly - _visible but cannot be edited_
 - hidden - _not visible but editable (eg. Via custom JavaScript)_
 - ignore - _not included in the form markup_
 
They can be imagined on a 2x2 matrix of editability and visibility.

```
            ----------------------
    ^       | hidden | editable  |
    |       |--------|-----------|
editability | ignore | readonly |
            ----------------------
              visibility -->

```

Let's use a school homework assignment as a simple example. Imagine a simple quiz form is started by a student who has to fill 3 answer fields. After submission, the form is passed to a teacher, those first 3 answer fields now become read-only and a 4th field called 'feedback' is revealed that only the teacher can fill. When the teacher submits the form, all 4 fields become read-only and neither student nor teacher can edit the fields but both can view them in a read-only state. 

In the above example the form has 3 states:
 1) The way the fields are displayed for the student
 2) The way the fields are displayed for the teacher
 3) The way the fields are displayed for the final state
 
They are set for each field via an object of key-pair values in the schema. The states are given key names, a human description relevant to that form, and the value is how the particular field is displayed. 

This is how the states object might be set for the first answer field:
 
 ```
 "states": {
     "student-answering": "editable",
     "teacher-marking": "readonly",
     "final-view": "readonly"
 }
 ```

### Legacy state values

The codebase currently contains some support for a number of deprecated values. These use-case-specific values are legacy of the application that FormBuilder was original developed for and will not be supported in future releases. 

For reference only and should not be used:

 - editable_if_true_else_ignore
 - editable_if_true_else_readonly
 - hidden-for-learner
 - readonly_for_owner

## Getting started

Installation is easy with the following steps:

Add the package to `composer.json` using the install command:

```bash
$ composer require nomensa/form-builder
```

Add the service provider in `config/app.php` file:
 
```php
'providers' => [
    ...
    Nomensa\FormBuilder\FormBuilderServiceProvider::class,
];
```
 
Add aliases to `config/app.php` file:

```php
'aliases' => [
    ...
    'FormBuilder' => Nomensa\FormBuilder\FormBuilder::class,
    'CSSClassFactory' => Nomensa\FormBuilder\BootstrapCSSClassFactory::class,
],
```

 - Run `php artisan formbuilder:install`
 - Add routes declaration
 - Run db migrations to rebuild database schema
 - Include the FormBuilder JavaScript script
 
### Creating a basic first form
 
Run the artisan command to create a form, let's call it _"My First Form"_.
 
```bash
$ php artisan formbuilder:make-form MY-FORM "My First Form"`
```
 
Now open `/app/FormBuilder/Forms/MY-FORM/schema.json` in your editor and paste in the following code: 
 
 ```json
[
  {
    "type": "dynamic",
    "rows": [
      {
        "editing_instructions": "Welcome to my great form."
      },
      {
        "columns": [
          {
            "field": "first-form-name",
            "label": "Name",
            "type": "text",
            "helptext": "What people call you",
            "states": {
              "state-1": "editable",
              "state-2": "readonly"
            }
          }
        ]
      },
      {
        "title": "Time",
        "columns": [
          {
            "field": "first-form-love-kittens",
            "label": "I love kittens",
            "type": "checkbox",
            "states": {
              "state-1": "editable",
              "state-2": "readonly"
             }
          }
        ]
      }
    ]
  }
]
```
 
Now add a link to create this type of form somewhere in your application's blade files: 

```php
{{ link_to_route('entryforms.cloneform', 'My First Form', ['entryform'=>'my-first-form']) }}
```

Congratulations! You should now have a working form that can be submitted and saved in database.


## Contributors

Read CONTRIBUTING.md


## Dependencies
 
FormBuilder depends upon Laravel Collective's HTML and FORM packages.
 
## Artisan commands
 
FormBuilder provides several Artisan commands to make life easier: 
 
  - `formbuilder:install` - Creates the required database migrations, models and controllers in your application. It also creates a folder to hold the schemas and another for any blade templates. 
  - `formbuilder:make-form [Form Code] [Form Title]` - Creates a new form schema in file system and accompanying entry in database table.

## Layout

FormBuilder assumes a row-column type layout but there is no assumption as to any CSS framework. A Bootstrap class factory is provided out-the-box but if you prefer a different framework or your own custom CSS classes you can write a class that implements CSSClassFactory and switch your alias in `config/app.php` to refer to that instead. 

## Field types

As well as the obvious expected types such as `text`, `textarea`, `select` and `checkbox` _(singular)_, FormBuilder also provides some multi-selects and lend themselves to a more accessible interface:

 - `checkboxes` - Multiple checkboxes all with a shared name prefix
 - `radios` - A group of radio buttons all with a shared name

## Cool edge cases

Because FormBuilder was developed as part of a client-specific application there are some cool edge cases accounted for that other FormBuilder packages do not: 

 - Type of radio button where the client required the user to explicitly set a value, no pre-selected. 
