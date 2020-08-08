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
	 * @return {?string}
	 */
	static getMessage(messageId: string): ?string
	{
		return message(messageId);
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