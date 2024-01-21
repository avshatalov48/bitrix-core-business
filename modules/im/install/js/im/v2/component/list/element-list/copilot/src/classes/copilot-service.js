import { Messenger } from 'im.public';
import { RestMethod } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { RecentService } from 'im.v2.provider.service';

import type { JsonObject } from 'main.core';
import type { ImModelRecentItem } from 'im.v2.model';

export class CopilotRecentService extends RecentService
{
	getQueryParams(firstPage: boolean): JsonObject
	{
		return {
			ONLY_COPILOT: 'Y',
			LIMIT: this.itemsPerPage,
			LAST_MESSAGE_DATE: firstPage ? null : this.lastMessageDate,
			GET_ORIGINAL_TEXT: 'Y',
		};
	}

	getModelSaveMethod(): string
	{
		return 'recent/setCopilot';
	}

	getCollection(): ImModelRecentItem[]
	{
		return this.store.getters['recent/getCopilotCollection'];
	}

	getExtractorOptions(): { withBirthdays?: boolean }
	{
		return { withBirthdays: false };
	}

	hideChat(dialogId)
	{
		Logger.warn('Im.CopilotRecentList: hide chat', dialogId);
		const recentItem = this.store.getters['recent/get'](dialogId);
		if (!recentItem)
		{
			return;
		}

		this.store.dispatch('recent/delete', {
			id: dialogId,
		});

		const chatIsOpened = this.store.getters['application/isChatOpen'](dialogId);
		if (chatIsOpened)
		{
			Messenger.openCopilot();
		}

		this.restClient.callMethod(RestMethod.imRecentHide, { DIALOG_ID: dialogId }).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('Im.CopilotRecentList: hide chat error', error);
		});
	}
}
