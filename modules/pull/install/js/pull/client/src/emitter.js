/* eslint-disable @bitrix24/bitrix24-rules/no-typeof */
/* eslint-disable @bitrix24/bitrix24-rules/no-bx */
/* eslint-disable @bitrix24/bitrix24-rules/no-bx-message */
// noinspection JSUnusedAssignment

import { clone, isArray, isFunction, isNotEmptyString, isObject, isPlainObject } from 'pull.util';
import { SenderType, SubscriptionType } from './consts';

interface Logger
{
	log(message: string, ...params): void
}

type PullEvent = {
	module_id: string,
	command: string,
	params: Object,
	extra: ?EventExtraFields
}

type InternalEvent = {
	type: $Values<typeof SubscriptionType>,
	moduleId: string,
	data: CallbackData,
}

type CallbackData = {
	command: string,
	params: Object,
	extra: ?EventExtraFields
}

type EmitterOptions = {
	logger: ?Logger,
}

type EventExtraFields = {
	sender: Sender
}

type Sender = {
	type: $Values<typeof SenderType>,
}

type SubscriptionParams = {
	// Subscription type (for possible values @see SubscriptionType); SubscriptionType.Server by default
	type: ?string,
	// Name of the module.
	moduleId: string,
	// Command to be subscribed to
	command: string,
	// Function, that will be called for incoming messages.
	callback: HandlerFunc
}

export type HandlerFunc = (params: Object, extra: Object, command: string) => void;

export type Handler = {
	getSubscriptionType: ? () => string,
	getModuleId: ? () => string,
	getMap: ? () => { [string]: HandlerFunc },
}

export class Emitter
{
	#subscribers = {};
	#logger: Logger;

	debug = false;
	userStatusCallbacks = {}; // [userId] => array of callbacks

	constructor(options: EmitterOptions = {})
	{
		this.#logger = options.logger;
	}

	/**
	 * Creates a subscription to incoming messages.
	 *
	 * @returns {Function} - Unsubscribe callback function
	 */
	subscribe(params: SubscriptionParams): () => void
	{
		/**
		 * After modify this method, copy to follow scripts:
		 * mobile/install/mobileapp/mobile/extensions/bitrix/pull/client/events/extension.js
		 * mobile/install/js/mobile/pull/client/src/client.js
		 */

		if (!isObject(params))
		{
			throw new TypeError('params must be an object');
		}

		if (!isPlainObject(params))
		{
			return this.attachCommandHandler(params);
		}

		const { command, moduleId, callback, type = SubscriptionType.Server } = params;

		if (type === SubscriptionType.Server || type === SubscriptionType.Client)
		{
			if (typeof (this.#subscribers[type]) === 'undefined')
			{
				this.#subscribers[type] = {};
			}

			if (typeof (this.#subscribers[type][moduleId]) === 'undefined')
			{
				this.#subscribers[type][moduleId] = {
					callbacks: [],
					commands: {},
				};
			}

			if (command)
			{
				if (!isArray(this.#subscribers[type][moduleId].commands[command]))
				{
					this.#subscribers[type][moduleId].commands[command] = [];
				}

				this.#subscribers[type][moduleId].commands[command].push(callback);

				return () => {
					// eslint-disable-next-line max-len
					this.#subscribers[type][moduleId].commands[command] = this.#subscribers[type][moduleId].commands[command].filter((element) => {
						return element !== callback;
					});
				};
			}

			this.#subscribers[type][moduleId].callbacks.push(callback);

			return () => {
				this.#subscribers[type][moduleId].callbacks = this.#subscribers[type][moduleId].callbacks.filter((element) => {
					return element !== callback;
				});
			};
		}

		if (typeof (this.#subscribers[type]) === 'undefined')
		{
			this.#subscribers[type] = [];
		}

		this.#subscribers[type].push(callback);

		return () => {
			this.#subscribers[type] = this.#subscribers[type].filter((element) => {
				return element !== callback;
			});
		};
	}

	/*
	 Subscribes provided handler to pull events.
	 @return {() => void} Returns function, that can be called to unsubscribe the handler.
	 */
	attachCommandHandler(handler: Handler): () => void
	{
		/**
		 * After modify this method, copy to follow scripts:
		 * mobile/install/mobileapp/mobile/extensions/bitrix/pull/client/events/extension.js
		 */
		const moduleId = isFunction(handler.getModuleId) ? handler.getModuleId() : '';
		if (!isNotEmptyString(moduleId))
		{
			throw new TypeError('handler.getModuleId() must return a string');
		}

		let type = SubscriptionType.Server;
		if (isFunction(handler.getSubscriptionType))
		{
			type = handler.getSubscriptionType();
			if (!Object.values(SubscriptionType).includes(type))
			{
				throw new Error('result of handler.getSubscriptionType() must return valid SubscriptionType element');
			}
		}

		return this.subscribe({
			type,
			moduleId,
			callback: (data: CallbackData) => {
				const method = findHandlerMethod(handler, data.command);

				if (method)
				{
					let loggableData = '';
					try
					{
						loggableData = JSON.stringify(data);
					}
					catch
					{
						loggableData = '(contains circular references)';
					}

					this.#logger?.log(`Pull.attachCommandHandler: receive command ${loggableData}`);
					method(data.params, data.extra, data.command);
				}
			},
		});
	}

	/**
	 *
	 * @param params {Object}
	 * @returns {boolean}
	 */
	emit(params: InternalEvent = {}): boolean
	{
		/**
		 * After modify this method, copy to follow scripts:
		 * mobile/install/mobileapp/mobile/extensions/bitrix/pull/client/events/extension.js
		 * mobile/install/js/mobile/pull/client/src/client.js
		 */
		if (params.type === SubscriptionType.Server || params.type === SubscriptionType.Client)
		{
			if (typeof (this.#subscribers[params.type]) === 'undefined')
			{
				this.#subscribers[params.type] = {};
			}

			if (typeof (this.#subscribers[params.type][params.moduleId]) === 'undefined')
			{
				this.#subscribers[params.type][params.moduleId] = {
					callbacks: [],
					commands: {},
				};
			}

			if (this.#subscribers[params.type][params.moduleId].callbacks.length > 0)
			{
				this.#subscribers[params.type][params.moduleId].callbacks.forEach((callback) => {
					callback(params.data, { type: params.type, moduleId: params.moduleId });
				});
			}

			if (
				this.#subscribers[params.type][params.moduleId].commands[params.data.command]
				&& this.#subscribers[params.type][params.moduleId].commands[params.data.command].length > 0)
			{
				this.#subscribers[params.type][params.moduleId].commands[params.data.command].forEach((callback) => {
					callback(params.data.params, params.data.extra, params.data.command, {
						type: params.type,
						moduleId: params.moduleId,
					});
				});
			}

			return true;
		}

		if (typeof (this.#subscribers[params.type]) === 'undefined')
		{
			this.#subscribers[params.type] = [];
		}

		if (this.#subscribers[params.type].length <= 0)
		{
			return true;
		}

		this.#subscribers[params.type].forEach((callback) => {
			callback(params.data, { type: params.type });
		});

		return true;
	}

	broadcastMessage(message: PullEvent)
	{
		const moduleId = message.module_id.toLowerCase();
		const command = message.command;
		const params = message.params;
		const extra = message.extra ?? {};

		this.logMessage(message);
		try
		{
			if (extra.sender && extra.sender.type === SenderType.Client)
			{
				this.emitClientEvent(moduleId, command, clone(params), clone(extra));
			}
			else if (moduleId === 'online')
			{
				if (extra.server_time_ago < 240)
				{
					this.emitOnlineEvent(moduleId, command, clone(params), clone(extra));
				}

				if (command === 'userStatusChange')
				{
					this.emitUserStatusChange(params.user_id, params.online);
				}
			}
			else
			{
				this.emitServerEvent(moduleId, command, clone(params), clone(extra));
			}
		}
		catch (e)
		{
			if (typeof (console) === 'object')
			{
				console.error('\n========= PULL ERROR ===========\n'
					+ 'Error type: broadcastMessages execute error\n'
					+ 'Error event: ', e, '\n'
					+ 'Message: ', message, '\n'
					+ '================================\n');
				if (isFunction(BX.debug))
				{
					BX.debug(e);
				}
			}
		}
	}

	emitServerEvent(moduleId: string, command: string, params: any, extra: any)
	{
		if ('BX' in globalThis && isFunction(BX.onCustomEvent))
		{
			BX.onCustomEvent(window, `onPullEvent-${moduleId}`, [command, params, extra], true);
			BX.onCustomEvent(window, 'onPullEvent', [moduleId, command, params, extra], true);
		}

		this.emit({ type: SubscriptionType.Server, moduleId, data: { command, params, extra } });
	}

	emitClientEvent(moduleId: string, command: string, params: any, extra: any)
	{
		if (isFunction(BX.onCustomEvent))
		{
			BX.onCustomEvent(window, `onPullClientEvent-${moduleId}`, [command, params, extra], true);
			BX.onCustomEvent(window, 'onPullClientEvent', [moduleId, command, params, extra], true);
		}

		this.emit({ type: SubscriptionType.Client, moduleId, data: { command, params, extra } });
	}

	emitOnlineEvent(moduleId: string, command: string, params: any, extra: any)
	{
		if (isFunction(BX.onCustomEvent))
		{
			BX.onCustomEvent(window, 'onPullOnlineEvent', [command, params, extra], true);
		}

		this.emit({ type: SubscriptionType.Online, data: { command, params, extra } });
	}

	addUserStatusCallback(userId: number, callback: UserStatusCallback)
	{
		if (!this.userStatusCallbacks[userId])
		{
			this.userStatusCallbacks[userId] = [];
		}

		if (isFunction(callback))
		{
			this.userStatusCallbacks[userId].push(callback);
		}
	}

	removeUserStatusCallback(userId: number, callback: UserStatusCallback)
	{
		if (this.userStatusCallbacks[userId])
		{
			this.userStatusCallbacks[userId] = this.userStatusCallbacks[userId].filter((cb) => cb !== callback);
		}
	}

	hasUserStatusCallbacks(userId: number): boolean
	{
		return this.userStatusCallbacks[userId].length > 0;
	}

	emitUserStatusChange(userId, isOnline)
	{
		if (this.userStatusCallbacks[userId])
		{
			this.userStatusCallbacks[userId].forEach((cb) => cb({ userId, isOnline }));
		}
	}

	getSubscribedUsersList(): number[]
	{
		const result = [];
		for (const userId of Object.keys(this.userStatusCallbacks))
		{
			if (this.userStatusCallbacks[userId].length > 0)
			{
				result.push(Number(userId));
			}
		}

		return result;
	}

	capturePullEvent(debugFlag: boolean = true)
	{
		this.debug = debugFlag;
	}

	logMessage(message: PullEvent)
	{
		if (!this.debug)
		{
			return;
		}

		if (message.extra.sender && message.extra.sender.type === SenderType.Client)
		{
			console.info(`onPullClientEvent-${message.module_id}`, message.command, message.params, message.extra);
		}
		else if (message.module_id === 'online')
		{
			console.info('onPullOnlineEvent', message.command, message.params, message.extra);
		}
		else
		{
			console.info('onPullEvent', message.module_id, message.command, message.params, message.extra);
		}
	}
}

function findHandlerMethod(handler: Handler, command: string): HandlerFunc | null
{
	let method = null;

	if (isFunction(handler.getMap))
	{
		const mapping = handler.getMap();
		if (isPlainObject(mapping))
		{
			if (isFunction(mapping[command]))
			{
				method = mapping[command].bind(handler);
			}
			else if (typeof mapping[command] === 'string' && isFunction(handler[mapping[command]]))
			{
				method = handler[mapping[command]].bind(handler);
			}
		}
	}

	if (!method)
	{
		const methodName = getDefaultHandlerMethodName(command);
		if (isFunction(handler[methodName]))
		{
			method = handler[methodName].bind(handler);
		}
	}

	return method;
}

function getDefaultHandlerMethodName(command: string): string
{
	return `handle${command.charAt(0).toUpperCase()}${command.slice(1)}`;
}
