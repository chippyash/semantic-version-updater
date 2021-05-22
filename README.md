# Semantic Version Updater

Build chain utility to update the semantic version for a PHP package

## PHP Version support
With the inexorable withdrawal of upstream library support for PHP<V8, I've
now also decided to remove support for <V8. If you still need <V8 support
use a tagged version <1 and build the package yourself. V1+ is PHP8 only.

## Quality Assurance

![PHP 8](https://img.shields.io/badge/PHP-8-blue.svg)
[![Build Status](https://travis-ci.org/chippyash/semantic-version-updater.svg)](https://travis-ci.org/chippyash/semantic-version-updater)
[![Test Coverage](https://api.codeclimate.com/v1/badges/e2dad65c6556353dae4b/test_coverage)](https://codeclimate.com/github/chippyash/semantic-version-updater/test_coverage)

The above badges represent the current development branch.  As a rule, I don't push
 to GitHub unless tests, coverage and usability are acceptable.  This may not be
 true for short periods of time; on holiday, need code for some other downstream
 project etc.  If you need stable code, use a tagged version.
 
See the [Test Contract](https://github.com/chippyash/semantic-version-updater/blob/master/docs/Test-Contract.md)

## How

### Initialisation

For a new package

add `"chippyash/semantic-version-updater":"*"` to your dev-requires section of the composer.json file

run `composer update`

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

Once you have finished initial development and you think you are good to go,
you can tag you package at its first 'real' release version.  You can
either run `bin/vupdate -pbcbreak` to update the major (M.n.n) part of
the version number, or `bin/vupdate -o 1.0.0` to force the version.  A one liner would be

<pre>
bin/vupdate -pbcbreak && cat VERSION | xargs git tag && git commit -am"First release" && git push origin master --tags
</pre>

Use `bin/vupdate -h` to see the help screen.

Use `bin/vupdate --version` for command version number.

### Employing into your build chain

The real purpose of the utility is to get it used in the build chain,
updating the tag, pushing to git and then updating the Satis/Composer
(or other repo) to tell it that a new version is available.

Download this repo as a zip and extract it. Move/copy the bin/vupdate
file to somewhere on your PATH, e.g. /usr/local/bin/vupdate.  You can also do
this if you just want the executable phar on your local machine to be
globally available.

Here is a jenkins job that we use in our build chain to update the version dependent
on the branch name prefix:

<pre>
VERSIONER=/usr/local/bin/vupdate
GIT=git

cd "${workingDir}";
${GIT} checkout ${gitBranch};
lastCommit=$(git log --branches | grep 'Merge pull request.* to master' | head -1)

if [[ $lastCommit == *"feature/"* ]] || [[ $lastCommit == *"release/"* ]]
then
        ${VERSIONER} -p feature;
        verType="Feature";
else
        ${VERSIONER};
        verType="Patch";
fi;


${GIT} commit -am"CD $verType Version update: $lastCommit";
cat VERSION | xargs ${GIT} tag;
${GIT} push origin ${gitBranch} --tags;
</pre>

The $workingDir and $gitBranch parameters are sent to the job from the main build
job.  $gitBranch defaults to 'master';

## Development

Clone the repo as normal.

Create a feature branch

run `composer update` to pull in the external libraries.

Commit your changes as normal and push to repo and make a pull request.
 
### The make file

Running `make` will rebuild the `bin/vupdate` phar file and push the changes to the repo.
As such, it is only of any use to you if you have write access on the code repo.

You can run `make build` to just build the `bin/vupdate`

### Notes
If you get `creating archive "/var/lib/jenkins/jobs/ci-version-updater-builder/workspace/bin/vupdate.phar"
disabled by the php.ini setting phar.readonly `
or something similar when using the make build tools, edit your php cli
ini file and set `phar.readonly = Off`.

## Acknowledgments

I first wrote the vupdate.php script some years ago.  At that time it relied on the 
'herrera-io/version' package from [Kevin Herrara](https://packagist.org/users/kherge/).  He's since abandoned that package, so
I've included his original code in the source of this package.  It still works just fine.
You can find it in the 'src' directory, along with his original tests in the 'test'
directory. The Test Contract can be found at `docs/Test-Contract.md`. He has
a permissive license on his code, so feel free to use this package
to get access to the original code if you need it in some other application.

The build routine managed by the Makefile relies on [Box](https://box-project.github.io/box2/).
There is a box phar distribution in the bin directory which will be used
by the makefile.
