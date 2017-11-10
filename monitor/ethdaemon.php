<?php

	(php_sapi_name() === 'cli') or die("not allowed");

	if(!file_exists(__DIR__ . '/ethdaemon.ini')){
		echo "The configuration file doesn't exist...";
		die();
	}

	$conf = parse_ini_file(__DIR__ . '/ethdaemon.ini');

	define("MONITOR_BY", "SCREEN"); // valid values are POOL and SCREEN

	//todo
	//nvidia-smi -q -d CLOCK
	//nvidia-smi

    function colorset($str, $color)
    {
        $ANSI_CODES = array(
            "off"        => 0,
            "bold"       => 1,
            "red"        => 31,
            "green"      => 32,
            "yellow"     => 33,
        );

        $color_attrs = explode("+", $color);
        $ansi_str = "";
        foreach ($color_attrs as $attr) {
            $ansi_str .= "\033[" . $ANSI_CODES[$attr] . "m";
        }
        $ansi_str .= $str . "\033[" . $ANSI_CODES["off"] . "m";
        return $ansi_str;
    }

    if(MONITOR_BY === "POOL") // connect to the pool and see how things are going
	    while (1) {
			$content = file_get_contents('https://eth.nanopool.org/api/v1/workers/0x83347b2d521c697bfb6b25b3633304c1fbd9a553');
			$content = json_decode($content);
			$entry = null;
			foreach ($content->data as $entry) {
				$time_since_last_share = time() - $entry->lastShare;
				$color = ($entry->hashrate > 40) ? 'green+bold' : 'red+bold';
				echo colorset("$entry->id: ", $color) . round($time_since_last_share/60, 2) . "m since last share. Hashrate: " . colorset($entry->hashrate, $color) . "\n";
			}
			echo "Sleeping ... ";
	        for ($i=10; $i > 0; --$i) { 
	            echo $i . " ";
	            sleep(1);
	        }
	        echo "\n";
	    }

	if(MONITOR_BY === "SCREEN") // connect to a screen session output and parse it
    	$logfile = __DIR__ . '/scr.log';
    	exec("screen -S miner -X logfile {$logfile}");

    	$last = [];
	    while (1) {
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

			if($content === $last) {
				echo colorset("Miner hanged.", "red+bold") . "\n";
				//trigger a webhook
				file_get_contents("https://www.codepunker.com/tools/web-hooks/82?execute=storeWebHook&pass=8nqjmb8d&miner=hanged");
				die;
			}
			$speed_found = 0;
			foreach ($content as $entry) {
				$matches = [];
				if(preg_match("/Speed.{8}(\d*)\./", $entry, $matches) === 1) {
					$speed_found = $matches[1];
					break;
				}
			}
			$color = ($speed_found > 200) ? 'green+bold' : 'red+bold';
			echo colorset("Speed found: ", $color) . colorset($speed_found, $color) . "\n";

			if($speed_found < 200) {
				//trigger a webhook
				file_get_contents("https://www.codepunker.com/tools/web-hooks/82?execute=storeWebHook&pass=8nqjmb8d&miner=slow");
				die;
			}

			echo "Sleeping ... ";
	        for ($i=10; $i > 0; --$i) { 
	            echo $i . " ";
	            sleep(1);
	        }
	        echo "\n";

	        $last = $content;
	    }

	
