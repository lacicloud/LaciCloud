#!/bin/bash

#Running on Raspberry Pi, attemps to restart server in case it goes down through WOL
#DO NOT EVER GIVE UP

mac=`cfget -C /var/ftp/www/developers/secrets.ini secrets/self_mac_address`

if ping -c 1 wheatley &> /dev/null
then
  echo "OK"
else
  etherwake "$mac"
fi

