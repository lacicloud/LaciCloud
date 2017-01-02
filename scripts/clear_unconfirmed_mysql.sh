#!/bin/bash
#A user has 24 hours to confirm their accounts

date=$(date +"%T")

#cfget is required
username = `cfget -C /var/ftp/www/developers/secrets.ini secrets/deletebot_user`
password = `cfget -C /var/ftp/www/developers/secrets.ini secrets/deletebot_password`

/usr/bin/mysql -u "$username" --password="$password" -e 'USE laci_corporations_users; DELETE FROM users WHERE unique_id != 1;'

echo "Deleted unconfirmed accounts at $date" >> /var/ftp/logs/events.txt
