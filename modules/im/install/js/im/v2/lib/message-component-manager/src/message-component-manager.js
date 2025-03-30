import { Utils } from 'im.v2.lib.utils';
import { Core } from 'im.v2.application.core';
import { MessageComponent } from 'im.v2.const';
import { SmileManager } from 'im.v2.lib.smile-manager';
import { OpenLinesMessageManager } from 'imopenlines.v2.lib.message-manager';

import type { Store } from 'ui.vue3.vuex';
import type { ImModelMessage } from 'im.v2.model';

const serverComponentList = new Set([
	MessageComponent.unsupported,
	MessageComponent.chatCreation,
	MessageComponent.ownChatCreation,
	MessageComponent.conferenceCreation,
	MessageComponent.callInvite,
	MessageComponent.copilotCreation,
	MessageComponent.copilotMessage,
	MessageComponent.supportVote,
	MessageComponent.supportSessionNumber,
	MessageComponent.supportChatCreation,
	MessageComponent.zoomInvite,
	MessageComponent.copilotAddedUsers,
	MessageComponent.supervisorUpdateFeature,
	MessageComponent.supervisorEnableFeature,
	MessageComponent.sign,
	MessageComponent.checkIn,
	MessageComponent.generalChatCreationMessage,
	MessageComponent.generalChannelCreationMessage,
	MessageComponent.channelCreationMessage,
	MessageComponent.callMessage,
	MessageComponent.voteMessage,
]);

export class MessageComponentManager
{
	#message: ImModelMessage;
	#store: Store;

	constructor(message: ImModelMessage)
	{
		this.#message = message;
		this.#store = Core.getStore();
	}

	getName(): $Values<typeof MessageComponent>
	{
		const openLinesManager = new OpenLinesMessageManager(this.#message);

		if (openLinesManager.checkComponentInOpenLinesList())
		{
			return openLinesManager.getMessageComponent();
		}

		if (this.#isDeletedMessage())
		{
			return MessageComponent.deleted;
		}

		if (this.#isServerComponent())
		{
			return this.#message.componentId;
		}

		if (this.#isSystemMessage())
		{
			return MessageComponent.system;
		}

		if (this.#hasFiles())
		{
			return MessageComponent.file;
		}

		if (this.#isEmojiOnly() || this.#hasSmilesOnly())
		{
			return MessageComponent.smile;
		}

		return MessageComponent.default;
	}

	#isServerComponent(): boolean
	{
		return serverComponentList.has(this.#message.componentId);
	}

	#hasFiles(): boolean
	{
		return this.#message.files.length > 0;
	}

	#hasText(): boolean
	{
		return this.#message.text.length > 0;
	}

	#hasAttach(): boolean
	{
		return this.#message.attach.length > 0;
	}

	#isEmptyMessage(): boolean
	{
		return !this.#hasText() && !this.#hasFiles() && !this.#hasAttach();
	}

	#isDeletedMessage(): boolean
	{
		return this.#message.isDeleted || this.#isEmptyMessage();
	}

	#isSystemMessage(): boolean
	{
		return this.#message.authorId === 0;
	}

	#isEmojiOnly(): boolean
	{
		if (this.#message.replyId > 0)
		{
			return false;
		}

		if (this.#isForward())
		{
			return false;
		}

		if (!this.#hasOnlyText())
		{
			return false;
		}

		return Utils.text.isEmojiOnly(this.#message.text);
	}

	#hasSmilesOnly(): boolean
	{
		if (this.#message.replyId > 0)
		{
			return false;
		}

		if (this.#isForward())
		{
			return false;
		}

		if (!this.#hasOnlyText())
		{
			return false;
		}

		// todo: need to sync with getSmileRatio in lib/parser/src/functions/smile.js
		const smileManager = SmileManager.getInstance();
		const smiles = smileManager.smileList?.smiles ?? [];
		const sortedSmiles = [...smiles].sort((a, b) => {
			return b.typing.localeCompare(a.typing);
		});
		const pattern = sortedSmiles.map((smile) => {
			return Utils.text.escapeRegex(smile.typing);
		}).join('|');

		const replacedText = this.#message.text.replaceAll(new RegExp(pattern, 'g'), '');
		const hasOnlySmiles = replacedText.trim().length === 0;

		const matchOnlySmiles = new RegExp(`(?:(?:${pattern})\\s*){4,}`);

		return hasOnlySmiles && !matchOnlySmiles.test(this.#message.text);
	}

	#hasOnlyText(): boolean
	{
		if (!this.#hasText())
		{
			return false;
		}

		return !this.#hasFiles() && !this.#hasAttach();
	}

	#isForward(): boolean
	{
		return this.#store.getters['messages/isForward'](this.#message.id);
	}
}
