#!/bin/bash
#Adds a file's uploaded size to total bandwidth used

filename=$1
size=$UPLOAD_SIZE 
size_in_mb=`python -c "from __future__ import division; print $size / 1024 / 1024"`
ftp_username=$UPLOAD_VUSER

#cfget is required
username=`cfget -C /var/ftp/www/developers/secrets.ini secrets/mysql_root_user`
password=`cfget -C /var/ftp/www/developers/secrets.ini secrets/mysql_root_password`

realID=$(/usr/bin/mysql pureftpd -u $username -p$password -se "SELECT realID FROM ftp_users WHERE user='$ftp_username'")

current_used=$(/usr/bin/mysql lacicloud -u $username -p$password -se "SELECT used_bandwidth FROM truebandwidthcounter WHERE id='$realID'")

used_bandwidth=`python -c "print $current_used + $size_in_mb"`

/usr/bin/mysql -u "$username" --password="$password" -e "USE lacicloud; UPDATE truebandwidthcounter SET used_bandwidth='$used_bandwidth' WHERE id='$realID'"

exit
