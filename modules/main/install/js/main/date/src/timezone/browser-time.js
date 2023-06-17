import { Text } from 'main.core';
import { Offset } from './offset';

/**
 * @memberOf BX.Main.Timezone
 *
 * WARNING! Don't use this class or any classes from Timezone namespace on sites without Bitrix Framework.
 * It is not designed to handle this case and will definitely break.
 */
export class BrowserTime
{
	/**
	 * Returns timestamp with current time in browser timezone.
	 *
	 * @returns {number} timestamp in seconds
	 */
	static getTimestamp(): number
	{
		return Math.round(Date.now() / 1000);
	}

	/**
	 * Returns Date object with current time in browser timezone.
	 *
	 * @returns {Date}
	 */
	static getDate(): Date
	{
		return new Date(this.getTimestamp() * 1000);
	}

	/**
	 * Converts timestamp in browser timezone to timestamp in user timezone.
	 *
	 * @param browserTimestamp timestamp in browser timezone in seconds
	 * @returns {number} timestamp in user timezone in seconds
	 */
	static toUser(browserTimestamp: number): number
	{
		return Text.toInteger(browserTimestamp) + Offset.USER_TO_SERVER;
	}

	/**
	 * Converts timestamp in browser timezone to timestamp in server timezone.
	 *
	 * @param browserTimestamp timestamp in browser timezone in seconds
	 * @returns {number} timestamp in server timezone in seconds
	 */
	static toServer(browserTimestamp: number): number
	{
		return this.#toUTC(browserTimestamp) + Offset.SERVER_TO_UTC;
	}

	static #toUTC(browserTimestamp: number): number
	{
		return Text.toInteger(browserTimestamp) - Offset.BROWSER_TO_UTC;
	}
}
