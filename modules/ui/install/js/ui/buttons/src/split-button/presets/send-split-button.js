import SplitButton from '../split-button';
import ButtonColor from '../../button/button-color';
import { Loc } from 'main.core';

/**
 * @namespace {BX.UI}
 */
export default class SendSplitButton extends SplitButton
{
	getDefaultOptions()
	{
		return {
			text: Loc.getMessage('UI_BUTTONS_SEND_BTN_TEXT'),
			color: ButtonColor.SUCCESS
		};
	}
}