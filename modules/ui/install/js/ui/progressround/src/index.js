import { Reflection } from 'main.core';

import ProgressRound from './progressround';
import ProgressRoundColor from './progressround-color';
import ProgressRoundStatus from './progressround-status';

export {
	ProgressRound,
};

const UI = Reflection.namespace('BX.UI');

/** @deprecated use BX.UI.ProgressRound or import { ProgressRound } from 'ui.progressround' */
UI.Progressround = ProgressRound;
