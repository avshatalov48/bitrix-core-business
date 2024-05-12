export type QueueItem = {
	id: string,
	data: ActionItem,
}

export type ActionItem = {
	action: string;
	actionParams: Object;
	id: string;
}

export type Options = {
	callbacks?: Callbacks;
	loadItemsDelay?: number;
	maxPendingItems?: number;
}

export type Callbacks = {
	onBeforeExecute: () => {},
	onExecute: () => {},
}