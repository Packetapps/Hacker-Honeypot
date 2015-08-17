#!/bin/sh
# called by /usr/local/appspacketapps/ihp/launch.php
echo $$ >> /usr/local/appsbypacketapps/hhp/pids
while (true); do
	int=$(echo $1)
	port=$(echo $2)
    ip=$(echo $3)
	packetframe=$(tcpdump -ni $int -c1 port $port and dst $ip)

    # ip* variables to verify, depend on tcpdump options
	ipsrc=$(echo $packetframe | cut -d' ' -f3 | cut -d'.' -f1-4)
	ipdst=$(echo $packetframe | cut -d' ' -f5 | cut -d'.' -f1-4)

    echo $packetframe | egrep -ho '(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?(\.|$)){4}' || exit
	macaddress=$(arp -an | grep $ipsrc | awk '{print $4}')
    ipnotif="$ipsrc has probed a unusual tcp or udp port ($port) on interface $int , device probed is [hostname] ."
	[ !  -z  $macaddress  ] && macnotif="Because $ipsrc in on local $int ip network, we found MAC-Address : $macaddress"
	echo -e "${ipnotif} \n ${macnotif} \n Interface : $int \n Source IP : $ipsrc \n Probed IP : $ipdst \n Probed port : $port \n MAC Address : $macaddress \n Dump : $packetframe" | /usr/local/bin/php /usr/local/appsbypacketapps/hhp/mail.php
	sleep 30
done