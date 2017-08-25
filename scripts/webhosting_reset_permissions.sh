#!/bin/bash
#Resets permissions on webhost environment

#path for cron
export PATH="/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin"

sitename=$1\_webhosting
id=$2

chown -R $sitename:$sitename /var/ftp/public_files/$id/www
chown -R $sitename:$sitename /var/ftp/public_files/$id/tmp
chown -R $sitename:$sitename /var/ftp/public_files/$id/logs
chown -R $sitename:$sitename /var/ftp/public_files/$id/config

#default 755 permissions
chmod -R 755 /var/ftp/public_files/$id/www
chmod -R 755 /var/ftp/public_files/$id/tmp
chmod -R 755 /var/ftp/public_files/$id/logs
chmod -R 755 /var/ftp/public_files/$id/config

#allow ftp write
usermod -a -G $sitename ftpuser

#allow group write
chmod -R g+w /var/ftp/public_files/$id/www
chmod -R g+w /var/ftp/public_files/$id/tmp
chmod -R g+w /var/ftp/public_files/$id/logs
chmod -R g+w /var/ftp/public_files/$id/config

exit 0
