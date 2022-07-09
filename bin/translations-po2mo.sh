#!/usr/bin/env bash

cd ../languages
for file in `find . -name "*.po"` ; do msgfmt -o ${file/.po/.mo} $file ; done
cd ../bin