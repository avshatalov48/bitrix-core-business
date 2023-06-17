import {SidebarFileTabTypes} from 'im.v2.const';
import {BaseFile} from './base-file';

export class Media extends BaseFile
{
	loadFirstPage(): Promise
	{
		return this.loadFirstPageBySubType(SidebarFileTabTypes.media);
	}

	loadNextPage(): Promise
	{
		return this.loadNextPageBySubType(SidebarFileTabTypes.media);
	}
}