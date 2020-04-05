import Type from '../type';

function isOptionSupported(name)
{
	let isSupported = false;

	try {
		const options = Object.defineProperty({}, name, {
			get() {
				isSupported = true;
				return undefined;
			},
		});

		window.addEventListener('test', null, options);
	}
	// eslint-disable-next-line
	catch (err) {}

	return isSupported;
}

export default function fetchSupportedListenerOptions(
	options?: T<{
		capture?: boolean,
		once?: boolean,
		passive?: boolean,
	}>,
): ?T
{
	if (!Type.isPlainObject(options))
	{
		return options;
	}

	return Object
		.keys(options)
		.reduce((acc, name) => {
			if (isOptionSupported(name))
			{
				acc[name] = options[name];
			}

			return acc;
		}, {});
}