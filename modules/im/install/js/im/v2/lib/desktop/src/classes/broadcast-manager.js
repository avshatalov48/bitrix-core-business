import { Event } from 'main.core';

import { DesktopBroadcastAction } from 'im.v2.const';
import { CallManager } from 'im.v2.lib.call';
import { MessageNotifierManager } from 'im.v2.lib.message-notifier';
import { DesktopApi } from 'im.v2.lib.desktop-api';

import { BxLinkProcessor } from '../helpers/bx-link-processor';

import type { JsonObject } from 'main.core';
import type { NotifierClickParams } from 'im.v2.lib.message-notifier';
import type { DesktopBxLinkKey, RawParams } from './event-handlers/bx-link';

type DesktopBroadcastActionValue = $Values<typeof DesktopBroadcastAction>;

type ActionMessageEvent = {
	action: DesktopBroadcastActionValue,
	params: JsonObject,
}

type BxLinkParams = {
	command: DesktopBxLinkKey,
	rawParams: ?RawParams
}

const CHANNEL_DESKTOP = 'im-channel-desktop';

export class DesktopBroadcastManager
{
	#actionHandlers = {
		[DesktopBroadcastAction.notification]: this.#onNotifierClick.bind(this),
		[DesktopBroadcastAction.answerButtonClick]: this.#onAnswerButtonClick.bind(this),
		[DesktopBroadcastAction.bxLink]: this.#handleBxLinkCommand.bind(this),
	};

	static getInstance(): DesktopBroadcastManager
	{
		if (!this.instance)
		{
			this.instance = new this();
		}

		return this.instance;
	}

	static init()
	{
		DesktopBroadcastManager.getInstance();
	}

	constructor()
	{
		this.initBroadcastHandler();
	}

	initBroadcastHandler()
	{
		this.channel = new BroadcastChannel(CHANNEL_DESKTOP);

		if (DesktopApi.isChatWindow())
		{
			return;
		}

		Event.bind(this.channel, 'message', (event: MessageEvent) => {
			const { data } = event;

			if (!DesktopApi.isActiveTab())
			{
				return;
			}

			const handleAction = this.#actionHandlers[data.action];

			if (!handleAction)
			{
				return;
			}

			handleAction(data.params);
		});
	}

	sendActionMessage(message: ActionMessageEvent)
	{
		this.channel.postMessage(message);
	}

	#onNotifierClick(params: NotifierClickParams)
	{
		MessageNotifierManager.getInstance().onNotifierClick(params);
	}

	#onAnswerButtonClick(params: JsonObject)
	{
		const { mediaParams, callParams } = params;

		CallManager.getInstance().onAnswerButtonClick(mediaParams, callParams);
	}

	#handleBxLinkCommand(params: BxLinkParams)
	{
		BxLinkProcessor.handleCommand(params.command, params.rawParams);
	}
}
