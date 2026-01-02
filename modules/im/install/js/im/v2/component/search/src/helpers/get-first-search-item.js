import type { SearchResultItem } from 'im.v2.lib.search';

export const getFirstItemFromSearchResults = ({ searchResult, recentItems }): ?SearchResultItem => {
	if (searchResult.length > 0)
	{
		return searchResult[0];
	}

	if (recentItems.length > 0)
	{
		return recentItems[0];
	}

	return null;
};
