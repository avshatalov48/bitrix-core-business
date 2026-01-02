import type { SearchResultItem } from 'im.v2.lib.search';

export const mergeSearchItems = (
	originalItems: SearchResultItem[],
	newItems: SearchResultItem[],
): SearchResultItem[] => {
	const mergedItems = [...originalItems, ...newItems].map((item) => {
		return [item.dialogId, item];
	});
	const result = new Map(mergedItems);

	return [...result.values()];
};
