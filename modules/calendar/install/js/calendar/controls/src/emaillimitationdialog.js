'use strict';
import {Tag, Dom, Loc, Event} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import {Popup} from 'main.popup';
import {EmailSelectorControl} from 'calendar.controls';
import {Util} from 'calendar.util';

export class EmailLimitationDialog extends EventEmitter
{
	Z_INDEX = 3200;
	EXPAND_LICENSE_URL = '/settings/license_all.php';
	WIDTH = 480;

	DOM = {};
	constructor()
	{
		super();
		this.setEventNamespace('BX.Calendar.Controls.EmailLimitationDialog');
		this.id = 'email-limitation-dialog-' + Math.round(Math.random() * 10000);
	}

	show()
	{
		const eventsAmount = Util.getEventWithEmailGuestAmount();
		const limit = Util.isEventWithEmailGuestAllowed();
		if (eventsAmount === 4)
		{
			this.text = Loc.getMessage('EC_EMAIL_LIMIT_5');
		}
		else if(eventsAmount === 8)
		{
			this.text = Loc.getMessage('EC_EMAIL_LIMIT_9');
		}
		else
		{
			this.text = Loc.getMessage('EC_EMAIL_LIMIT_DENY');
		}
		this.subText = Loc.getMessage('EC_EMAIL_LIMIT_SUBTEXT');

		this.DOM.content = Tag.render`<div>
			<div class="calendar-email-limit-text">${this.text}</div>
			<div class="calendar-email-limit-subtext">${this.subText}</div>
		</div>`;

		this.dialog = this.getDialogPopup();

		this.dialog.subscribe('onClose', ()=>{
			this.emit('onClose');
		});

		// this.DOM.processButton = this.dialog.buttons[0].button;
		//
		// this.DOM.select = this.DOM.content.querySelector('select.calendar-field-select');
		// Dom.addClass(this.DOM.select, BX.UI.Button.State.CLOCKING);
		// this.DOM.select.disabled = true;
		//
		// this.emailSelectorControl = new EmailSelectorControl({
		// 	selectNode: this.DOM.select,
		// 	allowAddNewEmail: true
		// });
		// Event.bind(this.DOM.select, 'change', this.handleSelectChanges.bind(this));
		// this.emailSelectorControl.subscribe('onSetValue', this.handleSelectChanges.bind(this));
		//
		// this.emailSelectorControl.loadMailboxData()
		// 	.then(()=> {
		// 		this.emailSelectorControl.setValue(Util.getUserSettings().sendFromEmail);
		// 		this.DOM.select.disabled = false;
		// 		this.DOM.select.className = 'calendar-field calendar-field-select';
		// 	});
		//
		// this.DOM.helpLinlk = this.DOM.content.querySelector('.calendar-confirm-email-help-link');
		// Event.bind(this.DOM.helpLinlk, 'click', this.openHelpSlider.bind(this));

		this.dialog.show();
	}

	getDialogPopup()
	{
		return new Popup(this.id, null, {
			overlay: {opacity: 10},
			autoHide: true,
			width: this.WIDTH,
			closeByEsc: true,
			zIndex: this.Z_INDEX,
			offsetLeft: 0,
			offsetTop: 0,
			draggable: true,
			bindOnResize: false,
			titleBar: Loc.getMessage('EC_EMAIL_LIMIT_TITLE'),
			closeIcon: {right: "12px", top: "10px"},
			className: 'email-limit-popup',
			content: this.DOM.content,
			events: {},
			cacheable: false,
			buttons: [
				new BX.UI.Button({
					text: Loc.getMessage('EC_EMAIL_LIMIT_EXPAND_PLAN'),
					className: `ui-btn ui-btn-primary ui-btn-icon-plan`,
					events: {
						click: () =>
						{
							window.open(this.EXPAND_LICENSE_URL, '_blank');
						}
					}
				}),
				new BX.UI.Button({
					text: Util.isEventWithEmailGuestAllowed() ? Loc.getMessage('EC_SEC_SLIDER_CLOSE') : Loc.getMessage('EC_EMAIL_LIMIT_SAVE_WITHOUT'),
					className: `ui-btn ui-btn-link`,
					events: {click: this.close.bind(this)}
				})
			]
		});
	}

	close()
	{
		if (this.dialog)
		{
			this.dialog.close();
		}
	}
}