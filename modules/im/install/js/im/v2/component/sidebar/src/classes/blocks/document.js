import {SidebarFileTabTypes} from 'im.v2.const';
import {BaseFile} from './base-file';

export class Document extends BaseFile
{
	loadFirstPage(): Promise
	{
		return this.loadFirstPageBySubType(SidebarFileTabTypes.document);
	}

	loadNextPage(): Promise
	{
		return this.loadNextPageBySubType(SidebarFileTabTypes.document);
	}
}