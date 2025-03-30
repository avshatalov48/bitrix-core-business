/* eslint-disable @bitrix24/bitrix24-rules/no-native-events-binding */
// noinspection ES6PreferShortImport

import { getTimestamp, isNumber, isPlainObject } from '../../util/src/util';
import { REVISION } from '../../client/src/consts';
import type { RestCaller } from '../../minirest/src/restcaller';

type ConfigHolderOptions = {
	configGetMethod: ?string,
	restClient: RestCaller,
	events: { [k: $Values<typeof ConfigHolderEvents>]: (e: CustomEvent) => void }
}

type ChannelDescription = {
	id: string,
	public_id: ?string,
	type: string,
	start: string, // DATE_ATOM
	end: string, // DATE_ATOM
}

type PublicChannelDescription = {
	user_id: number,
	public_id: string,
	signature: string,
	start: string, // DATE_ATOM
	end: string, // DATE_ATOM
}

export type PullConfig = {
	api: {
		revision_mobile: number,
		revision_web: number,
	},
	channels: {
		private: ?ChannelDescription,
		shared: ?ChannelDescription,
	},
	publicChannels: { [user_id: number]: PublicChannelDescription },
	server: {
		config_timestamp: number,
		long_polling: string,
		long_pooling_secure: string,
		mode: string,
		publish: string,
		publish_enabled: boolean,
		publish_secure: string,
		server_enabled: boolean,
		timeShift: number,
		version: number,
		websocket: string,
		websocket_enabled: boolean,
		websocket_secure: string,
	},
	clientId: null,
	jwt: null,
	exp: 0,
}

const CONFIG_CHECK_INTERVAL = 60000;

export const ConfigHolderEvents = {
	ConfigExpired: 'configExpired',
	RevisionChanged: 'revisionChanged',
};

export class ConfigHolder extends EventTarget
{
	configGetMethod = 'pull.config.get';
	restClient: RestCaller;

	config: ?PullConfig;

	checkInterval: ?number;

	constructor(options: ConfigHolderOptions = {})
	{
		super();

		if (options.configGetMethod)
		{
			this.configGetMethod = options.configGetMethod;
		}
		this.restClient = options.restClient;

		for (const eventName of Object.keys(options.events || {}))
		{
			this.addEventListener(eventName, options.events[eventName]);
		}
	}

	loadConfig(logTag): Promise<Config>
	{
		this.stopCheckConfig();

		return new Promise((resolve, reject) => {
			this.restClient.callMethod(this.configGetMethod, { CACHE: 'N' }, undefined, undefined, logTag).then((response) => {
				const data = response.data();
				const timeShift = Math.floor((getTimestamp() - new Date(data.serverTime).getTime()) / 1000);
				delete data.serverTime;

				this.config = { ...data };
				this.config.server.timeShift = timeShift;
				this.startCheckConfig();

				resolve(this.config);
			}).catch((response) => {
				this.config = undefined;

				const error = response.error();
				if (error.getError().error === 'AUTHORIZE_ERROR' || error.getError().error === 'WRONG_AUTH_TYPE')
				{
					error.status = 403;
				}
				reject(error);
			});
		});
	}

	startCheckConfig()
	{
		if (this.checkInterval)
		{
			clearInterval(this.checkInterval);
		}

		this.checkInterval = setInterval(() => this.checkConfig(), CONFIG_CHECK_INTERVAL);
	}

	stopCheckConfig()
	{
		if (this.checkInterval)
		{
			clearInterval(this.checkInterval);
		}
		this.checkInterval = null;
	}

	checkConfig()
	{
		if (!this.isConfigActual(this.config))
		{
			this.dispatchEvent(new CustomEvent(ConfigHolderEvents.ConfigExpired));
		}
		else if (this.config.api.revision_web !== REVISION)
		{
			this.dispatchEvent(new CustomEvent(ConfigHolderEvents.RevisionChanged, {
				detail: { revision: this.config.api.revision_web },
			}));
		}
	}

	isConfigActual(config): boolean
	{
		if (!isPlainObject(config))
		{
			return false;
		}

		if (config.server.config_timestamp < this.configTimestamp)
		{
			return false;
		}

		const now = new Date();

		if (isNumber(config.exp) && config.exp > 0 && config.exp < now.getTime() / 1000)
		{
			return false;
		}

		const channelTypes = Object.keys(config.channels || {});
		if (channelTypes.length === 0)
		{
			return false;
		}

		for (const channelType of channelTypes)
		{
			const channel = config.channels[channelType];
			const channelEnd = new Date(channel.end);

			if (channelEnd < now)
			{
				return false;
			}
		}

		return true;
	}

	dispose()
	{
		this.stopCheckConfig();
	}
}
