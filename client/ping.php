<?
    // 2011 Kanwei Li
    if ($env_reporter = getenv("REPORTER")) {
        define("REPORTER", rawurlencode($env_reporter));
    } else {
        define("REPORTER", "reporter");
    }
    if ($env_endpoint = getenv("API_ENDPOINT")) {
        if ($env_endpoint == "TEST") { return 0; }
        define("ENDPOINT", $env_endpoint);
    } else {
        define("ENDPOINT", "http://your.server.com");
    }
    
    if($argc < 2) {
      die("Usage: ping.php [check]");
    }
    $check = rawurlencode($argv[1]);
    $ping = curl_init(ENDPOINT . "/heartbeat/ping/$check/". REPORTER);
    curl_exec($ping);
    curl_close($ping);
    return 0;
?>