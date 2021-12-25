#!/usr/bin/env bash
for file in `find . -name "*.po"` ; do msgfmt -o ${file/.po/.mo} $file ; done
