#!/bin/bash

#be quiet please
hdparm -M 128 /dev/sda
hdparm -M 128 /dev/sdb

#wait for drive to be decrypted
while [ ! -f /var/ftp/.online ]
do
  sleep 5
done

#make required directory for scripts to run because the 4TB data drive is always mounted with nodev, noexec, nosuid
mkdir /tmp/scripts

#run script that starts LaciCloud
cp /var/ftp/scripts/bootstrap_stage_2.sh /tmp/scripts/bootstrap_stage_2.sh

chmod +x /tmp/scripts/bootstrap_stage_2.sh

/tmp/scripts/bootstrap_stage_2.sh &

#wait for it to terminate then delete it from the /tmp/scripts dir
sleep 30
srm /tmp/scripts/bootstrap_stage_2.sh 

exit 0

