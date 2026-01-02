import { ButtonCounterSize } from 'ui.buttons';
import ButtonSize from '../button/button-size';

export const getCounterSize = (buttonSize: string): string => ({
	[ButtonSize.EXTRA_EXTRA_SMALL]: ButtonCounterSize.SMALL,
	[ButtonSize.EXTRA_SMALL]: ButtonCounterSize.SMALL,
	[ButtonSize.SMALL]: ButtonCounterSize.SMALL,
	[ButtonSize.MEDIUM]: ButtonCounterSize.MEDIUM,
	[ButtonSize.LARGE]: ButtonCounterSize.LARGE,
	[ButtonSize.EXTRA_LARGE]: ButtonCounterSize.LARGE,
}[buttonSize] ?? ButtonCounterSize.MEDIUM);
