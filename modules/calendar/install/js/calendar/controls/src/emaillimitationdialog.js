'use strict';

import { Tag, Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Popup } from 'main.popup';
import { FeaturePromotersRegistry } from 'ui.info-helper';

export class EmailLimitationDialog extends EventEmitter
{
	Z_INDEX = 3200;
	WIDTH = 480;

	DOM = {};
	constructor()
	{
		super();
		this.setEventNamespace('BX.Calendar.Controls.EmailLimitationDialog');
		this.id = `email-limitation-dialog-${Math.round(Math.random() * 10000)}`;
	}

	show()
	{
		this.DOM.content = Tag.render`
			<div>
				<div class="calendar-email-limit-text">${Loc.getMessage('EC_EMAIL_LIMIT_DENY')}</div>
				<div class="calendar-email-limit-subtext">${Loc.getMessage('EC_EMAIL_LIMIT_SUBTEXT')}</div>
			</div>
		`;

		this.dialog = this.getDialogPopup();

		this.dialog.show();
	}

	getDialogPopup()
	{
		return new Popup(this.id, null, {
			overlay: { opacity: 10 },
			autoHide: true,
			width: this.WIDTH,
			closeByEsc: true,
			zIndex: this.Z_INDEX,
			offsetLeft: 0,
			offsetTop: 0,
			draggable: true,
			bindOnResize: false,
			titleBar: Loc.getMessage('EC_EMAIL_LIMIT_TITLE'),
			closeIcon: { right: '12px', top: '10px' },
			className: 'email-limit-popup',
			content: this.DOM.content,
			events: {},
			cacheable: false,
			buttons: [
				new BX.UI.Button({
					text: Loc.getMessage('EC_EMAIL_LIMIT_EXPAND_PLAN'),
					className: 'ui-btn ui-btn-primary ui-btn-icon-plan',
					events: {
						click: () => {
							FeaturePromotersRegistry.getPromoter({ featureId: 'calendar_events_with_email_guests' }).show();
							this.close();
						},
					},
				}),
				new BX.UI.Button({
					text: Loc.getMessage('EC_EMAIL_LIMIT_SAVE_WITHOUT'),
					className: 'ui-btn ui-btn-link',
					events: {
						click: () => {
							this.saveWithoutAttendees();
							this.close();
						},
					},
				}),
			],
		});
	}

	close()
	{
		if (this.dialog)
		{
			this.dialog.close();
		}
	}

	saveWithoutAttendees()
	{
		this.emit('onSaveWithoutAttendees');
	}
}
