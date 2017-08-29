#!/usr/bin/python2
#This script does things that PHP by it's configuration is not allowed to, such as access the /var/ftp directory and create symlinks to /var/ftp/public_files
#Python2 only

import MySQLdb
import time
import os
import shutil

import logging

logging.basicConfig(format='%(asctime)s %(message)s',filename='/var/ftp/logs/ftpactions.txt',level=logging.INFO)

try:
    from configparser import ConfigParser
except ImportError:
    from ConfigParser import ConfigParser  # ver. < 3.0

# instantiate
config = ConfigParser()

# parse existing file
config.read('/var/ftp/www/developers/secrets.ini')

username = config.get('secrets', 'ftpactions_user')
password = config.get('secrets', 'ftpactions_password')
db_host = config.get('secrets', 'db_host')
db_name = config.get('secrets', 'db_name')


#--- original ftpactions.py ---#

db = MySQLdb.connect(host=db_host,    
                     user=username,         
                     passwd=password,  
                     db=db_name)        

cursor = db.cursor()
cursor.execute("set autocommit = 1")

deletequery = "DELETE FROM ftpactions WHERE value=%s" #like this it prevents sql injection

logging.info("Script started")

while 1:
	cursor.execute("SELECT * FROM ftpactions")

	#avoid using shell commands because of shell injection

	for row in cursor.fetchall():
 	   	value = str(row[0])
    		type = str(row[1])

    		if int(type) == 0: #type 0 is make symlink to public files on user creation
			if "../" in value: 
                          logging.info("Refusing to run because ../ detected for type 0. Value is: " + value)
                          cursor.execute(deletequery, (value,))
                          continue
		        try:
			   if not os.path.exists("/var/ftp/users/" + value + "/public_files"):
    				if not os.path.exists("/var/ftp/users/" + value):
					os.makedirs("/var/ftp/users/" + value)
					os.chown("/var/ftp/users/" + value, 2001, 2001)
				os.makedirs("/var/ftp/users/" + value + "/public_files")
				os.chown("/var/ftp/users/" + value + "/public_files", 2001, 2001)
				logging.info("Created directory /var/ftp/users/" + value + " and /var/ftp/users/" + value + "/public_files and set permissions accordingly")
			   os.symlink("/var/ftp/users/" + value + "/public_files", "/var/ftp/public_files/" + value)
		           logging.info("Symlinked /var/ftp/users/" + value + "/public_files to /var/ftp/public_files/" + value)
			except: 
			   logging.info("Couldn't symlink /var/ftp/users/" + value + "/public_files to /var/ftp/public_files/" + value)
                        cursor.execute(deletequery, (value,))

		elif int(type)  == 1: #type 1 is remove leftover .ftpquota file from deleted FTP users directory
				
			try:
			  if os.listdir("/var/ftp/users/" + value) != ['.ftpquota']:
			  	logging.info("Refusing to run because directory not empty for type 1. Value is: " + value)	
				cursor.execute(deletequery, (value,))
				continue
			except:
				logging.info("Couldn't list directory contents, probably because directory does not exist, for type " + type + " and value is " + value)
				cursor.execute(deletequery, (value,))
				continue

			if "../" in value:
                          logging.info("Refusing to run because ../ detected for type 1. Value is: " + value)
                          cursor.execute(deletequery, (value,))
                          continue
                        try:
                           os.remove("/var/ftp/users/" + value + "/.ftpquota")
			   logging.info("Removed file " + value + "/.ftpquota")
                        except:
                           logging.info("Couldn't remove file /var/ftp/users/" + value + "/.ftpquota")
			   continue
                        cursor.execute(deletequery, (value,))
		else: 
		   logging.info("Type is not in valid range, type is " + type + " and value is " + value)
	break

cursor.close()

#--- truespacecounter.py ---#
#Counts real used space in user's directory

def get_size(start_path = '.'):
    total_size = 0
    for dirpath, dirnames, filenames in os.walk(start_path):
        for f in filenames:
            fp = os.path.join(dirpath, f)
            total_size += os.path.getsize(fp)
    return total_size


cursor = db.cursor()
cursor.execute("set autocommit = 1")

cursor.execute("SELECT id FROM users")

for row in cursor.fetchall():
        id = int(row[0])

	try:
        	used_space = int(get_size("/var/ftp/users/" + str(id))) / 1024 / 1024
     	except:	
		logging.info("An error occured while getting user's total used space for ID " + str(id) + "!")
		continue

        affected_rows = cursor.execute(("UPDATE truespacecounter SET used_space=%s WHERE id=%s"), (used_space, id))
	
	#value changed
	if affected_rows > 0:
		logging.info("Updated FTP space used for ID " + str(id) + " to " + str(used_space))

cursor.close()
db.close()

#--- qftp.py ---#

db_host_ftp = config.get('secrets', 'db_host_ftp')
db_name_ftp = config.get('secrets', 'db_name_ftp')

db = MySQLdb.connect(host=db_host_ftp,
                     user=username,
                     passwd=password,
                     db=db_name_ftp)

cursor = db.cursor()
cursor.execute("set autocommit = 1")

deletequery = "DELETE FROM ftp_users WHERE user=%s" #like this it prevents sql injection

cursor.execute("SELECT expiration, user FROM ftp_users")

for row in cursor.fetchall():
        expiration = str(row[0])
        user = str(row[1])
        if int(time.time()) > int(expiration) and int(expiration) != 0:
                shutil.rmtree("/var/ftp/users/qftp/" + user, ignore_errors=True)
                cursor.execute(deletequery, (user,))
                logging.info("Removed QFTP user " + user + " with expiration " + expiration)

cursor.close()
logging.info("Script stopped")
db.close()

