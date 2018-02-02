
Do the work

Commit the work

Have a look at what tags currently exist

```
git tag
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

