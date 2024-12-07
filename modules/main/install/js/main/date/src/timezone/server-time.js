import { Type } from 'main.core';
import { BrowserTime } from './browser-time';
import { getOffset } from './internal/di';
import { createDateFromTimestamp, normalizeTimeValue } from './internal/helpers';

/**
 * @memberOf BX.Main.Timezone
 *
 * WARNING! Don't use this class or any classes from Timezone namespace on sites without Bitrix Framework.
 * It is not designed to handle this case and will definitely break.
 */
export class ServerTime
{
	/**
	 * Returns a Date object with time and date that represent a specific moment in Server timezone.
	 *
	 * ATTENTION! Date.getTime() and Date.getTimezoneOffset() will return inaccurate data. Since a native Date object
	 * doesn't support timezone other that device timezone, we have to manually change timestamp to shift time value in
	 * a Date object.
	 *
	 * @param utcTimestamp - normal utc timestamp in seconds. 'now' by default
	 * @returns {Date}
	 */
	static getDate(utcTimestamp: ?number = null): Date
	{
		if (Type.isNumber(utcTimestamp))
		{
			const browserToServerOffset = getOffset().SERVER_TO_UTC - getOffset().BROWSER_TO_UTC;

			return createDateFromTimestamp(utcTimestamp + browserToServerOffset);
		}

		return BrowserTime.toServerDate(BrowserTime.getDate());
	}

	/**
	 * Transforms a moment in Server timezone to a moment in User timezone.
	 *
	 * ATTENTION! Date.getTime() and Date.getTimezoneOffset() will return inaccurate data. Since a native Date object
	 * doesn't support timezone other that device timezone, we have to manually change timestamp to shift time value in
	 * a Date object.
	 *
	 * @param serverTime - a moment in Server timezone. Either a Date object (recommended way). Or timestamp in seconds in
	 * Server timezone (see this.getTimestamp for details).
	 * @returns {Date}
	 */
	static toUserDate(serverTime: Date | number): Date
	{
		return createDateFromTimestamp(this.toUser(serverTime));
	}

	/**
	 * Transforms a moment in Server timezone to a moment in Browser (device) timezone.
	 *
	 * @param serverTime - a moment in Server timezone. Either a Date object (recommended way). Or timestamp in seconds in
	 * Server timezone (see this.getTimestamp for details).
	 * @returns {Date}
	 */
	static toBrowserDate(serverTime: Date | number): Date
	{
		return createDateFromTimestamp(this.toBrowser(serverTime));
	}

	/**
	 * Transforms a moment in Server timezone to a timestamp in User timezone.
	 * It's recommended to use this.toServerDate for more clear code.
	 *
	 * @param serverTime - a moment in Server timezone. Either a Date object (recommended way). Or timestamp in seconds in
	 * Server timezone (see this.getTimestamp for details).
	 * @returns {number} - timestamp that when passed to 'new Date' will create an object with absolute time matching
	 * the time in User timezone
	 */
	static toUser(serverTime: Date | number): number
	{
		return normalizeTimeValue(serverTime) + getOffset().USER_TO_SERVER;
	}

	/**
	 * Transforms a moment in Server timezone to a timestamp in Browser (device) timezone.
	 * It's recommended to use this.toBrowserDate for more clear code.
	 *
	 * @param serverTime - a moment in Server timezone. Either a Date object (recommended way). Or timestamp in seconds in
	 * Server timezone (see this.getTimestamp for details).
	 * @returns {number} - timestamp that when passed to 'new Date' will create an object with absolute time matching
	 * the time in Browser (device) timezone
	 */
	static toBrowser(serverTime: Date | number): number
	{
		return normalizeTimeValue(serverTime) + getOffset().BROWSER_TO_UTC - getOffset().SERVER_TO_UTC;
	}

	/**
	 * Returns 'now' timestamp in Server timezone - when it's passed to a 'new Date', it will create an object with
	 * absolute time matching the time as if it was in Server timezone.
	 *
	 * @returns {number}
	 */
	static getTimestamp(): number
	{
		return BrowserTime.toServer(BrowserTime.getTimestamp());
	}
}
