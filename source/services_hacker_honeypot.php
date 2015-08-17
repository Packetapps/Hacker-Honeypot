<?php
set_time_limit(900);
ini_set('max_execution_time', 900);

require("guiconfig.inc");
require_once("/etc/inc/functions.inc");

function isSelected($int,$interfaces){
	$i=0;
	while($i<count($interfaces)){
		if( $interfaces[$i][0] == $int ){
			return true;
		}
		$i++;
	}
	
	return false;
}

$config_honey = json_decode(file_get_contents('/usr/local/appsbypacketapps/hhp/honey.conf'),true);
if( isset($_POST['enable']) && isset($_POST['int']) && isset($_POST['port']) && isset($_POST['sub']) ){
    $config_honey['enable'] = $_POST['enable'];
	$interfaces = explode(';',$_POST['int']);
	$config_honey['interfaces'] = array();
	foreach( $interfaces as $target ){
		foreach ($config['interfaces'] as $ifname => $iface){
			if( $target == $iface['if'] ){
				$config_honey['interfaces'][] = array($iface['if'],$iface['ipaddr']);
			}
		}
	}
    $config_honey['ports'] = explode(';',$_POST['port']);
    $config_honey['subject'] = $_POST['sub'];
    conf_mount_rw();
	file_put_contents('/usr/local/appsbypacketapps/hhp/honey.conf',json_encode($config_honey));
	
	$pids = file_get_contents('/usr/local/appsbypacketapps/hhp/pids');
	$pid_explode = explode(PHP_EOL,$pids);
	
	foreach( $pid_explode as $pid ){
       if( $pid != "" && $pid != " " ){
			exec('kill -9 '.$pid);
	   }
	}
	file_put_contents('/usr/local/appsbypacketapps/hhp/pids',"");
	conf_mount_ro();
	exec('killall tcpdump');
	if( $config_honey['enable'] ){
		system('/usr/local/bin/php /usr/local/appsbypacketapps/hhp/launch.php > /dev/null &');
	}
	echo json_encode(array('res'=>"ok"));
}
else{
	include("head.inc");
	include("fbegin.inc");
?>
<body link="#0000CC" vlink="#0000CC" alink="#0000CC">
	<link rel='stylesheet' href='./css/bootstrap.css'/>
	<script language="javascript" src="javascript/jquery-1.11.1.min.js"></script>
	<script language="javascript" src="javascript/jquery/jquery-ui-1.11.1.min.js"></script>
	<script>
        jQuery(document).ready(function(){
		jQuery(document).on('click','#save',function(){
						var below = jQuery('#save').parent();
                        var enable = jQuery('#enable').is(':checked');
                        var interfaces = [];
                        jQuery('.interface:checked').each(function(){
                                interfaces.push(jQuery(this).val());
                        });
                        if( interfaces.length == 0 ){
							below.html("<span style='color:red'>You have to check at least one interface</span>");
							setTimeout(function(){
								below.html("<button class='formbtn' id='save'>Save</button>");
							},3000);
						}
						else{
							var ports = [];
                        	jQuery('.port:checked').each(function(){
                        	        ports.push(jQuery(this).val());
                        	});
                         	if( ports.length == 0 ){
								below.html("<span style='color:red'>You have to check at least one port</span>");
								setTimeout(function(){
									below.html("<button class='formbtn' id='save'>Save</button>");
								},3000);
							}
							else{
								var subject = jQuery('#subject').val();
								$.ajax({
									url : 'services_hacker_honeypot.php',
									type : 'POST',
									data : 'enable='+enable+'&int='+interfaces.join(';')+'&port='+ports.join(';')+'&sub='+subject,
									dataType : 'json',
									success : function(json){
										if ( json['res'] == "ok" ) {
											below.html("<span style='color:green'>Changes have been applied successfully</span>");
										}
										else{
											below.html("<span style='color:red'>A problem occured during Hacker Honeypot configuration save</span>");
										}
										
										setTimeout(function(){
											below.html("<button class='formbtn' id='save'>Save</button>");
										},3000);	
									}
								});
							}
						}
                });
        });
    </script>

    <div class='row'>
		<div class='col-xs-3' style='margin-bottom:40px;'>
			<img src='logo/Hacker_Honeypot' alt='Hacker Honeypot' style='width:70%;'>
		</div>
        <div class='col-xs-9' style='margin-bottom:40px;'>
    		<span style='margin-bottom:20px;color:#500;font-size:25px;'>Hacker Honeypot</span>
		</div>
   </div>
   <div class='row' style='background-color:#900;'>
		<div class='col-xs-12' style='color:#FFF;padding:10px;'>
				<strong>Deamon Settings</strong>
		</div>
		
   </div>
   <div class='row' style='border-bottom:1px solid #000;background-color:#DDD;'>
		<div class='col-xs-3' style='padding:10px;'>
			<span><strong>Enable</strong></span>  
		</div>
		<div class='col-xs-9' style='padding:10px;background-color:#FFF;'>
			<div class="row">
				<div class='col-xs-12'>
					<input id='enable' type='checkbox' <?php if( $config_honey['enable'] ){ echo "checked='true'"; }?>/> Enable Hacker Honeypot service
				</div>			
			</div>
			<div class="row">
				<div class="col-xs-12">	
					<span style='font-size:11px;'>Please verify this firewall email <a href='system_advanced_notifications.php'>settings</a>.</span>
				</div>
			</div>
		</div>
    </div>
   <div class='row' style='border-bottom:1px solid #000;background-color:#DDD;'>
		<div class='col-xs-3' style='padding:10px;'>
			<div class='row'>
				<div class='col-xs-12'>
					<span><strong>Interfaces to listen on</strong></span>
				</div>
			</div>
			<div class='row'>
				<div class='col-xs-12'>
					<span style='font-size:11px;'>Interfaces on which we could expect a hacker to be caught</span>
				</div>
			</div>	  
		</div>
		<div class='col-xs-9' style='padding:10px;background-color:#FFF;'>
				<?php
				$ifdescrs = get_configured_interface_with_descr(false, true);
				foreach ($ifdescrs as $ifdescr => $ifname){
					$ifinfo = get_interface_info($ifdescr);
					if( $ifinfo['status'] !== 'down' ){ ?>
						<div class='row'>
							<div class='col-xs-12'>
								<input type='checkbox' class='interface' style='margin-right:5px;' value='<?php echo $ifinfo['if'];?>' <?php if( isSelected($ifinfo['if'],$config_honey['interfaces']) ){ echo "checked='true'"; } ?> >  <?php echo $ifdescr.' ( '.$ifinfo['if'].' : '.$ifinfo['ipaddr'].' )';?></input>
							</div>
						</div>
					<?php }
				} ?>
		</div>
    </div>
    <div class='row' style='border-bottom:1px solid #000;background-color:#DDD;'>
		<div class='col-xs-3' style='padding:10px;'>
			<div class='row'>
				<div class='col-xs-12'>
					<span><strong>Trapped ports</strong></span>
				</div>
			</div>
			<div class='row'>
				<div class='col-xs-12'>
					<span style='font-size:11px;'>Unused TCP/UDP ports that will trigger the alarm</span>
				</div>
			</div>	  
		</div>
		<div class='col-xs-9' style='padding:10px;background-color:#FFF;'>
			<div class='row'>
				<div class='col-xs-12'>
					<input class='port' style='margin-right:5px;' type='checkbox' value='21' <?php if( in_array(21,$config_honey['ports']) || count($config_honey['ports']) == 0 ){ echo "checked='true'"; } ?> >21</input>
				</div>
			</div>
			<div class='row'>
				<div class='col-xs-12'>
					<input class='port' style='margin-right:5px;' type='checkbox' value='23' <?php if( in_array(23,$config_honey['ports'])){ echo "checked='true'"; } ?>>23</input>
				</div>
			</div>
			<div class='row'>
				<div class='col-xs-12'>
					<input class='port' style='margin-right:5px;' type='checkbox' value='25' <?php if( in_array(25,$config_honey['ports'])){ echo "checked='true'"; } ?>>25</input>
				</div>
			</div>
			<div class='row'>
				<div class='col-xs-12'>
					<input class='port' style='margin-right:5px;' type='checkbox' value='110' <?php if( in_array(110,$config_honey['ports'])){ echo "checked='true'"; } ?>>110</input>
				</div>
			</div>
			<div class='row'>
				<div class='col-xs-12'>
					<input class='port' style='margin-right:5px;' type='checkbox' value='8080' <?php if( in_array(8080,$config_honey['ports'])){ echo "checked='true'"; } ?>>8080</input>	
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12">	
					<span style='font-size:11px;'>Selected port should be unused by this firewall ( not in a listen state on this firewall interfaces ).</span>
				</div>
			</div>
		</div>
    </div>
    <div class='row' style='border-bottom:1px solid #000;background-color:#DDD'>
		<div class='col-xs-3' style='padding:10px;'>
			<strong>Email subject</strong>
		</div>
		<div class='col-xs-9' style='padding:10px;background-color:#FFF;'>
			<div class="row">
				<div class='col-xs-12'>
					<input id='subject' type='text' style='width:50%;background:#EEE none repeat scroll 0% 0%;' value='<?php if( $config_honey['subject'] == '' ){ echo '[hostname] Intrusion attempt from [ip]';}else{echo $config_honey['subject'];}?>'/>
				</div>			
			</div>
			<div class="row">
				<div class="col-xs-12">	
					<span style='font-size:11px;'>You can specify [ip], [hostname], [interface] or [port]. Items will be replaced in the email's subject.</span>
				</div>
			</div>
		</div>
    </div>
    <div class='row'>
		<div class='col-xs-12' style='text-align:center;margin:15px;'>
			<button class='formbtn' id='save'>Save</button>
		</div>
    </div>
    <?php include("fend.inc"); ?>
</body>
</html>
<?php } ?>