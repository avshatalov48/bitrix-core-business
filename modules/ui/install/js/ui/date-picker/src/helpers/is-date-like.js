import { Type } from 'main.core';
import type { DateLike } from '../date-picker-options';

export function isDateLike(date: DateLike): boolean
{
	return Type.isStringFilled(date) || Type.isNumber(date) || Type.isDate(date);
}
