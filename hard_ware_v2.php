<?php
		
	date_default_timezone_set( 'Asia/Chongqing' );
	
	pcntl_signal( SIGCHLD, SIG_IGN );
	
	$l_ip = '192.168.31.26';
	//$l_ip = '127.0.0.1';
	$l_port = 2023;
	
	$ops = getopt( "i:" );
	$ID = $ops['i'];
	$STA = '';
	
	while(TRUE) {
		
		$sock = socket_create( AF_INET, SOCK_STREAM, 0 );
		socket_set_option( $sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>10, "usec"=>0 ) );
		socket_set_option( $sock, SOL_SOCKET, SO_SNDTIMEO, array("sec"=>3, "usec"=>0 ) );
		socket_set_option( $sock, SOL_SOCKET, SO_REUSEADDR, 1 );
		
		if( socket_bind($sock, 0, 0)===FALSE ) {       		// 绑定 ip、port
			exit;
		}
	
		$res = socket_connect( $sock , $l_ip, $l_port );
		if($res===FALSE) {
			echo "connection timeout\r\n";
			sleep( 3 );
			continue;
		}
		
		echo "hard_ware is running!\r\n";
		
		$buff = "[$ID,0,0000c]";
		socket_write( $sock, $buff );
		
		$conns = array( $sock );
		
		$jump_heart_t = 0;
		$send_jump_num = 0;
		$if_break_conn = time();
		
		while(TRUE) {
			$read = $conns;
			$sele_res = socket_select( $read, $write=NULL, $except=NULL, 10 );
			if( FALSE===$sele_res )	{	
				socket_close( $conns[0] );
				break;
			}
			elseif( $sele_res>0 ) {
				$data = @socket_read( $read[0], 1300, PHP_BINARY_READ );
				if( $data===false ) {				// 出错，包括服务器断开连接
					socket_close( $read[0] );
					break;
				}
				else {
					if( !empty($data) ) {
						$mid_data = $data;
						strtok( $mid_data , "[,] \r\n" );
						$op_id = strtok ( "[,] \r\n" );
						
						switch( $op_id ) {
							case '1':
								$STA = strtok ( "[,] \r\n" );
								$buff = '';
								break;
								
							case '2':
								$STA = strtok ( "[,] \r\n" );
								$buff = "[$ID,3,$STA]";
								socket_write( $read[0], $buff );
								break;
								
							default:
								break;
							
						}

						echo "recv:  ".time()."    $data     send: $buff\r\n";
					}
					else {
						echo "server connection_aborted\r\n";
						socket_close( $read[0] );
						break;
					}
						
				}
			}
			
			// 超时
			// 发送心跳
			if ( (time()-$jump_heart_t)>=10 ) {
				
				$send_jump_num ++;
				$buff = "[$ID,6,$STA]";
				echo "\t\t\t\t\tsend heart-jump: $buff \r\n";
				$mid_res = socket_write( $sock, $buff );
				$jump_heart_t = time();
				if( $mid_res===FALSE ) {
					socket_close( $sock );
					break;
				}
				
				// send inquiry order 
				if( $send_jump_num>=10 ) {
					$send_jump_num = 0;
					$buff = "[$ID,0,$STA]";
					echo "\t\t\t\t\tsend inquiry-order: $buff \r\n";
					$mid_res = socket_write( $sock, $buff );
					if( $mid_res===FALSE ) {
						socket_close( $sock );
						break;
					}
				}
			}
			
			// 20 mins 后，中断连接
			if( (time()-$if_break_conn)>=5*60 ) {
				$if_break_conn = time();
				socket_close( $sock );
				$break_last_sec = rand( 60, 180 );
				echo "client break connection $break_last_sec secs\r\n";
				sleep( $break_last_sec );
				break;
			}
		}
		
	}
	
?>