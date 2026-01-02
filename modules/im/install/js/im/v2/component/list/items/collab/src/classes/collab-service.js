import { RestMethod } from 'im.v2.const';
import { BaseRecentService } from 'im.v2.provider.service.recent';

export class CollabService extends BaseRecentService
{
	getRestMethodName(): string
	{
		return RestMethod.imV2RecentCollabTail;
	}

	getRecentSaveActionName(): string
	{
		return 'recent/setCollab';
	}
}
