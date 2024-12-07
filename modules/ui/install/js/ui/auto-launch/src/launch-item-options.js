import { LaunchPriority } from './launch-priority';

export type LaunchItemCallback = (done: Function) => {};

export type LaunchItemOptions = {
	callback: LaunchItemCallback,
	id?: string,
	priority?: LaunchPriority,
	delay?: number,
	allowLaunchAfterOthers?: boolean,
	forceShowOnTop?: boolean | Function,
	context?: LaunchItemContext,
};

export type LaunchItemContext = {
	slider: Object,
	sliderId: string,
};
