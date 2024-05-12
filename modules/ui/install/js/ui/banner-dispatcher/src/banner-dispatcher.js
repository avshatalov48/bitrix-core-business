import { LaunchPriority, } from 'ui.auto-launch';
import { Queue } from './queue';

const criticalQueue = new Queue(LaunchPriority.CRITICAL, 1);
const highQueue = new Queue(LaunchPriority.HIGH, 5, true);
const normalQueue = new Queue(LaunchPriority.NORMAL, 5, true);

export const BannerDispatcher = {
	critical: {
		toQueue: (promise) => {
			criticalQueue.add(promise);
		}
	},
	high: {
		toQueue: (promise) => {
			highQueue.add(promise);
		}
	},
	normal: {
		toQueue: (promise) => {
			normalQueue.add(promise);
		}
	},

	toQueue: (promise) => {
		normalQueue.add(promise);
	},

	only(priorityList: Array<LaunchPriority>)
	{
		const priorityValues = Object.values(LaunchPriority);
		priorityValues.filter((priorityValue) => {
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
				}
			}
		});
	}
};