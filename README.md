# BattleBot (IRC Pick-Up Game Operator Bot)

I have created an IRC bot that will allow users to easily create and join pick-up games.  
(For example etqw/et/csgo and any other (type of) game).

To check it out, join the #battlebot irc channel on Quakenet! ( irc://irc.quakenet.org/battlebot )   
http://webchat.quakenet.org/   
http://www.team-aero.org/team-aero_esports/aero_tv.php  
  
mIRC: http://www.mirc.com/   
XChat: http://xchat.org/  
AdiIRC: https://adiirc.com/

## Installation:
  
- The bot requires php PEAR to be installed with the Net_SmartIRC package.  
  (You need to edit the path to the PEAR package in battlebot.php)
- Change the bots main channel, channelarray suffix and login details in battlebot.php  
  (<b>Important!</b> Change these or the bot will join existing channels!)  
- To add a game, check out the games folder and look at the structure of the default files.  

## Usage:
   
#### Basic commands
- <b>!help</b> - <i>This will return a list of available commands.</i>  
- <b>!create</b> - <i>This will return a list of available games.</i>   
- <b>!serverlist</b> - <i>This will return a list of available servers.</i>   
- <b>!queue</b> - <i>This will return a list of available pugs currently in queue to join.</i>  
- <b>!info</b> - <i>This will return a list of teams and their players, currently signed up for a pug. If no additional values are supplied, it will return the info of the pug you are currently in queue for. For example: !info etqw 4v4 </i>  
    
#### PUG commands  
- <b>!create</b> [game] [mode] [skill] [server]  
- <b>!join</b> [game] [mode] [skill] [team#]  
- <b>!need</b>  
- <b>!leave</b>  
- <b>!start</b>  
- <b>!callvote</b> [mode] [value]  
- <b>!vote</b> [yes/no]  
  
When a pick-up is filled or started, every player will be notified to join the server by having the ip and password sent to them in a pm. The pug will then be removed from queue and another one of the same game and mode may be started.  
(You can always start mutliple pugs at the same time if they are not identical)  
  
Greetings, Crytiqal.Aero  
  
##### UPDATE v2.0: The Pick-Up Game Operator Bot can now handle cross channel communication!  
  
You can now freely change your nickname and the bot will update your name in the pug queue list!  
Also, the bot will only automatically remove you from the line-up and, if needed, grant ownership to the next person in line if you leave ALL the channels on which the bot is hosted.  
  
• The bot is now authenticated with Q so it can retain its operator status.  
• It won't greet people anymore who join the channel, this could cause spam.  
• It also won't say goodbye when people leave/quit the channel.  
• It will only announce the queue if there is actually a pug in queue.  
• When people !join or !leave a pug or quit all channels while they were in queue, it will update and print the game's info.  
  
##### UPDATE v3.0: The Pick-Up Game Operator Bot can now handle votes!  
  
• When you are in a queue, you can now use <b>!callvote</b> and <b>!vote</b> commands.  
• Debug function added to view query responses from the bot.
  
The bot is capable of tracking the usernames accross all the pug's and updates the names when changed. A kickvote will work even if the player leaves the pug, changes his name, and joins again.  
   
   
###### UPDATE v3.2: The Pick-Up Game Operator Bot can now autochoose teams!  
  
• You can now use <b>!join</b> [game] [mode] [skill] without specifying a team and the bot will automatically add you to a team.  
  (It takes into account if you have been kicked and the team balance; % of team filled + team invites send)  
• You can now use <b>!join</b> [game] [mode] [skill] [team#] using a letter A/B/C as substitute for 1/2/3 for [team#]


