import { Type } from 'main.core';
import { isDatesEqual } from './is-dates-equal';
import { type DateMatcher } from '../date-picker-options';

export function isDateMatch(day: Date, matchers: DateMatcher[]): boolean
{
	return matchers.some((matcher: DateMatcher) => {
		if (Type.isFunction(matcher))
		{
			return matcher(day);
		}

		if (Type.isDate(matcher))
		{
			return isDatesEqual(day, matcher);
		}

		if (Type.isArray(matcher))
		{
			return matcher.some((date: Date) => {
				return isDatesEqual(day, date);
			});
		}

		if (Type.isBoolean(matcher))
		{
			return matcher;
		}

		return false;
	});
}
