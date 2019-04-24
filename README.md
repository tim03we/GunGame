# GunGame

GunGame is a nice MiniGame that hasn't been seen often in the Minecraft Bedrock Edition scene before. That's why I want to give you one. This MiniGame also includes a self-configurable Config that allows you to customize the game to your needs.

# Features
- Edit the game news
- Activate or deactivate certain events
- Change the chat format, as well as your nametag in the game
# Setup
- Download the plugin as Phar or folder
- Pack the plugin into the plugins folder
- Set the game mode in server.properties to Adventure (value: 2)
- Start / restart the server
- You can play with your friends now

# Config
```
# Please do not change this!
version: 1.0.0

# At which level should you no longer be able to receive kits?
Maximum-Level: 3

# Specify a percentage of the chance that you will lose many levels.
# Default = 0.60
Chance: 0.60

# Which events should be activated and which should be deactivated?
# Default = @all "false"
events:
    hunger: false
    place: false
    break: false
    inv-move: false
    drop: false
    
format:
    chat: "§8[§c{level}§8] §7{player} §8> §f{msg}"
    nametag: "§8[§c{level}§8] §7{player}"
    
    
# Here you can change the messages.
# {player} = The player is you
# {level} = Your current level
# {msg} = Your message
# {killer} = Who killed you
messages:
    join: "{player} has entered the GunGame server."
    quit: "{player} has left the GunGame server."
    chat: "§8[§c{level}§8] §7{player} §8> §f{msg}"
    death: "{player} died!"
    kill: "{player} was killed by {killer}."
    reload: "Your levels were reset by a server reload."
    max: "You have already reached the maximum level. Keep it up!"
```
----------------

If problems arise, create an issue or write us on Discord.

| Discord |
| :---: |
[![Discord](https://img.shields.io/discord/427472879072968714.svg?style=flat-square&label=discord&colorB=7289da)](https://discord.gg/Ce2aY25) |