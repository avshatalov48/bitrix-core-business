import SplitButton from '../split-button';
import ButtonColor from '../../button/button-color';
import { Loc } from 'main.core';

/**
 * @namespace {BX.UI}
 */
export default class CancelSplitButton extends SplitButton
{
	getDefaultOptions()
	{
		return {
			text: Loc.getMessage('UI_BUTTONS_CANCEL_BTN_TEXT'),
			color: ButtonColor.LINK
		};
	}
}