#!/bin/sh
./vendor/bin/phpdoc \
  -d ./private/class/ \
  -t ./public/sitedocs \
  --template="checkstyle" \
  --template="clean"
