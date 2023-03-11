import {Loc, Type} from 'main.core';

export class PullControllerMainUserCounter
{
	constructor(options)
	{
		this.componentName = options.componentName;
		this.signedParameters = options.signedParameters;

		this.userCounterManager = options.userCounterManager;

		this.timer = null;
		this.queueCounterData = new Map();
	}

	getModuleId()
	{
		return 'main';
	}

	getMap()
	{
		return {
			[ Loc.getMessage('PUSH_EVENT_MAIN_USER_COUNTER') ]: this.onUserCounter.bind(this),
		};
	}

	onUserCounter(data)
	{
		const siteId = Loc.getMessage('SITE_ID');
		const eventCounterData = (Type.isPlainObject(data[siteId]) ? data[siteId] : {});

		if (!this.timer)
		{
			this.timer = setTimeout(() => {
				this.freeCounterQueue();
			}, 1000);
		}

		Object.entries(eventCounterData).forEach(([ key, value]) => {
			const matches = key.match(/^\*\*SG(\d+)/i);
			if (matches)
			{
				const groupId = Number(matches[1]);
				value = Number(value)

				if (
					groupId === 0
					&& value !== 0
				)
				{
					return;
				}

				const counterData = {
					type: 'livefeed',
					value: value,
				};

				this.queueCounterData.set(groupId, counterData);
			}
		});
	}

	freeCounterQueue()
	{
		this.queueCounterData.forEach((counterData, groupId) => {
			// todo oh this.userCounterManager.processCounterItem(counterData, groupId);
		});

		this.queueCounterData.clear();
		this.timer = null;
	}

}
