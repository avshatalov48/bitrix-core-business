import type { ButtonOptions } from '../button/button-options';
import type { SplitSubButtonOptions } from './split-sub-button-options';
import SplitSubButtonType from './split-sub-button-type';
import SplitButtonState from './split-button-state';

export type SplitButtonOptions = Exclude<ButtonOptions, 'tag' | 'round' | 'state'> &
{
	state?: SplitButtonState,
	mainButton?: SplitSubButtonOptions,
	menuButton?: SplitSubButtonOptions,
	menuTarget?: SplitSubButtonType
};