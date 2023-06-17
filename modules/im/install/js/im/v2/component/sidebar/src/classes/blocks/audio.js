import {SidebarFileTabTypes} from 'im.v2.const';
import {BaseFile} from './base-file';

export class Audio extends BaseFile
{
	loadFirstPage(): Promise
	{
		return this.loadFirstPageBySubType(SidebarFileTabTypes.audio);
	}

	loadNextPage(): Promise
	{
		return this.loadNextPageBySubType(SidebarFileTabTypes.audio);
	}
}