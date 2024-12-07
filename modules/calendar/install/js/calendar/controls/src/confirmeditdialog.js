import { Loc, Tag } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';
import { Popup } from 'main.popup';
import { Button, ButtonSize, ButtonState, ButtonColor } from 'ui.buttons';

type Options = {
	canEditOnlyThis: ?boolean,
};

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

	show(options: Options)
	{
		this.dialog = new Popup({
			titleBar: Loc.getMessage('EC_EDIT_REC_EVENT'),
			content: this.getContent(options),
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

	getContent(options: Options)
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

		const notOnlyThisState = options.canEditOnlyThis ? ButtonState.DISABLED : null;

		const nextEventButton = new Button({
			size: ButtonSize.MEDIUM,
			color: ButtonColor.LIGHT_BORDER,
			text: Loc.getMessage('EC_REC_EV_NEXT'),
			events: {
				click: () => {
					if (options.canEditOnlyThis)
					{
						this.showCanEditOnlyThisPopup(
							nextEventButton.getContainer(),
							Loc.getMessage('EC_ONLY_AUTHOR_CAN_EDIT_NEXT'),
						);

						return;
					}

					this.emit('onEdit', new BaseEvent({data: {recursionMode: 'next'}}));
					this.close();
				},
			},
			state: notOnlyThisState,
		});

		const allEventButton = new Button({
			size: ButtonSize.MEDIUM,
			color: ButtonColor.LIGHT_BORDER,
			text: Loc.getMessage('EC_REC_EV_ALL'),
			events: {
				click : () => {
					if (options.canEditOnlyThis)
					{
						this.showCanEditOnlyThisPopup(
							allEventButton.getContainer(),
							Loc.getMessage('EC_ONLY_AUTHOR_CAN_EDIT_ALL'),
						);

						return;
					}

					this.emit('onEdit', new BaseEvent({data: {recursionMode: 'all'}}));
					this.close();
				},
			},
			state: notOnlyThisState,
		});

		return Tag.render`
			<div class="calendar__confirm-dialog-content">
				${thisEventButton.render()}
				${nextEventButton.render()}
				${allEventButton.render()}
			</div>
		`;
	}

	showCanEditOnlyThisPopup(bindElement, content)
	{
		clearTimeout(this.hideCanEditOnlyThisPopupTimeout);

		const popup = new Popup({
			bindElement,
			content,
			darkMode: true,
			bindOptions: { position: 'top' },
			offsetTop: -10,
			angle: true,
			autoHide: true,
			events: {
				onShow: () => {
					const angleLeft = Popup.getOption('angleMinBottom');
					const popupWidth = popup.getPopupContainer().offsetWidth;
					const elementWidth = popup.bindElement.offsetWidth;

					popup.setOffset({ offsetLeft: elementWidth / 2 - popupWidth / 2 });
					popup.adjustPosition();

					if (popup.angle)
					{
						popup.setAngle({ offset: popupWidth / 2 + angleLeft });
					}
				},
			},
		});

		popup.show();

		this.hideCanEditOnlyThisPopupTimeout = setTimeout(() => popup.close(), 2000);
	}

	close()
	{
		if (this.dialog)
		{
			this.dialog.close();
		}
	}
}
