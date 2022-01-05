import Type from './type';
import message from './loc/message';

/**
 * Implements interface for works with language messages
 * @memberOf BX
 */
export default class Loc
{
	/**
	 * Gets message by id
	 * @param {string} messageId
	 * @param {object} replacements
	 * @return {?string}
	 */
	static getMessage(messageId: string, replacements:? {[key: string]: string} = null): ?string
	{
		let mess = message(messageId);
		if (Type.isString(mess) && Type.isPlainObject(replacements))
		{
			Object.keys(replacements).forEach((replacement: string) => {
				const globalRegexp = new RegExp(replacement, 'gi');
				mess = mess.replace(
					globalRegexp,
					() => {
						return Type.isNil(replacements[replacement]) ? '' : String(replacements[replacement]);
					}
				);
			});
		}

		return mess;
	}

	static hasMessage(messageId: string): boolean
	{
		return Type.isString(messageId) && !Type.isNil(message[messageId]);
	}

	/**
	 * Sets message or messages
	 * @param {string | Object<string, string>} id
	 * @param {string} [value]
	 */
	static setMessage(id: string | {[key: string]: string}, value?: string)
	{
		if (Type.isString(id) && Type.isString(value))
		{
			message({[id]: value});
		}

		if (Type.isObject(id))
		{
			message(id);
		}
	}

	/**
	 * Gets plural message by id and number
	 * @param {string} messageId
	 * @param {number} value
	 * @param {object} [replacements]
	 * @return {?string}
	 */
	static getMessagePlural(messageId: string, value: number, replacements:? {[key: string]: string} = null)
	{
		let result = '';

		if (Type.isNumber(value))
		{
			if (this.hasMessage(`${messageId}_PLURAL_${this.getPluralForm(value)}`))
			{
				result = this.getMessage(`${messageId}_PLURAL_${this.getPluralForm(value)}`, replacements);
			}
			else
			{
				result = this.getMessage(`${messageId}_PLURAL_1`, replacements);
			}
		}
		else
		{
			result = this.getMessage(messageId, replacements);
		}

		return result;
	}

	/**
	 * Gets language plural form id by number
	 * see http://docs.translatehouse.org/projects/localization-guide/en/latest/l10n/pluralforms.html
	 * @param {number} value
	 * @param {string} [languageId]
	 * @return {?number}
	 */
	static getPluralForm(value: number, languageId?: string)
	{
		let pluralForm;

		if (!Type.isStringFilled(languageId))
		{
			languageId = message('LANGUAGE_ID');
		}

		if (value < 0)
		{
			value = (-1) * value;
		}

		switch (languageId)
		{
			case 'ar':
				pluralForm = ((value !== 1) ? 1 : 0);
/*
				if (value === 0)
				{
					pluralForm = 0;
				}
				else if (value === 1)
				{
					pluralForm = 1;
				}
				else if (value === 2)
				{
					pluralForm = 2;
				}
				else if (
					value % 100 >= 3
					&& value % 100 <= 10
				)
				{
					pluralForm = 3;
				}
				else if (value % 100 >= 11)
				{
					pluralForm = 4;
				}
				else
				{
					pluralForm = 5;
				}
 */
				break;

			case 'br':
			case 'fr':
			case 'tr':
				pluralForm = ((value > 1) ? 1 : 0);
				break;

			case 'de':
			case 'en':
			case 'hi':
			case 'it':
			case 'la':
				pluralForm = ((value !== 1) ? 1 : 0);
				break;

			case 'ru':
			case 'ua':
				if (
					(value % 10 === 1)
					&& (value % 100 !== 11)
				)
				{
					pluralForm = 0;
				}
				else if (
					(value % 10 >= 2)
					&& (value % 10 <= 4)
					&& (
						(value % 100 < 10)
						|| (value % 100 >= 20)
					)
				)
				{
					pluralForm = 1;
				}
				else
				{
					pluralForm = 2;
				}
				break;

			case 'pl':
				if (value === 1)
				{
					pluralForm = 0;
				}
				else if (
					value % 10 >= 2
					&& value % 10 <= 4
					&& (
						value % 100 < 10
						|| value % 100 >= 20
					)
				)
				{
					pluralForm = 1;
				}
				else
				{
					pluralForm = 2;
				}
				break;

			case 'id':
			case 'ja':
			case 'ms':
			case 'sc':
			case 'tc':
			case 'th':
			case 'vn':
				pluralForm = 0;
				break;

			default:
				pluralForm = 1;
				break;
		}

		return pluralForm;
	}

}