import type { BaseButtonOptions } from '../base-button-options';
import ButtonSize from './button-size';
import ButtonStyle from './button-style';
import AirButtonStyle from './air-button-style';
import ButtonColor from './button-color';
import ButtonIcon from './button-icon';
import ButtonState from './button-state';
import type { MenuOptions } from 'main.popup';
import type { CounterOptions } from 'ui.cnt';

export type ButtonOptions = BaseButtonOptions & {
	size?: ButtonSize,
	color?: ButtonColor,
	icon?: ButtonIcon,
	collapsedIcon: ButtonIcon,
	state?: ButtonState,
	id?: string,
	menu?: MenuOptions,
	context?: any,
	noCaps?: boolean,
	round?: boolean,
	dropdown?: boolean,
	dependOnTheme?: boolean,
	// Use only with useAirDesign: true option
	style?: ButtonStyle & AirButtonStyle;
	wide?: boolean;
	iconPosition?: 'left' | 'right';
	rightCounter?: CounterOptions;
	leftCounter?: CounterOptions;
	removeLeftCorners?: boolean;
	removeRightCorners?: boolean;
};
