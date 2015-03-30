<?php

// -------------------------------------------------- +
// The IRC class
require_once('H:/PHP/PEAR/NET/SmartIRC.php');

// -------------------------------------------------- +
// Load modules
$install_path = "H:/ircservers/ircbots";
$battlebot = $install_path.'/battlebot-popen';

// -------------------------------------------------- +
// Catch variables
$voteID = '';
$voteCHANNEL = '';


// -------------------------------------------------- +
// The bot class
class battleBotTimer {

  // Constructor
  function battleBotTimer() {
  }

// -------------------------------------------------- +
// ---/*IRC Functionality*/-------------------------- +
// -------------------------------------------------- +
	
/*Timer function*/
  function whosyourdaddy(&$irc, &$data) {
    $irc->message(SMARTIRC_TYPE_CHANNEL, '#battlebot', 'Time start!');
  }
  
  function timer(&$irc, &$data) {
    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Time start!');
	sleep(60);
    $irc->part($data->channel, 'Time\'s up!');			// Leave close the channel
  }
// -------------------------------------------------- +

} // end class

// -------------------------------------------------- +
// -------------------------------------------------- +
// -------------------------------------------------- +
// Command line vars

// $args = $_SERVER['argv'];
// $host = ((isset($args[1]) && !empty($args[1])) ? $args[1] : 'irc.xs4all.nl');
// $channel = ((isset($args[2]) && !empty($args[2])) ? $args[2] : '#phpircbot-test');
// -------------------------------------------------- +
// $host = 'xs4all.nl.quakenet.org';
// $channel = '#battlebot';
// -------------------------------------------------- +
// boolean connect( string $addr, [integer $port = 6667])
$addr = 'irc.quakenet.org';		// Get host from parent
$port = 6667;

// void login( string $nick, string $realname, [integer $usermode = 0], [string $username = null], [string $password = null]) 																	
$nick = 'pugOpBotT';
$realname = 'pugOpBotT';
$usermode = 0;
$username = 'pugopbott';
$password = '';

// void join( mixed $channelarray, [string $key = null], [integer $priority = SMARTIRC_MEDIUM])  
$channelarray = array();
$channelarray[0] = '#battlebot';
$key = null;
$priority = SMARTIRC_MEDIUM;

// -------------------------------------------------- +

// Initialisation
$bot = &new battleBotTimer();
$irc = &new Net_SmartIRC();
$irc->setDebugLevel(SMARTIRC_DEBUG_ALL);
$irc->setLogfile($battlebot.'/log/bbtimer.log');	// Log file
$irc->setLogdestination(SMARTIRC_FILE);
$irc->setAutoReconnect(true);						// Auto reconnect?
$irc->setUseSockets(true);
$irc->setChannelSyncing(TRUE); 						// activating the channel synching is important, or we won't have $irc->channel[] available


// -------------------------------------------------- +
// Registers

// -------------------------------------------------- +
// Handlers
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!whosyourdaddy.*', $bot, 'whosyourdaddy'); 	// Everyone
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!timer.*', $bot, 'timer'); 	// Everyone
$irc->message(SMARTIRC_TYPE_NOTICE, 'Crytiqal`Aero', 'execInBackground');
  

// -------------------------------------------------- +
// Connection
$irc->connect($addr, $port);																	// Connect
$irc->login($nick, $realname, $usermode, $username, $password);									// Login
$irc->message(SMARTIRC_TYPE_QUERY, 'Q@CServe.quakenet.org', 'AUTH pugOpBotT KsbuKoJt4W');	// Authenticate
$irc->mode('Referee', '+iwx');																	// Mode
$irc->join($channelarray, $key, $priority); 													// Join
$irc->listen(); 																				// Listen
$irc->message(SMARTIRC_TYPE_CHANNEL, $channelarray[0], 'execInBackground running');
$irc->disconnect(); 																			// Disconnect
// -------------------------------------------------- +

?> 