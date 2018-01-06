#!/bin/bash

date=`date +%Y_%m_%d_%H_%M_%S`

#pure-ftpd crashes sometimes
if [[ ! `pidof -s pure-ftpd` ]]; then
	/usr/local/sbin/pure-ftpd -f ftp -l mysql:/var/ftp/config/pure-ftpd/mysql.conf -0 -C 50 -c 10000 -E -A -H -D -S 21 -p 12000:13000 -u 1 -j -P lacicloud.net -F /var/ftp/config/pure-ftpd/fortune_cookie -k 98 -b --fscharset=UTF-8 --clientcharset=UTF-8 -Y 1 -y 50:1 -o &
	/root/pure-ftpd-implicit/pure-ftpd-implicit -f ftp -l mysql:/var/ftp/config/pure-ftpd/mysql.conf -0 -C 50 -c 10000 -E -A -H -D -S 990 -p 12000:13000 -u 1 -j -P lacicloud.net -F /var/ftp/config/pure-ftpd/fortune_cookie -k 98 -b --fscharset=UTF-8 --clientcharset=UTF-8 -Y 3 -y 50:1 -o &
	
	/usr/bin/pkill -f pure-uploadscript
	/usr/local/sbin/pure-uploadscript -B -r /etc/scripts/bandwidthcounter.sh
	
	date=$(date +"%T")
	echo "Restarted pure-ftpd at $date" >> /var/ftp/logs/events.txt
fi

#pure-ftpd-implicit also crashes sometimes 
SERVER=127.0.0.1
PORT=990
`nc -z -v -w5 $SERVER $PORT`
result=$?

if [  "$result" != 0 ]; then
	/usr/bin/pkill pure-ftpd

	/usr/local/sbin/pure-ftpd -f ftp -l mysql:/var/ftp/config/pure-ftpd/mysql.conf -0 -C 50 -c 10000 -E -A -H -D -S 21 -p 12000:13000 -u 1 -j -P lacicloud.net -F /var/ftp/config/pure-ftpd/fortune_cookie -k 98 -b --fscharset=UTF-8 --clientcharset=UTF-8 -Y 1 -y 50:1 -o &
	/root/pure-ftpd-implicit/pure-ftpd-implicit -f ftp -l mysql:/var/ftp/config/pure-ftpd/mysql.conf -0 -C 50 -c 10000 -E -A -H -D -S 990 -p 12000:13000 -u 1 -j -P lacicloud.net -F /var/ftp/config/pure-ftpd/fortune_cookie -k 98 -b --fscharset=UTF-8 --clientcharset=UTF-8 -Y 3 -y 50:1 -o &	

	/usr/bin/pkill -f pure-uploadscript
        /usr/local/sbin/pure-uploadscript -B -r /etc/scripts/bandwidthcounter.sh

	date=$(date +"%T")
        echo "Restarted pure-ftpd-implicit at $date" >> /var/ftp/logs/events.txt

fi

#anti-forensics
if [[  `pidof -s dd` ]]; then
        date=$(date +"%T")
        echo "DD detected at (shutdown initiated) $date" >> /var/ftp/logs/events.txt
        /etc/acpi/powerbtn.sh
fi

#be quiet please
if [ ! -f /var/run/fancontrol.pid ]; then
    /usr/sbin/fancontrol &
    echo "Restarted fancontrol at $date" >> /var/ftp/logs/events.txt
fi

if [[ ! `pidof -s mysqld` ]]; then
	service mysql restart
        date=$(date +"%T")
        echo "Restarted SQL server at $date" >> /var/ftp/logs/events.txt
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

CPUTEMP=$(sensors | grep temp2 |  grep -o -E '[0-9]+' | head -2 | sed -e 's/^0\+//' | tail -n 1)

if [[ $CPUTEMP  > 75 ]]; then 
	kill -9 $TOPPID
	echo "Killed top process $TOPPROCESS with pid $TOPPID with name $TOPNAME because cpu temperature was over safe limit (75) by $CPUTEMP at $date"
fi 

#restart networking in case of networking problems
ROUTER_IP=192.168.1.1

( ! ping -c1 $ROUTER_IP >/dev/null 2>&1 ) && ifdown eth0  && ifup eth0 && echo "Attempted network reconnection at $date" >> /var/ftp/logs/events.txt

#idk why it keeps reappearing, but i don't like it so
rm /var/ftp/config/misc/hosts.deny.purge.bak
