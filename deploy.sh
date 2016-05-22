#!/usr/bin/env bash

if [ ! -z "$TRAVIS_TAG" ] && [ "$TRAVIS_PULL_REQUEST" = "false" ]; then
  echo env=prod > lib/deploy.ini
  find handlers -type f -exec curl -u $USER:$PASS $HOST/handlers/ --ftp-create-dir -T {} \;
  find lib -type f -exec curl -u $USER:$PASS $HOST/lib/ --ftp-create-dir -T {} \;
  curl -u $USER:$PASS $HOST/ --ftp-create-dir -T index.php
  curl -u $USER:$PASS $HOST/ --ftp-create-dir -T .htaccess
else
  echo "This will not deploy!"
fi
