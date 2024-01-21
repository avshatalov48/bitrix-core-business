import { Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Button } from 'ui.buttons';
import { MessageBox } from 'ui.dialogs.messagebox';

import { EventType } from './event-type';

export class DialogClearing
{
	popup()
	{
		const messageBox = MessageBox.create({
			message: Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_ENABLE_POPUP_CONTENT_MSGVER_1'),
			buttons: [
				new Button({
					text: Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_ENABLE_POPUP_CONFIRM_BUTTON_MSGVER_1'),
					color: Button.Color.PRIMARY,
					onclick: () => {
						EventEmitter.emit(EventType.popup.enableWithResetDocuments, {});
						messageBox.close();
					},
				}),
				new Button({
					text: Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_POPUP_CANCEL_BUTTON_MSGVER_1'),
					color: Button.Color.LINK,
					onclick: () => {
						messageBox.close();
					},
				}),
			],
			maxWidth: 400,
		});

		messageBox.show();
	}
}
