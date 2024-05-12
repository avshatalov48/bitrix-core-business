import { Type } from 'main.core';
import { ActionItem, Callbacks, Options, QueueItem } from './queuetype';

const LOAD_ITEMS_DELAY = 5000;
const MAX_PENDING_ITEMS = 30;

export default class Queue
{
	#queue: Map<string, ActionItem> = new Map();
	#isProgress: boolean = false;
	#isFreeze: boolean = false;
	#loadItemsTimer: ?number = null;
	#callbacks: Callbacks;
	#loadItemsDelay: number = LOAD_ITEMS_DELAY;
	#maxPendingItems: number = MAX_PENDING_ITEMS;

	constructor(options: Options)
	{
		if (Type.isPlainObject(options.callbacks))
		{
			this.#callbacks = options.callbacks;
		}

		if (Type.isNumber(options.loadItemsDelay))
		{
			this.#loadItemsDelay = options.loadItemsDelay;
		}

		if (Type.isNumber(options.maxPendingItems))
		{
			this.#maxPendingItems = options.maxPendingItems;
		}
	}

	loadItem(ignoreProgressStatus: boolean = false, ignoreDelay: boolean = false): void
	{
		if (this.#loadItemsTimer && !ignoreDelay)
		{
			return;
		}

		this.#loadItemsTimer = setTimeout(
			() => this.loadItemHandler(ignoreProgressStatus),
			ignoreDelay ? 0 : this.#loadItemsDelay,
		);
	}

	loadItemHandler(ignoreProgressStatus: boolean = false): void
	{
		if (this.#isExecuteInProgress(ignoreProgressStatus) || this.#isInaccessibleQueue())
		{
			this.#loadItemsTimer = null;

			return;
		}

		const items = this.getAllAsArray();
		this.#queue.clear();

		if (!Type.isArrayFilled(items))
		{
			return;
		}

		let promise = null;
		const { onBeforeExecute } = this.#callbacks;
		if (Type.isFunction(onBeforeExecute))
		{
			// eslint-disable-next-line no-promise-executor-return
			promise = new Promise((resolve) => onBeforeExecute(items).then(resolve));
		}
		else
		{
			promise = Promise.resolve();
		}

		// eslint-disable-next-line promise/catch-or-return
		promise.then(() => this.process(items));
	}

	#isExecuteInProgress(ignoreProgressStatus: boolean): boolean
	{
		return (this.#isProgress && !ignoreProgressStatus);
	}

	#isInaccessibleQueue(): boolean
	{
		return (document.hidden || this.isOverflow() || this.#isFrozen());
	}

	process(items: QueueItem[]): void
	{
		this.#isProgress = true;

		const { onExecute } = this.#callbacks;
		if (Type.isFunction(onExecute))
		{
			onExecute(items)
				.then(this.loadNextOnSuccess.bind(this), this.doNothingOnError.bind(this))
				.catch(() => console.error('error'))
			;
		}
		else
		{
			this.loadNextOnSuccess();
		}
	}

	loadNextOnSuccess(): void
	{
		this.#loadItemsTimer = null;
		if (!this.isEmpty())
		{
			this.loadItem(true);
		}

		this.#isProgress = false;
	}

	doNothingOnError(): void
	{
		this.#loadItemsTimer = null;
	}

	push(id: string, item: ActionItem): Queue
	{
		if (this.has(id))
		{
			this.delete(id);
		}

		this.#queue.set(id, item);

		return this;
	}

	getAllAsArray(): QueueItem[]
	{
		return Array.from(
			this.#queue,
			([id, data]) => ({ id, data }),
		);
	}

	delete(id: string): void
	{
		this.#queue.delete(id);
	}

	has(id: string): boolean
	{
		return this.#queue.has(id);
	}

	clear(): void
	{
		this.#queue.clear();
	}

	isOverflow(): boolean
	{
		return (this.#queue.size > this.#maxPendingItems);
	}

	isEmpty(): boolean
	{
		return (this.#queue.size === 0);
	}

	freeze(): void
	{
		this.#isFreeze = true;
	}

	unfreeze(): void
	{
		this.#isFreeze = false;
	}

	#isFrozen(): boolean
	{
		return this.#isFreeze;
	}

	getLoadItemsDelay(): number
	{
		return this.#loadItemsDelay;
	}
}
