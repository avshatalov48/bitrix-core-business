import {Loc, Tag} from 'main.core';
import { EntryManager } from 'calendar.entry';
import { EventEmitter, BaseEvent} from 'main.core.events';
import { Button, ButtonColor, ButtonSize } from "ui.buttons";
import { Popup } from 'main.popup';


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
		this.dialog = new Popup({
			id: this.id,
			titleBar: Loc.getMessage('EC_DECLINE_REC_EVENT'),
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
			text: Loc.getMessage('EC_DECLINE_ONLY_THIS'),
			events: {
				click: () => {
					this.onDeclineHandler();
					this.emit('onDecline', new BaseEvent({data: {recursionMode: 'this'}}));
				},
			},
		});

		const nextEventButton = new Button({
			size: ButtonSize.MEDIUM,
			color: ButtonColor.LIGHT_BORDER,
			text: Loc.getMessage('EC_DECLINE_NEXT'),
			events: {
				click: () => {
					this.onDeclineHandler();
					this.emit('onDecline', new BaseEvent({data: {recursionMode: 'next'}}));
				},
			},
		});

		const allEventButton = new Button({
			size: ButtonSize.MEDIUM,
			color: ButtonColor.LIGHT_BORDER,
			text: Loc.getMessage('EC_DECLINE_ALL'),
			events: {
				click : () => {
					this.onDeclineHandler();
					this.emit('onDecline', new BaseEvent({data: {recursionMode: 'all'}}));
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

	onDeclineHandler()
	{
		this.close();
		const compactForm = EntryManager.getCompactViewForm();
		if (
			compactForm
			&& compactForm.isShown()
		)
		{
			compactForm.close();
		}
	}
}