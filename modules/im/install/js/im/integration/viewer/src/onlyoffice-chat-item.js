import {Reflection} from 'main.core';
import {OnlyOfficeItem} from 'disk.viewer.onlyoffice-item';

export default class OnlyOfficeChatItem extends OnlyOfficeItem
{
	constructor(options)
	{
		options = options || {};

		super(options);

		this.chatId = options.imChatId;
	}

	setPropertiesByNode (node: HTMLElement)
	{
		super.setPropertiesByNode(node);

		this.chatId = node.dataset.imChatId;
	}

	loadData ()
	{
		/** @see BXIM.callController.currentCall */
		if (!Reflection.getClass('BXIM.callController.currentCall'))
		{
			return super.loadData();
		}

		const callController = BXIM.callController;
		const dialogId = callController.currentCall.associatedEntity.id;
		const chatId = this.getChatId(dialogId);

		if (!chatId || chatId != this.chatId)
		{
			return super.loadData();
		}

		callController.unfold();
		callController.showDocumentEditor({
			viewerItem: this,
			force: true,
		});

		return new BX.Promise();
	}

	getChatId(dialogId): ?number
	{
		return dialogId.toString().startsWith('chat') ?
			dialogId.substr(4) : BXIM.messenger.userChat[dialogId];
	}
}