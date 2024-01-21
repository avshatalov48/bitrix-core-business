// @flow
'use strict';
import { Tag, Loc, Dom, Event } from 'main.core';
import { Util } from 'calendar.util';
import { Popup } from 'main.popup';
import { BaseEvent, EventEmitter } from 'main.core.events';

export default class IcloudAuthDialog extends EventEmitter
{
	zIndex = 3100;
	DOM = {};
	appPasswordTemplate = 'xxxx-xxxx-xxxx-xxxx';

	constructor(options = {})
	{
		super();
		this.type = options.type;
		this.setEventNamespace('BX.Calendar.Sync.Icloud');
		this.keyHandler = this.handleKeyPress.bind(this);
		this.checkOutsideClickClose = this.checkOutsideClickClose.bind(this);
		this.outsideMouseDownClose = this.outsideMouseDownClose.bind(this);
		this.initAlertBlock();
	}

	show()
	{
		this.popup = new Popup({
			className: 'calendar-sync__auth-popup calendar-sync__scope',
			titleBar: Loc.getMessage('CAL_ICLOUD_AUTH_TITLE'),
			draggable: true,
			content: this.getContainer(),
			width: 475,
			animation: 'fading-slide',
			zIndexAbsolute: this.zIndex,
			cacheable: false,
			closeByEsc: true,
			closeIcon: true,
			contentBackground: "#fff",
			overlay: {opacity: 15},
			lightShadow: true,
			buttons: [
				new BX.UI.Button({
					text : Loc.getMessage('CAL_ICLOUD_CONNECT_BUTTON'),
					className: `ui-btn ui-btn-md ui-btn-success ui-btn-round`,
					events : {click : this.authorize.bind(this)},
				}),
				new BX.UI.Button({
					text: Loc.getMessage('EC_SEC_SLIDER_CANCEL'),
					className: 'ui-btn ui-btn-md ui-btn-light-border ui-btn-round',
					events : {click : this.close.bind(this)}
				})
			],
			events: {
				onPopupClose: this.close.bind(this)
			},
		});
		this.popup.show();
		
		Event.bind(document, 'keydown', this.keyHandler);
		Event.bind(document, 'mouseup', this.checkOutsideClickClose);
		Event.bind(document, 'mousedown', this.outsideMouseDownClose);
	}
	
	authorize()
	{
		if (this.isFormDataValid())
		{
			const saveBtn = this.popup.getButtons()[0];
			saveBtn.setClocking(true);
			saveBtn.setDisabled(true);
			const cancelButton = this.popup.getButtons()[1];
			cancelButton.setDisabled(true);
			if (this.DOM.container.contains(this.DOM.alertBlock))
			{
				Dom.remove(this.DOM.alertBlock);
			}

			this.emit('onSubmit', new BaseEvent({
				data: {
					appleId: this.DOM.appleIdInput.value.toString().trim(),
					appPassword: this.DOM.appPasswordInput.value.toString().trim()
				}
			}));
		}
		else
		{
			this.highlightInvalidFormData();
		}
	}

	isFormDataValid()
	{
		return this.DOM.appleIdInput.value.toString().trim() !== ''
			&& this.DOM.appPasswordInput.value.toString().trim() !== ''
	}

	highlightInvalidFormData()
	{
		const saveBtn = this.popup.getButtons()[0];
		saveBtn.setClocking(false);
		saveBtn.setDisabled(false);
		const cancelButton = this.popup.getButtons()[1];
		cancelButton.setDisabled(false);

		if (this.DOM.appleIdInput.value.toString().trim() === '')
		{
			this.highlightInvalidAppleIdInput();
		}
		if (this.DOM.appPasswordInput.value.toString().trim() === '')
		{
			this.highlightInvalidPasswordInput();
		}
	}

	highlightInvalidAppleIdInput()
	{
		Dom.addClass(this.DOM.appleIdInput, 'calendar-field-string-error');

		const clearInvalidation = () => {
			Dom.removeClass(this.DOM.appleIdInput, 'calendar-field-string-error');
			Event.unbind(this.DOM.appleIdInput, 'change', clearInvalidation);
			Event.unbind(this.DOM.appleIdInput, 'keyup', clearInvalidation);
		};
		Event.bind(this.DOM.appleIdInput, 'change', clearInvalidation);
		Event.bind(this.DOM.appleIdInput, 'keyup', clearInvalidation);
	}

	highlightInvalidPasswordInput()
	{
		Dom.addClass(this.DOM.appPasswordInput, 'calendar-field-string-error');

		const clearInvalidation = () => {
			Dom.removeClass(this.DOM.appPasswordInput, 'calendar-field-string-error');
			Event.unbind(this.DOM.appPasswordInput, 'change', clearInvalidation);
			Event.unbind(this.DOM.appPasswordInput, 'keyup', clearInvalidation);
		};
		Event.bind(this.DOM.appPasswordInput, 'change', clearInvalidation);
		Event.bind(this.DOM.appPasswordInput, 'keyup', clearInvalidation);
		this.DOM.appPasswordInput.focus();

	}
	
	enableSaveButton()
	{
		const saveBtn = this.popup.getButtons()[0];
		saveBtn.setDisabled(false);
		const cancelButton = this.popup.getButtons()[1];
		cancelButton.setDisabled(false);
	}

	getContainer()
	{
		this.DOM.container = Tag.render `
			<div>
				${this.getAppleInfoBlock()}
				<div class="calendar-sync__auth-popup--row" id="calendar-apple-id-block">
					${this.getAppleIdTitle()}
					${this.getAppleIdInput()}
					${this.getAppleIdError()}
				</div>
				<div class="calendar-sync__auth-popup--row" id="calendar-apple-pass-block">
					<div class="calendar-sync__auth-popup--label-block">
						${this.getAppPasswordTitle()}
						${this.getLearnMoreButton()}
					</div>
					<div class="ui-ctl ui-ctl-w100 ui-ctl-after-icon">
						${this.getAppPasswordInput()}
						${this.getShowHidePasswordIcon()}
					</div>
					${this.getAppPasswordError()}
				</div>
			</div>
		`;

		return this.DOM.container;
	}

	getAppleInfoBlock()
	{
		if (!this.DOM.appleInfo)
		{
			this.DOM.appleInfo = Tag.render`
				<div class="calendar-sync__auth-popup--info">
					<div class="calendar-sync__auth-popup--logo-image --icloud"></div>
					<div class="calendar-sync__auth-popup--logo-text">${Loc.getMessage('CAL_ICLOUD_INFO_BLOCK')}</div>
				</div>
			`;
		}

		return this.DOM.appleInfo;
	}
	
	getAppleIdTitle()
	{
		if (!this.DOM.appleIdTitle)
		{
			this.DOM.appleIdTitle = Tag.render`
			<p class="calendar-sync__auth-popup--label-text">
				${Loc.getMessage('CAL_ICLOUD_APPLE_ID_PLACEHOLDER')}
			</p>
			`;
		}
		
		return this.DOM.appleIdTitle;
	}
	
	getAppPasswordTitle()
	{
		if (!this.DOM.appPasswordTitle)
		{
			this.DOM.appPasswordTitle = Tag.render`
				<p class="calendar-sync__auth-popup--label-text">
					${Loc.getMessage('CAL_ICLOUD_PASS_PLACEHOLDER')}
				</p>
			`;
		}
		
		return this.DOM.appPasswordTitle;
	}
	
	getAppleIdError()
	{
		if (!this.DOM.appleIdError)
		{
			this.DOM.appleIdError = Tag.render`
				<div class="calendar-sync__auth-popup--label-text --error">
					${Loc.getMessage('CAL_ICLOUD_APPLE_ID_ERROR')}
				</div>
			`;
		}
		
		return this.DOM.appleIdError;
	}
	
	getAppPasswordError()
	{
		if (!this.DOM.appPasswordError)
		{
			this.DOM.appPasswordError = Tag.render`
				<div class="calendar-sync__auth-popup--label-text --error">
					${Loc.getMessage(
						'CAL_ICLOUD_APP_PASSWORD_ERROR',
					{
							'#LINK_START#': '<a href="#" data-role="open-helpdesk-password">',
							'#LINK_END#': '</a>',
						}
					)}
				</div>
			`;

			const link = this.DOM.appPasswordError.querySelector('a[data-role="open-helpdesk-password"]');
			if (link)
			{
				Event.bind(link, 'click', this.openHelpDesk.bind(this));
			}
		}

		return this.DOM.appPasswordError;
	}

	getAppleIdInput()
	{
		if (!this.DOM.appleIdInput)
		{
			this.DOM.appleIdInput = Tag.render `
				<input
					type="text"
					placeholder="${Loc.getMessage('CAL_ICLOUD_AUTH_EMAIL_PLACEHOLDER')}"
					class="calendar-field-string ui-ctl-element"
				/>
			`;
			this.DOM.appleIdInput.onfocus = () => {
				if (Dom.hasClass(this.DOM.appleIdInput, 'calendar-field-string-error'))
				{
					Dom.removeClass(this.DOM.appleIdInput, 'calendar-field-string-error');
					Dom.removeClass(this.DOM.appleIdError, 'show');
				}
			};

			this.DOM.appleIdInput.onblur = () => {
				if (
					!this.validateAppleIdInput()
					&& !Dom.hasClass(this.DOM.appleIdInput, 'calendar-field-string-error')
				)
				{
					Dom.addClass(this.DOM.appleIdInput, 'calendar-field-string-error');
					Dom.addClass(this.DOM.appleIdError, 'show');
				}
			};
		}

		return this.DOM.appleIdInput;
	}

	getAppPasswordInput()
	{
		if (!this.DOM.appPasswordInput)
		{
			this.DOM.appPasswordInput = Tag.render `
				<input
					type="password"
					placeholder="${Loc.getMessage('CAL_ICLOUD_AUTH_APPPASS_PLACEHOLDER')}"
					class="calendar-field-string ui-ctl-element"
					required maxlength="19"
				/>
			`;
			Event.bind(this.DOM.appPasswordInput, 'input', this.validateAppPasswordInput.bind(this));
		}

		return this.DOM.appPasswordInput;
	}
	
	getShowHidePasswordIcon()
	{
		if (!this.DOM.showHidePasswordIcon)
		{
			this.DOM.showHidePasswordIcon = Tag.render`
				<div class="ui-ctl-after calendar-sync__auth-popup--icon-adjust-password"></div>
			`;
			Event.bind(this.DOM.showHidePasswordIcon, 'click', this.switchPasswordVisibility.bind(this));
		}
		
		return this.DOM.showHidePasswordIcon;
	}
	
	getLearnMoreButton()
	{
		if (!this.DOM.learnMoreButton)
		{
			this.DOM.learnMoreButton = Tag.render`
				<span class="calendar-sync__auth-popup--learn-more">${Loc.getMessage('CAL_ICLOUD_AUTH_APPPASS_ABOUT')}</span>
			`;
			Event.bind(this.DOM.learnMoreButton, 'click', this.openHelpDesk.bind(this));
		}
		
		return this.DOM.learnMoreButton;
	}

	initAlertBlock()
	{
		if (!this.DOM.alertBlock)
		{
			this.DOM.alertBlock = Tag.render`
				<div class="ui-alert ui-alert-danger calendar-sync__auth-error">
	                <span class="ui-alert-message">${Loc.getMessage('CAL_ICLOUD_AUTH_ERROR')}</span>
				</div>
			`;
		}
	}

	showErrorAuthorizationAlert()
	{
		this.highlightInvalidAppleIdInput();
		this.highlightInvalidPasswordInput();
		this.enableSaveButton();
		if (!this.DOM.container.contains(this.DOM.alertBlock))
		{
			Dom.append(this.DOM.alertBlock, this.DOM.container);
		}
	}
	
	validateAppleIdInput()
	{
		const emailRegExp = /^[a-zA-Z\d.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z\d-]+(?:\.[a-zA-Z\d-]+)*$/;
		const input = this.DOM.appleIdInput.value.toString().trim();
		if (input === '')
		{
			return true;
		}

		return emailRegExp.test(input);
	}
	
	validateAppPasswordInput()
	{
		const appPasswordRegExp = /^[a-z]{4}-[a-z]{4}-[a-z]{4}-[a-z]{4}$/;
		const input = this.completeWithTemplate(this.DOM.appPasswordInput.value.toString().trim());
		if (appPasswordRegExp.test(input))
		{
			Dom.removeClass(this.DOM.appPasswordInput, 'calendar-field-string-error');
			Dom.removeClass(this.DOM.appPasswordError, 'show');
		}
		else
		{
			Dom.addClass(this.DOM.appPasswordInput, 'calendar-field-string-error');
			Dom.addClass(this.DOM.appPasswordError, 'show');
		}
	}
	
	switchPasswordVisibility()
	{
		if (Dom.hasClass(this.DOM.showHidePasswordIcon, '--hide'))
		{
			this.DOM.appPasswordInput.type = 'password';
			Dom.removeClass(this.DOM.showHidePasswordIcon, '--hide');
		}
		else
		{
			this.DOM.appPasswordInput.type = 'text';
			Dom.addClass(this.DOM.showHidePasswordIcon, '--hide');
		}
	}
	
	clearForm()
	{
		this.DOM.appPasswordInput.value = '';
		this.DOM.appleIdInput.value = '';
		if (Dom.hasClass(this.DOM.appleIdInput, 'calendar-field-string-error'))
		{
			Dom.removeClass(this.DOM.appleIdInput, 'calendar-field-string-error');
		}
		if (Dom.hasClass(this.DOM.appPasswordInput, 'calendar-field-string-error'))
		{
			Dom.removeClass(this.DOM.appPasswordInput, 'calendar-field-string-error');
		}
		if (Dom.hasClass(this.DOM.appleIdError, 'show'))
		{
			Dom.removeClass(this.DOM.appleIdError, 'show');
		}
		if (Dom.hasClass(this.DOM.appPasswordError, 'show'))
		{
			Dom.removeClass(this.DOM.appPasswordError, 'show');
		}
	}
	
	completeWithTemplate(password)
	{
		const addition = this.appPasswordTemplate.slice(password.length, this.appPasswordTemplate.length);
		password += addition;
		return password;
	}
	
	openHelpDesk()
	{
		const helpDeskCode = '15426356';
		top.BX.Helper.show('redirect=detail&code=' + helpDeskCode);
	}
	
	handleKeyPress(e)
	{
		if (e.keyCode === Util.getKeyCode('enter'))
		{
			this.authorize();
		}
		else if (e.keyCode === Util.getKeyCode('escape'))
		{
			this.close();
		}
	}
	
	checkOutsideClickClose(e)
	{
		let target = e.target || e.srcElement;
		this.outsideMouseUp = !target.closest('div.popup-window');
		if (this.outsideMouseUp && this.outsideMouseDown && this.checkTopSlider())
		{
			this.close();
		}
	}
	
	outsideMouseDownClose(e)
	{
		let target = e.target || e.srcElement;
		this.outsideMouseDown = !target.closest('div.popup-window');
	}
	
	close()
	{
		if (this.popup)
		{
			this.popup.destroy();
		}
		Event.unbind(document, 'keydown', this.keyHandler);
		Event.unbind(document, 'mouseup', this.checkOutsideClickClose);
		Event.unbind(document, 'mousedown', this.outsideMouseDownClose);
		this.clearForm();
	}
	
	checkTopSlider()
	{
		return !Util.getBX().SidePanel.Instance.getTopSlider();
	}
}
