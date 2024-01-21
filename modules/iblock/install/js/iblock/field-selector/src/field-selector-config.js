import { TabMessages } from './tab-messages';

export type FieldSelectorConfig = {
	containerId: string,
	fieldName: string,
	multiple: boolean,
	collectionType: string,
	selectedItems: number[],
	iblockId: number,
	userType?: string,
	entityId: string,
	searchMessages: TabMessages,
	changeEvents?: string[],
};
