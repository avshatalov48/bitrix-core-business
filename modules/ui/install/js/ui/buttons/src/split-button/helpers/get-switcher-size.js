import { SwitcherSize } from 'ui.switcher';

import ButtonSize from '../../button/button-size';

const switcherSizeByButton = Object.freeze({
	[ButtonSize.EXTRA_LARGE]: SwitcherSize.large,
	[ButtonSize.LARGE]: SwitcherSize.medium,
	[ButtonSize.MEDIUM]: SwitcherSize.small,
	[ButtonSize.SMALL]: SwitcherSize.extraSmall,
	[ButtonSize.EXTRA_SMALL]: SwitcherSize.extraSmall,
	[ButtonSize.EXTRA_EXTRA_SMALL]: SwitcherSize.extraExtraSmall,
});

export const getSwitcherSizeByButtonSize = (buttonSize: ButtonSize): SwitcherSize => {
	return switcherSizeByButton[buttonSize];
};
