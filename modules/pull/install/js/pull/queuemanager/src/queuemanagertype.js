export type Options = {
	moduleId: string;
	pullTag?: string;
	userId: number;
	config?: Config;
	additionalData?: Object<string, any>;
	callbacks: Callbacks;
}

type Config = {
	showOutdatedDataDialog?: boolean;
	loadItemsDelay?: number;
	maxPendingItems?: number;
}

type Callbacks = {
	onBeforeQueueExecute: () => {};
	onQueueExecute: () => {};
	onReload: () => {},
}

export type PullData = {
	command: string;
	extra: Object;
	params: Object;
}
