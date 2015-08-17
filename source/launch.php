<?php

$config = json_decode(file_get_contents('/usr/local/appsbypacketapps/hhp/honey.conf'),true);

$interfaces = $config['interfaces'];
$ports = $config['ports'];
if( $config['enable'] ){                                                                     
    foreach( $interfaces as $interface ){
        foreach( $ports as $port ){
            passthru("/usr/local/appsbypacketapps/hhp/thread.sh ".$interface[0]." ".$port." ".$interface[1]." > /dev/null &");
        }
    }   
}

?>
