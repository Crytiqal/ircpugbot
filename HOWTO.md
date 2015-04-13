# IRC Pick-Up Game Operator Bot

This HOW-TO explains how you can authenticate your bot to be channel owner and explains in-depth what all the commands do. 

## Usage:   
#### Create a PUG  
- <b>!create</b> [game] [mode] [skill] [server]

<i>For example:</i> <b>!create etqw 4v4 low</b> <i>OR</i> <b>!create etqw 4v4 med server1</b> <i>OR</i> <b>!create etqw 4v4 pro 123.456.7.890:27733 mypw</b>

[game] - <i>Pick a game that is supported.</i>  
[mode] - <i>Pick a gamemode that is <b>supported</b> for that game (<b>4v4</b> / <b>2v2v2</b> / <b>2v6v4</b> etc).</i>  
[skill] - <i>Pick a skill level that is supported for that game (<b>low</b> / <b>med</b> / <b>high</b> / <b>pro</b> etc).</i>  
[server] - <i>Pick a server for the game.</i>   
<i>(If you leave this value blank, the bot will automatically select a default server from a pre-defined list, that is not currently in use by another pug in queue. You can also specify <b>which</b> default server you would like to use, by entering a value returned by typing !serverlist . In addition, you can also provide your own server (and password) in the following syntax: ip:port (pw). If this ip:port is already in use by another pug, you are asked to provide a different server)</i>    
  
You will automatically be the owner of the pug, and be granted special commands such as !start and !remove. (Additional commands like !kick,!ban,!unban, !shuffle will likely be added in the future)  
    
    
#### Join a PUG  
- <b>!join</b> [game] [mode] [skill] [team#]

<i>For example:</i> <b>!join etqw 4v4 med 2</b> <i>(joins team 2)</i>   
<i>Provide the game, mode and skill because several pick-up games with different modes and skill levels of the same game can be in queue at the same time! Provide the <#> of the team you would like to join (1/2/3/4 OR A/B/C/D etc). If you fail to provide a teamID, the bot will automatically add you to the first available slot.</i>  
  
    
#### Leave a PUG  
- <b>!leave</b>  
<i>This will remove you from the pug you are currently enlisted in.   
If you are the owner of the pug, the ownership will be transferred to the next player in the line-up.   
If no such player is found, the pug will be removed from queue.</i>  
  
#### PUG Player commands  
- <b>!need</b>  
<i>This will show how many players are still needed for the pug you are currently enlisted in.</i>  
  
- <b>!callvote</b> [mode]  
<i>For example:</i> <b>!callvote kick</b> [playerNAME/playerID]  
<i>When no playerNAME/playerID is given, the bot will return a list of current players and their ID's in your team.  
The vote will be valid for a set period of time (2min) and players of the same team can cast their vote within this time.  
When the vote reaches >50% within the valid time, the vote is granted.</i>  
 
Only 1 vote possible per team at a time. The votecaller will be placed in a timeout to prevent him from spamming votes and give others a chance to call a vote of their own. The timeout is set to 3 min OR when a new vote has been called.
  
- <b>!vote</b> [yes/no]  
<i>For example:</i> <b>!vote yes</b> <i>OR</i> <b>!vote no</b>  
  
  
#### PUG Owner commands  
- <b>!remove</b>   
<i>This will remove the entire pug from the queue. (Only pick-up owners have the power to remove a pug from queue)</i>  
  
- <b>!start</b>  
<i>Only pick-up owners can use this command to force start a Pick-Up Game.</i>  
