import { LaunchPriority, AutoLauncher } from 'ui.auto-launch'
import { Type, Text } from 'main.core';

export class Queue
{
	#priority: LaunchPriority;
	#delay: number;
	#itemList: Object = {};
	#enough: boolean = false;
	#launchPerHit: boolean = false;

	constructor(priority: LaunchPriority, delay: number, launchPerHit: boolean = false)
	{
		this.#delay = parseInt(delay) * 1000;
		this.#priority = priority;
		this.#launchPerHit = launchPerHit;
	}

	add(callback: function): void
	{
		if (this.#enough)
		{
			return;
		}

		if (!Type.isFunction(callback))
		{
			throw new TypeError('Unexpected type "promise" argument, expected Promise or callback');
		}
		const allowLaunchAfterOthers = !(this.#launchPerHit && Object.values(this.#itemList).length > 0);
		const id = Text.getRandom();
		this.#itemList[id] = callback;

		AutoLauncher.register(callback, {
			delay: this.#delay,
			priority: this.#priority,
			allowLaunchAfterOthers: allowLaunchAfterOthers,
			id: id
		})
	}

	getItems(): Object
	{
		return this.#itemList;
	}

	clean(): void
	{
		Object.keys(this.#itemList).forEach((id) => {
			this.remove(id);
		});
	}

	enough(): void
	{
		this.#enough = true;
	}

	notEnough(): void
	{
		this.#enough = false;
	}

	stop()
	{
		this.clean();
		this.enough();
	}

	remove(id: string): void
	{
		AutoLauncher.unregister(id);
	}
}