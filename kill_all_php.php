<?php
	$my_pid = getmypid();
	//echo "self-pid:  $my_pid\r\n";
	
	$cmd = 'ps -e | grep php';
	$handle = popen( $cmd, 'r' );
	$read = fread( $handle, 2096 );
	echo "res:\r\n$read\r\n\r\n";
	pclose( $handle );
	
	$cmd_array = array();
	$one_cmd = strtok( $read, "\r\n" );
	while( $one_cmd ) {
		$cmd_array[] = $one_cmd;
		$one_cmd = strtok( "\r\n" );
	}
	
	//var_dump( $cmd_array );
	
	foreach( $cmd_array as $v ) {
		$pid = strtok( $v, " \r\n" );
		
		if( $pid!=$my_pid ) {
			$cmd = "kill $pid";
			echo "$cmd\r\n";
			system( $cmd );
		}
	}


?>