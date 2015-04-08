# ircservers
<b>BattleBot (IRC Pick-Up Game Operator Bot)</b>

I have created an IRC bot that will allow users to easily create and join pick-up games for etqw (and any other game).

To check it out, join the #battlebot irc channel on Quakenet or from the list of current channels given below!   
http://webchat.quakenet.org/   
http://www.splashdamage.com/chat   
http://www.team-aero.org/team-aero_esports/aero_tv.php  
  
mIRC: http://www.mirc.com/   
XChat: http://xchat.org/  
  
<b>How To:</b>
- The bot requires php PEAR to be installed with the Net_SmartIRC package.
- To add a game, check out the games folder and look at the structure of the default files.
  
<b>Basic commands:</b>    
To view a list of available commands: <i>!help</i> - This will return a list of available commands.  
To view a list of available games: <i>!create</i> - This will return a list of available games. (Feel free to ask for more games to be added)  
To view a list of available default servers for a game: <i>!serverlist</i> - This will return a list of available servers. (Feel free to ask for more servers to be added)  
To view a list of available pugs in queue: <i>!queue</i> - This will return a list of available pugs to join.  
To view additional information about a specific pug: <i>!info</i> - For example: !info etqw 4v4 This will return a list of teams and their players, currently signed up for the pug. If no additional values are supplied, it will return the info of the pug you are currently in queue for.  
  
  
<b>Create a PUG:</b>  
To create a pick-up game: <i>!create <game> <mode> <skill> <server></i>  
For example: <i>!create etqw 4v4 low</i> OR <i>!create etqw 4v4 med server1</i> OR <i>!create etqw 4v4 pro 123.456.7.890:27733 mypw</i>   
<game> - Pick a game that is supported  
<mode> - Pick a gamemode that is supported for that game (4v4/6v6 etc) If a game supports 2v2v2, or asymmetrical teams (2v6v4) then the bot is also capable of handling those.  
<skill> - Pick a skill level that is supported for that game (low/med/high/pro etc)  
<server> - Select a server for the game   
If you leave this value blank, the bot will automatically select a default server from the list, that is not currently in use by another pug in queue.  
You can also specify which default server you would like to use, by entering the desired value given by typing !serverlist .   
In addition, you can also specify your own server ip and password in the following syntax: ip:port .   
If this ip is already in use by another pug, you are asked to provide a different server.  
  
You will automatically be the owner of the pug, and be granted special commands such as !start and !remove. (Additional commands like !kick,!ban,!unban, !shuffle will likely be added in the future)  
  
  
<b>Join a PUG:</b>  
To join a pick-up game: <i>!join</i>   
For example: <i>!join etqw 4v4 med 2</i> (joins team 2)   
Provide the <mode> and <skill> because several pick-up games with different modes and skill levels of the same game can be in queue at the same time!   
Provide the <#> of the team you would like to join! (1/2/3/4 etc) If you fail to provide a team number, the bot will respond that you will have to pick a team.  
  
To view how many players are needed: <i>!need</i>   
This will show how many players are still needed for the pug you are currently enlisted in.  
  
  
<b>Leave a PUG:</b>  
To leave a pick-up game: <i>!leave</i>  
This will remove you from the pug you are currently enlisted in.   
If you are the owner of the pug, the ownership will be transferred to the next player in the line-up.   
If no such player is found, the pug will be removed from queue.  
  
  
<b>PUG Owner commands:</b>  
To remove a pick-up game: <i>!remove</i>   
This will remove the entire pug from the queue. (Only pick-up owners have the power to remove a pug from queue)  
  
To force start a pick-up game: <i>!start</i>  
Only pick-up owners can use this command to force start a Pick-Up Game.  
  
When a pick-up is filled or started, every player will be notified to join the server by having the ip and password sent to them in a pm.   
The pug will then be removed from queue and another one of the same game and mode may be started. (You can always start mutliple pugs at the same time if they are not identical)  
  
If you change your nickname or quit the channel while you are in a pug queue, the bot will automatically remove you from the line-up and, if needed, grant ownership to the next person in line!  
  
To check it out, join the #battlebot irc channel on Quakenet!  
  
Greetings, Crytiqal.Aero  
  
<b>UPDATE v2.0: The Pick-Up Game Operator Bot can now handle cross channel communication!</b>  
  
You can now freely change your nickname and the bot will update your name in the pug queue list!  
Also, the bot will only automatically remove you from the line-up and, if needed, grant ownership to the next person in line if you leave ALL the channels on which the bot is hosted.  
  
The bot is now authenticated with Q so it can retain its operator status.  
•It won't greet people anymore who join the channel, this could cause spam.  
•It also won't say goodbye when people leave/quit the channel.  
•It will only announce the queue if there is actually a pug in queue.  
•When people !join or !leave a pug or quit all channels while they were in queue, it will update and print the game's info.  
  
<b>UPDATE v3.0: The Pick-Up Game Operator Bot can now handle votes!</b>  
  
When you are in a queue, you can now use !callvote and !vote commands.  
  
To call a vote: <i>!callvote <mode></i>  
For example: <i>!callvote kick playerNAME/playerID</i>  
When no <playerNAME/playerID> is given, the bot will return a list of current players and their ID's in your team.  
The vote will be valid for a set period of time (2min) and players of the same team can cast their vote within this time.  
When the vote reaches >50% within the valid time, the vote is granted.  
  
To cast a vote: <i>!vote y/n </i>  
For example: <i>!vote yes/y/1</i> OR <i>!vote no/n/0</i>  
  
The bot is capable of tracking the usernames accross all the pug's and updates the names when changed.  
A kickvote will work even if the player leaves the pug, changes his name, and joins again.  
  