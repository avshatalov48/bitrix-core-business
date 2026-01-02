import { Core } from 'im.v2.application.core';

import { getRecentItemDate } from './get-recent-item-date';

import type { SearchResultItem } from '../types/types';
import type { ImModelRecentItem } from 'im.v2.model';

export function getRecentListItems({ withFakeUsers }: {withFakeUsers: boolean}): SearchResultItem[]
{
	let recent: ImModelRecentItem[] = Core.getStore().getters['recent/getSortedCollection'];
	recent = recent.filter((item) => {
		if (withFakeUsers && item.isFakeElement)
		{
			return true;
		}

		return !item.isBirthdayPlaceholder && !item.isFakeElement;
	});

	return recent.map(({ dialogId }) => {
		return {
			dialogId,
			dateMessage: getRecentItemDate(dialogId),
		};
	});
}
