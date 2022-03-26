import {Dom, Loc} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';

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

		let i, userNames = [];
		for (i = 0; i < params.users.length; i++)
		{
			userNames.push(params.users[i].DISPLAY_NAME);
		}
		userNames = userNames.join(', ');

		let content = BX.create('DIV', {
			props: {className: 'calendar-busy-users-content-wrap'},
			html: '<div class="calendar-busy-users-content">'
				+ BX.util.htmlspecialchars(this.plural ?
					Loc.getMessage('EC_BUSY_USERS_PLURAL').replace('#USER_LIST#', userNames)
					:
					Loc.getMessage('EC_BUSY_USERS_SINGLE').replace('#USER_NAME#', params.users[0].DISPLAY_NAME))
				+ '</div>'
		});

		this.dialog = new BX.PopupWindow(this.id, null, {
			overlay: {opacity: 10},
			autoHide: true,
			closeByEsc : true,
			zIndex: this.zIndex,
			offsetLeft: 0,
			offsetTop: 0,
			draggable: true,
			bindOnResize: false,
			titleBar: Loc.getMessage('EC_BUSY_USERS_TITLE'),
			closeIcon: { right : "12px", top : "10px"},
			className: 'bxc-popup-window',
			// buttons: [
			// 	new BX.PopupWindowButtonLink({
			// 		text: Loc.getMessage('EC_BUSY_USERS_CLOSE'),
			// 		className: "popup-window-button-link-cancel",
			// 		events: {click : () => {
			// 			// if (this.calendar.editSlider)
			// 			// 	this.calendar.editSlider.close();
			//
			// 			this.close();
			// 		}
			// 		}
			// 	})
			// ],
			content: content,
			events: {}
		});

		content.appendChild(new BX.PopupWindowButton({
			text: Loc.getMessage('EC_BUSY_USERS_BACK2EDIT'),
			events: {click : () => {this.close();}}
		}).buttonNode);

		content.appendChild(new BX.PopupWindowButton({
			text: this.plural ? Loc.getMessage('EC_BUSY_USERS_EXCLUDE_PLURAL') : Loc.getMessage('EC_BUSY_USERS_EXCLUDE_SINGLE'),
			events: {click : () => {
				this.emit('onSaveWithout');
				this.close();
			}}
		}).buttonNode);

		this.dialog.show();
	}

	close()
	{
		if (this.dialog)
		{
			this.dialog.close();
		}
	}
}