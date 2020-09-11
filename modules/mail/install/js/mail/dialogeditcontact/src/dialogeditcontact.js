import { Validation } from 'main.core';
import { Tag } from 'main.core';
import { Text } from 'main.core';
import { Loc } from 'main.core';
import { SidePanelWrapper } from 'mail.sidepanelwrapper';
import { MessageBox } from 'ui.dialogs.messagebox';
import './css/style.css';
import '/bitrix/js/ui/forms/ui.forms.css';
import 'ui.forms';

export class DialogEditContact
{
	static getCheckedFields(contentElement)
	{
		const emailItem = contentElement.querySelector('[data-role="email-container"]');
		const emailInput = emailItem.querySelector('[data-role="input"]');
		const email = emailInput.value;

		const nameItem = contentElement.querySelector('[data-role="name-container"]');
		const nameInput = nameItem.querySelector('[data-role="input"]');
		let name = nameInput.value;

		let fieldsAreFilledCorrectly = true;
		let checkedFields = [];

		if (!Validation.isEmail(email))
		{
			fieldsAreFilledCorrectly = false;
			emailItem.showError();
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
		let contactData = {
			NAME: name,
			EMAIL: email,
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

	static showError(item = this)
	{
		let errorTitle = item.querySelector('[data-role="error-title"]');
		errorTitle.style.display = 'block';
	}

	static hideError(item = this)
	{
		let errorTitle = item.querySelector('[data-role="error-title"]');
		errorTitle.style.display = 'none';
	}

	static openDialog(titleText, contactConfig = {})
	{
		let currentEmail = "";
		let currentName = "";

		if (contactConfig['contactData'] !== undefined)
		{
			currentName = Text.encode(contactConfig['contactData']['name']);
			currentEmail = Text.encode(contactConfig['contactData']['email']);
		}

		let content = Tag.render`
			<div>
				<div data-role="name-container" class="mail-addressbook-dialogeditcontact-item">
					<label class="mail-addressbook-dialogeditcontact-lable">${Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_NAME_TITLE")}
						<div id="mail-addressbook-dialogeditcontact-contact-email-container" class="ui-ctl ui-ctl-textbox mail-addressbook-dialogeditcontact-field">
							<input data-role = "input" value="${currentName}" class="ui-ctl-element" placeholder="">
						</div>
					</label>
					<div data-role = "error-title" class="mail-addressbook-dialogeditcontact-contact-error"></div>
				</div>
				<div data-role="email-container" class="mail-addressbook-dialogeditcontact-item">
					<label class="mail-addressbook-dialogeditcontact-lable">${Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_EMAIL_TITLE")}
						<div id="mail-addressbook-dialogeditcontact-contact-email-container" class="ui-ctl ui-ctl-textbox mail-addressbook-dialogeditcontact-field">
							<input data-role = "input" value="${currentEmail}" class="ui-ctl-element" placeholder="info@example.com">
						</div>
					</label>
					<div data-role = "error-title" class="mail-addressbook-dialogeditcontact-contact-error">${Loc.getMessage("MAIL_DIALOG_EDIT_CONTACT_EMAIL_ERROR")}</div>			
				</div>
			</div>`;

		let emailItem = content.querySelector('[data-role="email-container"]');
		emailItem.showError = this.showError;
		emailItem.hideError = this.hideError;

		let nameItem = content.querySelector('[data-role="name-container"]');
		nameItem.showError = this.showError;
		nameItem.hideError = this.hideError;

		let emailInput = emailItem.querySelector('[data-role="input"]');
		emailInput.oninput = () => emailItem.hideError();

		let nameInput = nameItem.querySelector('[data-role="input"]');
		nameInput.oninput = () => nameItem.hideError();

		SidePanelWrapper.open({
			id: 'dialogEditContact',
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

						this.saveContact(checkedFields['name'], checkedFields['email'], contactConfig['contactID']).then(() => {
							BX.SidePanel.Instance.close();
							BX.SidePanel.Instance.postMessageAll('mail:side-panel', 'dialogEditContact::reloadList', {});
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