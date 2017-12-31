#!/bin/bash
#Adds new webhost environment

#path for cron
export PATH="/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin"

#to distinguish from normal users, name_webhosting is the final form
sitename=$1\_webhosting
#only the name, the name.lacicloud.net subdomain
sitename_name=$1
id=$2
mysql_username=$3
mysql_password=$4

#cfget is required
username=`cfget -C /var/ftp/www/developers/secrets.ini secrets/mysql_root_user`
password=`cfget -C /var/ftp/www/developers/secrets.ini secrets/mysql_root_password`

if id "$sitename" >/dev/null 2>&1; then
        echo "ERROR: User already exists!"
	exit 1
fi

groupadd $sitename 
useradd -s /bin/nologin -g $sitename $sitename 

mkdir -p /var/ftp/public_files/$id/www
mkdir -p /var/ftp/public_files/$id/tmp
mkdir -p /var/ftp/public_files/$id/logs
mkdir -p /var/ftp/public_files/$id/config

echo "[$sitename]
user = $sitename
group = $sitename
listen = /var/run/php7.0-fpm-$sitename.sock
listen.owner = www-data
listen.group = www-data
php_admin_value[disable_functions] = disk_total_space, diskfreespace, exec, system, popen, proc_open, proc_close, proc_nice, shell_exec, passthru, dl, mail, putenv, getenv, set_time_limit, pcntl_exec
php_admin_value[open_basedir] = '/var/ftp/public_files/$id/www:/var/ftp/public_files/$id/tmp:/var/ftp/public_files/$id/logs:/var/ftp/public_files/$id/config'
php_admin_flag[expose_php] = off
php_admin_flag[opcache.enable] = off
php_admin_value[cgi.fix_pathinfo] = 0
php_admin_value[memory_limit] = 256M
php_admin_value[max_execution_time] = 30
php_admin_value[max_input_time] = 30
php_admin_value[session.save_path] = '/var/ftp/public_files/$id/tmp'
php_admin_value[file_uploads] = On
php_admin_value[upload_max_filesize] = 4096M
php_admin_value[post_max_size] = 4096M
php_admin_value[upload_tmp_dir] = '/var/ftp/public_files/$id/tmp'
catch_workers_output = yes 
php_admin_value[error_log] = /var/ftp/public_files/$id/logs/php.txt 
php_admin_flag[log_errors] = on
security.limit_extensions = .php
php_admin_value[user_ini.filename] = webhosting.ini
pm = ondemand
pm.max_children = 5
pm.process_idle_timeout = 10s
pm.max_requests = 64
chdir = /
" > /var/ftp/config/php/pool.d/$sitename.conf

certbot certonly --webroot -w /var/ftp/www/developers/localweb -d $sitename_name.lacicloud.net --reinstall

#check letsencrypt success
if [ "$?" -eq "1" ]; then
   exit 5;
fi

#we don't need to keep the debug log
rm /var/log/letsencrypt/letsencrypt.log

cp /var/ftp/config/letsencrypt/live/lacicloud.net/dhparam.pem /var/ftp/config/letsencrypt/live/$sitename_name.lacicloud.net/dhparam.pem

echo "server {
        listen [::]:443 ssl http2;

        server_name $sitename_name.lacicloud.net;

        root /var/ftp/public_files/$id/www;
        index index.php index.html;

	add_header Strict-Transport-Security 'max-age=63072000; includeSubDomains; preload' always;

        ssl  on;
        ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
        ssl_certificate  /var/ftp/config/letsencrypt/live/$sitename_name.lacicloud.net/fullchain.pem;
        ssl_certificate_key  /var/ftp/config/letsencrypt/live/$sitename_name.lacicloud.net/privkey.pem;
        ssl_ciphers 'EECDH+AESGCM:EDH+AESGCM:AES256+EECDH:AES256+EDH';
	ssl_dhparam /var/ftp/config/letsencrypt/live/$sitename_name.lacicloud.net/dhparam.pem; 
	ssl_prefer_server_ciphers  on;
        ssl_session_cache shared:SSL:10m; 
 	ssl_verify_client  off;
	ssl_stapling on;
        ssl_stapling_verify on;
        ssl_trusted_certificate /var/ftp/config/letsencrypt/live/lacicloud.net/chain.pem;
        resolver 8.8.8.8 8.8.4.4 valid=300s;
        resolver_timeout 5s;
		
	proxy_temp_path /var/ftp/users/$id/public_files/tmp 1 2 3;

	access_log /var/ftp/users/$id/public_files/logs/nginx_access.txt;
	error_log /var/ftp/users/$id/public_files/logs/nginx.txt notice;

        location ~ \.php$ {
        	try_files \$uri =404;
        	fastcgi_split_path_info ^(.+\.php)(/.+)$;
       	        fastcgi_pass unix:/var/run/php7.0-fpm-$sitename.sock;
        	fastcgi_index index.php;
        	fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        	include fastcgi_params;
        }

}

" > /var/ftp/config/nginx/sites-enabled/$sitename.conf

echo "<?php echo 'Welcome to your personal domain!' ?>" > /var/ftp/public_files/$id/www/index.php

#chown to proper user
chown -R $sitename:$sitename /var/ftp/public_files/$id/www
chown -R $sitename:$sitename /var/ftp/public_files/$id/tmp
chown -R $sitename:$sitename /var/ftp/public_files/$id/logs
chown -R $sitename:$sitename /var/ftp/public_files/$id/config

#set to default 755 permissions
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

#add mysql user
/usr/bin/mysql -u "$username" --password="$password" -e "CREATE DATABASE $sitename; CREATE USER '$mysql_username'@'localhost' IDENTIFIED BY '$mysql_password'; GRANT ALL PRIVILEGES ON $sitename.* TO '$mysql_username'@'localhost';"

#check mysql success
if [ "$?" -eq "1" ]; then
   exit 2;
fi

service nginx reload

#check nginx success
if [ "$?" -eq "1" ]; then
   exit 3;
fi

service php7.0-fpm reload

#check php success
if [ "$?" -eq "1" ]; then
   exit 4;
fi

exit 0


