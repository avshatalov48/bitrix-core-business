import { Type } from 'main.core';
import { BrowserTime } from './browser-time';
import { getOffset } from './internal/di';
import { createDateFromTimestamp, normalizeTimeValue } from './internal/helpers';

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
	 * Returns a Date object with time and date that represent a specific moment in User timezone.
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
			return createDateFromTimestamp(utcTimestamp + this.#userToBrowserOffset);
		}

		return createDateFromTimestamp(this.getTimestamp());
	}

	static get #userToBrowserOffset(): number
	{
		const userToUTCOffset = getOffset().SERVER_TO_UTC + getOffset().USER_TO_SERVER;

		return userToUTCOffset - getOffset().BROWSER_TO_UTC;
	}

	/**
	 * Transforms a moment in User timezone to a moment in Browser (device) timezone.
	 *
	 * @param userTime - a moment in User timezone. Either a Date object (recommended way). Or timestamp in seconds in
	 * User timezone (see this.getTimestamp for details).
	 * @returns {Date}
	 */
	static toBrowserDate(userTime: Date | number): Date
	{
		return createDateFromTimestamp(this.toBrowser(userTime));
	}

	/**
	 * Transforms a moment in User timezone to a moment in Server timezone.
	 *
	 * ATTENTION! Date.getTime() and Date.getTimezoneOffset() will return inaccurate data. Since a native Date object
	 * doesn't support timezone other that device timezone, we have to manually change timestamp to shift time value in
	 * a Date object.
	 *
	 * @param userTime - a moment in User timezone. Either a Date object (recommended way). Or timestamp in seconds in
	 * User timezone (see this.getTimestamp for details).
	 * @returns {Date}
	 */
	static toServerDate(userTime: Date | number): Date
	{
		return createDateFromTimestamp(this.toServer(userTime));
	}

	static toUTCTimestamp(userTime: Date | number): number
	{
		return normalizeTimeValue(userTime) - this.#userToBrowserOffset;
	}

	/**
	 * Transforms a moment in User timezone to a timestamp in Browser timezone.
	 * It's recommended to use this.toBrowserDate for more clear code.
	 *
	 * @param userTime - a moment in User timezone. Either a Date object (recommended way). Or timestamp in seconds in
	 * User timezone (see this.getTimestamp for details).
	 * @returns {number} - timestamp that when passed to 'new Date' will create an object with absolute time matching
	 * the time in Browser (device) timezone
	 */
	static toBrowser(userTime: Date | number): number
	{
		return (
			normalizeTimeValue(userTime)
			+ getOffset().BROWSER_TO_UTC
			- getOffset().SERVER_TO_UTC
			- getOffset().USER_TO_SERVER
		);
	}

	/**
	 * Transforms a moment in User timezone to a timestamp in Server timezone.
	 * It's recommended to use this.toServerDate for more clear code.
	 *
	 * @param userTime - a moment in User timezone. Either a Date object (recommended way). Or timestamp in seconds in
	 * User timezone (see this.getTimestamp for details).
	 * @returns {number} - timestamp that when passed to 'new Date' will create an object with absolute time matching
	 * the time in Server timezone
	 */
	static toServer(userTime: Date | number): number
	{
		return normalizeTimeValue(userTime) - getOffset().USER_TO_SERVER;
	}

	/**
	 * Returns 'now' timestamp in User timezone - when it's passed to a 'new Date', it will create an object with absolute
	 * time matching the time as if it was in User timezone.
	 *
	 * @returns {number}
	 */
	static getTimestamp(): number
	{
		return BrowserTime.toUser(BrowserTime.getTimestamp());
	}
}
