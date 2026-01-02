import { RestMethod, ChatType } from 'im.v2.const';
import { BaseRecentService, type BaseRecentQueryParams } from 'im.v2.provider.service.recent';

export class TaskService extends BaseRecentService
{
	getRestMethodName(): string
	{
		return RestMethod.imV2RecentExternalChatTail;
	}

	getQueryParams(firstPage: boolean = false): BaseRecentQueryParams & { type: string }
	{
		return {
			...super.getQueryParams(firstPage),
			type: ChatType.taskComments,
		};
	}

	getRecentSaveActionName(): string
	{
		return 'recent/setTask';
	}
}
