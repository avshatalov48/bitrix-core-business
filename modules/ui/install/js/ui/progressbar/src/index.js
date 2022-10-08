import { Reflection } from 'main.core';
import 'ui.fonts.opensans';

import ProgressBar from './progressbar';
import ProgressBarColor from './progressbar-color';
import ProgressBarSize from './progressbar-size';
import ProgressBarStatus from './progressbar-status';

export {
	ProgressBar,
};

const UI = Reflection.namespace('BX.UI');

/** @deprecated use BX.UI.ProgressBar or import { ProgressBar } from 'ui.progressbar' */
UI.Progressbar = ProgressBar;
