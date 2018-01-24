<?php
	/**
	 * simple ethminer monitor written in PHP 
	 * to enable it put `cd /home/__USER__/Desktop/eth && php ethdaemon.php >> /home/__USER__/Desktop/eth/miner.log 2>&1` in `sudo crontab -e` every 10 minutes or so
	 */
	(php_sapi_name() === 'cli') or die("not allowed");
	$conf = parse_ini_file(__DIR__ . 'daemon.ini');
	(!empty($conf["NO_OF_GPUS"])) or die("You have to define the NO_OF_GPUS setting in daemon.ini");

	echo "I am " . shell_exec("whoami");
    for($i=0; $i<($conf["NO_OF_GPUS"]-1); $i++) {
        $output = shell_exec("nvidia-smi -i {$i} --query-gpu=utilization.gpu --format=csv,noheader,nounits");
        echo "Current GPU{$i} utilization is: {$output} \n";
            if($output<=40)
                break;
    }

	if($output <= 40) {

    	$logfile = __DIR__ . '/scr.log';
    	exec("screen -S miner -X logfile {$logfile}");

		echo "Miner failed at " . date('Y-m-d H:i:s') . PHP_EOL;
		echo "Gathering some output ... " . PHP_EOL;

		$content = [];
		exec("screen -S miner -X log on");

		//default screen dump to disk time is 10 secs ... so let's wait 15
		echo "Sleeping for 15 seconds to gather some output ... \n";
		sleep(15);
		$content = file($logfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$entry = null;
		exec('screen -S miner -X log off'); //disable logging
		if(file_exists($logfile)) // remove the file
			unlink($logfile);

		echo join("\n", $content) . PHP_EOL;

		exec("/sbin/shutdown -r now");
		echo "Restarting ... \n";

	}
	die;

