import type {ICacheStorage} from './i-cache-storage';
import Type from '../../type';

export default class LsStorage implements ICacheStorage
{
	stackKey = 'BX.Cache.Storage.LsStorage.stack';
	stack = null;

	/**
	 * @private
	 */
	getStack(): {[key: string]: any}
	{
		if (Type.isPlainObject(this.stack))
		{
			return this.stack;
		}

		const stack = localStorage.getItem(this.stackKey);

		if (Type.isString(stack) && stack !== '')
		{
			const parsedStack = JSON.parse(stack);

			if (Type.isPlainObject(parsedStack))
			{
				this.stack = parsedStack;
				return this.stack;
			}
		}

		this.stack = {};
		return this.stack;
	}

	/**
	 * @private
	 */
	saveStack()
	{
		if (Type.isPlainObject(this.stack))
		{
			const preparedStack = JSON.stringify(this.stack);
			localStorage.setItem(this.stackKey, preparedStack);
		}
	}

	get(key: string)
	{
		const stack = this.getStack();
		return stack[key];
	}

	set(key: string, value: any)
	{
		const stack = this.getStack();
		stack[key] = value;
		this.saveStack();
	}

	delete(key: string)
	{
		const stack = this.getStack();

		if (key in stack)
		{
			delete stack[key];
		}
	}

	has(key: string): boolean
	{
		const stack = this.getStack();
		return key in stack;
	}

	get size(): number
	{
		const stack = this.getStack();
		return Object.keys(stack).length;
	}

	keys(): Array<string>
	{
		const stack = this.getStack();
		return Object.keys(stack);
	}

	values(): Array<any>
	{
		const stack = this.getStack();
		return Object.values(stack);
	}
}