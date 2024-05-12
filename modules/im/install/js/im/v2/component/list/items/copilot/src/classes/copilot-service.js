import { Messenger } from 'im.public';
import { Core } from 'im.v2.application.core';
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
			PARSE_TEXT: 'Y',
		};
	}

	getModelSaveMethod(): string
	{
		return 'recent/setCopilot';
	}

	getCollection(): ImModelRecentItem[]
	{
		return Core.getStore().getters['recent/getCopilotCollection'];
	}

	getExtractorOptions(): { withBirthdays?: boolean }
	{
		return { withBirthdays: false };
	}

	hideChat(dialogId)
	{
		Logger.warn('Im.CopilotRecentList: hide chat', dialogId);
		const recentItem = Core.getStore().getters['recent/get'](dialogId);
		if (!recentItem)
		{
			return;
		}

		Core.getStore().dispatch('recent/delete', {
			id: dialogId,
		});

		const chatIsOpened = Core.getStore().getters['application/isChatOpen'](dialogId);
		if (chatIsOpened)
		{
			Messenger.openCopilot();
		}

		Core.getRestClient().callMethod(RestMethod.imRecentHide, { DIALOG_ID: dialogId }).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('Im.CopilotRecentList: hide chat error', error);
		});
	}
}
