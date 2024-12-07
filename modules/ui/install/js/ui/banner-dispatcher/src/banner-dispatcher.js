import { LaunchPriority, type LaunchItemOptions, type LaunchItemCallback } from 'ui.auto-launch';
import { Queue } from './queue';

const criticalQueue = new Queue(LaunchPriority.CRITICAL, 0);
const highQueue = new Queue(LaunchPriority.HIGH, 1);
const normalQueue = new Queue(LaunchPriority.NORMAL, 1, true);
const lowQueue = new Queue(LaunchPriority.LOW, 5, true);

export const BannerDispatcher = {
	critical: {
		toQueue: (callback: LaunchItemCallback, options: LaunchItemOptions = {}) => {
			criticalQueue.add(callback, {
				allowLaunchAfterOthers: true,
				forceShowOnTop: true,
				...options,
			});
		},
	},
	high: {
		toQueue: (callback: LaunchItemCallback, options: LaunchItemOptions = {}) => {
			highQueue.add(callback, {
				allowLaunchAfterOthers: true,
				...options,
			});
		},
	},
	normal: {
		toQueue: (callback: LaunchItemCallback, options: LaunchItemOptions = {}) => {
			normalQueue.add(callback, options);
		},
	},
	low: {
		toQueue: (callback: LaunchItemCallback, options: LaunchItemOptions = {}) => {
			lowQueue.add(callback, options);
		},
	},

	toQueue: (callback: LaunchItemCallback, options: LaunchItemOptions = {}) => {
		normalQueue.add(callback, options);
	},

	only(priorityList: Array<LaunchPriority>)
	{
		const priorityValues = Object.values(LaunchPriority);
		priorityValues.forEach((priorityValue) => {
			if (!priorityList.includes(priorityValue))
			{
				switch (priorityValue)
				{
					case LaunchPriority.CRITICAL:
						criticalQueue.stop();
						break;
					case LaunchPriority.HIGH:
						highQueue.stop();
						break;
					case LaunchPriority.NORMAL:
						normalQueue.stop();
						break;
					case LaunchPriority.LOW:
						lowQueue.stop();
						break;
				}
			}
		});
	},
};
