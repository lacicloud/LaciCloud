#!/bin/bash


#done manually of course with password enetered by me
if [ ! -f /var/ftp/.online ]; then
    cryptsetup luksOpen /dev/sda ftp && mount -o rw,noexec,nosuid,nodev /dev/mapper/ftp /var/ftp 
fi
