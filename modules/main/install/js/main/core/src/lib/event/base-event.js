import Type from '../type';
import BaseError from "../base-error";
/**
 * Implements base event object interface
 */
export default class BaseEvent<DataType: any>
{
	constructor(
		options: {
			data?: any,
			compatData?: Array
		} = {
			data: {},
		},
	)
	{
		this.type = '';
		this.data = null;
		this.target = null;
		this.compatData = null;
		this.defaultPrevented = false;
		this.immediatePropagationStopped = false;
		this.errors = [];

		this.setData(options.data);
		this.setCompatData(options.compatData);
	}

	static create(options): this
	{
		return new this(options);
	}

	/**
	 * Returns the name of the event
	 * @returns {string}
	 */
	getType(): string
	{
		return this.type;
	}

	/**
	 *
	 * @param {string} type
	 */
	setType(type: string): this
	{
		if (Type.isStringFilled(type))
		{
			this.type = type;
		}

		return this;
	}

	/**
	 * Returns an event data
	 */
	getData(): DataType
	{
		return this.data;
	}

	/**
	 * Sets an event data
	 * @param data
	 */
	setData(data: any): this
	{
		if (!Type.isUndefined(data))
		{
			this.data = data;
		}

		return this;
	}

	/**
	 * Returns arguments for BX.addCustomEvent handlers (deprecated).
	 * @returns {array | null}
	 */
	getCompatData(): Array | null
	{
		return this.compatData;
	}

	/**
	 * Sets arguments for BX.addCustomEvent handlers (deprecated)
	 * @param data
	 */
	setCompatData(data: Array): this
	{
		if (Type.isArrayLike(data))
		{
			this.compatData = data;
		}

		return this;
	}

	/**
	 * Sets a event target
	 * @param target
	 */
	setTarget(target): this
	{
		this.target = target;

		return this;
	}

	/**
	 * Returns a event target
	 */
	getTarget(): any
	{
		return this.target;
	}

	/**
	 * Returns an array of event errors
	 * @returns {[]}
	 */
	getErrors(): Array<BaseError>
	{
		return this.errors;
	}

	/**
	 * Adds an error of the event.
	 * Event listeners can prevent emitter's default action and set the reason of this behavior.
	 * @param error
	 */
	setError(error: BaseError)
	{
		if (BaseError.isError(error))
		{
			this.errors.push(error);
		}
	}

	/**
	 * Prevents default action
	 */
	preventDefault()
	{
		this.defaultPrevented = true;
	}

	/**
	 * Checks that is default action prevented
	 * @return {boolean}
	 */
	isDefaultPrevented(): boolean
	{
		return this.defaultPrevented;
	}

	/**
	 * Stops event immediate propagation
	 */
	stopImmediatePropagation()
	{
		this.immediatePropagationStopped = true;
	}

	/**
	 * Checks that is immediate propagation stopped
	 * @return {boolean}
	 */
	isImmediatePropagationStopped(): boolean
	{
		return this.immediatePropagationStopped;
	}
}