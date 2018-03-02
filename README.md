
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

Each form has a lifecycle as it moves through different states.

Let's use a school homework assignment as a simple example... Imagine a homework form is created by a student who fills out 3 answer fields. After submission the form is passed to a teacher, those first 3 fields become read-only and a 4th field called 'feedback' is revealed that only the teacher can fill. When the teacher submits the form, all 4 fields are read-only and both student and teacher can view the form in a read-only state. 


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
