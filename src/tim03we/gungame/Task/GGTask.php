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


namespace tim03we\gungame\Task;

use pocketmine\scheduler\Task;
use tim03we\gungame\GunGame;

class GGTask extends Task {

    public function __construct(GunGame $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onRun(int $currentTick)
    {
        foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {
            if(array_key_exists($player->getName(), $this->plugin->needLevel)) {
                $needLevel = $this->plugin->needLevel[$player->getName()];
                if ($needLevel === 1) {
                    $currLevel = $this->plugin->levels[$player->getName()];
                    $this->plugin->levelChange($player, $currLevel);
                }
            }
        }
    }
}