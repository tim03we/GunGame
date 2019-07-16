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


namespace tim03we\gungame\Events;

use JackMD\KDR\KDR;
use pocketmine\block\Block;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\Player;
use pocketmine\utils\Config;
use tim03we\gungame\GunGame;

class EntityListener implements Listener {

    public $plugin;

    public function __construct(GunGame $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onMove(PlayerMoveEvent $event) {
        if(in_array($event->getPlayer()->getLevel()->getName(), $this->plugin->settingsDB->get("worlds"))) {
            $player = $event->getPlayer();
            $x = intval($player->getX());
            $y = intval($player->getY());
            $z = intval($player->getZ());
            $level = $event->getPlayer()->getLevel();
            $ground = $level->getBlockIdAt($x, $y, $z);
            if($ground === Block::WATER || $ground === Block::FLOWING_WATER || $ground === Block::STILL_WATER) {
                $cause = $player->getLastDamageCause();
                if($cause instanceof EntityDamageByEntityEvent) {
                    $damager = $cause->getDamager();
                    if($damager instanceof Player) {
                        $this->plugin->levelUp($damager);
                        if($this->plugin->getServer()->getPluginManager()->getPlugin("KDR")) {
                            KDR::getInstance()->getProvider()->addKillPoints($damager, 1);
                        }
                        $player->attack(new EntityDamageEvent($player, EntityDamageEvent::CAUSE_CUSTOM, 1000));
                        $this->plugin->getServer()->broadcastMessage(str_replace(["{killer}", "{player}"], [$damager->getName(), $player->getName()], $this->plugin->settingsDB->getNested("messages.kill")));
                    }
                } else {
                    $player->attack(new EntityDamageEvent($player, EntityDamageEvent::CAUSE_DROWNING, 1000));
                }
            }
        }
    }

    public function onDamage(EntityDamageEvent $event) {
        if(in_array($event->getEntity()->getLevel()->getName(), $this->plugin->settingsDB->get("worlds"))) {
            $player = $event->getEntity();
            if($player instanceof Player) {
                $cause = $event->getCause();
                if($cause === EntityDamageEvent::CAUSE_FALL) {
                    $event->setCancelled();
                }
            }
        }
    }

    public function onDeath(PlayerDeathEvent $event) {
        if(in_array($event->getPlayer()->getLevel()->getName(), $this->plugin->settingsDB->get("worlds"))) {
            $event->setDrops([]);
            $player = $event->getEntity();
            if($player instanceof Player) {
                $player->setXpLevel(0);
                $event->setDeathMessage(str_replace("{player}", $player->getName(), $this->plugin->settingsDB->getNested("messages.death")));
                $this->plugin->levelDown($player);
            }
            $cause = $player->getLastDamageCause();
            if($cause instanceof EntityDamageByEntityEvent) {
                $damager = $cause->getDamager();
                if($damager instanceof Player) {
                    $this->plugin->levelUp($damager);
                    $event->setDeathMessage(str_replace(["{killer}", "{player}"], [$damager->getName(), $player->getName()], $this->plugin->settingsDB->getNested("messages.kill")));
                }
            }
            if($cause->getCause() === EntityDamageEvent::CAUSE_CUSTOM) {
                $event->setDeathMessage("");
            }
        }
    }
}