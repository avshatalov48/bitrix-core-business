import { Loc, Tag } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';
import { Popup } from 'main.popup';
import { Button, ButtonSize, ButtonColor } from 'ui.buttons';

export class ConfirmEditDialog extends EventEmitter
{
	DOM = {};
	constructor()
	{
		super();
		this.setEventNamespace('BX.Calendar.Controls.ConfirmEditDialog');
		this.zIndex = 3200;
		this.id = 'confirm-edit-dialog-' + Math.round(Math.random() * 10000);
	}

	show()
	{
		this.dialog = new Popup({
			titleBar: Loc.getMessage('EC_EDIT_REC_EVENT'),
			content: this.getContent(),
			className: 'calendar__confirm-dialog',
			lightShadow: true,
			maxWidth: 700,
			minHeight: 120,
			autoHide: true,
			closeByEsc: true,
			draggable: true,
			closeIcon: true,
			animation: 'fading-slide',
			contentBackground: "#fff",
			overlay: { opacity: 15 },
			cacheable: false,
		});

		this.dialog.show();
	}

	getContent()
	{
		const thisEventButton = new Button({
			size: ButtonSize.MEDIUM,
			color: ButtonColor.LIGHT_BORDER,
			text: Loc.getMessage('EC_REC_EV_ONLY_THIS_EVENT'),
			events: {
				click: () => {
					this.emit('onEdit', new BaseEvent({data: {recursionMode: 'this'}}));
					this.close();
				},
			},
		});

		const nextEventButton = new Button({
			size: ButtonSize.MEDIUM,
			color: ButtonColor.LIGHT_BORDER,
			text: Loc.getMessage('EC_REC_EV_NEXT'),
			events: {
				click: () => {
					this.emit('onEdit', new BaseEvent({data: {recursionMode: 'next'}}));
					this.close();
				},
			},
		});

		const allEventButton = 	new Button({
			size: ButtonSize.MEDIUM,
			color: ButtonColor.LIGHT_BORDER,
			text: Loc.getMessage('EC_REC_EV_ALL'),
			events: {
				click : () => {
					this.emit('onEdit', new BaseEvent({data: {recursionMode: 'all'}}));
					this.close();
				},
			},
		});

		return Tag.render`
			<div class="calendar__confirm-dialog-content">
				${thisEventButton.render()}
				${nextEventButton.render()}
				${allEventButton.render()}
			</div>
		`;
	}

	close()
	{
		if (this.dialog)
		{
			this.dialog.close();
		}
	}
}