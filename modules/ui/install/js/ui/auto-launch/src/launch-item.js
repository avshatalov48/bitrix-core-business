import { Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { LaunchPriority } from './launch-priority';
import { LaunchState } from './launch-state';

import type { LaunchItemOptions, LaunchItemCallback, LaunchItemContext } from './launch-item-options';

export default class LaunchItem extends EventEmitter
{
	#id: string = null;
	#callback: LaunchItemCallback = null;
	#priority: LaunchPriority = LaunchPriority.NORMAL;
	#delay: number = 5000;
	#allowLaunchAfterOthers: boolean = false;
	#forceShowOnTop: boolean | Function = false;
	#state: LaunchState = LaunchState.IDLE;
	#context: LaunchItemContext = {};

	constructor(itemOptions: LaunchItemOptions)
	{
		super();

		const options: LaunchItemOptions = Type.isPlainObject(itemOptions) ? itemOptions : {};
		if (!Type.isFunction(options.callback))
		{
			throw new TypeError('BX.Launcher: "callback" parameter is required.');
		}

		this.#callback = options.callback;
		this.#id = Type.isStringFilled(options.id) ? options.id : `launch-item-${BX.Text.getRandom().toLowerCase()}`;
		this.#priority = Type.isNumber(options.priority) ? options.priority : this.#priority;
		this.#delay = Type.isNumber(options.delay) && options.delay >= 0 ? options.delay : this.#delay;
		this.#allowLaunchAfterOthers = options.allowLaunchAfterOthers === true;
		this.#forceShowOnTop = (
			Type.isBoolean(options.forceShowOnTop) || Type.isFunction(options.forceShowOnTop)
				? options.forceShowOnTop
				: this.#forceShowOnTop
		);

		this.#context = Type.isPlainObject(options.context) ? options.context : {};

		this.setEventNamespace('BX.Main.Launcher.Item');
	}

	launch(done: Function)
	{
		if (this.#state !== LaunchState.IDLE)
		{
			return;
		}

		this.#state = LaunchState.RUNNING;

		const onDone = () => {
			this.#state = LaunchState.DONE;
			done();
		};

		this.#callback(onDone);
	}

	getId(): string
	{
		return this.#id;
	}

	getState(): LaunchState
	{
		return this.#state;
	}

	getPriority(): number
	{
		return this.#priority;
	}

	getDelay(): number
	{
		return this.#delay;
	}

	getContext(): LaunchItemContext
	{
		return this.#context;
	}

	canLaunchAfterOthers(): boolean
	{
		return this.#allowLaunchAfterOthers;
	}

	canShowOnTop(): boolean
	{
		if (Type.isFunction(this.#forceShowOnTop))
		{
			return this.#forceShowOnTop();
		}

		return this.#forceShowOnTop;
	}
}
