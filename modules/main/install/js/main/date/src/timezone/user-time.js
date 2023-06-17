import { Text } from 'main.core';
import { Offset } from './offset';
import { BrowserTime } from './browser-time';

/**
 * @memberOf BX.Main.Timezone
 *
 * WARNING! Don't use this class or any classes from Timezone namespace on sites without Bitrix Framework.
 * It is not designed to handle this case and will definitely break.
 *
 * ATTENTION! In Bitrix user timezone !== browser timezone. Users can change their timezone from their profile settings
 * and the timezone will be different from browser timezone.
 */
export class UserTime
{
	/**
	 * Returns timestamp with current time in user timezone.
	 *
	 * @returns {number} timestamp in seconds
	 */
	static getTimestamp(): number
	{
		return BrowserTime.toUser(BrowserTime.getTimestamp());
	}

	/**
	 * Returns Date object with current time in user timezone. If you need to get 'now' in a user's perspective,
	 * use this method instead of 'new Date()'.
	 *
	 * Note that 'getTimezoneOffset' will not return correct user timezone, its always returns browser offset
	 *
	 * @returns {Date}
	 */
	static getDate(): Date
	{
		return new Date(this.getTimestamp() * 1000);
	}

	/**
	 * Converts timestamp in user timezone to timestamp in browser timezone.
	 *
	 * @param userTimestamp timestamp in user timezone in seconds
	 * @returns {number} timestamp in browser timezone in seconds
	 */
	static toBrowser(userTimestamp: number): number
	{
		return (
			Text.toInteger(userTimestamp)
			+ Offset.BROWSER_TO_UTC
			- Offset.SERVER_TO_UTC
			- Offset.USER_TO_SERVER
		);
	}

	/**
	 * Converts timestamp in user timezone to timestamp in server timezone.
	 *
	 * @param userTimestamp timestamp in user timezone in seconds
	 * @returns {number} timestamp in server timezone in seconds
	 */
	static toServer(userTimestamp: number): number
	{
		return Text.toInteger(userTimestamp) - Offset.USER_TO_SERVER;
	}
}
