<?php

	(php_sapi_name() === 'cli') or die("not allowed");

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

	

	
