import { NotificationSettingsBlock } from 'im.v2.const';
import type { RawNotificationSettingsBlock, NotificationSettingsItem } from 'im.v2.const';

type PreparedNotificationSettings = {
	[block: string]: NotificationSettingsBlock,
};

const SortWeight = {
	tasks: 2600,
	calendar: 2500,
	im: 2400,
	blog: 2300,
	vote: 2200,
	main: 2100,
	socialnetwork: 2000,
	bizproc: 1900,
	rpa: 1800,
	lists: 1700,
	mail: 1600,
	crm: 1500,
	sender: 1400,
	booking: 1300,
	voximplant: 1200,
	imopenlines: 1100,
	timeman: 1000,
	disk: 900,
	bitrix24: 800,
	sign: 700,
	biconnector: 600,
	rest: 500,
	intranet: 400,
	photogallery: 300,
	wiki: 200,
	forum: 100,
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
