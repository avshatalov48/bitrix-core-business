import Type from '../type';
import bindOnce from './bind-once';

let stack: Array<Function> = [];
/**
 * For compatibility only
 * @type {boolean}
 */
// eslint-disable-next-line
export let isReady = false;

export default function ready(handler: () => void)
{
	if (!Type.isFunction(handler))
	{
		return;
	}

	if (isReady)
	{
		handler();
	}
	else
	{
		stack.push(handler);
	}
}

bindOnce(document, 'DOMContentLoaded', () => {
	isReady = true;

	stack.forEach((handler: Function) => {
		handler();
	});

	stack = [];
});
