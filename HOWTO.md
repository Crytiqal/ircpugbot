# BattleBot (IRC Pick-Up Game Operator Bot)

## Authenticate with Q:  
1. Let the bot join a channel in which you are OP (For example: #battlebot)  
<i>Enable </i>"!debug on"<i> to see R/Q responses to the bot.</i>  
<b>NOTICE:</b> <i>Please read through the debug function!</i>  
<b>WARNING:</b> <i>May cause security breach if everyone in the channel can view the query responses!</i>  
2. !query R REQUESTOP <#channel>  
3. !query R REQUESTBOT <#channel>  
4. !query Q@CServe.quakenet.org CHANLEV <#channel> [nickname] +amno  
<b>NOTICE:</b> <i>Disable </i>"!debug off"<i> if you turned it on!</i>  
   
## Usage:   
#### Create a PUG  
- <b>!create</b> [game] [mode] [skill] [server]

<i>For example:</i> !create etqw 4v4 low <i>OR</i> !create etqw 4v4 med server1 <i>OR</i> !create etqw 4v4 pro 123.456.7.890:27733 mypw

[game] - <i>Pick a game that is supported.</i>  
[mode] - <i>Pick a gamemode that is supported for that game (4v4/6v6 etc).</i>  
<i>(If a game supports 2v2v2, or asymmetrical teams (2v6v4) then the bot is also capable of handling those)</i>  
[skill] - <i>Pick a skill level that is supported for that game (low/med/high/pro etc).</i>  
[server] - <i>Pick a server for the game.</i>   
<i>(If you leave this value blank, the bot will automatically select a default server from a predefined list, that is not currently in use by another pug in queue. You can also specify which default server you would like to use, by entering a value returned by typing !serverlist . In addition, you can also specify your own server ip and password in the following syntax: ip:port . If this ip is already in use by another pug, you are asked to provide a different server)</i>    
  
You will automatically be the owner of the pug, and be granted special commands such as !start and !remove. (Additional commands like !kick,!ban,!unban, !shuffle will likely be added in the future)  
    
    
#### Join a PUG  
- <b>!join</b> [game] [mode] [skill] [team#]

<i>For example:</i> !join etqw 4v4 med 2 <i>(joins team 2)</i>   
<i>Provide the game, mode and skill because several pick-up games with different modes and skill levels of the same game can be in queue at the same time! Provide the <#> of the team you would like to join (1/2/3/4 OR A/B/C/D etc). <s>If you fail to provide a team number, the bot will respond that you will have to pick a team.</s> If you fail to provide a teamID, the bot will automatically add you to the first available slot.</i>  
  
    
#### Leave a PUG  
- <b>!leave</b>  
<i>This will remove you from the pug you are currently enlisted in.   
If you are the owner of the pug, the ownership will be transferred to the next player in the line-up.   
If no such player is found, the pug will be removed from queue.</i>  
  
#### PUG Player commands  
- <b>!need</b>  
<i>This will show how many players are still needed for the pug you are currently enlisted in.</i>  
  
- <b>!callvote</b> [mode]  
<i>For example:</i> !callvote kick playerNAME/playerID  
<i>When no playerNAME/playerID is given, the bot will return a list of current players and their ID's in your team.  
The vote will be valid for a set period of time (2min) and players of the same team can cast their vote within this time.  
When the vote reaches >50% within the valid time, the vote is granted.</i>  
 
Only 1 vote possible per team at a time. The votecaller will be placed in a timeout to prevent him from spamming votes and give others a chance to call a vote of their own. The timeout is set to 3 min OR when a new vote has been called.
  
- <b>!vote</b> [yes/no]  
<i>For example:</i> !vote yes <i>OR</i> !vote no  
  
  
#### PUG Owner commands  
- <b>!remove</b>   
<i>This will remove the entire pug from the queue. (Only pick-up owners have the power to remove a pug from queue)</i>  
  
- <b>!start</b>  
<i>Only pick-up owners can use this command to force start a Pick-Up Game.</i>  
