<?php

/*
 * Copyright (c) 2019 tim03we  < https://github.com/tim03we >
 * Discord: tim03we | TP#9129
 *
 * This software is distributed under "GNU General Public License v3.0".
 * This license allows you to use it and/or modify it but you are not at
 * all allowed to sell this plugin at any cost. If found doing so the
 * necessary action required would be taken.
 *
 * GunGame is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License v3.0 for more details.
 *
 * You should have received a copy of the GNU General Public License v3.0
 * along with this program. If not, see
 * <https://opensource.org/licenses/GPL-3.0>.
 */

declare(strict_types = 1);

namespace tim03we\gungame;

use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use tim03we\gungame\Task\GGTask;

class GunGame extends PluginBase {

    public $levels = [];
    public $needLevel = [];
    public $settingsDB, $levelDB;

    public function configUpdater(): void {
        if($this->settingsDB->get("version") !== "1.2"){
            rename($this->getDataFolder() . "settings.yml", $this->getDataFolder() . "settings_old.yml");
            $this->saveResource("settings.yml");
            $this->getLogger()->notice("We create a new settings.yml file for you.");
            $this->getLogger()->notice("Because the config version has changed. Your old configuration has been saved as settings_old.yml.");
        }
        if($this->settingsDB->get("version") !== "1.0.0"){
            rename($this->getDataFolder() . "level.yml", $this->getDataFolder() . "level_old.yml");
            $this->saveResource("level.yml");
            $this->getLogger()->notice("We create a new level.yml file for you.");
            $this->getLogger()->notice("Because the config version has changed. Your old configuration has been saved as level_old.yml.");
        }
    }

    public function onLoad()
    {
        foreach ($this->getServer()->getOnlinePlayers() as $player) {
            $this->levels[$player->getName()] = 0;
            $this->needLevel[$player->getName()] = 0;
            $player->sendMessage($this->settingsDB->getNested("messages.reload"));
        }
    }

    public function onEnable()
    {
        $this->initConfigs();
        $this->configUpdater();
        $this->initOptions();
        $this->initWorlds();
        $this->getScheduler()->scheduleRepeatingTask(new GGTask($this), 20);
    }

    public function initOptions()
    {
        if($this->settingsDB->getNested("events.place") == false && $this->settingsDB->getNested("events.break") == false) {
            $this->getServer()->getPluginManager()->registerEvents(new \tim03we\gungame\Events\BlockListener($this), $this);
        }
        if($this->settingsDB->getNested("events.hunger") == false) {
            $this->getServer()->getPluginManager()->registerEvents(new \tim03we\gungame\Events\HungerListener($this), $this);
        }
        if($this->settingsDB->getNested("events.drop") == false) {
            $this->getServer()->getPluginManager()->registerEvents(new \tim03we\gungame\Events\DropListener($this), $this);
        }
        if($this->settingsDB->getNested("events.inv-move") == false) {
            $this->getServer()->getPluginManager()->registerEvents(new \tim03we\gungame\Events\InventoryListener($this), $this);
        }
        if($this->settingsDB->getNested("events.important.entity") == true) {
            $this->getServer()->getPluginManager()->registerEvents(new \tim03we\gungame\Events\EntityListener($this), $this);
        }
        if($this->settingsDB->getNested("events.important.log") == true) {
            $this->getServer()->getPluginManager()->registerEvents(new \tim03we\gungame\Events\LogListener($this), $this);
        }
        if($this->settingsDB->getNested("events.important.respawn") == true) {
            $this->getServer()->getPluginManager()->registerEvents(new \tim03we\gungame\Events\RespawnListener($this), $this);
        }
        if($this->settingsDB->getNested("events.important.chat") == true) {
            $this->getServer()->getPluginManager()->registerEvents(new \tim03we\gungame\Events\ChatListener($this), $this);
        }
    }

    public function initWorlds()
    {
        try {
            if(is_array($this->settingsDB->get("worlds"))) {
                foreach ($this->settingsDB->get("worlds") as $world) {
                    $this->getServer()->loadLevel($world);
                }
            } else {
                $this->getServer()->loadLevel($this->settingsDB->get("worlds"));
            }
        } catch (\Throwable $exception) {
            $this->getLogger()->error($exception);
        }
    }

    public function initConfigs()
    {
        if (!file_exists($this->getDataFolder() . "settings.yml")) {
            $this->saveResource("settings.yml");
        }
        if (!file_exists($this->getDataFolder() . "level.yml")) {
            $this->saveResource("level.yml");
        }
        $this->settingsDB = new Config($this->getDataFolder() . "settings.yml", Config::YAML);
        $this->levelDB = new Config($this->getDataFolder() . "level.yml", Config::YAML);
    }


    public function invClear(Player $player) {
        $player->getArmorInventory()->clearAll();
        $player->getInventory()->clearAll();
        $player->getCursorInventory()->clearAll();
    }

    public function levelChange(Player $player, int $level) {
        $this->invClear($player);
        $this->needLevel[$player->getName()] = 0;
        $currLevel = $this->levels[$player->getName()];
        $player->setXpLevel($currLevel);
        $player->setHealth(20);
        $player->setNameTag(str_replace(["{level}", "{player}"], [$currLevel, $player->getName()], $this->getSettings("settings")->getNested("format.nametag")));
        if($this->settingsDB->get("Maximum-Level") < $currLevel) {
            $player->sendMessage($this->settingsDB->getNested("messages.max"));
            $player->getArmorInventory()->setHelmet(Item::get($this->levelDB->getNested($this->levelDB->get("Maximum-Level").".helmet.id"), 0, 1));
            $player->getArmorInventory()->setChestplate(Item::get($this->levelDB->getNested($this->levelDB->get("Maximum-Level").".chestplate.id"), 0, 1));
            $player->getArmorInventory()->setLeggings(Item::get($this->levelDB->getNested($this->levelDB->get("Maximum-Level").".leggings.id"), 0, 1));
            $player->getArmorInventory()->setBoots(Item::get($this->levelDB->getNested($this->levelDB->get("Maximum-Level").".boots.id"), 0, 1));
            $player->getInventory()->setItem(0, Item::get($this->levelDB->getNested($this->levelDB->get("Maximum-Level").".weapon.id"), 0, 1));
        } else {
            $player->getArmorInventory()->setHelmet(Item::get($this->levelDB->getNested($currLevel.".helmet.id"), 0, 1));
            $player->getArmorInventory()->setChestplate(Item::get($this->levelDB->getNested($currLevel.".chestplate.id"), 0, 1));
            $player->getArmorInventory()->setLeggings(Item::get($this->levelDB->getNested($currLevel.".leggings.id"), 0, 1));
            $player->getArmorInventory()->setBoots(Item::get($this->levelDB->getNested($currLevel.".boots.id"), 0, 1));
            $player->getInventory()->setItem(0, Item::get($this->levelDB->getNested($currLevel.".weapon.id"), 0, 1));
        }
    }

    public function levelUp(Player $player) {
        $this->levels[$player->getName()] = $this->levels[$player->getName()] + 1;
        $this->needLevel[$player->getName()] = 1;
    }

    public function levelDown(Player $player) {
        $cL = $this->levels[$player->getName()];
        $nL = intval($cL * $this->settingsDB->get("Chance"));
        $this->levels[$player->getName()] = $nL;
    }
}