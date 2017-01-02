#!/bin/bash

#pure-ftpd crashes sometimes
if [[ ! `pidof -s pure-ftpd` ]]; then
	/usr/local/sbin/pure-ftpd -f ftp -l mysql:/var/ftp/config/pure-ftpd/mysql.conf -t 8192:384 -0 -C 50 -c 10000 -E -A -H -D -Z -S 60 -p 12000:13000 -u 1 -j -P lacicloud.net -F /var/ftp/config/pure-ftpd/fortune_cookie -k 98 -b --fscharset=UTF-8 --clientcharset=UTF-8 -Y 1 -y 50:1 &
	date=$(date +"%T")
	echo "Restarted pure-ftpd at $date" >> /var/ftp/logs/events.txt
fi

#anti-forensics
if [[  `pidof -s dd` ]]; then
        date=$(date +"%T")
        echo "DD detected at (shutdown initiated) $date" >> /var/ftp/logs/events.txt
        /etc/acpi/powerbtn.sh
fi

#be quiet please
if [ ! -f /var/run/fancontrol.pid ]; then
    echo "Restarted fancontrol at $date" >> /var/ftp/logs/events.txt
    /usr/sbin/fancontrol &
fi

if [[ ! `pidof -s mysqld` ]]; then
	service mysql restart
        date=$(date +"%T")
        echo "Restarted SQL server  at $date" >> /var/ftp/logs/events.txt
fi

if [[ ! `pidof -s apcupsd` ]]; then
        service apcupsd restart
        date=$(date +"%T")
        echo "Restarted UPS monitoring software at $date" >> /var/ftp/logs/events.txt
fi

#don't be too hot for too long 
TOPPROCESS=$(top -b -n 1 | sed 1,6d | sed -n 2p)
TOPPID=$(echo "$TOPPROCESS" | awk '{print $1}')
TOPNAME=$(echo "$TOPPROCESS" | awk '{print $12}')

CPUTEMP = sensors | grep temp2 |  grep -o -E '[0-9]+' | head -2 | sed -e 's/^0\+//' | tail -n 1 

if [[ $CPUTEMP  > 75 ]]; then 
	kill -9 $TOPPID
	echo "Killed top process $TOPPROCESS with pid $TOPPID with name $TOPNAME because cpu temperature was over safe limit (75) by $CPUTEMP at $date"
fi 

#make these directories read only
#chattr -R +i /var/ftp/config
#chattr -R +i /var/ftp/scripts
#chattr -R +i /var/ftp/www

#restart networking in case of networking problems
ROUTER_IP=192.168.1.1

( ! ping -c1 $ROUTER_IP >/dev/null 2>&1 ) && service network restart >/dev/null 2>&1 && ifup eth0 && echo "Re-established network connection at $date" >> /var/ftp/logs/events.txt

#just in case
mkdir -p /tmp/scripts

#idk why it keeps reappearing, but i don't like it so
rm /var/ftp/config/misc/hosts.deny.purge.bak

#allow custom users to manage the custom directory through FTP
cp -r /var/ftp/users/3/public_files/Steam/* /var/ftp/www/developers/localweb/custom/dyosoft
cp -r /var/ftp/users/1/doidices/* /var/ftp/www/developers/localweb/custom/doidices.eu
