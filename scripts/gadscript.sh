#!/bin/bash
#Updates IP addresses for DNS (IPV4 and IPV6)

#cfget is required
api_key=`cfget -C /var/ftp/www/developers/secrets.ini secrets/gandi_api_key`

#we pipe it into tac twice because http://stackoverflow.com/questions/16703647/why-curl-return-and-error-23-failed-writing-body
curl https://ipv4.wtfismyip.com/text | tac | tac | /tmp/gad.sh -a $api_key -d lacicloud.net -r "*"
curl https://ipv4.wtfismyip.com/text | tac | tac | /tmp/gad.sh -a $api_key -d lacicloud.net -r "@"
curl https://ipv4.wtfismyip.com/text | tac | tac | /tmp/gad.sh -a $api_key -d lacicloud.net -r "A"

curl https://wtfismyip.com/text | tac | tac | /tmp/gad.sh -6 -s -a $api_key -d lacicloud.net -r "*"
curl https://wtfismyip.com/text | tac | tac | /tmp/gad.sh -6 -s -a $api_key -d lacicloud.net -r "@"
curl https://wtfismyip.com/text | tac | tac | /tmp/gad.sh -6 -s -a $api_key -d lacicloud.net -r "AAAA"


