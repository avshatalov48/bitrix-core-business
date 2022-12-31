import {Dom, Loc} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import {Popup} from 'main.popup';

export class ReinviteUserDialog extends EventEmitter
{
	DOM = {};
	constructor()
	{
		super();
		this.setEventNamespace('BX.Calendar.Controls.ReinviteUserDialog');
		this.zIndex = 3200;
		this.id = 'reinvite-dialog-' + Math.round(Math.random() * 10000);
	}

	show()
	{
		const content = Dom.create('DIV');
		this.close();
		this.dialog = new Popup(this.id, null, {
			overlay: {opacity: 10},
			autoHide: true,
			closeByEsc : true,
			zIndex: this.zIndex,
			offsetLeft: 0,
			offsetTop: 0,
			draggable: true,
			bindOnResize: false,
			titleBar: Loc.getMessage('EC_REINVITE_TITLE'),
			closeIcon: { right : "12px", top : "10px"},
			className: 'reinvite-popup-window',
			content: content,
			events: {},
			cacheable: false
		});

		new BX.UI.Button({
			text : Loc.getMessage('EC_REINVITE_YES'),
			className: "ui-btn ui-btn-primary",
			events : {click : () => {
					this.emit('onSelect', new BaseEvent({data: {sendInvitesAgain: true}}));
					this.close();
				}}
		}).renderTo(content);

		new BX.UI.Button({
			text: Loc.getMessage('EC_REINVITE_NO'),
			className: "ui-btn ui-btn-light-border",
			events : {click : () => {
					this.emit('onSelect', new BaseEvent({data: {sendInvitesAgain: false}}));
					this.close();
				}}
		}).renderTo(content);
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