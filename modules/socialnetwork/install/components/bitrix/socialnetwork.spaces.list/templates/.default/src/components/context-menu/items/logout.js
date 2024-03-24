import { Loc } from 'main.core';
import { MenuItem } from 'main.popup';
import { ContextItem } from './context-item';
import { MessageBox, MessageBoxButtons } from 'ui.dialogs.messagebox';
import { Controller } from 'socialnetwork.controller';

export class Logout extends ContextItem
{
	create(): Object
	{
		return {
			text: this.message,
			onclick: (event, menuItem: MenuItem) => {
				const messageBox = new MessageBox({
					message: Loc.getMessage('SN_SPACES_LIST_SPACE_COPY_LOGOUT_POPUP_TEXT'),
					buttons: MessageBoxButtons.OK_CANCEL,
					okCaption: Loc.getMessage('SN_SPACES_LIST_SPACE_COPY_LOGOUT_POPUP_CONFIRM_BTN'),
					onOk: () => {
						Controller.leaveGroup(this.spaceId)
							.then(() => {
								menuItem.getMenuWindow().close();
								messageBox.close();
								this.emit('click');
							})
							.catch(() => {
								messageBox.getOkButton().setDisabled(false);
							})
						;
					},
				});

				messageBox.show();
			},
		};
	}
}
