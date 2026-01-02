import { Core } from 'im.v2.application.core';
import { LegacyRecentService } from 'im.v2.provider.service.recent';

import type { JsonObject } from 'main.core';
import type { ImModelRecentItem } from 'im.v2.model';

export class CopilotRecentService extends LegacyRecentService
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
}
