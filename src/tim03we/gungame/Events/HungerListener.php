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

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\utils\Config;
use tim03we\gungame\GunGame;

class HungerListener implements Listener {

    public $plugin;

    public function __construct(GunGame $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onHunger(PlayerExhaustEvent $event) {
        if(in_array($event->getPlayer()->getLevel()->getName(), $this->plugin->settingsDB->get("worlds"))) {
            if($this->plugin->cfg->getNested("events.hunger") == false) {
                $event->setCancelled(true);
            } else {
                $event->setCancelled(false);
            }
        }
    }
}