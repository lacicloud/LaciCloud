#!/bin/bash

#be quiet please
hdparm -M 128 /dev/sda
hdparm -M 128 /dev/sdb

#wait for drive to be decrypted
while [ ! -f /var/ftp/.online ]
do
  sleep 5
done

#run script from /etc/scripts
/etc/scripts/bootstrap_stage_2.sh &


exit 0

