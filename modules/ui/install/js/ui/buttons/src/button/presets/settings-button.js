import Button from '../button';
import ButtonColor from '../button-color';
import ButtonIcon from '../button-icon';

/**
 * @namespace {BX.UI}
 */
export default class SettingsButton extends Button
{
	getDefaultOptions()
	{
		return {
			icon: ButtonIcon.SETTING,
			color: ButtonColor.LIGHT_BORDER,
			dropdown: false
		};
	}
}