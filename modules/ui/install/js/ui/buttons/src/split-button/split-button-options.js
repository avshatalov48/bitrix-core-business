import type { SwitcherOptions } from 'ui.switcher';

import type { ButtonOptions } from '../button/button-options';
import type { SplitSubButtonOptions } from './split-sub-button-options';
import SplitSubButtonType from './split-sub-button-type';
import SplitButtonState from './split-button-state';

export type SplitButtonSwitcherButtonOptions = $Diff<SwitcherOptions, {
	useAirDesign: boolean;
	size: string;
}>;

export type SplitButtonOptions = Exclude<ButtonOptions, 'tag' | 'round' | 'state'> &
{
	style?: string;
	state?: SplitButtonState,
	mainButton?: SplitSubButtonOptions,
	menuButton?: SplitSubButtonOptions,
	switcher?: SplitButtonSwitcherButtonOptions,
	menuTarget?: SplitSubButtonType
};
