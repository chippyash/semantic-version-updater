# Variables
SHELL := /bin/bash

## Applications
COMPOSER ?= composer.phar
BOX ?= bin/box
BIN ?= bin/vupdate
GIT ?= git

# Helpers
all: build push

.PHONY: all

# Dependencies
depend:
	${GIT} checkout master
	${COMPOSER} install

.PHONY: depend

# Building
build:
	${BOX} build
	mv bin/vupdate.phar bin/vupdate

push:
	${BIN}
	${GIT} add --all
	${GIT} commit -m"Build"
	${GIT} tag $$(cat VERSION)
	${GIT} push origin master --tags

.PHONY: build push

# Cleaning
clean:
	rm -rf vendor
	rm bin/vupdate
	rm composer.lock

.PHONY: clean
