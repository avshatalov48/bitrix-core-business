import Type from '../type';
import Runtime from '../runtime';
import BaseEvent from './base-event';
import EventStore from './event-emitter/eventstore';
import WarningStore from './event-emitter/warningstore';

const eventStore = new EventStore({ defaultMaxListeners: 10 });
const warningStore = new WarningStore();
const aliasStore = new Map();

const globalTarget = {
	GLOBAL_TARGET: 'GLOBAL_TARGET' // this key only for debugging purposes
};
eventStore.add(globalTarget, { maxListeners: 25 });

const isEmitterProperty = Symbol.for('BX.Event.EventEmitter.isEmitter');
const namespaceProperty = Symbol('namespaceProperty');
const targetProperty = Symbol('targetProperty');

export default class EventEmitter
{
	static GLOBAL_TARGET = globalTarget;
	static DEFAULT_MAX_LISTENERS = eventStore.getDefaultMaxListeners();

	/** @private */
	static sequenceValue = 1;

	constructor(...args)
	{
		this[targetProperty] = null;
		this[namespaceProperty] = null;
		this[isEmitterProperty] = true;

		let target = this;
		if (Object.getPrototypeOf(this) === EventEmitter.prototype && args.length > 0) //new EventEmitter(obj) case
		{
			if (!Type.isObject(args[0]))
			{
				throw new TypeError(`The "target" argument must be an object.`);
			}

			target = args[0];

			this.setEventNamespace(args[1]);
		}

		this[targetProperty] = target;

		setTimeout(() => {
			if (this.getEventNamespace() === null)
			{
				console.warn(
					'The instance of BX.Event.EventEmitter is supposed to have an event namespace. ' +
					'Use emitter.setEventNamespace() to make events more unique.'
				);
			}
		}, 500);
	}

	/**
	 * Makes a target observable
	 * @param {object} target
	 * @param {string} namespace
	 */
	static makeObservable(target: Object, namespace: string): void
	{
		if (!Type.isObject(target))
		{
			throw new TypeError('The "target" argument must be an object.');
		}

		if (!Type.isStringFilled(namespace))
		{
			throw new TypeError('The "namespace" must be an non-empty string.');
		}

		if (EventEmitter.isEventEmitter(target))
		{
			throw new TypeError('The "target" is an event emitter already.');
		}

		const targetProto = Object.getPrototypeOf(target);
		const emitter = new EventEmitter();
		emitter.setEventNamespace(namespace);

		Object.setPrototypeOf(emitter, targetProto);
		Object.setPrototypeOf(target, emitter);

		Object.getOwnPropertyNames(EventEmitter.prototype).forEach(method => {

			if (['constructor'].includes(method))
			{
				return;
			}

			emitter[method] = function(...args) {
				return EventEmitter.prototype[method].apply(target, args);
			}
		});
	}

	setEventNamespace(namespace)
	{
		if (Type.isStringFilled(namespace))
		{
			this[namespaceProperty] = namespace;
		}
	}

	getEventNamespace()
	{
		return this[namespaceProperty];
	}

	/**
	 * Subscribes listener on specified global event
	 * @param {object} target
	 * @param {string} eventName
	 * @param {Function<BaseEvent>} listener
	 * @param {object} options
	 */
	static subscribe(
		target: Object,
		eventName: string,
		listener: (event: BaseEvent) => void,
		options?: {
			compatMode?: boolean,
			useGlobalNaming?: boolean
		}
	): void
	{
		if (Type.isString(target))
		{
			options = listener;
			listener = eventName;
			eventName = target;
			target = this.GLOBAL_TARGET;
		}

		if (!Type.isObject(target))
		{
			throw new TypeError(`The "target" argument must be an object.`);
		}

		eventName = this.normalizeEventName(eventName);
		if (!Type.isStringFilled(eventName))
		{
			throw new TypeError(`The "eventName" argument must be a string.`);
		}

		if (!Type.isFunction(listener))
		{
			throw new TypeError(`The "listener" argument must be of type Function. Received type ${typeof listener}.`);
		}

		options = Type.isPlainObject(options) ? options : {};
		const fullEventName = this.resolveEventName(eventName, target, options.useGlobalNaming === true);
		const { eventsMap, onceMap } = eventStore.getOrAdd(target);
		const onceListeners = onceMap.get(fullEventName);
		let listeners = eventsMap.get(fullEventName);

		if ((listeners && listeners.has(listener)) || (onceListeners && onceListeners.has(listener)))
		{
			console.error(`You cannot subscribe the same "${fullEventName}" event listener twice.`);
		}
		else
		{
			if (listeners)
			{
				listeners.set(
					listener,
					{
						listener,
						options,
						sort: this.getNextSequenceValue()
					}
				)
			}
			else
			{
				listeners = new Map([[
					listener,
					{
						listener,
						options,
						sort: this.getNextSequenceValue()
					}
				]]);

				eventsMap.set(fullEventName, listeners);
			}
		}

		const maxListeners = this.getMaxListeners(target, eventName);
		if (listeners.size > maxListeners)
		{
			warningStore.add(target, fullEventName, listeners);
			warningStore.printDelayed();
		}
	}

	/**
	 * Subscribes a listener on a specified event
	 * @param {string} eventName
	 * @param {Function<BaseEvent>} listener
	 * @return {this}
	 */
	subscribe(eventName: string, listener: (event: BaseEvent) => void): this
	{
		EventEmitter.subscribe(this, eventName, listener);

		return this;
	}

	/**
	 *
	 * @param {object} options
	 * @param {object} [aliases]
	 * @param {boolean} [compatMode=false]
	 */
	subscribeFromOptions(
		options: { [eventName: string]: Function },
		aliases?: { [alias: string]: { eventName: string, namespace: string } },
		compatMode?: boolean
	)
	{
		if (!Type.isPlainObject(options))
		{
			return;
		}

		aliases = Type.isPlainObject(aliases) ? EventEmitter.normalizeAliases(aliases) : {};

		Object.keys(options).forEach((eventName) => {

			const listener = options[eventName];
			if (!Type.isFunction(listener))
			{
				throw new TypeError(`The "listener" argument must be of type Function. Received type ${typeof listener}.`);
			}

			eventName = EventEmitter.normalizeEventName(eventName);

			if (aliases[eventName])
			{
				const { eventName: actualName } = aliases[eventName];
				EventEmitter.subscribe(this, actualName, listener, { compatMode: compatMode !== false });
			}
			else
			{
				EventEmitter.subscribe(this, eventName, listener, { compatMode: compatMode === true });
			}
		})
	}

	/**
	 * Subscribes a listener that is called at
	 * most once for a specified event.
	 * @param {object} target
	 * @param {string} eventName
	 * @param {Function<BaseEvent>} listener
	 */
	static subscribeOnce(
		target: Object,
		eventName: string,
		listener: (event: BaseEvent) => void
	): void
	{
		if (Type.isString(target))
		{
			listener = eventName;
			eventName = target;
			target = this.GLOBAL_TARGET;
		}

		if (!Type.isObject(target))
		{
			throw new TypeError(`The "target" argument must be an object.`);
		}

		eventName = this.normalizeEventName(eventName);
		if (!Type.isStringFilled(eventName))
		{
			throw new TypeError(`The "eventName" argument must be a string.`);
		}

		if (!Type.isFunction(listener))
		{
			throw new TypeError(`The "listener" argument must be of type Function. Received type ${typeof listener}.`);
		}

		const fullEventName = this.resolveEventName(eventName, target);
		const { eventsMap, onceMap } = eventStore.getOrAdd(target);
		const listeners = eventsMap.get(fullEventName);
		let onceListeners = onceMap.get(fullEventName);

		if ((listeners && listeners.has(listener)) || (onceListeners && onceListeners.has(listener)))
		{
			console.error(`You cannot subscribe the same "${fullEventName}" event listener twice.`);
		}
		else
		{
			const once = (...args) => {
				this.unsubscribe(target, eventName, once);
				onceListeners.delete(listener);
				listener(...args);
			};

			if (onceListeners)
			{
				onceListeners.set(listener, once);
			}
			else
			{
				onceListeners = new Map([[listener, once]]);
				onceMap.set(fullEventName, onceListeners);
			}

			this.subscribe(target, eventName, once);
		}
	}

	/**
	 * Subscribes a listener that is called at most once for a specified event.
	 * @param {string} eventName
	 * @param {Function<BaseEvent>} listener
	 * @return {this}
	 */
	subscribeOnce(eventName: string, listener: (event: BaseEvent) => void): this
	{
		EventEmitter.subscribeOnce(this, eventName, listener);

		return this;
	}

	/**
	 * Unsubscribes an event listener
	 * @param {object} target
	 * @param {string} eventName
	 * @param {Function<BaseEvent>} listener
	 * @param options
	 */
	static unsubscribe(
		target: Object,
		eventName: string,
		listener: (event: BaseEvent) => void,
		options?: {
			useGlobalNaming?: boolean
		}
	): void
	{
		if (Type.isString(target))
		{
			listener = eventName;
			eventName = target;
			target = this.GLOBAL_TARGET;
		}

		eventName = this.normalizeEventName(eventName);
		if (!Type.isStringFilled(eventName))
		{
			throw new TypeError(`The "eventName" argument must be a string.`);
		}

		if (!Type.isFunction(listener))
		{
			throw new TypeError(
				`The "listener" argument must be of type Function. Received type ${typeof event}.`
			);
		}

		options = Type.isPlainObject(options) ? options : {};

		const fullEventName = this.resolveEventName(eventName, target, options.useGlobalNaming === true);
		const targetInfo = eventStore.get(target);
		const listeners = targetInfo && targetInfo.eventsMap.get(fullEventName);
		const onceListeners = targetInfo && targetInfo.onceMap.get(fullEventName);

		if (listeners)
		{
			listeners.delete(listener);
		}

		if (onceListeners)
		{
			const once = onceListeners.get(listener);
			if (once)
			{
				onceListeners.delete(listener);
				listeners.delete(once);
			}
		}
	}

	/**
	 * Unsubscribes an event listener
	 * @param {string} eventName
	 * @param {Function<BaseEvent>} listener
	 * @return {this}
	 */
	unsubscribe(eventName: string, listener: (event: BaseEvent) => void): this
	{
		EventEmitter.unsubscribe(this, eventName, listener);

		return this;
	}

	/**
	 * Unsubscribes all event listeners
	 * @param {object} target
	 * @param {string} eventName
	 * @param options
	 */
	static unsubscribeAll(
		target: Object,
		eventName?: string,
		options?: {
			useGlobalNaming?: boolean
		}
	): void
	{
		if (Type.isString(target))
		{
			eventName = target;
			target = this.GLOBAL_TARGET;
		}

		if (Type.isStringFilled(eventName))
		{
			const targetInfo = eventStore.get(target);
			if (targetInfo)
			{
				options = Type.isPlainObject(options) ? options : {};
				const fullEventName = this.resolveEventName(eventName, target, options.useGlobalNaming === true);
				targetInfo.eventsMap.delete(fullEventName);
				targetInfo.onceMap.delete(fullEventName)
			}
		}
		else if (Type.isNil(eventName))
		{
			if (target === this.GLOBAL_TARGET)
			{
				console.error('You cannot unsubscribe all global listeners.');
			}
			else
			{
				eventStore.delete(target);
			}
		}
	}

	/**
	 * Unsubscribes all event listeners
	 * @param {string} [eventName]
	 */
	unsubscribeAll(eventName?: string): void
	{
		EventEmitter.unsubscribeAll(this, eventName);
	}

	/**
	 *
	 * @param {object} target
	 * @param {string} eventName
	 * @param {BaseEvent | any} event
	 * @param {object} options
	 * @returns {Array}
	 */
	static emit(
		target: Object,
		eventName: string,
		event?: BaseEvent | {[key: string]: any},
		options?: {
			cloneData?: boolean,
			thisArg?: Object,
			useGlobalNaming?: boolean
		}
	): this
	{
		if (Type.isString(target))
		{
			options = event;
			event = eventName;
			eventName = target;
			target = this.GLOBAL_TARGET;
		}

		if (!Type.isObject(target))
		{
			throw new TypeError(`The "target" argument must be an object.`);
		}

		eventName = this.normalizeEventName(eventName);
		if (!Type.isStringFilled(eventName))
		{
			throw new TypeError(`The "eventName" argument must be a string.`);
		}

		options = Type.isPlainObject(options) ? options : {};

		const fullEventName = this.resolveEventName(eventName, target, options.useGlobalNaming === true);
		const globalEvents = eventStore.get(this.GLOBAL_TARGET);
		const globalListeners = (globalEvents && globalEvents.eventsMap.get(fullEventName)) || new Map();

		let targetListeners = new Set();
		if (target !== this.GLOBAL_TARGET)
		{
			const targetEvents = eventStore.get(target);
			targetListeners = (targetEvents && targetEvents.eventsMap.get(fullEventName)) || new Map();
		}

		const listeners = [...globalListeners.values(), ...targetListeners.values()];
		listeners.sort(function(a, b) {
			return a.sort - b.sort;
		});

		const preparedEvent = this.prepareEvent(target, fullEventName, event);
		const result = [];

		for (let i = 0; i < listeners.length; i++)
		{
			if (preparedEvent.isImmediatePropagationStopped())
			{
				break;
			}

			const { listener, options: listenerOptions } = listeners[i];

			//A previous listener could remove a current listener.
			if (globalListeners.has(listener) || targetListeners.has(listener))
			{
				let listenerResult;
				if (listenerOptions.compatMode)
				{
					let params = [];
					const compatData = preparedEvent.getCompatData();
					if (compatData !== null)
					{
						params = options.cloneData === true ? Runtime.clone(compatData) : compatData
					}
					else
					{
						params = [preparedEvent];
					}

					const context = Type.isUndefined(options.thisArg) ? target : options.thisArg;
					listenerResult = listener.apply(context, params);
				}
				else
				{
					listenerResult =
						Type.isUndefined(options.thisArg)
							? listener(preparedEvent)
							: listener.call(options.thisArg, preparedEvent)
					;
				}

				result.push(listenerResult);
			}
		}

		return result;
	}

	/**
	 * Emits specified event with specified event object
	 * @param {string} eventName
	 * @param {BaseEvent | any} event
	 * @return {this}
	 */
	emit(eventName: string, event?: BaseEvent | {[key: string]: any}): this
	{
		EventEmitter.emit(this, eventName, event);

		return this;
	}

	/**
	 * Emits global event and returns a promise that is resolved when
	 * all promise returned from event handlers are resolved,
	 * or rejected when at least one of the returned promise is rejected.
	 * Importantly. You can return any value from synchronous handlers, not just promise
	 * @param {object} target
	 * @param {string} eventName
	 * @param {BaseEvent | any} event
	 * @return {Promise<Array>}
	 */
	static emitAsync(target: Object, eventName: string, event?: BaseEvent | {[key: string]: any}): Promise<Array>
	{
		if (Type.isString(target))
		{
			event = eventName;
			eventName = target;
			target = this.GLOBAL_TARGET;
		}

		return Promise.all(this.emit(target, eventName, event));
	}

	/**
	 * Emits event and returns a promise that is resolved when
	 * all promise returned from event handlers are resolved,
	 * or rejected when at least one of the returned promise is rejected.
	 * Importantly. You can return any value from synchronous handlers, not just promise
	 * @param {string} eventName
	 * @param {BaseEvent|any} event
	 * @return {Promise<Array>}
	 */
	emitAsync(eventName: string, event?: BaseEvent | {[key: string]: any}): Promise<Array>
	{
		return EventEmitter.emitAsync(this, eventName, event);
	}

	/**
	 * @private
	 * @param {object} target
	 * @param {string} eventName
	 * @param {BaseEvent|any} event
	 * @returns {BaseEvent}
	 */
	static prepareEvent(
		target: Object,
		eventName: string,
		event?: BaseEvent | {[key: string]: any},
	): BaseEvent
	{
		let preparedEvent = event;
		if (!(event instanceof BaseEvent))
		{
			preparedEvent = new BaseEvent();
			preparedEvent.setData(event);
		}

		preparedEvent.setTarget(this.isEventEmitter(target) ? target[targetProperty] : target);
		preparedEvent.setType(eventName);

		return preparedEvent;
	}

	/**
	 * @private
	 * @returns {number}
	 */
	static getNextSequenceValue(): number
	{
		return this.sequenceValue++;
	}

	/**
	 * Sets max global events listeners count
	 * Event.EventEmitter.setMaxListeners(10) - sets the default value for all events (global target)
	 * Event.EventEmitter.setMaxListeners("onClose", 10) - sets the value for onClose event (global target)
	 * Event.EventEmitter.setMaxListeners(obj, 10) - sets the default value for all events (obj target)
	 * Event.EventEmitter.setMaxListeners(obj, "onClose", 10); - sets the value for onClose event (obj target)
	 * @return {void}
	 * @param args
	 */
	static setMaxListeners(...args): void
	{
		let target = this.GLOBAL_TARGET;
		let eventName = null;
		let count = undefined;

		if (args.length === 1)
		{
			count = args[0];
		}
		else if (args.length === 2)
		{
			if (Type.isString(args[0]))
			{
				[eventName, count] = args;
			}
			else
			{
				[target, count] = args;
			}
		}
		else if (args.length >= 3)
		{
			[target, eventName, count] = args;
		}

		if (!Type.isObject(target))
		{
			throw new TypeError(`The "target" argument must be an object.`);
		}

		if (eventName !== null && !Type.isStringFilled(eventName))
		{
			throw new TypeError(`The "eventName" argument must be a string.`);
		}

		if (!Type.isNumber(count) || count < 0)
		{
			throw new TypeError(
				`The value of "count" is out of range. It must be a non-negative number. Received ${count}.`
			);
		}

		const targetInfo = eventStore.getOrAdd(target);
		if (Type.isStringFilled(eventName))
		{
			const fullEventName = this.resolveEventName(eventName, target);
			targetInfo.eventsMaxListeners.set(fullEventName, count);
		}
		else
		{
			targetInfo.maxListeners = count;
		}
	}

	/**
	 * Sets max events listeners count
	 * this.setMaxListeners(10) - sets the default value for all events
	 * this.setMaxListeners("onClose", 10) sets the value for onClose event
	 * @return {this}
	 * @param args
	 */
	setMaxListeners(...args): this
	{
		EventEmitter.setMaxListeners(this, ...args);

		return this;
	}

	/**
	 * Returns max event listeners count
	 * @param {object} target
	 * @param {string} [eventName]
	 * @returns {number}
	 */
	static getMaxListeners(target: Object, eventName?: string): number
	{
		if (Type.isString(target))
		{
			eventName = target;
			target = this.GLOBAL_TARGET;
		}
		else if (Type.isNil(target))
		{
			target = this.GLOBAL_TARGET;
		}

		if (!Type.isObject(target))
		{
			throw new TypeError(`The "target" argument must be an object.`);
		}

		const targetInfo = eventStore.get(target);
		if (targetInfo)
		{
			let maxListeners = targetInfo.maxListeners;
			if (Type.isStringFilled(eventName))
			{
				const fullEventName = this.resolveEventName(eventName, target);
				maxListeners = targetInfo.eventsMaxListeners.get(fullEventName) || maxListeners;
			}

			return maxListeners;
		}

		return this.DEFAULT_MAX_LISTENERS;
	}

	/**
	 * Returns max event listeners count
	 * @param {string} [eventName]
	 * @returns {number}
	 */
	getMaxListeners(eventName?: string): number
	{
		return EventEmitter.getMaxListeners(this, eventName);
	}

	/**
	 * Adds or subtracts max listeners count
	 * Event.EventEmitter.addMaxListeners() - adds one max listener for all events of global target
	 * Event.EventEmitter.addMaxListeners(3) - adds three max listeners for all events of global target
	 * Event.EventEmitter.addMaxListeners(-1) - subtracts one max listener for all events of global target
	 * Event.EventEmitter.addMaxListeners('onClose') - adds one max listener for onClose event of global target
	 * Event.EventEmitter.addMaxListeners('onClose', 2) - adds two max listeners for onClose event of global target
	 * Event.EventEmitter.addMaxListeners('onClose', -1) - subtracts one max listener for onClose event of global target
	 *
	 * Event.EventEmitter.addMaxListeners(obj) - adds one max listener for all events of 'obj' target
	 * Event.EventEmitter.addMaxListeners(obj, 3) - adds three max listeners for all events of 'obj' target
	 * Event.EventEmitter.addMaxListeners(obj, -1) - subtracts one max listener for all events of 'obj' target
	 * Event.EventEmitter.addMaxListeners(obj, 'onClose') - adds one max listener for onClose event of 'obj' target
	 * Event.EventEmitter.addMaxListeners(obj, 'onClose', 2) - adds two max listeners for onClose event of 'obj' target
	 * Event.EventEmitter.addMaxListeners(obj, 'onClose', -1) - subtracts one max listener for onClose event of 'obj' target
	 * @param args
	 * @returns {number}
	 */
	static addMaxListeners(...args)
	{
		const [target, eventName, increment] = this.destructMaxListenersArgs(...args);
		const maxListeners = Math.max(this.getMaxListeners(target, eventName) + increment, 0);
		if (Type.isStringFilled(eventName))
		{
			EventEmitter.setMaxListeners(target, eventName, maxListeners);
		}
		else
		{
			EventEmitter.setMaxListeners(target, maxListeners);
		}

		return maxListeners;
	}

	/**
	 * Increases max listeners count
	 *
	 * Event.EventEmitter.incrementMaxListeners() - adds one max listener for all events of global target
	 * Event.EventEmitter.incrementMaxListeners(3) - adds three max listeners for all events of global target
	 * Event.EventEmitter.incrementMaxListeners('onClose') - adds one max listener for onClose event of global target
	 * Event.EventEmitter.incrementMaxListeners('onClose', 2) - adds two max listeners for onClose event of global target
	 *
	 * Event.EventEmitter.incrementMaxListeners(obj) - adds one max listener for all events of 'obj' target
	 * Event.EventEmitter.incrementMaxListeners(obj, 3) - adds three max listeners for all events of 'obj' target
	 * Event.EventEmitter.incrementMaxListeners(obj, 'onClose') - adds one max listener for onClose event of 'obj' target
	 * Event.EventEmitter.incrementMaxListeners(obj, 'onClose', 2) - adds two max listeners for onClose event of 'obj' target
	 */
	static incrementMaxListeners(...args): number
	{
		const [target, eventName, increment] = this.destructMaxListenersArgs(...args);

		return this.addMaxListeners(target, eventName, Math.abs(increment));
	}

	/**
	 * Increases max listeners count
	 * this.incrementMaxListeners() - adds one max listener for all events
	 * this.incrementMaxListeners(3) - adds three max listeners for all events
	 * this.incrementMaxListeners('onClose') - adds one max listener for onClose event
	 * this.incrementMaxListeners('onClose', 2) - adds two max listeners for onClose event
	 */
	incrementMaxListeners(...args): number
	{
		return EventEmitter.incrementMaxListeners(this, ...args);
	}

	/**
	 * Decreases max listeners count
	 *
	 * Event.EventEmitter.decrementMaxListeners() - subtracts one max listener for all events of global target
	 * Event.EventEmitter.decrementMaxListeners(3) - subtracts three max listeners for all events of global target
	 * Event.EventEmitter.decrementMaxListeners('onClose') - subtracts one max listener for onClose event of global target
	 * Event.EventEmitter.decrementMaxListeners('onClose', 2) - subtracts two max listeners for onClose event of global target
	 *
	 * Event.EventEmitter.decrementMaxListeners(obj) - subtracts one max listener for all events of 'obj' target
	 * Event.EventEmitter.decrementMaxListeners(obj, 3) - subtracts three max listeners for all events of 'obj' target
	 * Event.EventEmitter.decrementMaxListeners(obj, 'onClose') - subtracts one max listener for onClose event of 'obj' target
	 * Event.EventEmitter.decrementMaxListeners(obj, 'onClose', 2) - subtracts two max listeners for onClose event of 'obj' target
	 */
	static decrementMaxListeners(...args): number
	{
		const [target, eventName, increment] = this.destructMaxListenersArgs(...args);

		return this.addMaxListeners(target, eventName, -Math.abs(increment));
	}

	/**
	 * Increases max listeners count
	 * this.decrementMaxListeners() - subtracts one max listener for all events
	 * this.decrementMaxListeners(3) - subtracts three max listeners for all events
	 * this.decrementMaxListeners('onClose') - subtracts one max listener for onClose event
	 * this.decrementMaxListeners('onClose', 2) - subtracts two max listeners for onClose event
	 */
	decrementMaxListeners(...args): number
	{
		return EventEmitter.decrementMaxListeners(this, ...args);
	}

	/**
	 * @private
	 * @param {Array} args
	 * @returns Array
	 */
	static destructMaxListenersArgs(...args)
	{
		let eventName = null;
		let increment = 1;
		let target = this.GLOBAL_TARGET;

		if (args.length === 1)
		{
			if (Type.isNumber(args[0]))
			{
				increment = args[0];
			}
			else if (Type.isString(args[0]))
			{
				eventName = args[0];
			}
			else
			{
				target = args[0];
			}
		}
		else if (args.length === 2)
		{
			if (Type.isString(args[0]))
			{
				[eventName, increment] = args;
			}
			else if (Type.isString(args[1]))
			{
				[target, eventName] = args;
			}
			else
			{
				[target, increment] = args;
			}
		}
		else if (args.length >= 3)
		{
			[target, eventName, increment] = args;
		}

		if (!Type.isObject(target))
		{
			throw new TypeError(`The "target" argument must be an object.`);
		}

		if (eventName !== null && !Type.isStringFilled(eventName))
		{
			throw new TypeError(`The "eventName" argument must be a string.`);
		}

		if (!Type.isNumber(increment))
		{
			throw new TypeError(`The value of "increment" must be a number.`);
		}

		return [target, eventName, increment];
	}

	/**
	 * Gets listeners list for a specified event
	 * @param {object} target
	 * @param {string} eventName
	 */
	static getListeners(target: Object, eventName: string): Function[]
	{
		if (Type.isString(target))
		{
			eventName = target;
			target = this.GLOBAL_TARGET;
		}

		if (!Type.isObject(target))
		{
			throw new TypeError(`The "target" argument must be an object.`);
		}

		eventName = this.normalizeEventName(eventName);
		if (!Type.isStringFilled(eventName))
		{
			throw new TypeError(`The "eventName" argument must be a string.`);
		}

		const targetInfo = eventStore.get(target);
		if (!targetInfo)
		{
			return new Map();
		}

		const fullEventName = this.resolveEventName(eventName, target);
		return targetInfo.eventsMap.get(fullEventName) || new Map();
	}

	/**
	 * Gets listeners list for specified event
	 * @param {string} eventName
	 */
	getListeners(eventName: string): Function[]
	{
		return EventEmitter.getListeners(this, eventName);
	}

	/**
	 * Returns a full event name with namespace
	 * @param {string} eventName
	 * @returns {string}
	 */
	getFullEventName(eventName: string)
	{
		if (!Type.isStringFilled(eventName))
		{
			throw new TypeError(`The "eventName" argument must be a string.`);
		}

		return EventEmitter.makeFullEventName(this.getEventNamespace(), eventName);
	}

	/**
	 * Registers aliases (old event names for BX.onCustomEvent)
	 * @param aliases
	 */
	static registerAliases(aliases: { [alias: string]: { eventName: string, namespace: string } })
	{
		aliases = this.normalizeAliases(aliases);

		Object.keys(aliases).forEach((alias) => {
			aliasStore.set(alias, {
				eventName: aliases[alias].eventName,
				namespace: aliases[alias].namespace
			});
		});

		EventEmitter.mergeEventAliases(aliases);
	}

	/**
	 * @private
	 * @param aliases
	 */
	static normalizeAliases(aliases: { [alias: string]: { eventName: string, namespace: string } })
	{
		if (!Type.isPlainObject(aliases))
		{
			throw new TypeError(`The "aliases" argument must be an object.`);
		}

		const result = Object.create(null);
		for (let alias in aliases)
		{
			if (!Type.isStringFilled(alias))
			{
				throw new TypeError(`The alias must be an non-empty string.`);
			}

			const options = aliases[alias];
			if (!options || !Type.isStringFilled(options.eventName) || !Type.isStringFilled(options.namespace))
			{
				throw new TypeError(`The alias options must set the "eventName" and the "namespace".`);
			}

			alias = this.normalizeEventName(alias);

			result[alias] = {
				eventName: options.eventName,
				namespace: options.namespace
			};
		}

		return result;
	}

	/**
	 * @private
	 */
	static mergeEventAliases(aliases)
	{
		const globalEvents = eventStore.get(this.GLOBAL_TARGET);
		if (!globalEvents)
		{
			return;
		}

		Object.keys(aliases).forEach((alias) => {
			const options = aliases[alias];
			alias = this.normalizeEventName(alias);
			const fullEventName = this.makeFullEventName(options.namespace, options.eventName);

			const aliasListeners = globalEvents.eventsMap.get(alias);
			if (aliasListeners)
			{
				const listeners = globalEvents.eventsMap.get(fullEventName) || new Map();
				globalEvents.eventsMap.set(fullEventName, new Map([...listeners, ...aliasListeners]));
				globalEvents.eventsMap.delete(alias);
			}

			const aliasOnceListeners = globalEvents.onceMap.get(alias);
			if (aliasOnceListeners)
			{
				const onceListeners = globalEvents.onceMap.get(fullEventName) || new Map();
				globalEvents.onceMap.set(fullEventName, new Map([...onceListeners, ...aliasOnceListeners]));
				globalEvents.onceMap.delete(alias);
			}

			const aliasMaxListeners = globalEvents.eventsMaxListeners.get(alias);
			if (aliasMaxListeners)
			{
				const eventMaxListeners = globalEvents.eventsMaxListeners.get(fullEventName) || 0;
				globalEvents.eventsMaxListeners.set(fullEventName, Math.max(eventMaxListeners, aliasMaxListeners));
				globalEvents.eventsMaxListeners.delete(alias);
			}
		});
	}

	/**
	 * Returns true if the target is an instance of Event.EventEmitter
	 * @param {object} target
	 * @returns {boolean}
	 */
	static isEventEmitter(target: Object)
	{
		return Type.isObject(target) && target[isEmitterProperty] === true;
	}

	/**
	 * @private
	 * @param {string} eventName
	 * @returns {string}
	 */
	static normalizeEventName(eventName: string)
	{
		if (!Type.isStringFilled(eventName))
		{
			return '';
		}

		return eventName.toLowerCase();
	}

	/**
	 * @private
	 * @param eventName
	 * @param target
	 * @param useGlobalNaming
	 * @returns {string}
	 */
	static resolveEventName(eventName: string, target: Object, useGlobalNaming: boolean = false)
	{
		eventName = this.normalizeEventName(eventName);
		if (!Type.isStringFilled(eventName))
		{
			return '';
		}

		if (this.isEventEmitter(target) && useGlobalNaming !== true)
		{
			if (target.getEventNamespace() !== null && eventName.includes('.'))
			{
				console.warn(`Possible the wrong event name "${eventName}".`);
			}

			eventName = target.getFullEventName(eventName);
		}
		else if (aliasStore.has(eventName))
		{
			const { namespace, eventName: actualEventName } = aliasStore.get(eventName);
			eventName = this.makeFullEventName(namespace, actualEventName);
		}

		return eventName;
	}

	/**
	 * @private
	 * @param {string} namespace
	 * @param {string} eventName
	 * @returns {string}
	 */
	static makeFullEventName(namespace: string, eventName: string)
	{
		const fullName = Type.isStringFilled(namespace) ? `${namespace}:${eventName}` : eventName;

		return Type.isStringFilled(fullName) ? fullName.toLowerCase() : '';
	}
}