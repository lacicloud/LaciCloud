#!/bin/bash

#this is technically a continuation of the rc.local script
exec 2> /var/ftp/logs/rc.local.txt      # send stderr from rc.local to a log file
exec 1>&2                      # send stdout to the same log file

date=$(date)

echo "Turned on at $date" >> /var/ftp/logs/events.txt   

#apparmor causes some problems with many things
sudo update-rc.d -f apparmor remove

#turn on swap
swapon -a

#symlink configs

#first SSL certs for pure-ftpd
ln -sf /var/ftp/config/letsencrypt/live/lacicloud.net/pure-ftpd.pem /etc/ssl/private/pure-ftpd.pem
ln -sf /var/ftp/config/letsencrypt/live/lacicloud.net/dhparam.pem /etc/ssl/private/pure-ftpd-dhparams.pem

#PHP config
ln -sf /var/ftp/config/php/php.ini /etc/php/7.0/fpm/php.ini
ln -sf /var/ftp/config/php/php.ini /etc/php/7.0/cli/php.ini
ln -sf /var/ftp/config/php/php.ini /etc/php/7.0/cgi/php.ini
ln -sf /var/ftp/config/php/php-fpm.conf /etc/php/7.0/fpm/php-fpm.conf
ln -sn /var/ftp/config/php/pool.d /etc/php/7.0/fpm/pool.d

#nginx config
ln -sf /var/ftp/config/nginx/nginx.conf /etc/nginx/nginx.conf
ln -sf /var/ftp/config/nginx/mime.types /etc/nginx/mime.types
ln -sf /var/ftp/config/nginx/.webcam /etc/nginx/.webcam
ln -sf /var/ftp/config/nginx/.localweb_htpasswd /etc/nginx/.localweb_htpasswd
ln -sn /var/ftp/config/nginx/sites-enabled /etc/nginx/sites-enabled

#mysql config
ln -sf /var/ftp/config/mysql/my.cnf /etc/mysql/my.cnf

#monitorix looks nice so we have that as well; restart to make sure it processes full events as well
ln -sf  /var/ftp/config/monitorix/monitorix.conf /etc/monitorix/monitorix.conf
ln -sf  /var/ftp/config/monitorix/conf.d/00-debian.conf /etc/monitorix/conf.d/00-debian.conf

#overwrite the shutdown script with whatever is in the config directory
cp /var/ftp/config/misc/powerbtn.sh /etc/acpi/powerbtn.sh
#same with rc.local
cp /var/ftp/config/misc/rc.local /etc/rc.local
#and crontab
crontab /var/ftp/config/misc/crontab

#maltrail also looks nice
ln -sf /var/ftp/config/maltrail/maltrail.conf /root/maltrail/maltrail.conf

#denyhosts is a much better alternative to fail2ban in my opinion
ln -sf /var/ftp/config/misc/hosts.deny /etc/hosts.deny
ln -sf /var/ftp/config/denyhosts/denyhosts.conf /etc/denyhosts.conf
/etc/init.d/denyhosts restart

#start lm-sensors
service lm-sensors start

#set-up logrotate
ln -sf /var/ftp/config/logrotate/custom /etc/logrotate.d/custom

#set up UPS monitoring
cp /var/ftp/config/apcupsd/apcupsd.conf /etc/apcupsd/apcupsd.conf
#and drive monitoring
cp /var/ftp/config/smartd/smartd.conf /etc/smartd.conf

#be quiet please
cp /var/ftp/config/fancontrol/fancontrol /etc/fancontrol

#copy scripts
cp  /var/ftp/scripts/* /etc/scripts
chmod +x /etc/scripts/*

#overwrite sources with whatever is in the config directory
cp /var/ftp/config/misc/sources.list /etc/apt/sources.list
#and also set-up process limits
cp /var/ftp/config/misc/limits.conf /etc/security/limits.conf

#megatools config
ln -sf /var/ftp/config/megatools/.megarc /root/.megarc
#SSL certs (from LE!)
ln -sfT /var/ftp/config/letsencrypt /etc/letsencrypt

#a little optimization can never hurt
sudo echo 0 >> /proc/sys/vm/swappiness

#overwrite some misc config files with whatever is in config directory
cp /var/ftp/config/misc/sudoers /etc/sudoers
cp /var/ftp/config/misc/sysctl.conf /etc/sysctl.conf
cp /var/ftp/config/misc/rsyslog.conf /etc/rsyslog.conf
cp /var/ftp/config/misc/.bashrc ~/.bashrc
cp /var/ftp/config/misc/securetty /etc/securetty

#shut down server in case any unknown USB is plugged in (anti-forensics, see Ross Ulbricht)
cp /var/ftp/config/usbkill/usbkill.ini /etc/usbkill.ini
python /root/usbkill/usbkill/usbkill.py &

#git config files
ln -sf /var/ftp/config/git/.gitconfig /root/.gitconfig
ln -sf /var/ftp/config/git/.git-credentials /root/.git-credentials

#stop wget from writing known hosts to text file
chattr +i /root/.wget-hsts

#make these sensitive files read-only
chattr +i /etc/shadow
chattr +i /etc/passwd
chattr +i /etc/group
chattr +i /etc/shadow

#also these
chattr -R +i /var/ftp/config
chattr -R +i /var/ftp/scripts
chattr -R +i /var/ftp/www

#a little start never hurts
sudo service rsyslog start

#there is a error.log before the error.log, so we have to move all logs in the old directory to our own log
#error log ception?
mkdir -p /var/log/nginx
echo >  /var/log/nginx/error.log
sudo service nginx start

cat /var/log/nginx/error.log >> /var/ftp/logs/nginx.txt

#start services
sudo service php7.0-fpm start
sudo service cron start


#start monitorix, safe and sure
sudo service monitorix stop
sudo service monitorix stop
sudo service monitorix restart

#no need for these
#/etc/init.d/samba stop
systemctl stop samba-ad-dc
sudo service apache2 stop
sudo service php5-fpm stop
sudo service postfix stop
sudo service postgresql stop

#need this tho
/etc/init.d/samba start

#just to be sure
modprobe ip_conntrack_ftp
modprobe ip_nat_ftp

#load iptables rules
/sbin/iptables-restore < /var/ftp/config/misc/iptables.rules
/sbin/ip6tables-restore < /var/ftp/config/misc/ip6tables.rules

#allow FTP on port 21 as well for LAN, no need for it now
#iptables -t nat -A PREROUTING -p tcp -d 192.168.1.6 --dport 21 -j DNAT --to 192.168.1.6:60

#set total process limit
ulimit -n 4096

#pureftpd; add -b for compatibility with ftp_ssl_connect in php
#pureftpd; removed  -O clf:/var/ftp/logs/pureftpd_clf.txt 
#pureftpd; removed -l puredb:/var/ftp/users/accounts.pdb 
#pureftpd; removed -X, added -D
#pureftpd; removed -4 
#pureftpd; added -o for pure-uploadscript
#pureftpd; removed -Z for read-only users

#start pure-ftpd
/usr/local/sbin/pure-ftpd -f ftp -l mysql:/var/ftp/config/pure-ftpd/mysql.conf -0 -C 50 -c 10000 -E -A -H -D -S 21 -p 12000:13000 -u 1 -j -P lacicloud.net -F /var/ftp/config/pure-ftpd/fortune_cookie -k 98 -b --fscharset=UTF-8 --clientcharset=UTF-8 -Y 1 -y 50:1 -o &
/root/pure-ftpd-implicit/pure-ftpd-implicit -f ftp -l mysql:/var/ftp/config/pure-ftpd/mysql.conf -0 -C 50 -c 10000 -E -A -H -D -S 990 -p 12000:13000  -u 1 -j -P lacicloud.net -F /var/ftp/config/pure-ftpd/fortune_cookie -k 98 -b --fscharset=UTF-8 --clientcharset=UTF-8 -Y 3 -y 50:1 -o &

/usr/local/sbin/pure-uploadscript -B -r /etc/scripts/bandwidthcounter.sh

#for some reason MySQL only starts if we put sleep 5 before, and sleep 3 after
sleep 5

service mysql start

sleep 3

#start maltral
python /root/maltrail/sensor.py & 
python /root/maltrail/server.py & 

#start mosquitto server
service mosquitto start

exit 0

