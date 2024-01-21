import { Reflection } from 'main.core';
import 'ui.fonts.opensans';

import ProgressRound from './progressround';
import ProgressRoundColor from './progressround-color';
import ProgressRoundStatus from './progressround-status';

import './css/style.css';

export {
	ProgressRound,
	ProgressRoundColor,
	ProgressRoundStatus
};

const UI = Reflection.namespace('BX.UI');

/** @deprecated use BX.UI.ProgressRound or import { ProgressRound } from 'ui.progressround' */
UI.Progressround = ProgressRound;
