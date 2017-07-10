# Semantic Version Updater

Build chain utility to update semantic version for a Composer library package
 
## How

### Initialisation

For a new package

add `"chippyash/semantic-version-updater":"*"` to your dev-requires section of the composer.json file

run `composer.phar update`

run `vendor/bin/vupdate init` to create a new VERSION file in the root of your project

run

<pre>

    git commit -am"add vupdate"
    
    git tag 0.0.0
    
    git push origin master --tags
</pre>

### Manually updating the version and git tag

During initial development, you'll want to have your package tagged at various points.  You can keep your git tag version 
and the version contained in the VERSION file in sync with

<pre>
bin/vupdate && cat VERSION | xargs git tag
</pre>

Don't forget to push your tags to remote repo.

Once you have finished initial development and you think you are good to go, you can tag you package at its first 'real'
release version.  You can either run `bin/vupdate -p bcbreak` to update the major (M.n.n) part of the version number, or
`bin/vupdate -v 1.0.0` to force the version.  A one liner would be

<pre>
bin/vupdate -p bcbreak && cat VERSION | xargs git tag && git commit -am"First release" && git push origin master --tags
</pre>

Use `bin/vupdate -h` to see the help screen.

### Employing into your build chain

The real purpose of the utility is to get it used in the build chain, updating the tag, pushing to git and then
 updating the Satis/Composer (or other repo) to tell it that a new version is available.

## Development

Clone the repo as normal.

Create a feature branch

run `composer.phar update` to pull in the external libraries.

Commit your changes as normal and push to repo and make a pull request.
 
### The make file



### Notes

If you get `creating archive "/var/lib/jenkins/jobs/ci-version-updater-builder/workspace/bin/vupdate.phar" disabled by the php.ini setting phar.readonly `
or something similar when using the make build tools, edit your php cli ini file and set `phar.readonly = Off`.

## Acknowledgments

I first wrote the vupdate.php script some years ago.  At that time it relied on the 
'herrera-io/version' package from [Kevin Herrara](https://packagist.org/users/kherge/).  He's since abandoned that package, so
I've included his original code in the source of this package.  It still works just fine.
You can find it in the 'src' directory, along with his original tests in the 'test'
directory.  He has a permissive license on his code, so feel free to use this package
to get access to the original code if you need it in some other application.