#!/usr/bin/env php
<?php

/*
    Copyright (C) 2017 Jack-Benny Persson <jack-benny@cyberinfo.se>
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/* 
   This is a small Nagios plugin to warn for severe weather. It gets the data
   from meteoalarm.eu. The script takes the full URL of the region your are
   interested in. For example:
   ./check_meteo.php www.meteoalarm.eu/en_UK/0/0/SE002-Sk%E5ne.html
*/

// Sanity check & help text
if ($argc != 2)
{
    printf("Please provide a full URL for your region\n");
    printf("Usage:\n");
    printf("$argv[0] [URL]\n\n");
    printf("Please note that you must use the english version of the site/URL\n\n");
    printf("Example:\n");
    printf("$argv[0] http://www.meteoalarm.eu/en_UK/0/0/SE038-Halland.html\n");
    exit(3);
}

// Get HTML-file from Meteoalarm
$url = $argv[1];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
$data = curl_exec($ch);
curl_close($ch);

// Get all the warnings for the selected region
preg_match_all('/<div class="info"><b>(.*)&nbsp;<\/b><span style="position\:absolute\; right\:0px\;"> Awareness Level: <b>(.*)&nbsp;/i', $data, $warnMatches);

// If no warnings were found, check for the text "No special awarenes required"
// to make sure we're actually scanning a Meteoalarm page. If we're not, print
// an error message about it and exit with code 3 (UNKNOWN).
if (empty($warnMatches[2]))
{
    if(preg_match("/No special awareness required/", $data))
    {
        printf("OK - No warnings for the region\n");
        exit(0);  // Code 0 = OK
    }
    else
    {
        printf("UNKNOWN - That doesn't look like a Meteolarm page, please check the URL.\n");
        printf("Make sure you are using the english version of the site.\n");
        exit(3); // Code 3 = Unknown
    }
}

// There is a warning of some kind...
else
{
    // Get the severity of the warning (orange & red = high danger,
    // yellow = low danger)
    if (preg_match("/Orange/", $data) || preg_match("/Red/", $data))
    {
        printf("CRITICAL - "); // Start the message with "CRITICAL"
        // Loop through all the warnings and print them
        foreach($warnMatches[1] as $warn)
            printf($warn . ". ");
        exit(2); // Code 2 = Critical
    }
    if (preg_match("/Yellow/", $data))
    {
        printf("WARNING - "); // Start the message with "WARNING"
        // Loop through all the warnings and print them
        foreach($warnMatches[1] as $warn)
            printf($warn . ". ");
        exit(1); // Code 1 = Warning
    }

}
    

?>

