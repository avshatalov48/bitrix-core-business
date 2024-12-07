import { Layout } from 'ui.sidepanel.layout';
import { ajax, Dom, Event, Loc, Tag, Text } from 'main.core';
import { SmtpEditor } from './smtp-editor';
import { Button } from 'ui.buttons';
import './css/style.css';
import 'ui.forms';
import 'ui.layout-form';
import 'ui.sidepanel-content';
import 'ui.entity-selector';
import 'ui.icon-set.actions';
import 'ui.icon-set.main';

type Sender = {
	id: number,
	name: string,
	userId: number | null,
	isOwner: boolean,
	type: string,
	mailboxId: number | null,
	canEdit: boolean,
	editHref: string | null,
	avatar: string | null,
	userUrl: string | null,
}

type Options = {
	senderId: number,
	email: string,
	setSenderCallback?: Function,
	updateSenderList?: () => void,
}

const mailboxType = 'mailbox';
const senderType = 'sender';
const mailboxSenderType = 'mailboxSender';
const aliasType = 'alias';
const successSubmitMessage = 'mail-mailbox-config-success';
const deleteMessage = 'mail-mailbox-config-delete';
const aliasSliderUrl = 'mailAliasSlider';

export class AliasEditor
{
	wasSenderUpdated: boolean = false;
	aliasCounter: number = 0;
	#senderNameNodes: Map = new Map();
	constructor(options: Options)
	{
		this.senderId = Number(options.senderId);
		this.email = options.email;
		this.setSender = options.setSenderCallback;
		this.updateSenderList = options.updateSenderList;
		this.#createContentContainer();
		this.#createToolbarButtons();
	}

	static openSlider(options: Options): void
	{
		const instance = new AliasEditor(options);
		const onSliderMessage = function(event) {
			const [sliderEvent] = event.getData();
			if (!sliderEvent)
			{
				return;
			}

			const eventMessage = sliderEvent.getEventId();
			const data = sliderEvent.getData();
			const mailboxId = Number(sliderEvent.data.id);
			const slider = BX.SidePanel.Instance.getSlider(aliasSliderUrl);
			if (eventMessage === successSubmitMessage)
			{
				instance.wasSenderUpdated = true;
				instance.updateMainSenderName(mailboxId);

				if (slider)
				{
					slider.close();
				}

				return;
			}

			if (eventMessage === deleteMessage)
			{
				instance.wasSenderUpdated = true;
				if (instance.id === Number(mailboxId))
				{
					instance.setSender();
				}

				if (slider)
				{
					slider.close();
				}

				if (data && data.type !== senderType)
				{
					BX.SidePanel.Instance.postMessage(window, sliderEvent.getEventId(), sliderEvent.getData);
				}
			}
		};
		BX.SidePanel.Instance.open(aliasSliderUrl, {
			width: 800,
			cacheable: false,
			contentCallback: () => {
				return Layout.createContent({
					extensions: [
						'ui.mail.sender-editor',
					],
					title: options.email,
					design: {
						section: false,
						margin: false,
					},
					content(): Promise
					{
						return instance.loadSliderContent();
					},
					toolbar(): Array
					{
						return instance.getToolbarButtons();
					},
					buttons: () => {},
				});
			},
			events: {
				onClose: () => {
					top.BX.Event.EventEmitter.unsubscribe('SidePanel.Slider:onMessage', onSliderMessage);
					if (instance.updateSenderList && instance.wasSenderUpdated)
					{
						instance.updateSenderList();
					}
				},
			},
		});

		top.BX.Event.EventEmitter.subscribe('SidePanel.Slider:onMessage', onSliderMessage);
	}

	getContentContainer(): HTMLElement
	{
		return this.contentContainer;
	}

	getToolbarButtons(): Array
	{
		const buttons = [];

		if (this.settingsButton)
		{
			buttons.push(this.settingsButton);
		}

		return buttons;
	}

	loadSliderContent(): Promise
	{
		return BX.ajax.runAction(
			'main.api.mail.sender.getSenderTransitionalData',
			{
				data: { senderId: this.senderId },
			},
		).then((response) => {
			const data = response.data;
			const senders = data.senders ?? null;
			this.id = Number(data.id);
			this.email = data.email;
			this.#addSenders(senders);

			const type = data.type || null;
			switch (type)
			{
				case mailboxType:
					this.settingsButton.bindEvent('click', () => {
						this.#openMailboxSettings(data.href);
					});
					break;
				case senderType:
					this.settingsButton.bindEvent('click', () => {
						this.#openSmtpSettings(data.id);
					});
					break;
				default:
					this.settingsButton.setDisabled();
					break;
			}

			return this.getContentContainer();
		}).catch(() => {
			this.settingsButton.setDisabled();

			return this.getContentContainer();
		});
	}

	#createContentContainer(): void
	{
		this.senderList = Tag.render`
			<div class="mail-sender-list"></div>
		`;

		this.#createAddSenderContainer();

		this.contentContainer = Tag.render`
			<div class="ui-form">
				<div class="ui-slider-section">
					<div class="ui-slider-content-box" style="margin-bottom: 0">
						<div class="ui-slider-heading-4 sender-list-header">${Text.encode(Loc.getMessage('UI_MAIL_ALIAS_SLIDER_EMAIL_TITLE'))}</div>
						${this.senderList}
						${this.addSenderContainer}
					</div>
				</div>
			</div>
		`;
	}

	#createAddSenderContainer(): void
	{
		this.senderInput = Tag.render`
			<input type="text" class="ui-ctl-element" data-name="aliasName" placeholder="${Text.encode(Loc.getMessage('UI_MAIL_ALIAS_SLIDER_ADD_INPUT_PLACEHOLDER'))}">
		`;
		this.senderInputContainer = Tag.render`
			<div class="add-sender-input-container" hidden>
				<div class="ui-ctl ui-ctl-textbox ui-ctl-default-light ui-ctl-sm ui-ctl-w100">
					${this.senderInput}
				</div>
			</div>
		`;

		Dom.append(
			this.#renderSubmitButton(
				() => {
					return this.#addAliasPromise();
				},
				this.senderInput,
			),
			this.senderInputContainer,
		);
		Dom.append(
			this.#renderCancelButton(() => {
				Dom.hide(this.senderInputContainer);
				Dom.show(this.senderAddButton);
				this.senderInput.value = null;
			}),
			this.senderInputContainer,
		);

		this.senderAddButton = Tag.render`
			<div class="add-sender-button">${Text.encode(Loc.getMessage('UI_MAIL_ALIAS_SLIDER_ADD_BUTTON'))}</div>
		`;
		Event.bind(this.senderAddButton, 'click', () => {
			Dom.hide(this.senderAddButton);
			Dom.show(this.senderInputContainer);
			this.senderInput.focus();
		});

		this.addSenderContainer = Tag.render`
			<div class="add-sender-container">
				${this.senderInputContainer}
				${this.senderAddButton}
			</div>
		`;
	}

	#addAliasPromise(): Promise
	{
		return new Promise((resolve) => {
			const hideInputContainer = () => {
				Dom.hide(this.senderInputContainer);
				Dom.show(this.senderAddButton);
				this.senderInput.value = null;
				resolve();
			};

			if (this.senderInput.value.trim().length === 0)
			{
				hideInputContainer();

				return;
			}

			if (this.#hasNameInvalidCharacters(this.senderInput.value.trim()))
			{
				resolve();

				return;
			}

			const name = this.senderInput.value;
			ajax.runAction(
				'main.api.mail.sender.addAlias',
				{
					data: {
						name,
						email: this.email,
					},
				},
			).then((response) => {
				const data = response.data;
				const newSenderId = data.senderId;
				if (this.setSender && data.senderId)
				{
					this.setSender(data.senderId, name, this.email);
				}
				this.wasSenderUpdated = true;
				this.senderId = newSenderId;
				const senderNode = this.#renderSenderItem({
					id: newSenderId,
					name,
					isOwner: true,
					type: aliasType,
					canEdit: true,
					userUrl: data.userUrl ?? null,
					avatar: data.avatar ?? null,
				});
				Dom.append(senderNode, this.senderList);
				this.aliasCounter++;
				hideInputContainer();
			}).catch(() => {
				hideInputContainer();
			});
		});
	}

	#createToolbarButtons(): void
	{
		this.settingsButton = new Button({
			text: Loc.getMessage('UI_MAIL_ALIAS_SLIDER_MAIL_SETTINGS_BUTTON'),
			icon: Button.Icon.SETTING,
			color: Button.Color.LIGHT_BORDER,
		});
	}

	#renderSenderItem(sender: Sender): HTMLElement
	{
		const itemContainer = Tag.render`<div class="sender-list-item"></div>`;
		const {
			root: nameContainer,
			textNode: nameTextContainer,
		} = this.#renderSenderNameContainer(sender.name);
		let handleShowEditInput = null;

		if (sender.canEdit)
		{
			const {
				nameEditContainer,
				editInput: nameEditInput,
			} = this.#renderSenderEditNode(sender, nameTextContainer);
			Dom.append(nameEditContainer, nameContainer);
			handleShowEditInput = () => {
				nameEditInput.value = nameContainer.innerText;
				Dom.hide(nameTextContainer);
				Dom.show(nameEditContainer);
				nameEditInput.focus();
			};

			Event.bind(nameTextContainer, 'click', handleShowEditInput);
		}
		Dom.append(nameContainer, itemContainer);
		Dom.append(this.#renderSenderExtraInfoContainer(sender), itemContainer);
		Dom.append(this.#renderSenderAuthorContainer(sender, itemContainer), itemContainer);
		Dom.append(this.#renderSenderEditContainer(sender, itemContainer, handleShowEditInput), itemContainer);

		if (this.#isMainSender(sender))
		{
			this.mainSenderNameNode = nameContainer.querySelector('.sender-item-name-text-container');
		}

		this.#senderNameNodes.set(sender.id, nameTextContainer);

		return itemContainer;
	}

	#renderSenderNameContainer(senderName: string): { root: HTMLElement, textNode: HTMLElement}
	{
		const { root, textNode } = Tag.render`
			<div class="sender-item-name-container">
				<div class="sender-item-name-text-container" ref="textNode">
					${Text.encode(senderName)}
				</div>
			</div>
		`;

		return { root, textNode };
	}

	#renderSenderEditNode(
		sender: Sender,
		nameTextContainer: HTMLElement,
	): {nameEditContainer: HTMLElement, editInput: HTMLElement}
	{
		const textContainer = nameTextContainer;
		const { root, editInput } = Tag.render`
			<div class="edit-sender-container-content" ref="editContent">
				<div class="ui-ctl ui-ctl-textbox ui-ctl-default-light ui-ctl-sm ui-ctl-w100">
					<input type="text" class="ui-ctl-element" ref="editInput" placeholder="${Loc.getMessage('UI_MAIL_ALIAS_SLIDER_ADD_INPUT_PLACEHOLDER')}">
				</div>
			</div>
		`;

		const nameEditContainer = root;

		const submitPromise = () => {
			return new Promise((resolve) => {
				const hideEditContainer = () => {
					editInput.value = nameTextContainer.innerText;
					Dom.hide(nameEditContainer);
					Dom.show(textContainer);
					resolve();
				};

				if (editInput.value.length === 0 || editInput.value === nameTextContainer.innerText)
				{
					hideEditContainer();

					return;
				}

				if (this.#hasNameInvalidCharacters(editInput.value))
				{
					resolve();

					return;
				}
				const senderNewName = editInput.value;

				ajax.runAction(
					'main.api.mail.sender.updateSenderName',
					{
						data: {
							senderId: sender.id,
							name: senderNewName,
						},
					},
				).then(() => {
					textContainer.innerText = senderNewName;
					if (this.setSender)
					{
						this.setSender(sender.id, senderNewName, this.email);
					}
					this.wasSenderUpdated = true;
					hideEditContainer();
				}).catch(() => {
					hideEditContainer();
				});
			});
		};
		Dom.append(this.#renderSubmitButton(submitPromise, editInput), root);

		const cancelHandler = () => {
			Dom.hide(nameEditContainer);
			Dom.show(textContainer);
			editInput.value = null;
		};
		Dom.append(this.#renderCancelButton(cancelHandler), root);
		Dom.hide(root);

		return { nameEditContainer, editInput };
	}

	#renderSenderExtraInfoContainer(sender: Sender): HTMLElement
	{
		return Tag.render`
			<div class="sender-item-type-container">${Text.encode(this.#getExtraInfoText(sender))}</div>
		`;
	}

	#getExtraInfoText(sender: Sender): string
	{
		if (this.#isMainSender(sender))
		{
			return Loc.getMessage('UI_MAIL_ALIAS_EDITOR_CURRENT_SENDER_NAME');
		}

		if ([senderType, mailboxSenderType].includes(sender.type))
		{
			return Loc.getMessage('UI_MAIL_ALIAS_EDITOR_ANOTHER_SENDER_NAME');
		}

		if (sender.type === aliasType && sender.isOwner)
		{
			return Loc.getMessage('UI_MAIL_ALIAS_EDITOR_ADDITIONAL_SENDER_NAME');
		}

		return '';
	}

	#renderSenderEditContainer(sender: Sender, senderNode: HTMLElement, handleShowInput: null | () => void): HTMLElement
	{
		const senderEditContainer = Tag.render`
			<div class="sender-item-edit-container"></div>
		`;

		if (!sender.canEdit && !sender.isOwner)
		{
			return senderEditContainer;
		}

		const senderNameEditButton = Tag.render`
			<div class="sender-item-btn ui-btn ui-btn-xs ui-icon-set --pencil-50"></div>
		`;
		Dom.append(senderNameEditButton, senderEditContainer);

		if (handleShowInput)
		{
			Event.bind(senderNameEditButton, 'click', handleShowInput);
		}

		if (sender.type === aliasType)
		{
			Dom.append(this.#renderDeleteButton(sender.id, senderNode), senderEditContainer);

			return senderEditContainer;
		}

		Dom.append(this.#renderSettingsButton(sender.type, sender.id, sender.editHref), senderEditContainer);

		return senderEditContainer;
	}

	#renderSenderAuthorContainer(sender: Sender, senderNode: HTMLElement): HTMLElement
	{
		const authorEditContainer = Tag.render`
			<div class="sender-item-author-container"></div>
		`;

		if (sender.userUrl)
		{
			Dom.append(this.#renderUserInfoNode(sender.userUrl, sender.avatar ?? null), authorEditContainer);
		}

		return authorEditContainer;
	}

	#renderUserInfoNode(userUrl: string, avatar: string | null): HTMLElement
	{
		const { root, userAvatarContainer } = Tag.render`
			<div class="sender-item-owner-info">
				${Loc.getMessage('UI_MAIL_ALIAS_EDITOR_ANOTHER_USER_SENDER_NAME')}
				<a href="${Text.encode(userUrl)}" class="ui-icon ui-icon-common-user sender-item-owner-avatar" ref="userAvatarContainer"></a> 
			</div>
		`;
		let avatarIcon = '';
		if (avatar)
		{
			avatarIcon = Tag.render`<i style="background-image: url('${Text.encode(avatar)}')"></i>`;
		}
		else
		{
			avatarIcon = Tag.render`<div class="sender-item-owner-avatar-icon ui-icon-set --person"></div>`;
		}
		Dom.append(avatarIcon, userAvatarContainer);

		return root;
	}

	#renderDeleteButton(senderId: number, senderNode: HTMLElement): HTMLElement
	{
		const deleteButton = Tag.render`
			<div class="sender-item-btn ui-btn ui-btn-xs ui-icon-set --trash-bin" style="margin: 0"></div>
		`;

		Event.bind(deleteButton, 'click', () => {
			Dom.removeClass(deleteButton, ['ui-icon-set', '--trash-bin']);
			Dom.addClass(deleteButton, ['ui-btn-light', 'ui ui-btn-wait']);
			ajax.runAction(
				'main.api.mail.sender.deleteSender',
				{
					data: {
						senderId,
					},
				},
			).then(() => {
				senderNode.remove();
				this.wasSenderUpdated = true;
				if (Number(senderId) === this.senderId)
				{
					this.setSender();
				}
				this.#senderNameNodes.delete(senderId);
				this.aliasCounter--;
				this.#checkAliasCounter();
			}).catch(() => {
				Dom.removeClass(deleteButton, 'ui-btn-wait');
			});
		});

		return deleteButton;
	}

	#renderSettingsButton(type: string, senderId: number | string, editHref: string | null): HTMLElement
	{
		const editButton = Tag.render`
			<div class="sender-item-btn ui-btn ui-btn-xs ui-icon-set --settings-1" style="margin: 0"></div>
		`;

		if (type === mailboxSenderType)
		{
			Event.bind(editButton, 'click', () => {
				this.#openMailboxSettings(editHref);
			});

			return editButton;
		}

		Event.bind(editButton, 'click', () => {
			this.#openSmtpSettings(senderId);
		});

		return editButton;
	}

	#renderSubmitButton(submitPromise: Promise, targetElement: HTMLInputElement): HTMLElement
	{
		const submitButton = Tag.render`
			<div class="ui-btn ui-btn-xs ui-btn-primary ui-btn-icon-done" style="margin: 0"></div>
		`;
		Event.bind(submitButton, 'click', () => {
			Dom.addClass(submitButton, 'ui ui-btn-wait');
			submitPromise()
				.then(() => {
					Dom.removeClass(submitButton, 'ui-btn-wait');
				})
				.catch(() => {})
			;
		});
		Event.bind(targetElement, 'keypress', (event: KeyboardEvent) => {
			if (event.key === 'Enter')
			{
				submitButton.click();
			}
		});

		return submitButton;
	}

	#renderCancelButton(cancelHandler: () => void): HTMLElement
	{
		const cancelButton = Tag.render`
			<div class="sender-item-btn ui-btn ui-btn-xs ui-icon-set --cross-45" style="margin: 0"></div>
		`;

		Event.bind(cancelButton, 'click', cancelHandler);

		return cancelButton;
	}

	#addSenders(senders: Sender[] | null): void
	{
		if (!senders)
		{
			return;
		}

		senders.sort((a, b) => a.id - b.id);
		senders.forEach((sender: Sender) => {
			if (!this.id)
			{
				if (sender.type === senderType)
				{
					this.id = sender.id;
				}

				if (sender.type === mailboxSenderType)
				{
					this.id = sender.mailboxId;
				}
			}
			const senderNode = this.#renderSenderItem(sender);
			if (this.#isMainSender(sender))
			{
				Dom.prepend(senderNode, this.senderList);
			}
			else
			{
				Dom.append(senderNode, this.senderList);
			}
			this.aliasCounter++;
		});
	}

	#openSmtpSettings(senderId: number | string): void
	{
		SmtpEditor.openSlider({
			senderId: Number(senderId),
			setSenderCallback: (id: number | string, name: string, email: string) => {
				if (this.#senderNameNodes.has(id))
				{
					this.#senderNameNodes.get(id).innerText = name;
				}
				this.setSender(id, name, email);
				this.wasSenderUpdated = true;
			},
		});
	}

	#openMailboxSettings(href: string): void
	{
		BX.SidePanel.Instance.open(href);
	}

	#hasNameInvalidCharacters(name: string): boolean
	{
		// regex checks for characters other than letters of the alphabet, numbers, spaces
		// and special characters ("-", ".", "'", "(", ")", ",")
		const regexForInvalidCharacters = /[^\p{L}\p{N}\p{Zs}\-.'(),]+/ug;

		if (regexForInvalidCharacters.test(name))
		{
			top.BX.UI.Notification.Center.notify({
				content: Text.encode(Loc.getMessage('UI_MAIL_ALIAS_EDITOR_INVALID_SYMBOLS_NOTIFICATION')),
			});

			return true;
		}

		return false;
	}

	updateMainSenderName(mailboxId: number): void
	{
		return BX.ajax.runAction(
			'main.api.mail.sender.getSenderByMailboxId',
			{
				data: { mailboxId },
			},
		)
			.then((response) => {
				const name = response.data?.name;
				if (!name || !this.mainSenderNameNode)
				{
					return;
				}

				this.mainSenderNameNode.innerText = name;
			})
			.catch(() => {})
		;
	}

	#checkAliasCounter(): void
	{
		if (this.aliasCounter === 0)
		{
			const slider = BX.SidePanel.Instance.getSlider(aliasSliderUrl);
			if (slider)
			{
				slider.close();
			}
		}
	}

	#isMainSender(sender: Sender): boolean
	{
		return (sender.type === senderType && this.id === Number(sender.id))
			|| ((sender.type === mailboxSenderType) && this.id === Number(sender.mailboxId))
		;
	}
}
