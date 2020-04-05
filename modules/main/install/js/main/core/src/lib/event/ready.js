import Type from '../type';

let stack: Array<Function> = [];
/**
 * For compatibility only
 * @type {boolean}
 */
// eslint-disable-next-line
export let isReady = false;

export default function ready(handler: () => void)
{
	switch (document.readyState)
	{
		case 'loading':
			stack.push(handler);
			break;
		case 'interactive':
		case 'complete':
			if (Type.isFunction(handler))
			{
				handler();
			}

			isReady = true;
			break;
		default:
			break;
	}
}

document.addEventListener('readystatechange', () => {
	if (!isReady)
	{
		stack.forEach(ready);
		stack = [];
	}
});