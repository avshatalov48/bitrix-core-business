import { type EntityOptions } from 'ui.entity-selector';
import { TabMessages } from './tab-messages';

export type FieldSelectorConfig = {
	containerId: string,
	fieldName: string,
	multiple: boolean,
	collectionType: string,
	selectedItems: number[],
	context: ?string,
	entities: EntityOptions[],
	searchMessages: TabMessages,
	changeEvents?: string[],
};
