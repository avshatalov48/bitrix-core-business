import { Type } from 'main.core';
import { getNowTimestamp, getOffset } from './internal/di';
import { createDateFromTimestamp, normalizeTimeValue } from './internal/helpers';

/**
 * @memberOf BX.Main.Timezone
 *
 * WARNING! Don't use this class or any classes from Timezone namespace on sites without Bitrix Framework.
 * It is not designed to handle this case and will definitely break.
 */
export class BrowserTime
{
	/**
	 * Returns a Date object with time and date that represent a specific moment in Browser (device) timezone.
	 *
	 * @param utcTimestamp - normal utc timestamp in seconds. 'now' by default
	 * @returns {Date}
	 */
	static getDate(utcTimestamp: ?number = null): Date
	{
		const timestamp = Type.isNumber(utcTimestamp) ? utcTimestamp : this.getTimestamp();

		return createDateFromTimestamp(timestamp);
	}

	/**
	 * Transforms a moment in Browser (device) timezone to a moment in User timezone.
	 *
	 * ATTENTION! Date.getTime() and Date.getTimezoneOffset() will return inaccurate data. Since a native Date object
	 * doesn't support timezone other that device timezone, we have to manually change timestamp to shift time value in
	 * a Date object.
	 *
	 * @param browserTime - a moment in Browser (device) timezone. Either a Date object (recommended way). Or timestamp
	 * in seconds in Browser timezone (see this.getTimestamp for details).
	 * @returns {Date}
	 */
	static toUserDate(browserTime: Date | number): Date
	{
		return createDateFromTimestamp(this.toUser(browserTime));
	}

	/**
	 * Transforms a moment in Browser (device) timezone to a moment in Server timezone.
	 *
	 * ATTENTION! Date.getTime() and Date.getTimezoneOffset() will return inaccurate data. Since a native Date object
	 * doesn't support timezone other that device timezone, we have to manually change timestamp to shift time value in
	 * a Date object.
	 *
	 * @param browserTime - a moment in Browser (device) timezone. Either a Date object (recommended way). Or timestamp
	 * in seconds in Browser timezone (see this.getTimestamp for details).
	 * @returns {Date}
	 */
	static toServerDate(browserTime: Date | number): Date
	{
		return createDateFromTimestamp(this.toServer(browserTime));
	}

	/**
	 * Transforms a moment in Browser (device) timezone to a timestamp in User timezone.
	 * It's recommended to use this.toUserDate for more clear code.
	 *
	 * @param browserTime - a moment in Browser timezone. Either a Date object (recommended way). Or timestamp in seconds
	 * in Browser timezone (see this.getTimestamp for details).
	 * @returns {number} - timestamp that when passed to 'new Date' will create an object with absolute time matching
	 * the time in User timezone
	 */
	static toUser(browserTime: Date | number): number
	{
		return this.toServer(browserTime) + getOffset().USER_TO_SERVER;
	}

	/**
	 * Transforms a moment in Browser (device) timezone to a timestamp in Server timezone.
	 * It's recommended to use this.toServerDate for more clear code.
	 *
	 * @param browserTime - a moment in Browser timezone. Either a Date object (recommended way). Or timestamp in seconds
	 * in Browser timezone (see this.getTimestamp for details).
	 * @returns {number} - timestamp that when passed to 'new Date' will create an object with absolute time matching
	 * the time in Server timezone
	 */
	static toServer(browserTime: Date | number): number
	{
		return normalizeTimeValue(browserTime) - getOffset().BROWSER_TO_UTC + getOffset().SERVER_TO_UTC;
	}

	/**
	 * Returns 'now' timestamp in Browser (device) timezone - when it's passed to a 'new Date', it will create an object
	 * with absolute time matching the time as if it was in Browser (device) timezone.
	 *
	 * @returns {number}
	 */
	static getTimestamp(): number
	{
		// since 'Date' class in JS is hardcoded to use device timezone, 'browser timestamp' is just normal UTC timestamp :)

		return getNowTimestamp();
	}
}
