import {SidebarFileTypes} from 'im.v2.const';
import {BaseFile} from './base-file';

export class Brief extends BaseFile
{
	loadFirstPage(): Promise
	{
		return this.loadFirstPageBySubType(SidebarFileTypes.brief);
	}

	loadNextPage(): Promise
	{
		return this.loadNextPageBySubType(SidebarFileTypes.brief);
	}
}