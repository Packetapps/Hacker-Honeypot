#!/bin/sh
touch /usr/local/appsbypacketapps/hhp/pids
chmod 777 /usr/local/appsbypacketapps/hhp/pids
/usr/local/etc/rc.d/hackerhoneypot.sh &
