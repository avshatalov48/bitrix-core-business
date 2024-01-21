import { Tag, Loc, Runtime } from 'main.core';
import { DefaultFooter } from 'ui.entity-selector';
import type { Dialog } from 'ui.entity-selector';
import { Button, ButtonColor, ButtonSize } from 'ui.buttons';

export default class CreateChatFooter extends DefaultFooter
{
	#buttons: {
		createChatButton: Button,
		cancelButton: Button,
	};

	constructor(dialog: Dialog, options: { [option: string]: any })
	{
		super(dialog, options);

		this.#buttons = {
			createChatButton: null,
			cancelButton: null,
		};

		this.handleDialogDestroy = this.handleDialogDestroy.bind(this);
		this.handleSelectedItemsUpdated = this.handleSelectedItemsUpdated.bind(this);

		this.bindEvents();
	}

	getContent(): HTMLElement
	{
		this.options.containerClass = 'sn-ui-selector-footer-create-chat';

		return Tag.render`
			<div>
				${this.renderCreateChatButton()}
				${this.renderCancelButton()}
			</div>
		`;
	}

	renderCreateChatButton(): HTMLElement
	{
		this.#buttons.createChatButton = new Button({
			text: Loc.getMessage('SOCNET_ENTITY_SELECTOR_CREATE'),
			round: true,
			color: ButtonColor.PRIMARY,
			size: ButtonSize.SMALL,
			events: {
				click: this.createChatButtonClickHandler.bind(this),
			},
		});

		this.#buttons.createChatButton.setDisabled(true);

		return this.#buttons.createChatButton.getContainer();
	}

	createChatButtonClickHandler(): void
	{
		const userIds = this.getDialog().getSelectedItems().map((item) => item.getId());

		this.#buttons.createChatButton.setWaiting(true);
		this.createChatAction(userIds).then(async (response) => {
			const chatId = 'chat' + response.data;

			const { Messenger } = await Runtime.loadExtension('im.public.iframe');
			Messenger.openChat(chatId);

			this.#buttons.createChatButton.setWaiting(false);
			this.getDialog().deselectAll();
			this.getDialog().hide();
		});
	}

	createChatAction(userIds): Promise
	{
		return BX.ajax.runAction('socialnetwork.api.chat.create', {
			data: {
				userIds,
			},
		});
	}

	renderCancelButton(): HTMLElement
	{
		this.#buttons.cancelButton = new Button({
			text: Loc.getMessage('SOCNET_ENTITY_SELECTOR_CANCEL'),
			round: true,
			color: ButtonColor.LIGHT_BORDER,
			size: ButtonSize.SMALL,
			events: {
				click: this.cancelButtonClickHandler.bind(this),
			},
		});

		return this.#buttons.cancelButton.getContainer();
	}

	cancelButtonClickHandler(): void
	{
		this.getDialog().hide();
	}

	bindEvents(): void
	{
		this.getDialog().subscribe('onDestroy', this.handleDialogDestroy);
		this.getDialog().subscribe('Item:onSelect', this.handleSelectedItemsUpdated);
		this.getDialog().subscribe('Item:onDeselect', this.handleSelectedItemsUpdated);
	}

	unbindEvents(): void
	{
		this.getDialog().unsubscribe('onDestroy', this.handleDialogDestroy);
		this.getDialog().unsubscribe('Item:onSelect', this.handleSelectedItemsUpdated);
		this.getDialog().unsubscribe('Item:onDeselect', this.handleSelectedItemsUpdated);
	}

	handleSelectedItemsUpdated(): void
	{
		if (!this.#buttons.createChatButton)
		{
			return;
		}

		this.#buttons.createChatButton.setDisabled(this.getDialog().getSelectedItems().length === 0);
	}

	handleDialogDestroy(): void
	{
		this.unbindEvents();
	}
}