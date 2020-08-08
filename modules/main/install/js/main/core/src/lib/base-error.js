import Type from './type';

const isError = Symbol.for('BX.BaseError.isError');

/**
 * @memberOf BX
 */
export default class BaseError
{
	constructor(message?: string, code?: string, customData?: any)
	{
		this[isError] = true;
		this.message = '';
		this.code = null;
		this.customData = null;

		this.setMessage(message);
		this.setCode(code);
		this.setCustomData(customData);
	}

	/**
	 * Returns a brief description of the error
	 * @returns {string}
	 */
	getMessage(): string
	{
		return this.message;
	}

	/**
	 * Sets a message of the error
	 * @param {string} message
	 * @returns {this}
	 */
	setMessage(message: string): this
	{
		if (Type.isString(message))
		{
			this.message = message;
		}

		return this;
	}

	/**
	 * Returns a code of the error
	 * @returns {?string}
	 */
	getCode(): string
	{
		return this.code;
	}

	/**
	 * Sets a code of the error
	 * @param {string} code
	 * @returns {this}
	 */
	setCode(code: string): this
	{
		if (Type.isStringFilled(code) || code === null)
		{
			this.code = code;
		}

		return this;
	}


	/**
	 * Returns custom data of the error
	 * @returns {null|*}
	 */
	getCustomData(): any
	{
		return this.customData;
	}

	/**
	 * Sets custom data of the error
	 * @returns {this}
	 */
	setCustomData(customData: any): this
	{
		if (!Type.isUndefined(customData))
		{
			this.customData = customData;
		}

		return this;
	}

	toString()
	{
		const code = this.getCode();
		const message = this.getMessage();

		if (!Type.isStringFilled(code) && !Type.isStringFilled(message))
		{
			return '';
		}
		else if (!Type.isStringFilled(code))
		{
			return `Error: ${message}`;
		}
		else if (!Type.isStringFilled(message))
		{
			return code;
		}
		else
		{
			return `${code}: ${message}`;
		}
	}

	/**
	 * Returns true if the object is an instance of BaseError
	 * @param error
	 * @returns {boolean}
	 */
	static isError(error: BaseError)
	{
		return Type.isObject(error) && error[isError] === true;
	}
}