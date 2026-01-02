import { Core } from 'im.v2.application.core';
import { ChatType, UserType } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';

import type { ImModelChat, ImModelUser } from 'im.v2.model';
import type { SearchResultItem } from 'im.v2.lib.search';

export const sortByDate = (items: SearchResultItem[]): SearchResultItem[] => {
	return [...items].sort((firstItem, secondItem) => {
		// Both items have dates - compare them
		if (firstItem.dateMessage && secondItem.dateMessage)
		{
			return Utils.date.cast(secondItem.dateMessage) - Utils.date.cast(firstItem.dateMessage);
		}

		// Only one item has a date - item with date comes first
		if (firstItem.dateMessage || secondItem.dateMessage)
		{
			return firstItem.dateMessage ? -1 : 1;
		}

		// Case 3: Neither item has a date - non-extranet item comes first
		const firstIsExtranet = isExtranet(firstItem.dialogId);
		const secondIsExtranet = isExtranet(secondItem.dialogId);
		if (firstIsExtranet !== secondIsExtranet)
		{
			return firstIsExtranet ? 1 : -1;
		}

		return 0;
	});
};

const isExtranet = (dialogId: string): boolean => {
	const dialog: ImModelChat = Core.getStore().getters['chats/get'](dialogId);
	if (!dialog)
	{
		return false;
	}

	if (dialog.type === ChatType.user)
	{
		const user: ImModelUser = Core.getStore().getters['users/get'](dialogId);

		return user && user.type === UserType.extranet;
	}

	return dialog.extranet;
};
