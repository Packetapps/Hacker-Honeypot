killall tcpdump
kill `cat /usr/local/appsbypacketapps/hhp/pids | tr "\n" " "`
rm -R /usr/local/appsbypacketapps/hhp/ 
