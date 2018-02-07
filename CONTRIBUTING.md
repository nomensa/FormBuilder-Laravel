
When Composer pulls this repo into your project's `/vendor` folder it does not bring the `.git` folder required for contributing. Lucky for you, your friendly local developer has made a bash script to grab it for you! 

Run the following to make the `vendor/nomensa/form-builder` a fully fledged Git repo: 

```bash
$ bash getgit
```

Do the work

## Commit the work

Have a look at what tags currently exist

```bash
$ git fetch
$ git tag
```

Tag the version

```
git tag -a v0.6.0 -m "Added great new method"
```

Push the tag to GitHub repo

```
git push origin v0.6.0
```

Change directory back up to the application and tell composer to use the new version

```
composer require nomensa/form-builder 0.6.0
```

