#!/usr/bin/python2
#This script sets up user's own domains and the webhosting feature
#Python2 only

import MySQLdb
import time
import os
import shutil
import subprocess
import sys

import logging

logging.basicConfig(format='%(asctime)s %(message)s',filename='/var/ftp/logs/webhosting.txt',level=logging.INFO)

try:
    from configparser import ConfigParser
except ImportError:
    from ConfigParser import ConfigParser  # ver. < 3.0

# instantiate
config = ConfigParser()

# parse existing file
config.read('/var/ftp/www/developers/secrets.ini')

username = config.get('secrets', 'webhosting_user')
password = config.get('secrets', 'webhosting_password')

#--- mysql starts here ---#

db = MySQLdb.connect(host="localhost",
                     user=username,
                     passwd=password,
                     db="laci_corporations_users")

cursor = db.cursor()
cursor.execute("set autocommit = 1")

updatequery = "UPDATE webhosting SET done=1 WHERE id=%s" 
deletequery = "DELETE FROM webhosting WHERE id=%s"

logging.info("Script started")

while 1:
        cursor.execute("SELECT * FROM webhosting WHERE done=0")


        for row in cursor.fetchall():
	        id = row[0]
		realID = row[1]
		action = row[2]
		sitename = row[3]
		mysql_username = row[4]
		mysql_password = row[5]
		done = row[6]
		if done == 0:
			if action == "addwebhostingenv":  
				
				#ftpactions.py hasn't set-up the directories yet
				if not os.path.exists("/var/ftp/users/" + str(realID)):
					continue

				return_code = subprocess.call(["/etc/scripts/webhosting.sh",sitename,str(realID),mysql_username,mysql_password])
				if return_code != 0:
					logging.info("Error adding webhosting user " + sitename + " with id " + str(realID) + ", return code was " + str(return_code) + "!")
					print("Error adding webhosting user " + sitename + " with id " + str(realID) + ", return code was " + str(return_code) + "!")
					sys.exit()
					continue
				else:
					logging.info("Added webhosting user " + sitename + " with id " + str(realID) + "!")
					print("Added webhosting user " + sitename + " with id " + str(realID) + "!")
				cursor.execute(updatequery, (str(id),))

			elif action == "resetperms":
			
				return_code = subprocess.call(["/etc/scripts/webhosting_reset_permissions.sh",sitename,str(realID)])
				logging.info("Reset permissions for " + sitename + " with id " + str(realID) + "!")
				cursor.execute(deletequery, (str(id),))

			elif action == "resetmysql":
			
				return_code = subprocess.call(["/etc/scripts/webhosting_reset_mysql.sh",mysql_username,mysql_password])
				if return_code != 0:
					logging.info("Error resetting mysql for " + sitename + " with id " + str(realID) + ", return code was " + str(return_code) + "!") 
				else:
					logging.info("Reset mysql for " + sitename + " with id " + str(realID) + "!")
				cursor.execute(deletequery, (str(id),))
			
			else:
			    logging.info("Critical error while running script, value of action, not valid, is " + action +  " for user " + sitename + " and ID " + str(id) + "!")

		else:
			 logging.info("Critical error while running script, value of done, not valid, is " + str(done) +  " for user " + sitename + " and ID " + str(id) + "!")
	break


