import { Loc, Tag, Dom, ready, ajax, Type } from 'main.core';
import { Layout } from 'ui.sidepanel.layout';
import { LayoutForm } from 'ui.layout-form';
import './css/style.css';
import 'ui.hint';
import 'ui.alerts';
import { SaveButton } from "ui.buttons";
import { AliasEditor } from './alias-editor';

type SenderData = {
	name: string,
	isPublic: boolean;
	email: string,
	server: string,
	port: number,
	protocol: string,
	limit: null | number,
};

type Options = {
	senderId?: number,
	setSenderCallback?: Function,
	addSenderCallback?: Function,
	onClose?: Function,
};

const SidePanel = BX.SidePanel;
const emailRegularEx = /\S+@\S+\.\S+/;
const deleteMessage = 'mail-mailbox-config-delete';
const senderType = 'sender';

export class SmtpEditor
{
	constructor(options: Options)
	{
		if (options)
		{
			if (options.senderId && Type.isInteger(options.senderId) && options.senderId > 0)
			{
				this.title = Loc.getMessage('UI_MAIL_SMTP_SLIDER_EDIT_TITLE');
				this.senderId = options.senderId;
			}
			else
			{
				this.title = Loc.getMessage('UI_MAIL_SMTP_SLIDER_ADD_TITLE');
			}

			this.setSender = options.setSenderCallback ?? null;
			this.addSender = options.addSenderCallback ?? null;
		}
		this.onCloseAction = options.onClose ?? null;

		this.#createContentContainer();
		this.#prepareNecessaryFields();
	}

	static openSlider(options: Options): void {
		const instance = new SmtpEditor(options);
		SidePanel.Instance.open('smtpSender', {
			width: 760,
			cacheable: false,
			contentCallback: () => {
				return instance.getContentCallback();
			},
			events: {
				onLoad: () => {
					ready(() => {
						new LayoutForm({ container: instance.limitSection });
					});
				},
			},
		});
	}

	getContentCallback(): Layout
	{
		return Layout.createContent({
			extensions: [
				'ui.mail.sender-editor',
			],
			title: this.title,
			design: {
				section: false,
				margin: false,
			},
			content: () => {
				if (this.senderId > 0)
				{
					return this.loadSender(this.senderId);
				}

				return ajax.runAction('main.api.mail.sender.getDefaultSenderName')
					.then((response) => {
						this.#setUserName(response.data);

						return this.getContentContainer();
					})
					.catch(() => {
						return this.getContentContainer();
					})
				;
			},
			buttons: ({ cancelButton, Button }) => {
				const buttonArray = [];
				const saveButton = new SaveButton({
					onclick: () => {
						this.#save(saveButton);
					},
				});
				buttonArray.push(saveButton);

				if (this.senderId > 0)
				{
					this.disconnectButton = new Button({
						text: Loc.getMessage('UI_MAIL_SMTP_SLIDER_DISCONNECT_BUTTON'),
						color: BX.UI.Button.Color.DANGER,
						onclick: () => {
							this.#showDisconnectDialog();
						},
					});
					buttonArray.push(this.disconnectButton);
				}
				buttonArray.push(cancelButton);

				return buttonArray;
			},
		});
	}

	loadSender(senderId: number): Promise
	{
		return ajax.runAction(
			'main.api.mail.sender.getSenderData',
			{
				data: { senderId },
			},
		).then((response) => {
			this.#setFieldData(response.data);

			return this.getContentContainer();
		}).catch(() => {
			return this.getContentContainer();
		});
	}

	#setFieldData(senderData: SenderData): void
	{
		this.nameField.value = senderData.name;
		this.accessField.checked = senderData.isPublic;
		this.emailField.value = senderData.email;
		this.serverField.value = senderData.server;
		this.portField.value = senderData.port;
		if (senderData.protocol === 'smtps')
		{
			this.sslField.checked = true;
		}

		if (Type.isNumber(senderData.limit) && senderData.limit > 0)
		{
			this.senderLimitCheckbox.checked = true;
			this.senderLimitField.value = senderData.limit;
		}
	}

	#showDisconnectDialog(): void
	{
		top.BX.UI.Dialogs.MessageBox.show({
			message: Loc.getMessage('UI_MAIL_SMTP_SLIDER_DISCONNECT_MESSAGE'),
			modal: true,
			buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
			onOk: (messageBox) => {
				this.#disconnect();
				messageBox.close();
			},
			onCancel: (messageBox) => {
				messageBox.close();
			},
		});
	}

	#save(button: SaveButton): void
	{
		this.#clearInvalidFields();
		if (this.#hasInvalidFields())
		{
			return;
		}
		this.#hideAlertNode();
		button.setClocking();
		this.#saveSender()
			.then((response) => {
				const data = response.data;
				if (this.setSender)
				{
					this.setSender(data.senderId, data.name, this.email);
				}

				if (this.addSender)
				{
					const mailbox = [];
					mailbox.name = data.name;
					mailbox.email = this.email;
					this.addSender(mailbox);
				}

				BX.SidePanel.Instance.getTopSlider().close();
				if (!this.senderId && !this.addSender)
				{
					AliasEditor.openSlider({
						senderId: data.senderId,
						email: this.email,
						setSenderCallback: this.setSender,
						onClose: this.onCloseAction,
					});
				}
			})
			.catch((response) => {
				this.#showAlertNode(response.errors[0].message);
				button.setClocking(false);
			})
		;
	}

	#disconnect(): void
	{
		Dom.addClass(this.disconnectButton, 'ui-btn-wait');
		ajax.runAction(
			'main.api.mail.sender.deleteSender',
			{
				data: {
					senderId: this.senderId,
				},
			},
		).then(() => {
			Dom.removeClass(this.disconnectButton, 'ui-btn-wait');
			SidePanel.Instance.getTopSlider().close();
			top.BX.SidePanel.Instance.postMessage(
				window,
				deleteMessage,
				{
					id: this.senderId,
					type: senderType,
				},
			);
		}).catch(() => {
			Dom.removeClass(this.disconnectButton, 'ui-btn-wait');
		});
	}

	#saveSender(): Promise
	{
		this.email = this.emailField.value;

		const data = {
			id: this.senderId ?? null,
			name: this.nameField.value,
			email: this.email,
			smtp: {},
			public: this.accessField.checked ? 'Y' : 'N',
		};

		data.smtp = {
			server: this.serverField.value,
			port: this.portField.value,
			ssl: this.sslField.checked ? this.sslField.value : '',
			login: this.emailField.value,
			password: this.passwordField.value,
			limit: this.senderLimitCheckbox.checked ? this.senderLimitField.value : null,
		};

		return ajax.runAction('main.api.mail.sender.submitSender', {
			data: { data },
		}).then((response) => {
			return response;
		});
	}

	#createContentContainer(): void
	{
		this.#createAlertNode();
		this.#createSenderSection();
		this.#createSmtpServerSection();
		this.#createLimitSection();

		this.contentContainer = Tag.render`
			<div class="ui-form">
				${this.alertNode}
				${this.senderSection}
				${this.smtpServerSection}
				${this.limitSection}
			</div>
		`;
	}

	getContentContainer(): HTMLElement
	{
		return this.contentContainer;
	}

	#createAlertNode(): void
	{
		this.alertNode = Tag.render`
			<div class="ui-alert ui-alert-danger ui-alert-icon-warning" style="display: none">
				<span class="ui-alert-message"></span>
			</div>
		`;
	}

	#createSenderSection(): void
	{
		const { root, nameField, accessField } = Tag.render`
			<div class="ui-slider-section">
				<div class="ui-slider-content-box">
					<div class="ui-slider-heading-4">${Loc.getMessage('UI_MAIL_SMTP_SLIDER_SENDER_MAIN_SECTION_TITLE')}</div>
					<div class="ui-form-row">
						<div class="ui-ctl-top smtp-sender-name">
							<div class="ui-form-label">${Loc.getMessage('UI_MAIL_SMTP_SLIDER_SENDER_NAME')}</div>
							<span data-hint="${Loc.getMessage('UI_MAIL_SMTP_SLIDER_NAME_HINT')}"></span>
						</div>
						<div class="ui-form-row-inline ui-ctl-w100">
							<div class="ui-form-row">
								<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
									<input type="text" data-name="name" value="" class="ui-ctl-element" ref="nameField">
								</div>
							</div>
							<div class="ui-form-row">
								<label class="ui-ctl ui-ctl-checkbox">
									<input type="checkbox" class="ui-ctl-element" data-name="access" ref="accessField">
									<div class="ui-ctl-label-text">${Loc.getMessage('UI_MAIL_SMTP_SLIDER_SENDER_AVAILABLE_TOGGLE')}</div>
									<span data-hint="${Loc.getMessage('UI_MAIL_SMTP_SLIDER_SENDER_AVAILABLE_TOGGLE_HINT')}"></span>
								</label>
							</div>
						</div>
					</div>
				</div>
			</div>
		`;
		this.senderSection = root;
		this.nameField = nameField;
		this.accessField = accessField;

		this.hintInstence = top.BX.UI.Hint?.createInstance();
		this.hintInstence.init(this.senderSection);
	}

	#createSmtpServerSection(): void
	{
		this.#createSmtpEmailRow();
		this.#createSmtpServerRow();
		this.#createSmtpPortAndSafeConnectionRow();
		this.#createSmtpPasswordRow();

		this.smtpServerSection = Tag.render`
			<div class="ui-slider-section">
				<div class="ui-slider-content-box">
					<div class="ui-slider-heading-4">${Loc.getMessage('UI_MAIL_SMTP_SLIDER_SMTP_SECTION_TITLE')}</div>
					${this.smtpServerRow}
					${this.smtpPortAndSafeConnectionRow}
					${this.smtpEmailRow}
					${this.smtpPasswordRow}
				</div>
			</div>
		`;
	}

	#createSmtpEmailRow(): void
	{
		const { root, emailField } = Tag.render`
			<div class="ui-form-row">
				<div class="ui-ctl-top">
					<div class="ui-form-label">${Loc.getMessage('UI_MAIL_SMTP_SLIDER_EMAIL')}</div>
				</div>
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
					<input type="email" name="email" class="ui-ctl-element" data-name="email" placeholder="info@example.com" ref="emailField">
				</div>
			</div>
		`;

		this.smtpEmailRow = root;
		this.emailField = emailField;
	}

	#createSmtpServerRow(): void
	{
		const { root, serverField } = Tag.render`
			<div class="ui-form-row">
				<div class="ui-ctl-top">
					<div class="ui-form-label">${Loc.getMessage('UI_MAIL_SMTP_SLIDER_SERVER')}</div>
				</div>
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
					<input type="text" name="server" class="ui-ctl-element" data-name="server" placeholder="smtp.example.com" ref="serverField">
				</div>
			</div>
		`;

		this.smtpServerRow = root;
		this.serverField = serverField;
	}

	#createSmtpPortAndSafeConnectionRow(): void
	{
		const { root, portField, sslField } = Tag.render`
			<div class="ui-form-row">
				<div class="ui-ctl-top">
					<div class="ui-form-label">${Loc.getMessage('UI_MAIL_SMTP_SLIDER_PORT')}</div>
				</div>
				<div class="ui-form-row-inline" style="margin-bottom: 0">
					<div class="ui-form-row">
						<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
						<input type="text" 
							class="ui-ctl-element" 
							data-name="port" 
							placeholder="555"
							ref="portField"
						>
						</div>
					</div>
					<div class="ui-form-row">
						<label class="ui-ctl ui-ctl-checkbox">
							<input type="checkbox" class="ui-ctl-element" value="Y" data-name="ssl" ref="sslField">
							<div class="ui-ctl-label-text">${Loc.getMessage('UI_MAIL_SMTP_SLIDER_SSL')}</div>
						</label>
					</div>
				</div>
			</div>
		`;

		this.smtpPortAndSafeConnectionRow = root;
		this.portField = portField;
		this.sslField = sslField;
	}

	#createSmtpPasswordRow(): void
	{
		const { root, passwordField } = Tag.render`
			<div class="ui-form-row">
				<div class="ui-ctl-top">
					<div class="ui-form-label">${Loc.getMessage('UI_MAIL_SMTP_SLIDER_PASSWORD')}</div>
				</div>
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
					<input type="password" class="ui-ctl-element" data-name="password" ref="passwordField">
				</div>
			</div>
		`;

		this.smtpPasswordRow = root;
		this.passwordField = passwordField;
	}

	#createLimitSection(): void
	{
		const { root, senderLimitCheckbox, senderLimitField } = Tag.render`
			<div class="ui-slider-section">
				<div class="ui-slider-content-box">
					<div class="ui-slider-heading-4">${Loc.getMessage('UI_MAIL_SMTP_SLIDER_SENDER_LIMIT_SECTION_TITLE')}</div>
					<div class="ui-form-row">
						<div class="ui-form-label" data-form-row-hidden="">
							<label class="ui-ctl ui-ctl-checkbox smtp-editor-limit-checkbox">
								<input type="checkbox" class="ui-ctl-element" data-name="hasLimit" ref="senderLimitCheckbox">
								<div class="ui-ctl-label-text">${Loc.getMessage('UI_MAIL_SMTP_SLIDER_SENDER_LIMIT_SETTINGS')}</div>
							</label>
						</div>
						<div class="ui-form-row-hidden">
							<div class="ui-form-row">
								<div class="ui-ctl-top">
									<div class="ui-form-label">${Loc.getMessage('UI_MAIL_SMTP_SLIDER_SENDER_LIMIT_TITLE')}</div>
								</div>
								<div class="ui-ctl ui-ctl-textbox ui-ctl-w25">
									<input type="number" class="ui-ctl-element" data-name="limit" value="250" min="0" ref="senderLimitField">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		`;

		this.limitSection = root;
		this.senderLimitCheckbox = senderLimitCheckbox;
		this.senderLimitField = senderLimitField;
	}

	#showAlertNode(message: string = null): void
	{
		if (message)
		{
			const spanNode = this.alertNode.querySelector('span');
			spanNode.textContent = message;
		}

		Dom.style(this.alertNode, 'display', 'block');
	}

	#hideAlertNode(): void
	{
		Dom.style(this.alertNode, 'display', 'none');
	}

	#prepareNecessaryFields(): void
	{
		this.requiredFields = [
			{ row: this.smtpEmailRow, input: this.emailField, type: 'email' },
			{ row: this.smtpServerRow, input: this.serverField, type: 'server' },
			{ row: this.smtpPortAndSafeConnectionRow, input: this.portField, type: 'port' },
		];

		if (!this.senderId)
		{
			this.requiredFields.push({
				row: this.smtpPasswordRow,
				input: this.passwordField,
				type: 'pass',
			});
		}
	}

	#hasInvalidFields(): boolean
	{
		let count = 0;
		this.requiredFields.forEach((field) => {
			if (!this.#isInvalidField(field.type, field.input.value))
			{
				return;
			}
			count++;
			Dom.addClass(field.row, 'ui-ctl-warning');
			const errorMessage = this.#getErrorMessage(field.type, field.input.value);
			const invalidField = Tag.render`
				<div class="ui-mail-field-error-message ui-ctl-bottom">${errorMessage}</div>
			`;
			Dom.append(invalidField, field.row);

			if (this.topEmptyNode)
			{
				return;
			}
			this.topEmptyNode = field.row;
			this.topEmptyNode.scrollIntoView();
		});

		return count > 0;
	}

	#clearInvalidFields(): void
	{
		if (!this.requiredFields)
		{
			return;
		}

		this.requiredFields.forEach((field) => {
			Dom.removeClass(field.row, 'ui-ctl-warning');
			const errorMessageFiled = field.row.querySelector('.ui-mail-field-error-message');
			if (Type.isDomNode(errorMessageFiled))
			{
				Dom.remove(errorMessageFiled);
			}
		});
		this.topEmptyNode = null;
		this.invalidFieldNode?.remove();
	}

	#isInvalidField(type: string, input: string | number): boolean
	{
		if (input.length === 0)
		{
			return true;
		}

		if (type === 'port'
			&& (
				!Number.isInteger(Number(input))
				|| input < 0
				|| input > 65535
			)
		)
		{
			return true;
		}

		return type === 'email' && !emailRegularEx.test(input);
	}

	#getErrorMessage(type: string, input: string | number): string
	{
		switch (type)
		{
			case 'email':
				if (Type.isString(input) && input.length > 0)
				{
					return Loc.getMessage('UI_MAIL_SMTP_SLIDER_INVALID_EMAIL');
				}

				return Loc.getMessage('UI_MAIL_SMTP_SLIDER_EMPTY_EMAIL');
			case 'server':
				return Loc.getMessage('UI_MAIL_SMTP_SLIDER_EMPTY_SERVER');
			case 'port':
				return Loc.getMessage('UI_MAIL_SMTP_SLIDER_INVALID_PORT');
			default:
				return Loc.getMessage('UI_MAIL_SMTP_SLIDER_EMPTY_PASSWORD');
		}
	}

	#setUserName(name: string)
	{
		this.nameField.value = name;
	}
}
