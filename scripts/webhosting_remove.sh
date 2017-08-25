#!/bin/bash
#Removes a webhost environment
#development only, not used in prod

#cfget is required
username=`cfget -C /var/ftp/www/developers/secrets.ini secrets/mysql_root_user`
password=`cfget -C /var/ftp/www/developers/secrets.ini secrets/mysql_root_password`

#to distinguish from normal users, name_webhosting is the final form
sitename=$1\_webhosting
#only the name, the name.lacicloud.net subdomain
sitename_name=$1
id=$2
aggressive=$3
mysql_username=$4

if id "$sitename" >/dev/null 2>&1; then
        service nginx stop
	service php7.0-fpm stop
else
        echo "Error: User does not exist..."
	exit 1
fi

gpasswd -d ftpuser $sitename
userdel -r $sitename

if [ "$aggressive" = true ] ; then
    rm -r /var/ftp/public_files/$id/www
    rm -r /var/ftp/public_files/$id/config
    rm -r /var/ftp/public_files/$id/tmp
    rm -r /var/ftp/public_files/$id/logs
fi

rm /var/ftp/config/nginx/sites-enabled/$sitename.conf
rm /var/ftp/config/php/pool.d/$sitename.conf

rm -r /var/ftp/config/letsencrypt/live/$sitename_name.lacicloud.net
rm -r /var/ftp/config/letsencrypt/archive/$sitename_name.lacicloud.net
rm -r /var/ftp/config/letsencrypt/renewal/$sitename_name.lacicloud.net*

/usr/bin/mysql -u "$username" --password="$password" -e "DROP DATABASE $sitename; DROP USER '$mysql_username'@'localhost'"

service nginx start
service php7.0-fpm start

exit 0
