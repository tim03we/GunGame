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
    public $maxLevel = 10;

    public function configUpdater(): void {
        if($this->cfg->get("version") !== "1.1"){
            rename($this->getDataFolder() . "settings.yml", $this->getDataFolder() . "settings_old.yml");
            $this->saveResource("settings.yml");
            $this->getLogger()->notice("We create a new settings.yml file for you.");
            $this->getLogger()->notice("Because the config version has changed. Your old configuration has been saved as settings_old.yml.");
        }
        if($this->cfg->get("version") !== "1.0.0"){
            rename($this->getDataFolder() . "level.yml", $this->getDataFolder() . "level_old.yml");
            $this->saveResource("level.yml");
            $this->getLogger()->notice("We create a new level.yml file for you.");
            $this->getLogger()->notice("Because the config version has changed. Your old configuration has been saved as level_old.yml.");
        }
    }

    public function onEnable()
    {
        $this->register();
        $this->getScheduler()->scheduleRepeatingTask(new GGTask($this), 20);
        $this->saveResource("level.yml");
        $this->saveResource("settings.yml");
        $this->cfg = new Config($this->getDataFolder() . "settings.yml", Config::YAML);
        $this->lcfg = new Config($this->getDataFolder() . "level.yml", Config::YAML);
        $this->configUpdater();
    }

    public function onLoad()
    {
        foreach ($this->getServer()->getOnlinePlayers() as $player) {
            $this->levels[$player->getName()] = 0;
            $this->needLevel[$player->getName()] = 0;
            $player->sendMessage($this->cfg->getNested("messages.reload"));
        }
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
        $nametag = $this->cfg->getNested("format.nametag");
        $nametag = str_replace("{player}", $player->getName(), $nametag);
        $nametag = str_replace("{level}", $currLevel, $nametag);
        $player->setNameTag($nametag);
        if($this->cfg->get("Maximum-Level") < $currLevel) {
            $player->sendMessage($this->cfg->getNested("messages.max"));
            $helmet = Item::get($this->lcfg->getNested($this->lcfg->get("Maximum-Level").".helmet.id"), 0, 1);
            $chestplate = Item::get($this->lcfg->getNested($this->lcfg->get("Maximum-Level").".chestplate.id"), 0, 1);
            $leggings = Item::get($this->lcfg->getNested($this->lcfg->get("Maximum-Level").".leggings.id"), 0, 1);
            $boots = Item::get($this->lcfg->getNested($this->lcfg->get("Maximum-Level").".boots.id"), 0, 1);
            $weapon = Item::get($this->lcfg->getNested($this->lcfg->get("Maximum-Level").".weapon.id"), 0, 1);
            $player->getArmorInventory()->setHelmet($helmet);
            $player->getArmorInventory()->setChestplate($chestplate);
            $player->getArmorInventory()->setLeggings($leggings);
            $player->getArmorInventory()->setBoots($boots);
            $player->getInventory()->setItem(0, $weapon);
        } else {
            $helmet = Item::get($this->lcfg->getNested($currLevel.".helmet.id"), 0, 1);
            $chestplate = Item::get($this->lcfg->getNested($currLevel.".chestplate.id"), 0, 1);
            $leggings = Item::get($this->lcfg->getNested($currLevel.".leggings.id"), 0, 1);
            $boots = Item::get($this->lcfg->getNested($currLevel.".boots.id"), 0, 1);
            $weapon = Item::get($this->lcfg->getNested($currLevel.".weapon.id"), 0, 1);
            $player->getArmorInventory()->setHelmet($helmet);
            $player->getArmorInventory()->setChestplate($chestplate);
            $player->getArmorInventory()->setLeggings($leggings);
            $player->getArmorInventory()->setBoots($boots);
            $player->getInventory()->setItem(0, $weapon);
        }
    }

    public function register() {
        $this->getServer()->getPluginManager()->registerEvents(new \tim03we\gungame\Events\ChatListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new \tim03we\gungame\Events\HungerListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new \tim03we\gungame\Events\LogListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new \tim03we\gungame\Events\BlockListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new \tim03we\gungame\Events\EntityListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new \tim03we\gungame\Events\InventoryListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new \tim03we\gungame\Events\RespawnListener($this), $this);
        $this->getServer()->getPluginManager()->registerEvents(new \tim03we\gungame\Events\DropListener($this), $this);
    }

    public function levelUp(Player $player) {
        $this->levels[$player->getName()] = $this->levels[$player->getName()] + 1;
        $this->needLevel[$player->getName()] = 1;
    }

    public function levelDown(Player $player) {
        $cL = $this->levels[$player->getName()];
        $nL = intval($cL * $this->cfg->get("Chance"));
        $this->levels[$player->getName()] = $nL;
    }
}