import {EventEmitter} from 'main.core.events';
import {Type} from 'main.core';
import {Utils} from 'im.v2.lib.utils';

export class BroadcastManager extends EventEmitter
{
	static instance = null;
	static channelName = 'im-recent';
	static eventNamespace = 'BX.Messenger.v2.Recent.BroadcastManager';
	static events = {
		recentListUpdate: 'recentListUpdate'
	};

	static getInstance()
	{
		if (!this.instance)
		{
			this.instance = new this();
		}

		return this.instance;
	}

	constructor()
	{
		super();
		this.setEventNamespace(BroadcastManager.eventNamespace);
		this.init();
	}

	isSupported()
	{
		return !Type.isUndefined(window.BroadcastChannel) && !Utils.platform.isBitrixDesktop();
	}

	init()
	{
		if (!this.isSupported())
		{
			return;
		}

		this.channel = new BroadcastChannel(BroadcastManager.channelName);
		this.channel.addEventListener('message', ({data: {type, data}}) => {
			this.emit(type, data);
		});
	}

	sendRecentList(recentData: Object)
	{
		if (!this.isSupported())
		{
			return;
		}

		this.channel.postMessage({
			type: BroadcastManager.events.recentListUpdate,
			data: recentData
		});
	}
}