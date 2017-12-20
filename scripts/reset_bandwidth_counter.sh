#!/bin/bash
#Resets monthly bandwidth usage

#cfget is required
username=`cfget -C /var/ftp/www/developers/secrets.ini secrets/mysql_root_user`
password=`cfget -C /var/ftp/www/developers/secrets.ini secrets/mysql_root_password`

/usr/bin/mysql -u "$username" --password="$password" -e "USE lacicloud; UPDATE truebandwidthcounter SET used_bandwidth=0"

exit


