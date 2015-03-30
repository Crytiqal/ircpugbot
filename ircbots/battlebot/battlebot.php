<?php

ini_set('error_reporting', E_ALL);

// -------------------------------------------------- +
// The IRC class
require_once('../dependencies/php/PEAR/NET/SmartIRC.php');

// -------------------------------------------------- +
// Load modules
$install_path = "H:/ircservers/ircbots";
$battlebot = $install_path.'/battlebot';
$pug_queue = array();

// -------------------------------------------------- +
// The bot class
class battleBot {

  // Constructor
  function battleBot() {
  }

// -------------------------------------------------- +
// ---/*IRC Functionality*/-------------------------- +
// -------------------------------------------------- +

/*Debug function*/
  function debug($arr) {
	  // Turn debugging in the console on or off
  	  $debug = 'on';
      if ($debug == "on") {
		  print_r($arr);
	  }
  }

/*Owner function*/
  function whosyourdaddy(&$irc, &$data) {
      $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'My daddy and creator of this bot is Crytiqal.Aero!');
  }
  
/*Whoami function*/
  function whoami(&$irc, &$data) {
      $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'nickname:' .$data->nick);
  }

/*Help function*/
  function help(&$irc, &$data) {
    
	  $pugstring = substr(strstr($data->message, " "), 1);
      $pugdata = explode(" ", $pugstring);
	
	  if(!isset($pugdata[0])) {
          $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Type any of the available commands for info:');
          $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, '!whosyourdaddy,!help <cmd>');
          $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, '!queue,!info');
          $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, '!create,!join,!need,!leave,!msg,!start');
	  } else {
	  	  if($pugdata[0] == "queue") 		{$helpmessage = "Use !queue to see a list of current pickup games.";}
	  	  else if($pugdata[0] == "info") 	{$helpmessage = "Use !info <game> <mode> <skill> to see detailed information about a pickup game.";}
	  	  else if($pugdata[0] == "create") 	{$helpmessage = "Use !create <game> <mode> <skill> to initiate a pickup game. (You can also use !<game> <mode> <skill> or !<mode> <skill> if you are in the correct game channel)";}
	  	  else if($pugdata[0] == "join") 	{$helpmessage = "Use !join <game> <mode> <skill> <#team> to join a current pickup game in queue. (You can also use !join <mode> <skill> <#team> if you are in the correct game channel)";}
		  else if($pugdata[0] == "need") 	{$helpmessage = "Use !need when joined, to broadcast how many players are still needed for the pickup game to start.";}
		  else if($pugdata[0] == "leave") 	{$helpmessage = "Use !leave to leave a pickup game queue.";}
		  else if($pugdata[0] == "msg") 	{$helpmessage = "Use !msg when joined, to broadcast a message to all pickup game participants.";}
		  else if($pugdata[0] == "start") 	{$helpmessage = "Use !start to force start a pickup game. (Pickup initiator only)";}
		  else {$helpmessage = "No command specified. Use !help <cmd> for detailed help information.";}
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, $helpmessage);
	  }  	  
  }

/*OP-list function*/
  function op_list(&$irc, &$data) { 

	  $oplist = ""; 
      // Here we're going to get the Channel Operators, the voices and users 
      // Method is available too, e.g. $irc->channel['#test']->users will 
      // Return the channel's users. 
      foreach ($irc->channel['#test']->ops as $key => $value) { 
	      $oplist .= ' '.$key; 
      } 
      // result is send to #team-aero.bb (we don't want to spam the other channels) 
      $irc->message(SMARTIRC_TYPE_CHANNEL, '#team-aero.bb', 'ops on this channel are:'); 
      $irc->message(SMARTIRC_TYPE_CHANNEL, '#team-aero.bb', $oplist); 
  }

/*Userlist function*/
  function user_list(&$irc) { 
      global $channelarray;
      $userlist = array(); 
	
      // Here we're going to get the Channel Operators, the voices and users 
      // Method is available too, e.g. $irc->channel['#test']->users will 
      // Return the channel's users. 
      foreach($channelarray as $value1) {
	      foreach ($irc->channel[$value1]->users as $key => $value) {
		      if(!in_array($key, $userlist)) {
	              $userlist[] = $key; 
        	  }
      	  }
      } 

      $user_list = implode(", ", $userlist);
      // result is send to #team-aero.bb (we don't want to spam the other channels)
	  $irc->message(SMARTIRC_TYPE_CHANNEL, '#team-aero.bb', 'users on this channel are:');  
      $irc->message(SMARTIRC_TYPE_CHANNEL, '#team-aero.bb', $user_list); 

      return $userlist;
  }

// -------------------------------------------------- +
// ---/*BOT Functionality*/-------------------------- +
// -------------------------------------------------- +
  function onjoin_greeting(&$irc, &$data) {
      global $pug_queue, $channelarray;
	
// echo "function onjoin_greeting";
// echo  "\n";

      if($data->nick == $irc->_nick) {
      	  return;
	  } 

	  // Display a list of current pick-up games in queue
	  if(in_array($data->channel,$channelarray)) {
          if($pug_queue) { 
	          $pug_queue_list = $this->pug_queue_list($pug_queue);
	          $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Current pick-up games: '.$pug_queue_list.''); 
	      }
      } else {
	  	  // Check if player joins the invite channel (can only join if invited (mode = +i)
	      $p_channel = explode(":",$data->channel);
	      $g_channel = explode("-",$p_channel[2]);
	      $g_team = substr($g_channel[3], 4); // Remove 'team' prefix

	      if(isset($pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['invited'])) {
		      if(in_array($data->nick,$pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['invited'])) {

		          // Check if team hasn't filled up in the meantime
		          // Extend to x teams and x size
		          $team_size = explode("v", $g_channel[1]);
		          $team_size = array_combine(range(1, count($team_size)), array_values($team_size));

		          if(count($pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['players']) < $team_size[$g_team]) {
			          // Add player to team!
			          $pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['players'][] = $data->nick;
			          // Check if all teams are full now!
			          $player_slots = 0;
			          $players = 0;

			          foreach($team_size as $key => $value)
			          {
			              $player_slots = $player_slots + $team_size[$key];             
			              $players = $players + count($pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]]['team'.$key]['players']);
			          }
 
			          if($players == $player_slots) {
						  // Check if $g_server is set else set timeout to find a server so other people can create a pug
			              if(isset($pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]]['server'])) {
			                  $this->pug_autostart($irc, $g_channel[0], $g_channel[1], $g_channel[2]);
			              } else {
			                  // Set timeout of 2 min.
							  $return = $this->joined_in_pug($data->nick);
				              $pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]]['timeout'] = 'timeout';
			                  foreach($pug_queue[$return['game']][$return['mode']][$return['skill']]['irc'] as $value)
			                  {
				                  $irc->message(SMARTIRC_TYPE_CHANNEL, $value, '!addserver auto; OR !addserver server#; OR !addserver <ip>:<port> <pw>');
				                  $irc->message(SMARTIRC_TYPE_CHANNEL, $value, 'You have 2 minutes to add a server.');
			                  }
			              }
			          } else {
			              $this->pug_update($irc, $g_channel[0], $g_channel[1], $g_channel[2]);      
			          }  
		          } else {
			          // Team has filled up in the meantime
			          $irc->kick($pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]]['irc'][$g_channel[3]],$data->nick);
			          $this->pug_autokick($irc, $data->nick);
		          }		  
		          $pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['invited'] = array_values(array_diff($pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['invited'],array($data->nick)));          
	          }
	      }
	  }
  } // end

// -------------------------------------------------- +
  function pug_queue_list($pug_queue) {

// echo "function pug_queue_list";
// echo  "\n";

	  unset($pug_queue_games);
	  unset($pug_queue_games_list);
	  $pug_queue_games_list = array();
	  
	  foreach(array_keys($pug_queue) as $game) {
	      foreach(array_keys($pug_queue[$game]) as $mode) {
	  	      foreach(array_keys($pug_queue[$game][$mode]) as $skill) {
	  	          $pug_queue_games_modes_skills[] = $skill;
	  	      }
	  	      $pug_queue_games_modes_skills_list = implode(",", $pug_queue_games_modes_skills);
	  	      $pug_queue_games_modes[] = ''.$mode.' '.$pug_queue_games_modes_skills_list.'';
	  	      unset($pug_qeue_games_modes_skills);
	  	      $pug_queue_games_modes_skills_list = '';
	      }
	      $pug_queue_games_modes_list = implode(",", $pug_queue_games_modes);
	      $pug_queue_games[] = ''.$game.' '.$pug_queue_games_modes_list.'';
	      unset($pug_queue_games_modes);
	      $pug_queue_games_modes_list = '';
 	  }
	          
	  if(!$pug_queue_games) {
  		  $pug_queue_games_list = 'none';
	  } else {
	      $pug_queue_games_list = implode("; ", $pug_queue_games);
 	  }
	  
	  return $pug_queue_games_list;
	  $pug_queue_games_list = '';
  }

// -------------------------------------------------- +
  function joined_in_pug($player) {
	  global $pug_queue;
	
// echo "function joined_in_pug";
// echo "\n";

	  unset($return);
	  $return = array();
	  $return[0] = 0;

	  foreach($pug_queue as $key => $pug_game) {
	      foreach($pug_game as $key2 => $pug_mode) {
		      foreach($pug_mode as $key3 => $pug_skill) {
		          foreach($pug_skill as $key4 => $pug_team) {
					  // $key4 doesn't always have to be an array  
			          if(is_array($pug_game[$key2][$key3][$key4])) {
						  if(array_key_exists("players", $pug_game[$key2][$key3][$key4])) {
							  // This is the correct array!;	
					          foreach($pug_team as $key5 => $pug_status) {
								  // Check if the $player can be found in any of the statuses (players/invited/kicked)	  			  
				                  if(in_array($player, $pug_game[$key2][$key3][$key4][$key5])) {
					                  // Found him!
									  if($pug_game[$key2][$key3]['owner'] == $player) { 
					                      $return[0] = 'owner'; 
					                  } else { 
						                  $return[0] = 'player'; 
					                  }
                   
					                  $key6 = array_search($player, $pug_game[$key2][$key3][$key4][$key5]);
                 
					                  $return['game'] = $key;  // Game
					                  $return['mode'] = $key2; // Mode
					                  $return['skill'] = $key3; // Skill
					                  $return['teamID'] = $key4; // Team#
									  $return['status'] = $key5; // Status (Player, Invited, Kicked)
					                  $return['playerID'] = $key6; // PlayerID
									  
								  }
							  } 
						  } 
					  }
				  }
			  }
		  }
	  }
      return $return;
  } // end joined_in_pug 

// -------------------------------------------------- +
  function pug_update($irc, $pug_game, $pug_mode, $pug_skill) {
	  global $pug_queue, $channelarray;

// echo "function pug_update";
// echo "\n";

// 0 = message all channels (WARNING: possible excess flood) 
// 1 = message <game> channel + masterchannel 
// 2 = message <game> channel
// Default: $bb_shortmsg = 1;
	  $bb_shortmsg = 1;  
	  $message = array();
	  $prefix_msg = 'PUG: '.$pug_game.' '.$pug_mode.' '.$pug_skill.' --(';

	  $team_size = explode("v", $pug_mode);
	  $team_size = array_combine(range(1, count($team_size)), array_values($team_size));

	  foreach($team_size as $key => $value)
	  {
		  $team[$key] = implode(",", $pug_queue[$pug_game][$pug_mode][$pug_skill]['team'.$key]['players']);
		  $message[] = 'Team '.$key.': '.$team[$key].''; 
	  }
	  
	  $info_msg = implode("; ", $message);
	  $suffix_msg = ')--';
	  
	  if($bb_shortmsg == 0) {
		  // Multiple channel reply!
		  foreach($channelarray as $value)
		  {
			  $irc->message(SMARTIRC_TYPE_CHANNEL, $value, ''.$prefix_msg.' '.$info_msg.' '.$suffix_msg.'');
		  }
	  } else if($bb_shortmsg == 1) {
		  $irc->message(SMARTIRC_TYPE_CHANNEL, $channelarray[0], ''.$prefix_msg.' '.$info_msg.' '.$suffix_msg.'');        
		  $irc->message(SMARTIRC_TYPE_CHANNEL, $channelarray[$pug_game], ''.$prefix_msg.' '.$info_msg.' '.$suffix_msg.'');        
	  } else {
		  $irc->message(SMARTIRC_TYPE_CHANNEL, $channelarray[$pug_game], ''.$prefix_msg.' '.$info_msg.' '.$suffix_msg.'');
	  }
  }

// -------------------------------------------------- +
  function pug_autokick($irc, $player) {
	  global $pug_queue, $channelarray;
	
// echo "function pug_autokick";
// echo "\n";
	  // Check if channel was in pug team channel
	  $return = $this->joined_in_pug($player);
	  
	  // Remove player from pug
	  unset($pug_queue[$return['game']][$return['mode']][$return['skill']][$return['teamID']]['players'][$return['playerID']]);
	  $pug_queue[$return['game']][$return['mode']][$return['skill']][$return['teamID']]['players'] = array_values($pug_queue[$return['game']][$return['mode']][$return['skill']][$return['teamID']]['players']);
	  
	  // Check if owner -> new owner or remove entire pug
	  if($return[0] == "owner") {
		  $team_size = explode("v", $return['mode']);
		  $team_size = array_combine(range(1, count($team_size)), array_values($team_size));
		  
		  // Check for new owner
		  foreach($team_size as $key => $value) {
			  if(array_key_exists("0", $pug_queue[$return['game']][$return['mode']][$return['skill']]['team'.$key]['players'])) {
				  $pug_queue[$return['game']][$return['mode']][$return['skill']]['owner'] = $pug_queue[$return['game']][$return['mode']][$return['skill']]['team'.$key]['players'][0];
				  break;
			  }
		  }
		  
		  if($pug_queue[$return['game']][$return['mode']][$return['skill']]['owner'] == $nick) {
			  // Remove PUG
			  foreach($pug_queue[$return['game']][$return['mode']][$return['skill']]['irc'] as $value)
			  {
				  $irc->message(SMARTIRC_TYPE_CHANNEL, $value, 'PUG Removed: '.$return['game'].' '.$return['mode'].' '.$return['skill'].'');
				  $irc->part($value,'PUG Removed: '.$return['game'].' '.$return['mode'].' '.$return['skill'].'');			// Leave close the channel
			  }
			  
			  unset($pug_queue[$return['game']][$return['mode']][$return['skill']]);   
			  if(empty($pug_queue[$return['game']][$return['mode']])) {
				  unset($pug_queue[$return['game']][$return['mode']]);
				  if(empty($pug_queue[$return['game']])) {
					  unset($pug_queue[$return['game']]);
				  }
			  }
			  $irc->message(SMARTIRC_TYPE_CHANNEL, $channelarray[0], 'PUG Removed: '.$return['game'].' '.$return['mode'].' '.$return['skill'].'');
			  $irc->message(SMARTIRC_TYPE_CHANNEL, $channelarray[$return['game']], 'PUG Removed: '.$return['game'].' '.$return['mode'].' '.$return['skill'].'');
		  } else {
			  $this->pug_update($irc, $return['game'], $return['mode'], $return['skill']);
		  }
	  } else {
		  $this->pug_update($irc, $return['game'], $return['mode'], $return['skill']);
	  }
  } 

// -------------------------------------------------- +
  function pug_autostart($irc, $pug_game, $pug_mode, $pug_skill) {
	  global $pug_queue;
	  	  
// echo "function pug_autostart";
// echo "\n";

	  $server_info[0] = $pug_queue[$pug_game][$pug_mode][$pug_skill]['server'][0];
	  $server_info[1] = $pug_queue[$pug_game][$pug_mode][$pug_skill]['server'][1];
	
	  // Do some rcon stuff here
	  // Get the correct rcon file for the game protocol to connect to the server
	
	  foreach($pug_queue[$pug_game][$pug_mode][$pug_skill] as $key => $value) {
		  if(is_array($pug_queue[$pug_game][$pug_mode][$pug_skill][$key])) {
			  foreach($pug_queue[$pug_game][$pug_mode][$pug_skill][$key] as $player) {
				  $irc->message(SMARTIRC_TYPE_QUERY, $player, 'PUG '.$pug_game.' '.$pug_mode.' '.$pug_skill.' has started. You ('.$player.') are part of '.$key.''); 
				  $irc->message(SMARTIRC_TYPE_QUERY, $player, 'connect '.$server_info[0].'; password '.$server_info[1].''); 
			  }
		  }
	  }
	  
	  unset($pug_queue[$pug_game][$pug_mode][$pug_skill]);
	  if(empty($pug_queue[$pug_game][$pug_mode])) {
		  unset($pug_queue[$pug_game][$pug_mode]);
		  if(empty($pug_queue[$pug_game])) {
			  unset($pug_queue[$pug_game]);
		  }
	  }
  }

// -------------------------------------------------- +
// http://php.net/manual/en/features.commandline.php
// This is just stupid!
/*
  function execInBackground($cmd) {  
    if (substr(php_uname(), 0, 7) == "Windows"){
	    pclose(popen("start /B ". $cmd, "r")); 
		// popen($cmd . " &", "r"); 
    } else { 
        shell_exec($cmd . " > /dev/null &");   
    } 
  } 
*/

// -------------------------------------------------- +
// ---/*BOT Commands*/------------------------------- +
// -------------------------------------------------- +
/*command: !queue*/
  function pug_queue($irc, $data) {
	  global $pug_queue;
	
// echo "function pug_queue";
// echo "\n";
// -->
  $this->debug($pug_queue);
// <--

	  if(!$pug_queue) {
		  $pug_queue_list = 'none'; 
	  } else {
		  $pug_queue_list = $this->pug_queue_list($pug_queue);
	  }
	  
	  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Current pick-up games: '.$pug_queue_list.''); 
	  
  } // end queue

// -------------------------------------------------- +
/*command: !info*/
  function pug_info($irc, $data) {
	  global $pug_queue, $extended, $channelarray, $ch_suffix;
	
// echo "function pug_info";
// echo "\n";
// -->
//  $this->debug($pug_queue);
// <--

//  Stop caching unless it's actually used, thank you!
//	  $pugstring = trim(array_pop(explode("!info", $data->message)));
	  $pugstring = substr(strstr($data->message, "!"), 1);
	  $pugdata = explode(" ", $pugstring);

	  if (!isset($pugdata[1])) {
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Specify which PUG you want info from');
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Use the following command: !info <game> <mode> <skill>');	
		  $this->pug_queue($irc, $data);
		  return; 
	  } else {
		  
		  // Check if $pugdata[1] is <game> or <mode>
		  if(preg_match("/^[0-9]+v[0-9]+(v[0-9]+)*$/", $pugdata[1])) {
			  // $pugdata[1] is <mode>, retrieve <game> from channel name
			  if(isset($pugdata[0])) { $p_game = strtolower(substr($data->channel, 1, -strlen($ch_suffix))); }
			  if(isset($pugdata[1])) { $p_mode = strtolower($pugdata[1]); }
			  if(isset($pugdata[2])) { $p_skill = strtolower($pugdata[2]); }
		  } else {
			  // $pugdata[1] is <game>, check if <game> is found in channel name
			  if($data->channel == $channelarray[0] || strpos($data->channel,$pugdata[1])) {
				  if(isset($pugdata[1])) { $p_game = strtolower($pugdata[1]); }
				  if(isset($pugdata[2])) { $p_mode = strtolower($pugdata[2]); }
				  if(isset($pugdata[3])) { $p_skill = strtolower($pugdata[3]); }
			  } else {
				  echo "This game is not supported in this channel";
			  }
		  }
		  
		  $return = $this->joined_in_pug($data->nick);
		  if($return[0] != "0") {
			  $this->pug_update($irc, $return['game'], $return['mode'], $return['skill']);
			  return;
		  } else {
			  if(!isset($pug_queue[$p_game][$p_mode][$p_skill])) {
				  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'PUG does not exist!');
				  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Use the following command: !info <game> <mode> <skill>');
				  return;
			  } else {
				  $this->pug_update($irc, $p_game, $p_mode, $p_skill);
				  return;
			  }
		  }
	  }
  }

// -------------------------------------------------- +
/*command: !serverlist*/
  function pug_serverlist($irc, $data) {
	  global $battlebot, $pug_queue, $bb_shortcmd, $ch_suffix;
	  
//  echo "function pug_serverlist";
//  echo "\n";
// -->
//  $this->debug($pug_queue);
// <--
	  
	  $return = $this->joined_in_pug($data->nick);
	  if($return[0] == "0") {
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'You are not in a PUG!');
		  return;
	  }

//  Stop caching unless it's actually used, thank you!
//  $pugstring = trim(array_pop(explode("!serverlist", $data->message)));
	  $pugstring = substr(strstr($data->message, " "), 1);
	  $pugdata = explode(" ", $pugstring);
	
	  $fh = opendir($battlebot.'/games/');
	  while($fn = readdir($fh)) 
	  {
		  if ($fn == "." || $fn == ".." || $fn == "default") { continue; }
		  $p_gamearray[] = $fn; // REMOVE .PUG AND PUT INTO ARRAY
	  }
	  closedir($fh);
	  sort($p_gamearray); // SORT THE GAMES INTO ALPHABETICAL ORDER
	  
	  $p_gamelist = implode(",", $p_gamearray);
	  
	  if($bb_shortcmd != "0") {
		  $p_game = strtolower(substr($data->channel, 1, -strlen($ch_suffix)));
	  } else {
		  $p_game = strtolower($pugdata[0]);
	  }
	  
	  if (!in_array($p_game,$p_gamearray)) {
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Use the following command: !serverlist <game>');
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Supported games are: '.$p_gamelist.'');
	  } else {
		  include($battlebot.'/games/'.$p_game.'/'.$p_game.'.pug');
		  
		  if (!$g_serverlist) {
			  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'This game has no default servers!');
		  } else {
			  // Get all the server variables and list the IPs!
			  foreach($g_serverlist as $key => $value) 
			  {
				  $message[] = ''.$value.': '.${$value}[0].'';
			  }
			  $svr_msg = implode("; ", $message);
			  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, ''.$svr_msg.'');
		  }
	  }
  } // end serverlist

// -------------------------------------------------- +
/*command: !create*/
  function pug_create($irc, $data) {
	  global $battlebot, $pug_queue, $channelarray, $ch_suffix;

// echo "function pug_create";
// echo "\n";
// -->
//  $this->debug($pug_queue);
// <--
	
	  $return = $this->joined_in_pug($data->nick);
	  if($return[0] != "0") {
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'You are already in a PUG! ('.$return['game'].' '.$return['mode'].' '.$return['skill'].')');
		  return;
	  }
	  
	  // Check if command is !<cmd>, !<game>, !<mode>
	  // Stop caching unless it's actually used, thank you!
	  // $pugstring = trim(array_pop(explode("!create", $data->message)));
	  $pugstring = substr(strstr($data->message, "!"), 1);
	  $pugdata = explode(" ", $pugstring);
	  $p_game = "";
	  $p_mode = "";
	  $p_skill = "";
	  $p_server = "";
	  
	  if($pugdata[0] == "create") {
		  // Reindex $pugdata
		  array_shift($pugdata);
	  }
	  
	  // Check for allowed <game>
	  $fh = opendir($battlebot.'/games/');
	  while($fn = readdir($fh))
	  {
		  if($fn == "." || $fn == ".." || $fn == "default") { continue; }
		  $p_gamearray[] = $fn; // REMOVE .PUG AND PUT INTO ARRAY
	  }
	  closedir($fh);
	  sort($p_gamearray); // SORT THE GAMES INTO ALPHABETICAL ORDER
	  $p_gamelist = implode(",", $p_gamearray);
	  
	  // Check if $pugdata[0] is <game> or <mode>
	  if(!isset($pugdata[0])) {
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Use the following command: !create <game> <mode> <skill> <server>');
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Supported games are: '.$p_gamelist.'');
		  return;
	  } elseif(preg_match("/^[0-9]+v[0-9]+(v[0-9]+)*$/", $pugdata[0])) {
		  // $pugdata[0] is <mode>, retrieve <game> from channel name
		  $p_game = strtolower(substr($data->channel, 1, -strlen($ch_suffix)));
		  if(isset($pugdata[0])) { $p_mode   = strtolower($pugdata[0]); }
		  if(isset($pugdata[1])) { $p_skill  = strtolower($pugdata[1]); }
		  if(isset($pugdata[2])) { $p_server = strtolower($pugdata[2]); }
	  } else {
		  // $pugdata[1] is <game>, check if <game> is found in channel name
		  if(isset($pugdata[0])) { $p_game   = strtolower($pugdata[0]); }
		  if(isset($pugdata[1])) { $p_mode   = strtolower($pugdata[1]); }
		  if(isset($pugdata[2])) { $p_skill  = strtolower($pugdata[2]); }
		  if(isset($pugdata[3])) { $p_server = strtolower($pugdata[3]); }
	  }
	  
	  // Check if there isn't already a pug in queue!            
	  if(isset($pug_queue[$p_game][$p_mode][$p_skill])) {
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'There is already a '.$p_game.' '.$p_mode.' '.$p_skill.' PUG in queue!');
		  return;
	  } else {
		  if(!in_array($p_game,$p_gamearray)) {
			  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Use the following command: !create <game> <mode> <skill> <server>');
			  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Supported games are: '.$p_gamelist.'');
		  } else {
			  // <game> approved 
			  if($data->channel != $channelarray[0] && !strpos($data->channel,$p_game)) {
				  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Please join '.$channelarray[$p_game].'');
				  return;
			  }
			  
			  // Retrieve allowed <mode>
			  include($battlebot.'/games/'.$p_game.'/'.$p_game.'.pug');
			  $g_modearray = ${'g_'.$p_game};
			  $g_modelist = implode(",", $g_modearray);
			  
			  if(!in_array($p_mode,$g_modearray)) {
				  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Use the following command: !create '.$p_game.' <mode> <skill> <server>');
				  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Supported modes are: '.$g_modelist.''); 
			  } else {
				  // <mode> approved
				  $g_skillarray = $g_skill;
				  $g_skilllist = implode(",", $g_skillarray);
				  
				  if(!in_array($p_skill,$g_skillarray)) {
					  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Use the following command: !create '.$p_game.' '.$p_mode.' <skill> <server>');
					  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Supported skills are: '.$g_skilllist.'');
				  } else {
					  // <skill> approved
					  // <game> <mode> and <skill> exist, now we can check for map and server!
					  $g_maparray = ${'g_'.$p_mode};
					  $g_maplist = implode(",", $g_maparray);
					  $g_server = array();
					  
				// -->
					  if($p_server == "auto") {
						  if(!empty($g_serverlist)) {
							  if(!empty($pug_queue[$p_game])) {
								  foreach($g_serverlist as $server) {
									  $g_server = ${$server};
									  foreach($pug_queue[$p_game] as $key2 => $pug_mode) {
										  foreach($pug_queue[$p_game][$key2] as $key3 => $pug_skill) {
											  if($pug_queue[$p_game][$key2][$key3]['server'][0] == ${$server}[0]) {
												  unset($g_server);
												  break 2;
											  } else { continue; }
										  }
									  }
									  if(isset($g_server)) { break; } else { continue; }
								  }
							  } else { $g_server = ${$g_serverlist[0]}; }
						  }
				// <--
				// -->						  
					  } else if(in_array($p_server,$g_serverlist)) {
						  $g_server = ${$p_server};
						  if(!empty($pug_queue[$p_game])) {
							  foreach($pug_queue[$p_game] as $key2 => $pug_mode) {
								  foreach($pug_queue[$p_game][$key2] as $key3 => $pug_skill) {
									  if($pug_queue[$p_game][$key2][$key3]['server'][0] == ${$p_server}[0]) {
										  unset($g_server);
										  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'That server is already in use.');
										  break 2;
									  } else { continue; }
								  }
							  }
						  }
				  // <--
				  // -->
					  } else if(strpos($p_server, ":") !== false) {
						  // check if it is a genuine server ip
						  $ip_port = explode(":", $p_server);
						  
						  if (filter_var($ip_port[0], FILTER_VALIDATE_IP) && isset($ip_port[1]) && isset($pugdata[4])) {
							  
							  $g_server[0] = $p_server;
							  $g_server[1] = $pugdata[4];
							  
							  if(!empty($pug_queue[$p_game])) {
								  foreach($pug_queue[$p_game] as $key2 => $pug_mode) {
									  foreach($pug_queue[$p_game][$key2] as $key3 => $pug_skill) {
										  if($pug_queue[$p_game][$key2][$key3]['server'][0] == $p_server) {
											  unset($g_server);
											  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'That server is already in use.');
											  break 2;
										  } else { continue; }
									  }
								  }
							  }
						  } else {
							  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Provide a server in the following syntax: <IP>:<PORT> <PW>');
						  }
					  }
					  
				// <--
				// --> Setting up the game
					  $pugID = substr(uniqid(),6);
					  ${$p_game}[$p_mode] = array();
					  ${$p_game}[$p_mode][$p_skill]['pugID'] = $pugID;              // Unique ID for pugID channel so we have OP for the bot
					  ${$p_game}[$p_mode][$p_skill]['owner'] = $data->nick;         // Pug iniator gets owner rights
					  
					  if($g_server) {
						  ${$p_game}[$p_mode][$p_skill]['server'] = $g_server;        // Server from list or supplied
					  } else {
						  ${$p_game}[$p_mode][$p_skill]['server'] = "";
						  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'No server set yet.');
					  }
					  
					  // --> Ban is per match
					  ${$p_game}[$p_mode][$p_skill]['banlist'] = array();
					  
					  // --> Setting up teams
					  $team_size = explode("v", $p_mode);
					  foreach($team_size as $key => $value)
					  {
						  $teamkey = $key + 1;
						  ${$p_game}[$p_mode][$p_skill]['irc']['team'.$teamkey] = '#pugbot:ID'.$pugID.':'.$p_game.'-'.$p_mode.'-'.$p_skill.'-team'.$teamkey.'';
						  $irc->join(${$p_game}[$p_mode][$p_skill]['irc']['team'.$teamkey]);
						  $irc->mode(${$p_game}[$p_mode][$p_skill]['irc']['team'.$teamkey], "+i");	// Set mode to only accept invited players to join channel
						  $irc->setTopic($value,'IDLE FOR MATCH: '.$p_game.' '.$p_mode.'');
						  
						  // ${$p_game}[$p_mode][$p_skill]['team'.$teamkey] = array();
						  ${$p_game}[$p_mode][$p_skill]['team'.$teamkey]['players'] = array();
						  ${$p_game}[$p_mode][$p_skill]['team'.$teamkey]['invited'] = array();
						  ${$p_game}[$p_mode][$p_skill]['team'.$teamkey]['kicked'] = array();
						  ${$p_game}[$p_mode][$p_skill]['team'.$teamkey]['votes'] = array();
					  }
					  
					  ${$p_game}[$p_mode][$p_skill]['team1']['invited'][] = $data->nick;
					  $pug_queue[$p_game][$p_mode][$p_skill] = ${$p_game}[$p_mode][$p_skill];
					  $pug_queue_list = $this->pug_queue_list($pug_queue);
					  
					  $irc->invite($data->nick,${$p_game}[$p_mode][$p_skill]['irc']['team1']);
					  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Please join '.${$p_game}[$p_mode][$p_skill]['irc']['team1'].'');
					  $this->pug_update($irc, $p_game, $p_mode, $p_skill);
				  }
			  }
		  }
	  }
  } // end create
  
// -------------------------------------------------- +
/*command: !addserver*/
  function pug_addserver($irc, $data) {
	  global $battlebot, $pug_queue;

// echo "function pug_create";
// echo "\n";	  

//  Stop caching unless it's actually used, thank you!
//  $pugstring = trim(array_pop(explode("!addserver", $data->message)));
	  $pugstring = substr(strstr($data->message, " "), 1);
	  $pugdata = explode(" ", $pugstring);
	  
	  $return = $this->joined_in_pug($data->nick);
	  
	  if($return[0] !== "owner") {
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Only pug owner can add server');
		  return;
	  } else if(!isset($pugdata[0])) {
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, '!addserver auto; OR !addserver server#; OR !addserver 012.345.6.789:12345 password');
		  return;
	  } else {
		  include($battlebot.'/games/'.$return['game'].'/'.$return['game'].'.pug');
		  
		  if($pugdata[0] == "auto") {
			  if(!empty($g_serverlist)) {
				  foreach($g_serverlist as $server) {
					  $g_server = ${$server};
					  foreach($pug_queue[$return['game']] as $key2 => $pug_mode) {
						  foreach($pug_queue[$return['game']][$key2] as $key3 => $pug_skill) {
							  if($pug_queue[$return['game']][$key2][$key3]['server'][0] == ${$server}[0]) {
								  unset($g_server);
								  break 2;
							  } else { continue; }
						  }
					  }
					  if(isset($g_server)) { break; } else { continue; }
				  }
			  }
		  } else if(in_array($pugdata[0],$g_serverlist)) {
			  $g_server = ${$pugdata[0]};
			  foreach($pug_queue[$return['game']] as $key2 => $pug_mode) {
				  foreach($pug_queue[$return['game']][$key2] as $key3 => $pug_skill) {
					  if($pug_queue[$return['game']][$key2][$key3]['server'][0] == ${$pugdata[0]}[0]) {
						  unset($g_server);
						  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'That server is already in use.');
						  break 2;
					  } else { continue; }
				  }
			  }
		  } else if(strpos($pugdata[0], ":") !== false) {
			  
			  // check if it is a genuine server ip
			  $ip_port = explode(":", $pugdata[0]);
			  
			  if (filter_var($ip_port[0], FILTER_VALIDATE_IP) && isset($ip_port[1]) && isset($pugdata[1])) {
				  $g_server = $pugdata;
				  foreach($pug_queue[$return['game']] as $key2 => $pug_mode) {
					  foreach($pug_queue[$return['game']][$key2] as $key3 => $pug_skill) {
						  if($pug_queue[$return['game']][$key2][$key3]['server'][0] == $pugdata[0]) {
							  unset($g_server);
							  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'That server is already in use.'); 
							  break 2;
						  } else { continue; }
					  }
				  }
			  }
		  }
	  }
	  
	  if(!$g_server) {
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, '!addserver auto; OR !addserver server#; OR !addserver 123.45.67.890:12345 password');    
		  return;
	  } else {
		  $pug_queue[$return['game']][$return['mode']][$return['skill']]['server'] = $g_server;
		  
		  // Check if all teams are full now!
		  $player_slots = 0;
		  $players = 0;
		  
		  // Extend to x teams and x size
		  $team_size = explode("v", $return['mode']);
		  $team_size = array_combine(range(1, count($team_size)), array_values($team_size));
		  
		  foreach($team_size as $key => $value) {
			  $player_slots = $player_slots + $team_size[$key];
			  $players = $players + count($pug_queue[$return['game']][$return['mode']][$return['skill']]['team'.$key]);
		  }
		  
		  if($players == $player_slots) {
			  $this->pug_autostart($irc, $return['game'], $return['mode'], $return['skill']);
		  } else { return; }
	  }
  } // end addserver

// -------------------------------------------------- +
/*command: !join*/
  function pug_join($irc, $data) {
	  global $pug_queue, $channelarray, $ch_suffix, $bb_shortcmd;
	
// echo "function pug_join";
// echo "\n";

//  Stop caching unless it's actually used, thank you!
//  $pugstring = trim(array_pop(explode("!join", $data->message)));
	  $pugstring = substr(strstr($data->message, " "), 1);
	  $pugdata = explode(" ", $pugstring);
	  
	  $pug_queue_list = $this->pug_queue_list($pug_queue);
	  
	  if(empty($pugdata[0])) {
		  // Join what? 
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Use the following command: !join <game> <mode> <skill> <#team>');
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Current pick-up games: '.$pug_queue_list.'');
		  return; 
	  } else {
// -->	  print_r($pugdata);		  
		  if(preg_match("/^[0-9]+v[0-9]+(v[0-9]+)*$/", $pugdata[0])) {
			  // $pugdata[1] is <mode>, retrieve <game> from channel name
			  $p_game = strtolower(substr($data->channel, 1, -strlen($ch_suffix)));
// -->		  echo $p_game;
			  if(isset($pugdata[0])) { $p_mode   = strtolower($pugdata[0]); }
			  if(isset($pugdata[1])) { $p_skill  = strtolower($pugdata[1]); }
			  if(isset($pugdata[2])) { $p_team   = strtolower($pugdata[2]); }
		  } else {
			  // $pugdata[1] is <game>, check if <game> is found in channel name or is main channel
			  if(strpos($data->channel,$pugdata[0]) || $data->channel == $channelarray[0] ) {
				  if(isset($pugdata[0])) { $p_game   = strtolower($pugdata[0]); }
				  if(isset($pugdata[1])) { $p_mode   = strtolower($pugdata[1]); }
				  if(isset($pugdata[2])) { $p_skill  = strtolower($pugdata[2]); }
				  if(isset($pugdata[3])) { $p_team   = strtolower($pugdata[3]); }
			  } else {
				  echo "This game is not supported in this channel";
				  return;
			  }
		  }
		  
		  $return = $this->joined_in_pug($data->nick);
		  
		  // Get $return['status'], we should check if possibily kicktime has passed
		  if($return[0] != "0") {
			  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'You are already in queue for a PUG! ('.$return['game'].' '.$return['mode'].' '.$return['skill'].')');
			  return;
		  }
		  
		  if(!isset($p_game) || !isset($p_mode) || !isset($p_skill) || !isset($p_team)) {
			  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Use the following command: !join <game> <mode> <skill> <#team>');
			  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Current pick-up games: '.$pug_queue_list.'');
			  return;
		  }
		  
		  // Extend to x teams and x size
		  $team_size = explode("v", $p_mode);
		  $team_size = array_combine(range(1, count($team_size)), array_values($team_size));
		  
		  if(!in_array($p_team,array_keys($team_size))) {
			  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Choose which team; 1,2,etc.');
		  } else {
			  // Check if player isn't kicked
			  
// --> 		  if(!in_array($data->nick, $pug_queue[$p_game][$p_mode][$p_skill]['team'.$p_team]['kicked'])){
// -->		  } else {
// Get the timestamp and calculate remaining time
// $key is playerID, $value is timestamp
// $kicktime = $timestamp;
// 				  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'You have been kicked! Please wait another '.$kicktime.' seconds!');
// -->		  }

			  // Feel free to join
			  if(count($pug_queue[$p_game][$p_mode][$p_skill]['team'.$p_team]['players']) < $team_size[$p_team]) {
				  // Put player in place holder until he actually joins the channel!
				  $pug_queue[$p_game][$p_mode][$p_skill]['team'.$p_team]['invited'][] = $data->nick;
				  $irc->invite($data->nick,$pug_queue[$p_game][$p_mode][$p_skill]['irc']['team'.$p_team]);
				  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Please join '.$pug_queue[$p_game][$p_mode][$p_skill]['irc']['team'.$p_team].'');
			  } else {
				  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Team is full!');
			  }
		  }
	  }
  } // end join	


		
// -------------------------------------------------- +
/*command: !need*/
  function pug_need($irc, $data) {
	  global $pug_queue, $channelarray, $ch_suffix;
	
// echo "function pug_need";
// echo "\n";

	  $return = $this->joined_in_pug($data->nick, $pug_queue);
	  if($return[0] == "0") {
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'You are not in a PUG!');
		  return;
	  }
	  
	  // Extend to x teams and x size
	  $team_size = explode("v", $return['mode']);
	  $team_size = array_combine(range(1, count($team_size)), array_values($team_size));
	  
	  // Check if all teams are full now!
	  $player_slots = 0;
	  $players = 0;
	  
	  foreach($team_size as $key => $value)
	  {
		  $player_slots = $player_slots + $team_size[$key];
		  $players = $players + count($pug_queue[$return['game']][$return['mode']][$return['skill']]['team'.$key]['players']);
	  }
	  $players_needed = $player_slots - $players;
	  $irc->message(SMARTIRC_TYPE_CHANNEL, $channelarray[$return['game']], 'There are still '.$players_needed.' players needed for '.$return['game'].' '.$return['mode'].' '.$return['skill'].'!');
  } // end need

// -------------------------------------------------- +
/*command: !leave*/
  function pug_leave($irc, $data) {
	  global $pug_queue;
	
// echo "function pug_leave";
// echo "\n";

	  $return = $this->joined_in_pug($data->nick);
	  if($return[0] == "0") {
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'You are not queueing for a PUG');
		  return;
	  } else {
		  $irc->kick($pug_queue[$return['game']][$return['mode']][$return['skill']]['irc'][$return['teamID']],$data->nick);
		  $this->pug_autokick($irc, $data->nick);
		  return;
	  }
  } // end leave

// -------------------------------------------------- +
/*command: !callvote*/
  function pug_callvote($irc, $data) {
	  global $pug_queue;
		  
// echo "function pug_callvote";
// echo "\n";

// Prevent new vote (in THIS PUG) from being called untill current vote is finished!!! (we have pugID, playerID, teamID, voteTIMESTAMP)
// Prevent new vote (in THIS PUG) from being called by the same person untill cooldown period has passed! (track his playerID)
// Determine votetype:
// - Kickvote
// - Mapvote
// - Startvote

//  Stop caching unless it's actually used, thank you!
//  $pugstring = trim(array_pop(explode("!vote", $data->message)));
	  $pugstring = substr(strstr($data->message, " "), 1);
	  $pugdata = explode(" ", $pugstring);
	  $g_channel[0] = "game";
  	  $g_channel[1] = "mode";
  	  $g_channel[2] = "skill";
  	  $g_channel[3] = "team#";
	   
	  // Check if player joins the invite channel (can only join if invited (mode = +i)
	  if(strpos($data->channel, ":") !== false) {
		  $p_channel = explode(":",$data->channel);
		  if(isset($p_channel[2])) {
		  	  if(strpos($p_channel[2], "-") !== false) {
				  $g_channel = explode("-",$p_channel[2]);
				  $g_team = substr($g_channel[3], 4); // Remove 'team' prefix
			  }
		  }
	  }
	  
	  if($data->channel != $pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]]['irc'][$g_channel[3]]) {
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'You need to be in a PUG channel to use this command');
		  return;	
	  } else {
		  // Callvote is allowed here
		  // Check if there is an ongoing time vote OR if current player is not in cooldown
		  if(!empty($pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'])){
			  // There is currently an ongoing vote in progress
			  
			  // Automatic vote validation (using pthreads or popen):
			  // If !empty() then asynchronous vote timer function has not finished, goto else
			  
			  // Manual vote validation:
			  $pug_vote_timestamp = key($pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes']);
			  $current_timestamp = time();
			  $pug_vote_timeout = 120; // Timeout in seconds
			  if($current_timestamp - $pug_vote_timestamp < $pug_vote_timeout) {
				  // Vote is valid - cannot start a new vote
				  // Notice works for both manual and automatic timer method
				  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'There is currently a vote in progress!');
				  return;
			  } else {
				  // Vote is invalid - can start a new vote
				  // Check if player is allowed to call a new vote
				  $pug_vote_cooldown = 180; // Timeout in seconds
				  if(($pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$pug_vote_timestamp]['callvote'] == $data->nick) && ($current_timestamp - $pug_vote_timestamp < $pug_vote_cooldown)) {
					  // Not allowed to callvote
					  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'You are in a cooldown period!');
					  return;
				  } else {
					  // Allowed to callvote
					  
					  // Spawn an asynchronous timer function if we use automatic validation!
					  // $cmd = "H:/php/php.exe H:/ircservers/ircbots/battlebot/timewrapper.php";
					  // echo $this->execInBackground($cmd);
					  
					  // Check voteType
					  $allowed_votes = array();
					  if(!in_array($pugdata[0],$allowed_votes)) {
						  $allowed_votes = implode(",", $allowed_votes);
						  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Use the following command: !callvote <type> <name>');
			  			  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Allowed vote types: '.$allowed_votes.'');
						  return;			  
					  } else if($pugdata[0] == "kick") {
						  // Not allowed to kick yourself...
						  $allowed_kicklist = $pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['players'];
						  if(($key = array_search($data->nick,$allowed_kicklist)) !== false) {
							  unset($allowed_kicklist[$key]);
						  }
						  		  
						  if(!isset($pugdata[1])) {
  							  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Use the following command: !callvote kick <id/name>');							  
						  } else {
							  // Kickvote (kick by playerID or playerNAME)
							  if(!array_key_exists($pugdata[1],$allowed_kicklist) && !in_array($pugdata[1],$allowed_kicklist)) {
								  // playerID nor playerNAME found
								  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'No matching player found!');
								  return;
							  } else {
								  // Kickvote $pugdata[1]
								  if(array_key_exists($pugdata[1],$allowed_kicklist)) {
									  // Get the playerNAME from the playerID
									  $pugdata[1] = $allowed_kicklist[$pugdata[1]];
								  }
								  
								  $pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][] = $current_timestamp;
								  $pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$current_timestamp]['callvote'] = $data->nick;
								  $pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$current_timestamp][$pugdata[0]] = $pugdata[1];
								  $pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$current_timestamp][0] = array();
								  $pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$current_timestamp][1] = array();
							  }
						  }
					  } else if($pugdata[0] == "map") {
						  
						  $allowed_maplist = "";

						  if(!isset($pugdata[1])) {
   							  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Use the following command: !callvote map <id/name>');
						  } else {
							  // Mapvote (map by mapID or mapNAME
							  if(!array_key_exists($pugdata[1],$allowed_maplist) && !in_array($pugdata[1],$allowed_maplist)) {
								  // mapID nor mapNAME found
								  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'No matching map found!');
								  return;
							  } else {
								  // Mapvote $pugdata[1]
								  if(array_key_exists($pugdata[1],$allowed_maplist)) {
									  // Get the mapNAME from the mapID
									  $pugdata[1] = $allowed_maplist[$pugdata[1]];
								  }
								  
								  $pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][] = $current_timestamp;
								  $pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$current_timestamp]['callvote'] = $data->nick;
								  $pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$current_timestamp][$pugdata[0]] = $pugdata[1];
								  $pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$current_timestamp][0] = array($pugdata[1]);
								  $pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$current_timestamp][1] = array($data->nick);
							  }
						  }
					  }
					  $pug_vote_message = ''.$pugdata[0].' '.$pugdata[1].'';
					  $pug_vote_message = trim($message);
					  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Current vote: '.$pug_vote_message.'');
					  return;
				  }
			  }
		  } else {
			  // There is currently no ongoing vote in progress
			  // Allowed to callvote
			  
			  // Spawn an asynchronous timer function	if we use automatic validation!
			  // $cmd = "H:/php/php.exe H:/ircservers/ircbots/battlebot/timewrapper.php";
			  // echo $this->execInBackground($cmd);
			  
			  $current_timestamp = time();
			  $pug_queue[$return['game']][$return['mode']][$return['skill']][$return['teamID']]['votes'][] = $current_timestamp;
			  $pug_queue[$return['game']][$return['mode']][$return['skill']][$return['teamID']]['votes'][$current_timestamp]['callvote'] = $data->nick; 
			  $pug_queue[$return['game']][$return['mode']][$return['skill']][$return['teamID']]['votes'][$current_timestamp][$pugdata[0]] = $pugdata[1];
			  $pug_queue[$return['game']][$return['mode']][$return['skill']][$return['teamID']]['votes'][$current_timestamp][0] = array($pugdata[1]);
			  $pug_queue[$return['game']][$return['mode']][$return['skill']][$return['teamID']]['votes'][$current_timestamp][1] = array($data->nick);	
			  
			  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Current vote:');
			  return;
		  }
	  }
  } // end callvote

// -------------------------------------------------- +
/*command: !vote*/
  function pug_castvote($irc, $data) {
	  global $pug_queue;
	
// echo "function pug_castvote";
// echo "\n";

//  Stop caching unless it's actually used, thank you!
//  $pugstring = trim(array_pop(explode("!vote", $data->message)));
	  $pugstring = substr(strstr($data->message, "!"), 1);
	  $pugdata = explode(" ", $pugstring);
	  
	  if($pugdata[0] == "vote") {
		  // Reindex $pugdata
	  }
	  $g_channel[0] = "game";
  	  $g_channel[1] = "mode";
  	  $g_channel[2] = "skill";
  	  $g_channel[3] = "team#";
	   
	  // Check if player joins the invite channel (can only join if invited (mode = +i)
	  if(strpos($data->channel, ":") !== false) {
		  $p_channel = explode(":",$data->channel);
		  if(isset($p_channel[2])) {
		  	  if(strpos($p_channel[2], "-") !== false) {
				  $g_channel = explode("-",$p_channel[2]);
				  $g_team = substr($g_channel[3], 4); // Remove 'team' prefix
			  }
		  }
	  }
	  
	  if($data->channel != $pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]]['irc'][$g_channel[3]]) {
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'You need to be in a PUG channel to use this command');
		  return;
	  } else {
		  // Voting is allowed here
		  // Check if there is an ongoing vote
		  if(empty($pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'])){
			  // There is currently no ongoing vote in progress
			  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'There is currently no vote in progress!');
			  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Use the following command: !callvote <cmd>');
			  return;
		  } else {
			  // There is currently an ongoing vote in progress
			  // Check if player is allowed to vote
			  $pug_vote_timestamp = key($pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes']);
			  if(in_array($data->nick,$pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$pug_vote_timestamp][0]) || in_array($data->nick,$pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$pug_vote_timestamp][1])) {
				  // You already voted!
				  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'You already voted!');
				  return;
			  } else {
				  // You can vote!
				  
				  if($pugdata[0] == "0" || $pugdata[0] == "no" || $pugdata[0] == "n") {
					  // Vote is no
					  $pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$pug_vote_timestamp][0] = $data->nick;
					  // Manual check if vote is timed out
					  if($current_timestamp - $pug_vote_timestamp < $pug_vote_timeout) {
						  // Vote is still in progress
						  return;
					  } else {
						  // Vote is timed out
						  
						  // Check if enough players have voted
						  $team_size = explode("v", $g_channel[1]);
						  $team_size = array_combine(range(1, count($team_size)), array_values($team_size));
						  $pug_vote_n = count($pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$pug_vote_timestamp][0]);
						  $pug_vote_y = count($pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$pug_vote_timestamp][1]);
						  $g_team = substr($g_channel[3], 4); // Remove 'team' prefix
						  unset($pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$pug_vote_timestamp]);
						  
						  if(($pug_vote_n + $pug_vote_y) < ($team_size[$g_team] / 2)) {
							  // Not enough players voted
							  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Not enough players voted...');
							  return;
						  } else {
							  // Vote is validated
							  if(count($pug_vote_n >= $pug_vote_y)) {
								  // Failed
								  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Vote failed...');
								  return;
							  } else {
								  // Passed
								  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Vote passed...');
								  if(array_key_exists("kick",$pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$pug_vote_timestamp])) {
									  // Kickvote
									  $channel = $pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]]['irc'][$g_channel[3]];
									  $nicknamearray = $pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$pug_vote_timestamp]['kick'];
									  $reason = "You have been voted off";
									  $irc->kick($channel,$nicknamearray,$reason);
									  $this->pug_autokick($irc, $nicknamearray);
  									  return;
								  } else if(array_key_exists("map",$pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$pug_vote_timestamp])) {
									  // Mapvote
								  } else {
									  // Wtf just happened?
									  echo "What kind of vote was this??";
								  }
								  return;
							  }
						  }
					  }
				  } else if($pugdata[0] == "1" || $pugdata[0] == "yes" || $pugdata[0] == "y") {
					  // Vote is yes
					  $pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$pug_vote_timestamp][1] = $data->nick;
					  // Manual check if vote is timed out
					  if($current_timestamp - $pug_vote_timestamp < $pug_vote_timeout) {
						  // Vote is still in progress
						  return;
					  } else {
						  // Vote is timed out
						  
						  // Check if enough players have voted
						  $team_size = explode("v", $g_channel[1]);
						  $team_size = array_combine(range(1, count($team_size)), array_values($team_size)); 
						  $pug_vote_n = count($pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$pug_vote_timestamp][0]);
						  $pug_vote_y = count($pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$pug_vote_timestamp][1]);
						  $g_team = substr($g_channel[3], 4); // Remove 'team' prefix
						  unset($pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$pug_vote_timestamp]);
						  
						  if(($pug_vote_n + $pug_vote_y) < ($team_size[$p_team] / 2)) {
							  // Not enough players voted
							  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Not enough players voted...');
							  return;
						  } else {
							  // Vote is validated
							  if(count($pug_vote_n >= $pug_vote_y)) {
								  // Failed
								  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Vote failed...');	
								  return;
							  } else {
								  // Passed
								  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'Vote passed...');
								  if(array_key_exists("kick",$pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$pug_vote_timestamp])) {
									  // Kickvote
									  $channel = $pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]]['irc'][$g_channel[3]];
									  $nicknamearray = $pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$pug_vote_timestamp]['kick'];
									  $reason = "You have been voted off";
									  $irc->kick($channel,$nicknamearray,$reason);
									  $this->pug_autokick($irc, $nicknamearray);
									  return;
								  } else if(array_key_exists("map",$pug_queue[$g_channel[0]][$g_channel[1]][$g_channel[2]][$g_channel[3]]['votes'][$pug_vote_timestamp])) {
									  // Mapvote
								  } else {
									  // Wtf just happened?
									  echo "What kind of vote was this??";
								  }
								  return;
							  }
						  }
					  }
				  } else {
					  // Vote is unknown
					  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Use the following command: !vote <y/n>');
					  return;
				  }
			  }
		  }
	  }
  } // end castvote
  
// -------------------------------------------------- +
/*command: !msg*/
  function pug_msg($irc, $data) {
	  global $battlebot, $pug_queue;
	
// echo "function pug_msg";
// echo "\n";

//  Stop caching unless it's actually used, thank you!
//  $pugstring = trim(array_pop(explode("!msg", $data->message)));
	  $pugstring = substr(strstr($data->message, " "), 1);
	  $pugdata = $pugstring;
	  
	  // Check if channel was in pug team channel
	  $return = $this->joined_in_pug($data->nick);
	  
	  if($return[0] == "0") {
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'You need to be in a PUG to use this command');
		  return;
	  } else if($data->channel == $pug_queue[$return['game']][$return['mode']][$return['skill']]['irc'][$return['teamID']]) {
		  foreach($pug_queue[$return['game']][$return['mode']][$return['skill']]['irc'] as $value)
		  {
			  if($value == $data->channel) { continue; } else { $irc->message(SMARTIRC_TYPE_CHANNEL, $value, $pugdata); }
		  }
	  } else { return; }
  } // end msg

// -------------------------------------------------- +
/*command: !start*/
  function pug_start($irc, $data) {
	  global $pug_queue;
	
// echo "function pug_start";
// echo "\n";

	  $return = $this->joined_in_pug($data->nick);
	  if($return[0] == "0") {
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'You are not in a PUG!');
	  } else if ($return[0] == "owner") {
		  $this->pug_autostart($irc, $return['game'], $return['mode'], $return['skill']);
		  $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'IP and password send to all participants!  GL&HF!');
	  } else {
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'You do not have permission to force start this PUG!'); 
	  }
  } // end start

// -------------------------------------------------- +
/*command: !kick*/
  function pug_kick($irc, $data) {
	  global $pug_queue;	
		  
// echo "function pug_kick";
// echo "\n";
	  
	  $return = $this->joined_in_pug($data->nick);
	  if($return[0] == "0") {
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'You are not in a PUG!');
	  } else if ($return[0] == "owner") {
		  
		  // Stop caching unless it's actually used, thank you!
		  // $pugstring = trim(array_pop(explode("!kick", $data->message)));
		  $pugstring = substr(strstr($data->message, " "), 1);
		  $pugdata = explode(" ", $pugstring);
		  
		  if (!isset($pugdata[0])) {
			  // Kick who?
			  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Use the following command: !kick <#player>');
			  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'Current players in pick-up game: '.$pug_player_list.'');  // Return playerlist ID
			  return;
		  }	else {
			  
			  // Remove from pug
			  // unset($pug_queue[$return['game']][$return['mode']][$return['skill']][$return['teamID']][$return['playerID']]);
			  // $pug_queue[$return['game']][$return['mode']][$return['skill']][$return['teamID']] = array_values($pug_queue[$return['game']][$return['mode']][$return['skill']][$return['teamID']]);
			  
			  // Kick #player for x minutes and update player array
			  // (Need to create a kick array with timestamps and unique ID, keep this updated through disconnects/name changes and check on join)
			  // $irc->kick($pug_queue[$return['game']][$return['mode']][$return['skill']]['irc'][$return['teamID']],$data->nick);
			  
		  }
	  } else {
		  $irc->message(SMARTIRC_TYPE_NOTICE, $data->nick, 'You do not have permission to kick someone!'); 
	  }
  } // end kick

// -------------------------------------------------- +
// ---/*IRC Events*/--------------------------------- +
// -------------------------------------------------- +	
  function evt_nickchange($irc, $data) {
	  global $pug_queue;

// -->
//  $this->debug($pug_queue);
// <--

	  $return = $this->joined_in_pug($data->nick);
	  if($return[0] == "0") {
		  return;															// user not in pug -> return
	  } else if ($return[0] == "owner") {
		  $pug_queue[$return['game']][$return['mode']][$return['skill']]['owner'] = $data->message; 						// update owner
		  $pug_queue[$return['game']][$return['mode']][$return['skill']][$return['teamID']][$return['playerID']] = $data->message; 	// update player
	  } else {
		  $pug_queue[$return['game']][$return['mode']][$return['skill']][$return['teamID']][$return['playerID']] = $data->message; 	// update player
	  }
  } 

// -------------------------------------------------- +
  function evt_quit($irc, $data) {
	  global $pug_queue, $channelarray;
	  
	  if($data->nick == $irc->_nick) {
		  return;
	  }
	  
	  // Check if channel was in pug team channel
	  $return = $this->joined_in_pug($data->nick);
	  
	  if($return[0] == "0") {
		  return;
	  } else if($data->channel == $pug_queue[$return['game']][$return['mode']][$return['skill']]['irc'][$return['teamID']]) {
		  
		  // Remove from pug
		  unset($pug_queue[$return['game']][$return['mode']][$return['skill']][$return['teamID']]['players'][$return['playerID']]);
		  $pug_queue[$return['game']][$return['mode']][$return['skill']][$return['teamID']]['players'] = array_values($pug_queue[$return['game']][$return['mode']][$return['skill']][$return['teamID']]['players']);
		  
		  // Check if owner -> new owner or remove entire pug
		  if($return[0] == "owner") {
			  
			  $team_size = explode("v", $return['mode']);
			  $team_size = array_combine(range(1, count($team_size)), array_values($team_size));
			  
			  // Check for new owner
			  foreach($team_size as $key => $value) {
				  if(array_key_exists("0", $pug_queue[$return['game']][$return['mode']][$return['skill']]['team'.$key]['players'])) {
					  $pug_queue[$return['game']][$return['mode']][$return['skill']]['owner'] = $pug_queue[$return['game']][$return['mode']][$return['skill']]['team'.$key]['players'][0];
					  break;
				  }
			  }
			  
			  if($pug_queue[$return['game']][$return['mode']][$return['skill']]['owner'] == $data->nick) {
				  // Remove PUG
				  foreach($pug_queue[$return['game']][$return['mode']][$return['skill']]['irc'] as $value)
				  {
					  $irc->message(SMARTIRC_TYPE_CHANNEL, $value, 'PUG Removed: '.$return['game'].' '.$return['mode'].' '.$return['skill'].'');
					  $irc->part($value,'PUG Removed: '.$return['game'].' '.$return['mode'].' '.$return['skill'].'');			// Leave close the channel
				  }
				  unset($pug_queue[$return['game']][$return['mode']][$return['skill']]);
				  if(empty($pug_queue[$return['game']][$return['mode']])) {
					  unset($pug_queue[$return['game']][$return['mode']]);
					  if(empty($pug_queue[$return['game']])) {
						  unset($pug_queue[$return['game']]);
					  }
				  }
				  $irc->message(SMARTIRC_TYPE_CHANNEL, $channelarray[0], 'PUG Removed: '.$return['game'].' '.$return['mode'].' '.$return['skill'].'');
				  $irc->message(SMARTIRC_TYPE_CHANNEL, $channelarray[$return['game']], 'PUG Removed: '.$return['game'].' '.$return['mode'].' '.$return['skill'].'');
			  } else {
				  $this->pug_update($irc, $return['game'], $return['mode'], $return['skill']);
			  }
		  } else {
			  $this->pug_update($irc, $return['game'], $return['mode'], $return['skill']);
		  }
	  }
  }

// -------------------------------------------------- +

} // end class


// -------------------------------------------------- +
// -------------------------------------------------- +
// -------------------------------------------------- +
// Command line vars

// $args = $_SERVER['argv'];
// $host = ((isset($args[1]) && !empty($args[1])) ? $args[1] : 'irc.xs4all.nl');
// $channelarray = ((isset($args[2]) && !empty($args[2])) ? $args[2] : '#phpircbot-test');
// -------------------------------------------------- +
// $host = 'xs4all.nl.quakenet.org';
// $channelarray = '#battlebot';
// -------------------------------------------------- +
// boolean connect( string $addr, [integer $port = 6667]) 
$addr = 'irc.quakenet.org';
$port = 6667; 

// void login( string $nick, string $realname, [integer $usermode = 0], [string $username = null], [string $password = null])
$nick = 'pugOpBot';
$realname = 'pugOpBot';
$usermode = 0;
$username = 'pugopbot';
$password = '';

// void join( mixed $channelarray, [string $key = null], [integer $priority = SMARTIRC_MEDIUM])  
$channelarray = array();
$channelarray[0] = '#battlebot';
$ch_suffix = '.bb'; 

// -->
  $fh = opendir($battlebot.'/games/');
  while($fn = readdir($fh)) 
  {
	  if($fn == "." || $fn == ".." || $fn == "default") { continue; } 
	  $channelarray[$fn] = '#'.$fn.''.$ch_suffix.''; 	// REMOVE .PUG AND PUT INTO ARRAY
  }
  closedir($fh);
// -->
$key = null;
$priority = SMARTIRC_MEDIUM;

/*
$host = 'irc.quakenet.org';
$channelarray = array();
$channelarray[0] = '#battlebot';
$ch_suffix = '.bb'; 

$fh = opendir($battlebot.'/games/');
while($fn = readdir($fh)) 
{
  if($fn == "." || $fn == ".." || $fn == "default") { continue; } 
  $channelarray[$fn] = '#'.$fn.''.$ch_suffix.''; 	// REMOVE .PUG AND PUT INTO ARRAY
}
closedir($fh);
*/

// -------------------------------------------------- +
// Initialisation
$bot = &new battleBot();
$irc = &new Net_SmartIRC();
$irc->setDebugLevel(SMARTIRC_DEBUG_ALL);
$irc->setLogfile($battlebot.'/log/bb.log');		// Log file
$irc->setLogdestination(SMARTIRC_FILE);
$irc->setAutoReconnect(true);					// Auto reconnect?
$irc->setUseSockets(true);
$irc->setChannelSyncing(TRUE); 					// activating the channel synching is important, or we won't have $irc->channel[] available

// -------------------------------------------------- +
// Registers
$irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '.*', $bot, 'onjoin_greeting');
$irc->registerActionhandler(SMARTIRC_TYPE_NAME, '.*', $bot, 'evt_namelist'); 
$irc->registerActionhandler(SMARTIRC_TYPE_MODECHANGE, '.*', $bot, 'evt_modechange'); 
$irc->registerActionhandler(SMARTIRC_TYPE_NICKCHANGE, '.*', $bot, 'evt_nickchange'); 
$irc->registerActionhandler(SMARTIRC_TYPE_PART | SMARTIRC_TYPE_QUIT, '.*', $bot, 'evt_quit');
$irc->registerActionhandler(SMARTIRC_TYPE_ALL, '!debug.*', $bot, 'debug'); 						// Everyone

// -------------------------------------------------- +
// Handlers
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!whosyourdaddy.*', $bot, 'whosyourdaddy'); 	// Everyone
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!whoami.*', $bot, 'whoami'); 				// Everyone
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!help.*', $bot, 'help');                   	// Everyone
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!userlist.*', $bot, 'user_list'); 			// Everyone
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!oplist.*', $bot, 'op_list'); 				// Everyone
// -->
  $b_games = array();
  $b_skills = array();
  $fh = opendir($battlebot.'/games/');
  while($fn = readdir($fh)) 
  {
	  if($fn == "." || $fn == ".." || $fn == "default") { continue; }
	  include($battlebot.'/games/'.$fn.'/'.$fn.'.pug');
	  $b_games[] = $fn;
	  $b_skills = array_merge($b_skills,${'g_'.$fn});
  }
  closedir($fh);
  $b_skills = array_unique($b_skills);
  sort($b_games);
  sort($b_skills);
  foreach($b_games as $value) { $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!'.$value.'.*', $bot, 'pug_create'); } 		// Everyone -Players
  foreach($b_skills as $value) { $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!'.$value.'.*', $bot, 'pug_create');	} 	// Everyone -Players
  unset($b_games);
  unset($b_skills);
// <--
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!create.*', $bot, 'pug_create'); 			// Everyone -Players
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!join.*', $bot, 'pug_join');     			// Everyone -Players (Check for full teams)
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!need.*', $bot, 'pug_need');     			// Players
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!leave.*', $bot, 'pug_leave');   			// Players
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!msg.*', $bot, 'pug_msg');					// Players
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!start.*', $bot, 'pug_start');				// Owner +Op only
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!addserver.*', $bot, 'pug_addserver');		// Owner +Op only
// $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!remove.*', $bot, 'pug_remove');			// Owner +Op only
// $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!shuffle.*', $bot, 'pug_shuffle');		// Owner +Op only
// $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!kick.*', $bot, 'pug_kick');				// Owner +Op only
// $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!ban.*', $bot, 'pug_ban');				// Owner +Op only
// $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!unban.*', $bot, 'pug_unban');			// Owner +Op only
// $irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!swap.*', $bot, 'pug_swap');				// Players (Check for full teams)
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!callvote.*', $bot, 'pug_callvote');		// Players (Check callvote command)
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!vote.*', $bot, 'pug_castvote');			// Players (Check castvote command)
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!queue.*', $bot, 'pug_queue');     			// Everyone
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!info.*', $bot, 'pug_info');    			// Everyone
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!serverlist.*', $bot, 'pug_serverlist');	// Everyone

// -------------------------------------------------- +
// Connection
$irc->connect($addr, $port); 																// Connect
$irc->login($nick, $realname, $usermode, $username, $password);								// Login
$irc->message(SMARTIRC_TYPE_QUERY, 'Q@CServe.quakenet.org', 'AUTH pugOpBot KsbuKoJt4W');	// Authenticate
$irc->mode('BattleBot', '+iwx');															// Mode
$irc->join($channelarray); 																	// Join
$irc->listen(); 																			// Listen
$irc->disconnect(); 																		// Disconnect

/*
$irc->connect($host, 6667); 																// Connect
$irc->login('pugOpBot', 'pugOpBot', 0, 'pugopbot');											// Login
$irc->message(SMARTIRC_TYPE_QUERY, 'Q@CServe.quakenet.org', 'AUTH pugOpBot KsbuKoJt4W');	// Authenticate
$irc->mode('BattleBot', '+iwx');															// Mode
$irc->join($channelarray); 																	// Join
$irc->listen(); 																			// Listen
$irc->disconnect(); 																		// Disconnect
*/
// -------------------------------------------------- +

?> 