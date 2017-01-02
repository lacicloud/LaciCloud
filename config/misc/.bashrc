alias openencrypteddrive='cryptsetup luksOpen /dev/sda ftp && mount -o rw,noexec,nosuid,nodev /dev/mapper/ftp /var/ftp'
