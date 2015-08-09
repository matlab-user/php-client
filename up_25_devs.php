<?php
	
	for( $i=1; $i<=25; $i++ ) {
		$ID = sprintf( "%03d", $i );
		$cmd = "nohup php hard_ware_v2.php -i $ID > /dev/null 2>&1 &";
		echo "$cmd\r\n";
		exec( $cmd );
	}

?>