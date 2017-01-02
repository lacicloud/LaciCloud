#!/bin/bash
#this is the /etc/acpi/powerbtn.sh script, which tells linux what to do in case of the hardware shutdown button pressed


date=$(date +"%T")

echo "Hardware Shutdown at $date" >> /var/ftp/logs/events.txt

service nginx stop
service php7.0-fpm stop
service mysql stop
service cron stop

pkill pure-ftpd
/etc/init.d/samba stop

#clear leftover logs
#for i in /var/log/*; do cat /dev/null > $i; done 
rm -r /var/log
cat /dev/null > ~/.bash_history && history -c && history -w
echo > .wget-hsts

sudo umount /var/ftp
sudo cryptsetup luksClose /dev/mapper/ftp

/sbin/shutdown -h now "Hardware Shutdown"
