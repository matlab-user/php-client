<?php
		
	date_default_timezone_set( 'Asia/Chongqing' );
	
	pcntl_signal( SIGCHLD, SIG_IGN );
	
	//$l_ip = '192.168.31.18';
	$l_ip = '10.71.29.11';
	$l_port = 2023;				
		
	$sock = socket_create( AF_INET, SOCK_STREAM, 0 );
	socket_set_option( $sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>10, "usec"=>0 ) );
	socket_set_option( $sock, SOL_SOCKET, SO_SNDTIMEO, array("sec"=>3, "usec"=>0 ) );
	socket_set_option( $sock, SOL_SOCKET, SO_REUSEADDR, 1 );
	
	if( socket_bind($sock, 0, 0)===FALSE ) {       		// 绑定 ip、port
		//error_log( "water-M socket_bind failed!\r\n", 3, '/tmp/water-M.log' );
		exit;
	}
	
	$res = socket_connect( $sock , $l_ip, $l_port );
	if($res===FALSE) {
		echo "connection timeout\r\n";
		sleep( 3 );
		continue;
	}
	
	echo "order_send is running!\r\n";
	$loop = 3000;
	$dt = array();
	$i = 0;
	for($i=0;$i<$loop;$i++) {
		if( mt_rand(0,1)==0 )
			$buff = "[".time().",OPEN,106]";
		else
			$buff = "[".time().",CLOSE,106]";
		
		socket_write( $sock, $buff );
		$t1 = time();
		$data = socket_read( $sock, 1300, PHP_BINARY_READ );
		$t2 = time();
		
		echo ($t2-$t1)." s\r\n";
		$dt[] = $t2 - $t1;
	}
	
	echo "max: ".max($dt)."      min: ".min($dt)."      avg:".array_sum($dt)/$loop."\r\n";
	socket_close( $sock );

?>