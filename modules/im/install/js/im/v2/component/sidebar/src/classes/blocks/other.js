import {SidebarFileTabTypes} from 'im.v2.const';
import {BaseFile} from './base-file';

export class Other extends BaseFile
{
	loadFirstPage(): Promise
	{
		return this.loadFirstPageBySubType(SidebarFileTabTypes.other);
	}

	loadNextPage(): Promise
	{
		return this.loadNextPageBySubType(SidebarFileTabTypes.other);
	}
}