import { Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { MessageBox } from 'ui.dialogs.messagebox';
import 'ui.design-tokens';

import { EventType } from './event-type';

export class DialogDisable
{
	popup()
	{
		this.disablePopup();
	}

	disablePopup()
	{
		MessageBox.confirm(
			Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_DISABLE_POPUP_CONTENT'),
			Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_DISABLE_POPUP_TITLE_MSGVER_1'),
			(messageBox) => {
				messageBox.close();
				EventEmitter.emit(EventType.popup.disable, {});
			},
			Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_DISABLE_POPUP_CONFIRM_BUTTON'),
			(messageBox) => messageBox.close(),
			Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_POPUP_CANCEL_BUTTON'),
		);
	}
}
