#!/bin/bash

#stop nginx to allow certbot to renew certificates
service nginx stop 
/usr/bin/certbot --standalone renew 
#start nginx
service nginx start 
#use new cert for pureftpd also
cat /var/ftp/config/letsencrypt/live/lacicloud.net/privkey.pem /var/ftp/config/letsencrypt/live/lacicloud.net/fullchain.pem > /var/ftp/config/letsencrypt/live/lacicloud.net/pure-ftpd.pem 
#allow basic_checks.sh to restart pureftpd
/usr/bin/pkill pure-ftpd
#we do not need this in webroot anymore
rm -r /var/ftp/www/developers/localweb/.well-known

