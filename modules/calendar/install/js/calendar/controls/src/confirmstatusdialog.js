import {Dom, Loc} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';

export class ConfirmStatusDialog extends EventEmitter
{
	DOM = {};
	constructor()
	{
		super();
		this.setEventNamespace('BX.Calendar.Controls.ConfirmStatusDialog');
		this.zIndex = 3200;
		this.id = 'confirm-status-dialog-' + Math.round(Math.random() * 10000);
	}

	show()
	{
		let content = Dom.create('DIV');
		this.dialog = new BX.PopupWindow(this.id, null, {
			overlay: {opacity: 10},
			autoHide: true,
			closeByEsc : true,
			zIndex: this.zIndex,
			offsetLeft: 0,
			offsetTop: 0,
			draggable: true,
			bindOnResize: false,
			titleBar: Loc.getMessage('EC_DECLINE_REC_EVENT'),
			closeIcon: { right : "12px", top : "10px"},
			className: 'bxc-popup-window',
			buttons: [
				new BX.PopupWindowButtonLink({
					text: Loc.getMessage('EC_SEC_SLIDER_CANCEL'),
					className: "popup-window-button-link-cancel",
					events: {click : this.close.bind(this)}
				})
			],
			content: content,
			events: {}
		});

		content.appendChild(new BX.PopupWindowButton({
			text: Loc.getMessage('EC_DECLINE_ONLY_THIS'),
			events: {
				click : function()
				{
					this.emit('onDecline', new BaseEvent({data: {recursionMode: 'this'}}));
					this.close();
				}.bind(this)
			}
		}).buttonNode);

		content.appendChild(new BX.PopupWindowButton({
			text: Loc.getMessage('EC_DECLINE_NEXT'),
			events: {
				click : function()
				{
					this.emit('onDecline', new BaseEvent({data: {recursionMode: 'next'}}));
					this.close();
				}.bind(this)
			}
		}).buttonNode);

		content.appendChild(new BX.PopupWindowButton(
			{
				text: Loc.getMessage('EC_DECLINE_ALL'),
				events: {
					click : function()
					{
						this.emit('onDecline', new BaseEvent({data: {recursionMode: 'all'}}));
						this.close();
					}.bind(this)
				}
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