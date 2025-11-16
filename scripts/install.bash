#! /usr/bin/env bash

HOSTNAME=activity-log.phpolar.org
PROXY_SERVER_PROG=nginx
BACKEND_SERVER_PROG=lighttpd
MAX_DELETE=2000
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

iLog 'Installing libraries...' $ICON_DOWN_ARROW $PURPLE
composer \
	--quiet \
	--no-progress \
	--working-dir="/tmp/$HOSTNAME" \
	--no-dev \
	--optimize-autoloader \
	--audit \
	install

iLog 'Installing web application...' $ICON_FILE_FOLDER $PURPLE
sudo rsync \
	--quiet \
	--times \
	--perms \
	--recursive \
	--progress \
	--delete \
	--max-delete "$MAX_DELETE" \
	--exclude ".prettier*" \
	--exclude "scripts" \
	--exclude "public/resources" \
	--exclude "vendor/bin" \
	--exclude "composer.*" \
	--exclude "server" \
	"/tmp/$HOSTNAME/" "/srv/www/servers/$HOSTNAME/"

iLog 'Installing static files...' $ICON_FILE_FOLDER $PURPLE
sudo rsync \
	--quiet \
	--times \
	--perms \
	--recursive \
	--progress \
	--delete \
	--max-delete "$MAX_DELETE" \
	--exclude "*.php" \
	"/tmp/$HOSTNAME/public/" "/srv/www/htdocs/"

sudo cp "/tmp/$HOSTNAME/vendor/picocss/pico/css/pico.classless.min.css" "/srv/www/htdocs/resources/css/"
sudo cp "/tmp/$HOSTNAME/vendor/picocss/pico/css/pico.min.css" "/srv/www/htdocs/resources/css/"

iLog 'Installing proxy server configuration...' $ICON_DOWN_ARROW $BLUE
sudo cp "/tmp/$HOSTNAME/server/$PROXY_SERVER_PROG.conf" "/etc/$PROXY_SERVER_PROG/sites-available/$HOSTNAME"

iLog 'Installing backend server configuration...' $ICON_DOWN_ARROW $BLUE
sudo cp "/tmp/$HOSTNAME/server/$BACKEND_SERVER_PROG.conf" "/etc/$BACKEND_SERVER_PROG/vhosts.d/$HOSTNAME.conf"
sudo cp "/tmp/$HOSTNAME/server/setenv.conf" "/etc/$BACKEND_SERVER_PROG/conf.d/"

iLog 'Restarting proxy server...' $ICON_TRAFFICLIGHT $BLUE
sudo systemctl restart $PROXY_SERVER_PROG
iLog 'Restarting backend server...' $ICON_TRAFFICLIGHT $BLUE
sudo systemctl restart $BACKEND_SERVER_PROG

iLog 'Cleaning up temporary files...' $ICON_WASTEBASKET $GREEN
rm -rf "/tmp/$HOSTNAME"

iLog 'Installation complete.' $ICON_COG $GREEN
exit 0
