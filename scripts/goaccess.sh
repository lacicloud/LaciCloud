#!/bin/bash

/usr/local/bin/goaccess /var/ftp/logs/nginx_access.txt*  -a -o /var/ftp/www/developers/localweb/monitoring/statistics_longrandomstring.html --log-format=COMBINED --num-tests=0

