#!/bin/bash
#backup script to Mega

date=`date +%Y_%m_%d_%H_%M_%S`

#fix megasync error
export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:/usr/local/lib/:/usr/local/bin/

#set config options
MEGASYNC='/usr/local/bin/megasync'
MEGARM='/usr/local/bin/megarm'
BACKUP_TIME=`date +%c`
LOG="/var/ftp/logs/backup.txt"
hostname=`hostname`
LOCALDIR="/var/ftp/users"
REMOTEDIR="/Root/LaciCloudBackup/users"
SEDLOCALDIR="\/var\/ftp\/users"
SEDREMOTEDIR="\/Root\/LaciCloudBackup\/users"

#cfget is required
username=`cfget -C /var/ftp/www/developers/secrets.ini secrets/bitbucket_user`
password=`cfget -C /var/ftp/www/developers/secrets.ini secrets/bitbucket_password`

#for mysqldump
export MYSQL_PWD=`cfget -C /var/ftp/www/developers/secrets.ini secrets/mysql_root_password`

#backup permissions and structure (filelist) with temporary filename for Mega
find /var/ftp -ls -path /var/ftp/private -prune -o -iname findme -print -iname findme -print  > /var/ftp/config/misc/filelist_"$date".txt

#backup MySql database
mysqldump -u root --all-databases > /var/ftp/tmp/backup/lacicloud_"$date".sql

megaput --path /Root/LaciCloudBackup /var/ftp/tmp/backup/lacicloud_"$date".sql
megaput --path /Root/LaciCloudBackup  /var/ftp/config/misc/filelist_"$date".txt

#remove filelist with temporary filename and create real one
rm /var/ftp/config/misc/filelist_"$date".txt
find /var/ftp -ls -path /var/ftp/private -prune -o -iname findme -print -iname findme -print  > /var/ftp/config/misc/filelist.txt

#cleanup
rm /var/ftp/tmp/backup/*

#git backup
git add /var/ftp/*
git commit -m "Auto-commit at $date by backup.sh"
git push https://"$username":"$password"@bitbucket.org/lacicloud/lacicloud.git/

echo "Backed-up system at $date" >> /var/ftp/logs/events.txt
echo "Backed-up system at $date" >> $LOG
