#!/bin/bash

git fetch origin master:master
fork_point=$(git merge-base --octopus master)
FILES=$(git diff --diff-filter=AMRC --name-only ${fork_point} | grep .php | tr "\n" " ")

if [ "$FILES" != "" ]
then
    ./bin/phpcs -p -v --standard=build/phpcs/ruleset.xml --report=full --extensions=php --report-file=phpcs_report.txt --encoding=utf-8 ${FILES}
fi


