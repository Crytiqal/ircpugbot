<?php

  $cmd = "H:/php/php.exe H:/ircservers/ircbots/battlebot-popen/timer.php";
  $timerID = '';
  $time = '';
  $timer = popen("start /B ". $cmd, "r"); 
  sleep(30);
  pclose($timer);

?>