import { Tag, Loc, Validation } from 'main.core';
import { SidePanelWrapper } from 'mail.sidepanelwrapper';
import { MessageBox } from 'ui.dialogs.messagebox';
import { Avatar } from 'mail.avatar';

import './css/style.css';
import '/bitrix/js/ui/forms/ui.forms.css';
import 'ui.forms';
import 'ui.alerts';

export class DialogEditContact
{
	static getCheckedFields(contentElement)
	{
		const emailItem = contentElement.querySelector('[data-role="email-container"]');
		const emailInput = emailItem.querySelector('[data-role="input-field"]');
		const email = emailInput.value;

		const nameItem = contentElement.querySelector('[data-role="name-container"]');
		const nameInput = nameItem.querySelector('[data-role="input-field"]');
		let name = nameInput.value;

		let fieldsAreFilledCorrectly = true;
		let checkedFields = [];

		if (!Validation.isEmail(email))
		{
			fieldsAreFilledCorrectly = false;
			emailItem.showError(0);
		}
		else if (name.length < 1)
		{
			name = email.split('@')[0];
		}

		checkedFields = {
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

	static saveContact(name, email, id)
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

	static hideError(id= 'all')
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

	static openDialog(titleText, contactConfig = {
		contactID: 'new'
	})
	{
		const sliderId = 'dialogEditContact_' + contactConfig['contactID'];

		let currentEmail = '';
		let currentName = '';
		let disablingEmailInputClass = '';
		let disablingEmailInputAttribute = '';

		if (contactConfig['contactData'] !== undefined)
		{
			currentName = contactConfig['contactData']['name'];
			currentEmail = contactConfig['contactData']['email'];
			disablingEmailInputClass = 'ui-ctl-disabled';
			disablingEmailInputAttribute = 'disabled';
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

		const openEditSliderBtn = errorTitleEmailIsAlreadyExists.querySelector('[data-role="contact-email"]')

		const emailItem = Tag.render`<div data-role="email-container" class="mail-addressbook-dialogeditcontact-item">
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
			${emailItem}
		</div>`;

		emailItem.errorTitle = [
			errorTitleEmailIsIncorrect,
			errorTitleEmailIsAlreadyExists
		];

		emailItem.emailInputWrapper = emailInputWrapper;
		emailItem.showError = this.showError;
		emailItem.hideError = this.hideError;
		emailItem.hideError();

		emailInput.oninput = () => emailItem.hideError();

		SidePanelWrapper.open({
			id: sliderId,
			titleText: titleText,
			footerIsActive: true,
			content: content,
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
							BX.SidePanel.Instance.postMessageAll(sliderId, 'dialogEditContact::reloadList', {});
							BX.SidePanel.Instance.close();
						}).catch((response) => {
							const message = response.errors.pop().message.pop();
							if(message['ID'])
							{
								eventObject.setClocking(false);
								openEditSliderBtn.onclick = () => {
									this.openEditDialog({
										contactID: Number(message['ID']),
										contactData: {
											name: message['NAME'],
											email: message['EMAIL'],
										},
									})
								};
								emailItem.showError(1);
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
	}

	static openCreateDialog(config)
	{
		this.openDialog(Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_TITLE_BAR_ADD"), config);
	}

	static openEditDialog(config = {
		contactID: '',
		contactData: {
			name: '',
			email: '',
		},
	})
	{
		this.openDialog(Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_TITLE_BAR_EDIT"), config);
	}
}