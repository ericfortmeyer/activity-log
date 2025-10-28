#! /usr/bin/env bash

HOSTNAME=activity-log.phpolar.org
ORG=ericfortmeyer
SERVER_HOSTNAME=baruq
MAX_DELETE=20

rsync \
  --progress \
  --verbose \
  --itemize-changes \
  --compress \
  --archive \
  --delete \
  --max-delete "$MAX_DELETE" \
  --exclude ".git*" \
  --exclude ".phan" \
  --exclude ".vscode" \
  --exclude ".semgrepignore" \
  --exclude ".prettier*" \
  --exclude ".pre-commit*" \
  --exclude ".cz.*" \
  --exclude "README.md" \
  --exclude "LICENSE" \
  --exclude "phpunit.xml" \
  --exclude "**/.gitkeep" \
  --exclude "build" \
  --exclude ".phpunit.cache" \
  --exclude "data" \
  --exclude "scripts/*.bash" \
  --exclude "tests" \
  --exclude "public/resources/css/pico.classless.min.css" \
  --exclude "public/resources/css/pico.min.css" \
  -e "ssh -o StrictHostKeyChecking=no" \
  "$HOME/Projects/$ORG/$HOSTNAME/" "$USER@$SERVER_HOSTNAME:/tmp/$HOSTNAME/"

ssh -o StrictHostKeyChecking=no "$USER@$SERVER_HOSTNAME" "bash -s" < ./scripts/install.bash