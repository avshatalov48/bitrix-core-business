import {RestMethod} from 'im.v2.const';
import {Base} from './base';

const REQUEST_ITEMS_LIMIT = 5;
export class Reminder extends Base
{
	hasMoreItemsToLoad: boolean = true;
	pagesLoaded: number = 0;

	getInitialRequest()
	{
		return {};
	}

	getResponseHandler()
	{
		return () => {
			return Promise.resolve();
		};
	}

	extractLoadCountersError(response): string
	{
		return '';
	}

	loadNextPage()
	{
		return Promise.resolve();
	}
}
