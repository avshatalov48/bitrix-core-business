'use strict';
import { Popup } from 'main.popup';
import { Button, ButtonColor, ButtonSize } from "ui.buttons";
import {Loc, Tag} from "main.core";


export class ConfirmDeleteDialog
{
	constructor(params = {})
	{
		this.entry = params.entry;
	}

	show()
	{
		this.dialog = new Popup({
			titleBar: Loc.getMessage('EC_DEL_REC_EVENT'),
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
					this.entry.deleteThis();
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
					this.entry.deleteNext();
					this.close();
				},
			},
		});

		const allEventButton = new Button({
			size: ButtonSize.MEDIUM,
			color: ButtonColor.LIGHT_BORDER,
			text: Loc.getMessage('EC_REC_EV_ALL'),
			events: {
				click : () => {
					this.entry.deleteAll();
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