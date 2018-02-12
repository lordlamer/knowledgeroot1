<?php
$CONF = array(
	"whitelist" => 1, // enable/disable this filter
	"blacklist" => 1, // enable/disable this filter
	"order" => 'whitelist,blacklist', // order of filters

	"fileuploads" => 1, // should i check fileuploads?

	"whitelistconfig" => array(
		"useXhtml" => true, // should we use XHTML modus for whitelisting?
	),

	"whitelistitems" => array( // which global vars should be checked with whitelisting
                "POST" => 1,
                "GET" => 1,
                "COOKIE" => 1,
                "SERVER" => 1,
                "SESSION" => 0,
		"REQUEST" => 1,
	),

	"blacklistitems" => array( // which global vars should be checked with blacklisting
		"POST" => 1,
		"GET" => 1,
		"COOKIE" => 1,
		"SERVER" => 1,
		"SESSION" => 0,
		"REQUEST" => 1,
	),
);
?>
