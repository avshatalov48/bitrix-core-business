import { TabMessages } from './tab-messages';

export type FieldSelectorConfig = {
	containerId: string,
	fieldName: string,
	multiple: boolean,
	selectedItems: number[],
	iblockId: number,
	userType?: string,
	entityId: string,
	searchMessages: TabMessages,
};
