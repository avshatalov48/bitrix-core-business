/**
 * @memberOf BX
 */
export default class Validation
{
	/**
	 * Checks that value is valid email
	 * @param value
	 * @return {boolean}
	 */
	static isEmail(value)
	{
		const exp = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
		return exp.test(String(value).toLowerCase());
	}
}