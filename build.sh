#!/bin/bash
cd ~/Projects/chippyash/source/semantic-version-updater/
vendor/phpunit/phpunit/phpunit -c phpunit.xml --testdox-html contract.html test/
tdconv -t "Chippyash Semantic Version Updater" contract.html docs/Test-Contract.md
rm contract.html

