import { Text } from 'main.core';
import { BrowserTime } from './browser-time';
import { Offset } from './offset';

/**
 * @memberOf BX.Main.Timezone
 *
 * WARNING! Don't use this class or any classes from Timezone namespace on sites without Bitrix Framework.
 * It is not designed to handle this case and will definitely break.
 */
export class ServerTime
{
	/**
	 * Returns timestamp with current time in server timezone.
	 *
	 * @returns {number} timestamp in seconds
	 */
	static getTimestamp(): number
	{
		return BrowserTime.toServer(BrowserTime.getTimestamp());
	}

	/**
	 * Returns Date object with current time in server timezone.
	 *
	 * Note that 'getTimezoneOffset' will not return correct server timezone, its always returns browser offset
	 *
	 * @returns {Date}
	 */
	static getDate(): Date
	{
		return new Date(this.getTimestamp() * 1000);
	}

	/**
	 * Converts timestamp in server timezone to timestamp in user timezone.
	 *
	 * @param serverTimestamp timestamp in server timezone in seconds
	 * @returns {number} timestamp in user timezone in seconds
	 */
	static toUser(serverTimestamp: number): number
	{
		return Text.toInteger(serverTimestamp) + Offset.USER_TO_SERVER;
	}

	/**
	 * Converts timestamp in server timezone to timestamp in browser timezone.
	 *
	 * @param serverTimestamp timestamp in server timezone in seconds
	 * @returns {number} timestamp in browser timezone in seconds
	 */
	static toBrowser(serverTimestamp: number): number
	{
		return Text.toInteger(serverTimestamp) + Offset.BROWSER_TO_UTC - Offset.SERVER_TO_UTC;
	}
}
