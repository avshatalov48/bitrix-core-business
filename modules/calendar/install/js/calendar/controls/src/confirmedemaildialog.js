import {Tag, Dom, Loc, Event} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import {Popup} from 'main.popup';
import {EmailSelectorControl} from 'calendar.controls';
import {Util} from 'calendar.util';

export class ConfirmedEmailDialog extends EventEmitter
{
	Z_INDEX = 3200;
	SLIDER_Z_INDEX = 4400;
	WIDTH = 400;

	DOM = {};
	constructor()
	{
		super();
		this.setEventNamespace('BX.Calendar.Controls.ConfirmedEmailDialog');
		this.id = 'confirm-email-dialog-' + Math.round(Math.random() * 10000);
	}

	show()
	{
		this.DOM.content = Tag.render`<div>
			<div class="calendar-confirm-email-text">${Loc.getMessage('EC_CONFIRMED_EMAIL_TEXT_1')}</div>
			<div class="calendar-confirm-email-text"><a class="calendar-confirm-email-help-link" href="javascript:void(0);">${Loc.getMessage('EC_CONFIRMED_EMAIL_HELP_LINK')}</a></div>
			<div class="calendar-field-block">
				<select class="calendar-field calendar-field-select ui-btn ui-btn ui-btn-light-border ui-btn-clock"></select>
			</div>
		</div>`;

		this.dialog = new Popup(this.id, null, {
			overlay: {opacity: 10},
			autoHide: true,
			width: this.WIDTH,
			closeByEsc : true,
			zIndex: this.Z_INDEX,
			offsetLeft: 0,
			offsetTop: 0,
			draggable: true,
			bindOnResize: false,
			titleBar: Loc.getMessage('EC_CONFIRMED_EMAIL_TITLE'),
			closeIcon: { right : "12px", top : "10px"},
			className: 'confirmemail-popup-window',
			content: this.DOM.content,
			events: {},
			cacheable: false,
			buttons: [
				new BX.UI.Button({
					text : Loc.getMessage('EC_CONFIRMED_EMAIL_SEND'),
					className: `ui-btn ui-btn-primary ${BX.UI.Button.State.DISABLED}`,
					events : {click : () => {
						if (this.DOM.select.value && this.DOM.select.value !== 'add')
						{
							const userSettings = Util.getUserSettings();
							userSettings.sendFromEmail = this.emailSelectorControl.getValue();
							Util.setUserSettings(userSettings);
							BX.userOptions.save('calendar', 'user_settings', 'sendFromEmail', userSettings.sendFromEmail);
							this.emit('onSelect', new BaseEvent({data: {sendFromEmail: userSettings.sendFromEmail}}));
							this.close();
						}
					}}
				}),
				new BX.UI.Button({
					text: Loc.getMessage('EC_SEC_SLIDER_CANCEL'),
					className: "ui-btn ui-btn-light-border",
					events : {click : this.close.bind(this)}
				})
			]
		});

		this.DOM.processButton = this.dialog.buttons[0].button;

		this.DOM.select = this.DOM.content.querySelector('select.calendar-field-select');
		Dom.addClass(this.DOM.select, BX.UI.Button.State.CLOCKING);
		this.DOM.select.disabled = true;

		this.emailSelectorControl = new EmailSelectorControl({
			selectNode: this.DOM.select,
			allowAddNewEmail: true
		});
		Event.bind(this.DOM.select, 'change', this.handleSelectChanges.bind(this));
		this.emailSelectorControl.subscribe('onSetValue', this.handleSelectChanges.bind(this));

		this.emailSelectorControl.loadMailboxData()
			.then(()=> {
				this.emailSelectorControl.setValue(Util.getUserSettings().sendFromEmail);
				this.DOM.select.disabled = false;
				this.DOM.select.className = 'calendar-field calendar-field-select';
			});

		this.DOM.helpLinlk = this.DOM.content.querySelector('.calendar-confirm-email-help-link');
		Event.bind(this.DOM.helpLinlk, 'click', this.openHelpSlider.bind(this));

		this.dialog.show();
	}

	close()
	{
		if (this.dialog)
		{
			this.dialog.close();
		}
	}

	handleSelectChanges()
	{
		if (this.DOM.select.value && this.DOM.select.value !== 'add')
		{
			Dom.removeClass(this.DOM.processButton, BX.UI.Button.State.DISABLED);
		}
		else
		{
			Dom.addClass(this.DOM.processButton, BX.UI.Button.State.DISABLED);
		}
	}

	openHelpSlider()
	{
		if(BX.Helper)
		{
			BX.Helper.show("redirect=detail&code=12070142", {zIndex: this.SLIDER_Z_INDEX});
		}
	}
}