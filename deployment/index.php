<?php
$host = gethostname();
$ip = gethostbyname($host);
echo "Hostname of this server is: $host";
echo "IP of this server is: $ip";
