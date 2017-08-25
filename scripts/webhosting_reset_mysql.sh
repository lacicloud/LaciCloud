#!/bin/bash
#Resets mysql password on a webhost environment

#path for cron
export PATH="/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin"

mysql_username=$1
mysql_password=$2

#cfget is required
username=`cfget -C /var/ftp/www/developers/secrets.ini secrets/mysql_root_user`
password=`cfget -C /var/ftp/www/developers/secrets.ini secrets/mysql_root_password`

/usr/bin/mysql -u "$username" --password="$password" -e "USE mysql; SET PASSWORD FOR '$mysql_username'@'localhost' = PASSWORD('$mysql_password');"

#check mysql success
if [ "$?" -eq "1" ]; then
   exit 1;
fi

exit 0


