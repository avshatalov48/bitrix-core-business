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
}