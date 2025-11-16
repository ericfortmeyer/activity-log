#! /usr/bin/env bash

HOSTNAME=activity-log.phpolar.org
ORG=ericfortmeyer
SERVER_HOSTNAME=baruq
MAX_DELETE=20
SCRIPTS_FOLDER=`dirname $0`

ICON_COMPUTER='\U1F5A5';
ICON_COG='\U2699';
ICON_CIRCLE_ARROW='\U1F501'
ICON_DOWN_ARROW='\U2B07';
ICON_FILE_FOLDER='\U1F4C1';
ICON_WASTEBASKET='\U1F5D1';
ICON_TRAFFICLIGHT='\U1F6A6';

GREEN='32m';
BLUE='34m';
PURPLE='35m';

iLog () {
	local -r color=$3;
	local -r icon=$2;
	local -r message=$1;
	echo -e "$icon:\\033[${color}[$HOSTNAME]\\033[00m:$message" | column -t -s ':'
}

iLog "Uploading application to server" $ICON_COMPUTER $GREEN
rsync \
  --progress \
  --verbose \
  --itemize-changes \
  --quiet \
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
  --exclude "vendor" \
  --exclude ".phpunit.cache" \
  --exclude "data" \
  --exclude "scripts/*.bash" \
  --exclude "tests" \
  --exclude "public/resources/css/pico.classless.min.css" \
  --exclude "public/resources/css/pico.min.css" \
  -e "ssh -o StrictHostKeyChecking=no" \
  "$HOME/Projects/$ORG/$HOSTNAME/" "$USER@$SERVER_HOSTNAME:/tmp/$HOSTNAME/"

iLog "Running installation script..." $ICON_COG $GREEN
ssh -o StrictHostKeyChecking=no "$USER@$SERVER_HOSTNAME" "bash -s" < ./scripts/install.bash
