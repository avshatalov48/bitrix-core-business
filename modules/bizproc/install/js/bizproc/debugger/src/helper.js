import {Loc, Text, Type} from 'main.core';
import {DateTimeFormat} from 'main.date';
import {Operator} from "bizproc.condition";

export class Helper
{
	/** Finds whether a variable is a number or a numeric string */
	static isNumeric(num: string | number): boolean
	{
		if (Type.isNumber(num))
		{
			return true;
		}

		if (!Type.isStringFilled(num))
		{
			return false;
		}

		return (Number(num).toString() === num.trim());
	}

	/** Checks whether the variable is a date or a timestamp */
	static isDate(date: string | Date): boolean
	{
		if (Type.isDate(date))
		{
			return true;
		}

		if (!Helper.isNumeric(date))
		{
			return false;
		}

		return (new Date(Number(date)).getTime() === Number(date));
	}

	/** Convert date from DataBase to date in JS */
	static convertDateFromDB(date: string | number): ?Date
	{
		if (!Helper.isNumeric(date))
		{
			return null;
		}

		return new Date(date * 1000);
	}

	/** if the variable is a date or a timestamp return Date, else null  */
	static toDate(date: string | Date): ?Date
	{
		if (DateTimeFormat.parse(date))
		{
			return DateTimeFormat.parse(date, false);
		}

		if (Date.parse(date))
		{
			return new Date(date);
		}

		if (!Helper.isDate(date))
		{
			return null;
		}

		if (Type.isDate(date))
		{
			return date;
		}

		return Helper.convertDateFromDB(date);
	}

	/** formats the date */
	static formatDate(format: string, date: Date): string
	{
		if (!Type.isStringFilled(format))
		{
			format = 'j F Y H:i:s';
		}

		return DateTimeFormat.format(format, date);
	}

	/** return condition operators label */
	static getOperatorsLabel(): object
	{
		return Operator.getAllLabels();
	}

	/** return condition operator label */
	static getOperatorLabel(operator: string): string
	{
		return Operator.getOperatorLabel([operator]);
	}

	/** return joiner label */
	static getJoinerLabel(joiner: string): string
	{
		const joiners = {
			'AND': Loc.getMessage('BIZPROC_JS_DEBUGGER_LOG_CONDITION_AND'),
			'OR': Loc.getMessage('BIZPROC_JS_DEBUGGER_LOG_CONDITION_OR'),
		};

		return joiners[joiner];
	}

	static getColorBrightness(bgColor: string): number
	{
		if (bgColor[0] === '#')
		{
			bgColor = bgColor.replace('#', '');
		}

		const bigint = parseInt(bgColor, 16);
		const r = (bigint >> 16) & 255;
		const g = (bigint >> 8) & 255;
		const b = bigint & 255;

		return 0.21 * r + 0.72 * g + 0.07 * b;
	}

	static getBgColorAdditionalClass(bgColor: string): boolean
	{
		const brightness = Helper.getColorBrightness(bgColor);
		if (brightness > 224)
		{
			return '--with-border --light-color';
		}

		if (brightness > 145)
		{
			return '--light-color';
		}

		return '';
	}

	static toHtml(text): string
	{
		return Text.encode(text || '')
			.replace(/\[(\/)?b\]/ig, '<$1b>')
		;
	}
}