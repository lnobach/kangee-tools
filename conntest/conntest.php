<?php

header("Content-Type: text/plain");

// Roo IP addres determination tool. (c) Leo Nobach
// Checks if the Roo application is contactable from
// WAN and returns the IP addresses.

$port = $_REQUEST['port'];
$ping = $_REQUEST['ping'];

$TIMEOUT = 10;
$EXP_ANSWER = "ROO-PONG";

function removereturns($string) {
	$result = str_replace("\r", "\\r", $string);
	$result = str_replace("\n", "\\n", $result);
	return $result;
}

// Requests and waits for a HTTP response of the format ROO-PONG_[rooID]
// Returns the roo ID if everything was fine, or else false.
function http_ping_request($host, $port) {
	global $timeout;

	$url = "http://" . $host . ":" . $port . "/ping";

	echo "# Requesting: " . $url . "\n";

 	$ch = curl_init ($url) ;
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1) ;
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        $res = curl_exec ($ch) ;

	if ($res == false) {
		$errno = curl_errno($ch);
		$error = curl_error($ch);
		echo ("# Could not ping you. " . $error ."\n");
		echo ("PING_ERRSTR=" . $error . "\n");
		echo ("PING_ERRNO=" . $errno . "\n");
		curl_close ($ch);
		return false;
	}

	$res_arr = explode("_", $res, 2);

	if (strcmp($res_arr[0], $EXP_ANSWER)) {
		curl_close ($ch);		
		return $res_arr[1];
	}

	echo ("# Expected answer was not " . $EXP_ANSWER . ", instead it was: " . removereturns($res) . "\n");
	echo ("PING_ERRSTR=Wrong Answer\n");
	echo ("PING_ERRNO=2000\n");
	curl_close ($ch);
	return false;

}

if (!is_numeric($port) || $port < 0 || $port > 65535) {
	echo("ERR_STR=Illegal port parameter. Stopping for security reasons.\n");
	echo("SUCCESS=false\n");
} else if (!isset($ping) || ($ping != 0 && $ping != 1)) {
	echo("ERR_STR=Ping is not set or has a wrong value. Stopping for security reasons.\n");
	echo("SUCCESS=false\n");
} else {

	$ip = getenv('REMOTE_ADDR');
	echo("IPADDR=" . $ip . "\n");

	$hostname = gethostbyaddr($ip);
	echo("hostname=" . $hostname . "\n");
 
	if ($ping == 1) {

		echo("# Trying to ping " . $ip . "...\n");

		$rooID = http_ping_request($ip, $port);

		if($rooID !== false) {
			echo("ROO_ID=" . $rooID . "\n");
			echo("REACHABLE=true\n");
		} else {
			echo("REACHABLE=false\n");
		}
	}

	echo("SUCCESS=true\n");

}

?>

