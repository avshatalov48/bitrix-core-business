import { NotificationSettingsBlock } from 'im.v2.const';
import type { RawNotificationSettingsBlock, NotificationSettingsItem } from 'im.v2.const';

type PreparedNotificationSettings = {
	[block: string]: NotificationSettingsBlock,
};

const SortWeight = {
	im: 10,
};

export const prepareNotificationSettings = (target: RawNotificationSettingsBlock[]): PreparedNotificationSettings => {
	const result = {};

	const sortedTarget = sortNotificationSettingsBlock(target);
	sortedTarget.forEach((block: RawNotificationSettingsBlock) => {
		const preparedItems = {};
		block.notices.forEach((item: NotificationSettingsItem) => {
			preparedItems[item.id] = item;
		});
		result[block.id] = {
			id: block.id,
			label: block.label,
			items: preparedItems,
		};
	});

	return result;
};

const sortNotificationSettingsBlock = (target: RawNotificationSettingsBlock[]): RawNotificationSettingsBlock[] => {
	return [...target].sort((a, b) => {
		const weightA = SortWeight[a.id] ?? 0;
		const weightB = SortWeight[b.id] ?? 0;

		return weightB - weightA;
	});
};
