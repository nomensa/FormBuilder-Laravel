
When Composer pulls this repo into your project's `/vendor` folder it does not bring the `.git` folder required for contributing. Lucky for you, your friendly local developer has made a bash script to grab it for you! 

Run the following to make the `vendor/nomensa/form-builder` a fully fledged Git repo: 

```bash
$ bash getgit
```

## Do the work

Write your code conforming to all the standards that Laravel does. Space-indentation, PSR-2 style. Make it beautiful.


## Test your work

From the package directory run `composer install` to download the PHPUnit binaries and then simply run `composer test` to run the tests.

Obviously you're going to want to write tests to cover your new code so do that in the `tests` folder. 


## Commit and version tag your work

Commit your work on the master branch.

Push `master` branch to origin.

Have a look at what tags currently exist:

```bash
$ git fetch
$ git tag
```

Tag your new work with a new version number:

```
git tag -a v0.6.0 -m "Added great new method"
```

Push the tag to the GitHub repo:

```
git push origin v0.6.0
```

Change directory back up 3 levels to app level and tell Composer to use the new version of the package:

```
$ composer update nomensa/form-builder
$ composer require nomensa/form-builder ~0.19.2
```

_N.B. I recommend using the tilda to lock major and minor version number but allow patch version to be higher._
