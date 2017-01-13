# check_meteoalarm.php

This is a Nagios plugin written in PHP to look for weather alerts on the site
http://www.meteoalarm.eu. The plugin checks a specific region, which is entered
as the full URL of the region as an argument to the script.

The script is released under GNU GPL and hence I can take no responsibility for
the correctness of the script. It might not work as expected and should not be
be used as a critical tool for weather alerts.

## Requirements

PHP version 5 or higher with cURL. To install these packages on Debian/Ubuntu
enter `sudo apt-get install php5-cli php-curl`. That should be enough to get the
script running.

## Usage

	./check_meteoalarm.php [FULL URL]

For example

	./check_meteoalarm.php http://www.meteoalarm.eu/en_UK/0/0/SE002-Sk%E5ne.html
	
Note that the URL must be the english version (en_UK), otherwise the script
can't regex for the correct strings.

### Usage within Nagios (on Debian systems)

**Step 1:** Place the script in `/usr/lib/nagios/plugins/`

**Step 2:** Create the following snippet in `/etc/nagios3/commands.cfg`

    define command{
        command_name	check_meteoalarm
    	command_line	/usr/lib/nagios/plugins/check_meteoalarm.php $ARG1$
    }

**Step 3:** Create a service definition for a region, for example in your localhost in
   `/etc/nagios3/conf.d/localhost_nagios2.cfg`
   
    define service{
        use                             generic-service
        host_name                       localhost
        service_description             Meteoalarm Skane
        check_interval                  15
        retry_interval                  3
        check_command                   check_meteoalarm!'http://www.meteoalarm.eu/en_UK/0/0/SE002-Sk%E5ne.html'
        }
   
