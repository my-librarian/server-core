#!/usr/bin/env bash

file_deploy() {
    if [ ! -z "$TRAVIS_TAG" ] && [ "$TRAVIS_PULL_REQUEST" = "false" ]; then
      echo env=prod > lib/deploy.ini
      find handlers -type f -exec curl -u $USER:$PASS $HOST/handlers/ --ftp-create-dir -T {} \;
      find lib -type f -exec curl -u $USER:$PASS $HOST/lib/ --ftp-create-dir -T {} \;
      curl -u $USER:$PASS $HOST/ --ftp-create-dir -T index.php
      curl -u $USER:$PASS $HOST/ --ftp-create-dir -T .htaccess
      curl -u $USER:$PASS $HOST/ --ftp-create-dir -T version.ini
    else
      echo "This will not deploy!"
    fi
}

os_deploy() {
    echo env=openshift > lib/deploy.ini
    mkdir -p api
    mv handlers api/
    mv lib api/
    mv index.php api/
    mv .htaccess api/
    sh -c "ls -a | grep -v -E 'api|deploy.sh' | xargs rm -rf || true"
    git clone https://github.com/my-librarian/ui-core.git
    cd ui-core
    lastRelease=$(git describe --tags --abbrev=0)
    git checkout ${lastRelease}
    npm i
    npm run build
    cd ..
    mv ui-core/dist/* ./
    rm -rf ui-core
    git init
    echo "api-version=$TRAVIS_TAG, ui-version=$lastRelease" > version.txt
    git config --global user.email "vipranarayan14@gmail.com"
    git config --global user.name "Travis CI"
    git add --all
    git commit -am "Deploy version $TRAVIS_TAG"
}
