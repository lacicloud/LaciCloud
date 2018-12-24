#!/bin/bash

sed 's/\([0-9]\{1,3\}\.\)\{3,3\}[0-9]\{1,3\}/0.0.0.0/g' /var/ftp/logs/pureftpd_sys.txt > /var/ftp/logs/out.txt
sed 's/\([A-Za-z0-9]*:\)\{1,7\}[A-Za-z0-9]\{1,4\}/0:0:0:0:0:0:0:0/2g' /var/ftp/logs/out.txt > /var/ftp/logs/pureftpd_sys.txt
rm /var/ftp/logs/out.txt

sed 's/\([0-9]\{1,3\}\.\)\{3,3\}[0-9]\{1,3\}/0.0.0.0/g' /var/log/messages > /var/log/out.txt
sed 's/\([A-Za-z0-9]*:\)\{1,7\}[A-Za-z0-9]\{1,4\}/0:0:0:0:0:0:0:0/2g' /var/log/out.txt > /var/log/messages
rm /var/log/out.txt
