import { Tag, Loc, Validation } from 'main.core';
import { SidePanelWrapper } from 'mail.sidepanelwrapper';
import { MessageBox } from 'ui.dialogs.messagebox';
import { Avatar } from 'mail.avatar';
import { BaseEvent, EventEmitter } from "main.core.events";

import './css/style.css';
import '/bitrix/js/ui/forms/ui.forms.css';
import 'ui.forms';
import 'ui.alerts';

export class DialogEditContact
{
	static getCheckedFields(contentElement)
	{
		const emailContainer = contentElement.querySelector('[data-role="email-container"]');
		const emailInput = emailContainer.querySelector('[data-role="input-field"]');
		const email = emailInput.value;

		const nameItem = contentElement.querySelector('[data-role="name-container"]');
		const nameInput = nameItem.querySelector('[data-role="input-field"]');
		let name = nameInput.value;

		let fieldsAreFilledCorrectly = true;

		if (!Validation.isEmail(email))
		{
			fieldsAreFilledCorrectly = false;
			emailContainer.showError(0);
		}
		else if (name.length < 1)
		{
			name = email.split('@')[0];
		}

		const checkedFields = {
			name: name,
			email: email,
		};

		if (fieldsAreFilledCorrectly)
		{
			return checkedFields;
		}

		return false;
	}

	static openRemoveDialog(config = {
		id: '',
	})
	{
		let promiseRemoveContact = new BX.Promise();
		let removeContact = this.removeContact;
		const topSlider = BX.SidePanel.Instance.getTopSlider();
		let messageBoxZIndex = 1;

		if (topSlider != null)
		{
			messageBoxZIndex += topSlider.getZindex();
		}

		const messageBox = new MessageBox({
			title: Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_REMOVE_DIALOG_TITLE"),
			message: Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_REMOVE_DIALOG_MESSAGE"),
			buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
			popupOptions: {
				zIndex: messageBoxZIndex,
			},
			onOk: function() {
				removeContact(config['id']).then(() => promiseRemoveContact.fulfill());
				messageBox.close();
			},
			onCancel: function() {
				promiseRemoveContact.reject();
				messageBox.close();
			},
		});

		messageBox.show();

		return promiseRemoveContact;
	}

	static removeContact(id)
	{
		return BX.ajax.runAction('mail.addressbook.removecontacts', {
			data: {
				idSet: [id],
			},
		});
	}

	static saveContact(name, email, id = 'new')
	{
		const data = Avatar.getAvatarData({
			fullName: name,
			email: email,
		});

		let contactData = {
			NAME: name,
			EMAIL: email,
			COLOR: data['color'],
			INITIALS: data['abbreviation'],
		};

		if (id !== undefined)
		{
			contactData['ID'] = id;
		}

		return BX.ajax.runAction('mail.addressbook.savecontact', {
			data: {
				contactData: contactData,
			},
		});
	}

	static showError(id=0)
	{
		this.emailInputWrapper.classList.add('ui-ctl-danger');
		BX.show(this.errorTitle[id]);
	}

	static hideError(id = 'all')
	{
		this.emailInputWrapper.classList.remove('ui-ctl-danger');
		if(id === 'all'){
			this.errorTitle.forEach(element => {
				BX.hide(element)
			});
			return;
		}
		BX.hide(this.errorTitle[id]);
	}

	static showErrorEmailAlreadyExists(responseError, errorAlreadyExistLink, emailContainer): boolean
	{
		const contact = this.getLastOfMatchingContactFromError(responseError);

		const {
			contactID,
			contactData,
		} = contact;

		if(contact !== null)
		{
			errorAlreadyExistLink.onclick = () => {
				this.openEditDialog({
					contactID,
					contactData,
				})
			};
			emailContainer.showError(1);

			return true;
		}

		return false;
	}

	static openDialog(titleText, contactConfig)
	{
		const {
			contactID = 'new',
			showEmailError = false,
			prefixId,
			contactData,
			responseError,
		} = contactConfig;

		let sliderId = 'dialogEditContact_' + contactID;

		if (prefixId !== undefined)
		{
			sliderId += '_' + prefixId;
		}

		let currentEmail = '';
		let currentName = '';
		let disablingEmailInputClass = '';
		let disablingEmailInputAttribute = '';

		if (contactData !== undefined)
		{
			currentName = contactData.name;
			currentEmail = contactData.email;

			if (contactID !== 'new')
			{
				disablingEmailInputClass = 'ui-ctl-disabled';
				disablingEmailInputAttribute = 'disabled';
			}
		}

		const emailInput = Tag.render`<input data-role="input-field" type="text" class="ui-ctl-element" value="" placeholder="info@example.com"  ${disablingEmailInputAttribute}>`;
		const emailInputWrapper = Tag.render`<div class="ui-ctl ui-ctl-textbox ui-ctl-w100 ${disablingEmailInputClass}">
			${emailInput}
		</div>`;
		emailInput.value = currentEmail;

		const errorTitleEmailIsIncorrect = Tag.render`<div class="ui-alert ui-alert-danger mail-addressbook-error-box">
			<span class="ui-alert-message">${Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_EMAIL_ERROR")}</span>
		</div>`;

		const errorTitleEmailIsAlreadyExists = Tag.render`<div class="ui-alert ui-alert-danger mail-addressbook-error-box">
			<span class="ui-alert-message">${Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_EMAIL_ERROR_EMAIL_IS_ALREADY_EXISTS")}</span>
			<br>
		</div>`;

		const errorAlreadyExistLink = errorTitleEmailIsAlreadyExists.querySelector('[data-role="contact-email"]')

		const emailContainer = Tag.render`<div data-role="email-container" class="mail-addressbook-dialogeditcontact-item">
			<label class="mail-addressbook-dialogeditcontact-lable">${Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_EMAIL_TITLE")}
				<div id="mail-addressbook-dialogeditcontact-contact-email-container" class="ui-ctl ui-ctl-textbox mail-addressbook-dialogeditcontact-field">
					${emailInputWrapper}
				</div>
			</label>
			${errorTitleEmailIsIncorrect}
			${errorTitleEmailIsAlreadyExists}
		</div>`;

		const nameInput = Tag.render`<input data-role="input-field" type="text" class="ui-ctl-element" value="" placeholder="">`;
		const nameInputWrapper = Tag.render`<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
			${nameInput}
		</div>`;
		nameInput.value = currentName;

		const nameItem = Tag.render`<div data-role="name-container" class="mail-addressbook-dialogeditcontact-item">
			<label class="mail-addressbook-dialogeditcontact-lable">${Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_NAME_TITLE")}
				<div id="mail-addressbook-dialogeditcontact-contact-email-container" class="ui-ctl ui-ctl-textbox mail-addressbook-dialogeditcontact-field">
					${nameInputWrapper}
				</div>
			</label>
		</div>`;

		let content = Tag.render`
		<div>
			${nameItem}
			${emailContainer}
		</div>`;

		emailContainer.errorTitle = [
			errorTitleEmailIsIncorrect,
			errorTitleEmailIsAlreadyExists
		];

		emailContainer.emailInputWrapper = emailInputWrapper;
		emailContainer.showError = this.showError;
		emailContainer.hideError = this.hideError;
		emailContainer.hideError();

		if (showEmailError === true)
		{
			emailContainer.showError();
		}

		emailInput.oninput = () => emailContainer.hideError();

		if(responseError !== undefined)
		{
			this.showErrorEmailAlreadyExists(responseError, errorAlreadyExistLink, emailContainer);
		}

		SidePanelWrapper.open({
			id: sliderId,
			titleText: titleText,
			footerIsActive: true,
			content,
			cancelButton: {
				text: Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_BUTTON_CANCEL"),
			},
			consentButton: {
				text: Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_BUTTON_SAVE"),
				function: (eventObject) => {
					const checkedFields = this.getCheckedFields(content);

					if (checkedFields)
					{
						eventObject.setClocking(true);

						this.saveContact(checkedFields['name'], checkedFields['email'], contactConfig['contactID']).then((response) => {

							EventEmitter.emit('BX.DialogEditContact:onSaveContact', new BaseEvent(
								{
									data: {
										items: response.data,
										prefixId: contactConfig.prefixId,
									},
								})
							);

							BX.SidePanel.Instance.postMessageAll(sliderId, 'dialogEditContact::reloadList', {});
							BX.SidePanel.Instance.close();
						}).catch((response) => {
							if(this.showErrorEmailAlreadyExists(response, errorAlreadyExistLink, emailContainer))
							{
								eventObject.setClocking(false);
							}
							else
							{
								BX.SidePanel.Instance.postMessageAll(sliderId, 'dialogEditContact::reloadList', {});
								BX.SidePanel.Instance.close();
							}
						});
					}
				},
			},
		});

		return contactID;
	}

	static openCreateDialog(config = {})
	{
		const {
			responseError,
			prefixId,
		} = config;

		if(responseError !== undefined)
		{
			const contact = this.getLastOfMatchingContactFromError(responseError);
			return this.openEditDialog({
				prefixId,
				...contact,
			});
		}

		return this.openDialog(Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_TITLE_BAR_ADD_MSGVER_1"), config);

	}

	static getLastOfMatchingContactFromError(responseError)
	{
		let lastMessage = null;

		for (const error of responseError.errors)
		{
			if (error.code === 'ALL_CONTACTS_ALREADY_ADDED')
			{
				lastMessage = error.customData.lastFound[0];
				break;
			}
		}

		if(lastMessage !== null)
		{
			return {
				contactID: Number(lastMessage['ID']),
				contactData: {
					name: lastMessage['NAME'],
					email: lastMessage['EMAIL'],
				}
			};
		}

		return null;
	}

	static openEditDialog(config = {
		contactID: '',
		contactData: {
			name: '',
			email: '',
		},
	})
	{
		return this.openDialog(Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_TITLE_BAR_EDIT_MSGVER_1"), config);
	}
}