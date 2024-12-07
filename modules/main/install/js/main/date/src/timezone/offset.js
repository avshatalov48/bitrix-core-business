import { Cache, Loc, Text } from 'main.core';

const cache = new Cache.MemoryCache();

export type OffsetInterface = {
	SERVER_TO_UTC: number,
	USER_TO_SERVER: number,
	BROWSER_TO_UTC: number,
}

/**
 * @memberOf BX.Main.Timezone
 *
 * WARNING! Don't use this class or any classes from Timezone namespace on sites without Bitrix Framework.
 * It is not designed to handle this case and will definitely break.
 */
const Offset: OffsetInterface = {
	get SERVER_TO_UTC(): number
	{
		return cache.remember('SERVER_TO_UTC', () => {
			return Text.toInteger(Loc.getMessage('SERVER_TZ_OFFSET'));
		});
	},

	get USER_TO_SERVER(): number
	{
		return cache.remember('USER_TO_SERVER', () => {
			return Text.toInteger(Loc.getMessage('USER_TZ_OFFSET'));
		});
	},

	// Date returns timezone offset in minutes by default, change it to seconds
	// Also offset is negative in UTC+ timezones and positive in UTC- timezones.
	// By convention Bitrix uses the opposite approach, so change offset sign.
	get BROWSER_TO_UTC(): number
	{
		return cache.remember('BROWSER_TO_UTC', () => {
			const offset = Text.toInteger((new Date()).getTimezoneOffset() * 60);

			return -offset;
		});
	},
};

Object.freeze(Offset);

export {
	Offset,
};
