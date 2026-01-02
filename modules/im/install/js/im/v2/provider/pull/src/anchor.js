import { Core } from 'im.v2.application.core';

import type { ImModelAnchor } from 'im.v2.model';
import type { DeleteChatAnchorsParams } from './types/anchor';

export class AnchorPullHandler
{
	constructor()
	{
		this.store = Core.getStore();
	}

	getModuleId(): string
	{
		return 'im';
	}

	handleAddAnchor(anchor: ImModelAnchor): void
	{
		this.store.dispatch('messages/anchors/addAnchor', { anchor });
	}

	handleDeleteAnchor(anchor: ImModelAnchor): void
	{
		this.store.dispatch('messages/anchors/removeAnchor', { anchor });
	}

	handleDeleteAllAnchors(): void
	{
		this.store.dispatch('messages/anchors/removeAllAnchors');
	}

	handleDeleteChatAnchors(payload: DeleteChatAnchorsParams): void
	{
		this.store.dispatch('messages/anchors/removeChatAnchors', {
			chatId: payload.chatId,
			userId: payload.userId,
		});
	}
}
