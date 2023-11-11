import { Dom } from 'main.core';
import { Popup } from 'main.popup';
import { MessageBox } from 'ui.dialogs.messagebox';

import '../css/confirm.css';

const CONTAINER_CLASS = 'im-confirm-container';
const CONTAINER_MIN_HEIGHT = 110;

export class ChatConfirm extends MessageBox
{
	// noinspection JSCheckFunctionSignatures
	getPopupWindow(): Popup
	{
		const popup = super.getPopupWindow();
		Dom.addClass(popup.getPopupContainer(), CONTAINER_CLASS);
		Dom.style(popup.getPopupContainer(), 'minHeight', `${CONTAINER_MIN_HEIGHT}px`);

		return super.getPopupWindow();
	}
}
