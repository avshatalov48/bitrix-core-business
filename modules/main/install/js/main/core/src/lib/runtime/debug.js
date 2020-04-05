import Type from '../type';

// eslint-disable-next-line
export let debugState = true;

export function enableDebug()
{
	debugState = true;
}

export function disableDebug()
{
	debugState = false;
}

export function isDebugEnabled()
{
	return debugState;
}

export default function debug(...args: any)
{
	if (
		isDebugEnabled()
		&& Type.isObject(window.console)
	)
	{
		if (Type.isFunction(window.console.log))
		{
			window.console.log('BX.debug: ', args.length > 0 ? args : args[0]);

			if (args[0] instanceof Error && args[0].stack)
			{
				window.console.log('BX.debug error stack trace', args[0].stack);
			}
		}

		if (Type.isFunction(window.console.trace))
		{
			// eslint-disable-next-line
			console.trace();
		}
	}
}