import Button from '../button';
import ButtonColor from '../button-color';
import { Loc } from 'main.core';

/**
 * @namespace {BX.UI}
 */
export default class CloseButton extends Button
{
	getDefaultOptions()
	{
		return {
			text: Loc.getMessage('UI_BUTTONS_CLOSE_BTN_TEXT'),
			color: ButtonColor.LINK
		};
	}
}