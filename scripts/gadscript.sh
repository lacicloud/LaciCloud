#!/bin/bash
#Updates IP addresses for DNS (IPV4 and IPV6)

#cfget is required
api_key=`cfget -C /var/ftp/www/developers/secrets.ini secrets/gandi_api_key`

#we pipe it into tac twice because http://stackoverflow.com/questions/16703647/why-curl-return-and-error-23-failed-writing-body
curl https://ipv4.ident.me | tac | tac | $PWD/gad.sh -a $api_key -d lacicloud.net -r "*"
curl https://ipv4.ident.me | tac | tac | $PWD/gad.sh -a $api_key -d lacicloud.net -r "@"
curl https://ipv4.ident.me | tac | tac | $PWD/gad.sh -a $api_key -d lacicloud.net -r "A"

curl https://ipv6.ident.me | tac | tac | $PWD/gad.sh -6 -s -a $api_key -d lacicloud.net -r "*"
curl https://ipv6.ident.me | tac | tac | $PWD/gad.sh -6 -s -a $api_key -d lacicloud.net -r "@"
curl https://ipv6.ident.me | tac | tac | $PWD/gad.sh -6 -s -a $api_key -d lacicloud.net -r "AAAA"


