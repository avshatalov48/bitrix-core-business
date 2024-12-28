import { Reflection } from 'main.core';
import { Player } from './player';
import { PlayerManager } from './player-manager';

import './css/player.css';
import './css/audio-wave-skin.css';

// compatibility
const filemanNS = Reflection.namespace('BX.Fileman');
filemanNS.Player = Player;
filemanNS.PlayerManager = PlayerManager;

export {
	Player,
	PlayerManager,
};
