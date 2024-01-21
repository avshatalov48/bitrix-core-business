import { Loc, Text } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { MessageBox } from 'ui.dialogs.messagebox';
import { Button, ButtonSize, ButtonColor } from 'ui.buttons';

export class BusyUsersDialog extends EventEmitter
{
	DOM = {};
	constructor()
	{
		super();
		this.setEventNamespace('BX.Calendar.Controls.ConfirmStatusDialog');
		this.zIndex = 3200;
		this.id = 'busy-user-dialog-' + Math.round(Math.random() * 10000);
	}

	show(params = {})
	{
		this.plural = params.users.length > 1;

		const userNames = [];
		params.users.forEach((user) => {
			userNames.push(user.DISPLAY_NAME);
		});

		const userNamesPrepared = userNames.join(', ');

		const message = this.plural
			? Loc.getMessage('EC_BUSY_USERS_PLURAL').replace('#USER_LIST#', userNamesPrepared)
			: Loc.getMessage('EC_BUSY_USERS_SINGLE').replace('#USER_NAME#', params.users[0].DISPLAY_NAME)
		;

		this.dialog = new MessageBox({
			title: Loc.getMessage('EC_BUSY_USERS_TITLE'),
			message: Text.encode(message),
			buttons: this.getButtons(),
			popupOptions: {
				autoHide: true,
				closeByEsc: true,
				draggable: false,
				closeIcon: true,
				maxWidth: 700,
				minHeight: 150,
				animation: 'fading-slide',
			},
		});

		this.dialog.show();
	}

	getButtons()
	{
		return [
			new Button({
				size: ButtonSize.SMALL,
				color: ButtonColor.PRIMARY,
				text: Loc.getMessage('EC_BUSY_USERS_BACK2EDIT'),
				events: {
					click: () => {
						this.emit('onContinueEditing');
						this.close();
					},
				},
			}),
			new Button({
				size: ButtonSize.SMALL,
				color: ButtonColor.LIGHT_BORDER,
				text: this.plural
					? Loc.getMessage('EC_BUSY_USERS_EXCLUDE_PLURAL')
					: Loc.getMessage('EC_BUSY_USERS_EXCLUDE_SINGLE')
				,
				events: {
					click : () => {
						this.emit('onSaveWithout');
						this.close();
					},
				},
			}),
		];
	}

	close()
	{
		if (this.dialog)
		{
			this.dialog.close();
		}
	}

	isShown()
	{
		if (this.dialog)
		{
			return this.dialog.getPopupWindow().isShown();
		}

		return false;
	}
}