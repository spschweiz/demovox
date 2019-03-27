#!/usr/bin/env bash
for file in `find . -name "*.pot"` ; do msgfmt -o ${file/.pot/.mo} $file ; done