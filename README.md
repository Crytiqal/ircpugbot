# README.md
<b>BattleBot (IRC Pick-Up Game Operator Bot)</b>

I have created an IRC bot that will allow users to easily create and join pick-up games. (For example etqw/et/csgo and any other (type of) game).

To check it out, join the #battlebot irc channel on Quakenet! ( irc://irc.quakenet.org/battlebot )   
http://webchat.quakenet.org/   
http://www.team-aero.org/team-aero_esports/aero_tv.php  
  
mIRC: http://www.mirc.com/   
XChat: http://xchat.org/  
AdiIRC: https://adiirc.com/
  
- The bot requires php PEAR to be installed with the Net_SmartIRC package.
(You need to edit the path to the PEAR package in battlebot.php)
- To add a game, check out the games folder and look at the structure of the default files.
   
    
<b>Basic commands:</b>
- !help - <i>This will return a list of available commands.</i>  
- !create - <i>This will return a list of available games.</i>   
- !serverlist - <i>This will return a list of available servers.</i>   
- !queue - <i>This will return a list of available pugs currently in queue to join.</i>  
- !info - <i>This will return a list of teams and their players, currently signed up for a pug. If no additional values are supplied, it will return the info of the pug you are currently in queue for. For example: !info etqw 4v4 </i>  
    
<b>PUG commands:</b>  
- !create [game] [mode] [skill] [server]  
- !join [game] [mode] [skill] [team#]  
- !need  
- !leave  
- !start  
- !callvote [mode] [value]  
- !vote [yes/no]  
  
When a pick-up is filled or started, every player will be notified to join the server by having the ip and password sent to them in a pm.   
The pug will then be removed from queue and another one of the same game and mode may be started. (You can always start mutliple pugs at the same time if they are not identical)  
  
<s>If you change your nickname or quit the channel while you are in a pug queue, the bot will automatically remove you from the line-up and, if needed, grant ownership to the next person in line!</s>  
  
Greetings, Crytiqal.Aero  
  
<b>UPDATE v2.0: The Pick-Up Game Operator Bot can now handle cross channel communication!</b>  
  
You can now freely change your nickname and the bot will update your name in the pug queue list!  
Also, the bot will only automatically remove you from the line-up and, if needed, grant ownership to the next person in line if you leave ALL the channels on which the bot is hosted.  
  
• The bot is now authenticated with Q so it can retain its operator status.  
• It won't greet people anymore who join the channel, this could cause spam.  
• It also won't say goodbye when people leave/quit the channel.  
• It will only announce the queue if there is actually a pug in queue.  
• When people !join or !leave a pug or quit all channels while they were in queue, it will update and print the game's info.  
  
<b>UPDATE v3.0: The Pick-Up Game Operator Bot can now handle votes!</b>  
  
• When you are in a queue, you can now use !callvote and !vote commands.
• Debug function added to view query responses from the bot.
  
The bot is capable of tracking the usernames accross all the pug's and updates the names when changed. A kickvote will work even if the player leaves the pug, changes his name, and joins again.  
   
   
