## Installation:
  
- The bot requires php PEAR to be installed with the Net_SmartIRC package.  
  (You need to edit the path to the PEAR package in battlebot.php)
- Change the bots main channel, channelarray suffix and login details in battlebot.php  
  (<b>Important!</b> Change these or the bot will join existing channels!)  
- To add a game, check out the games folder and look at the structure of the default files.  

## Authenticate with Q:  
1. Let the bot join a channel in which you are OP (For example: #battlebot)  
<b>NOTICE:</b> <i>Enable </i>"!debug on"<i> to see R/Q responses to the bot.</i>     
<b>WARNING:</b> <i>May cause security breach if everyone in the channel can view the query responses!</i>  
2. !query R REQUESTOP <#channel>  
3. !query R REQUESTBOT <#channel>  
4. !query Q@CServe.quakenet.org CHANLEV <#channel> [nickname] +amno  
<b>NOTICE:</b> <i>Disable </i>"!debug off"<i> if you turned it on!</i>  
   
## Changelog:  
##### UPDATE v2.0  
<b>The Pick-Up Game Operator Bot can now handle cross channel communication!</b>  
  
You can now freely change your nickname and the bot will update your name in the pug queue list!  
Also, the bot will only automatically remove you from the line-up and, if needed, grant ownership to the next person in line if you leave ALL the channels on which the bot is hosted.  
  
• The bot is now authenticated with Q so it can retain its operator status.  
• It won't greet people anymore who join the channel, this could cause spam.  
• It also won't say goodbye when people leave/quit the channel.  
• It will only announce the queue if there is actually a pug in queue.  
• When people <b>!join</b> or <b>!leave</b> a pug or quit all channels while they were in queue, it will update and print the game's info.  
  
##### UPDATE v3.0  
<b>The Pick-Up Game Operator Bot can now handle votes!</b>  
  
• When you are in a queue, you can now use <b>!callvote</b> and <b>!vote</b> commands.  
• Debug function added to view query responses from the bot.
  
The bot is capable of tracking the usernames accross all the pug's and updates the names when changed. A kickvote will work even if the player leaves the pug, changes his name, and joins again.  
   
   
###### UPDATE v3.2  
<b>The Pick-Up Game Operator Bot can now autochoose teams!</b>
  
- You can now use <b>!join</b> [game] [mode] [skill] without specifying a team, the bot will automatically assign you.  
  (It takes into account if you have been kicked and the team balance; % of team filled + team invites send)    
- You can now use <b>!join</b> [game] [mode] [skill] [team#] using a letter (A,B,etc as substitute for 1,2,etc) for [team#]
