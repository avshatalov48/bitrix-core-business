import extensionsStorage from './extensions-storage';

export default function registerExtension(options)
{
	if (!extensionsStorage.has(options.name))
	{
		const namespace = options?.namespace ?? 'window';
		extensionsStorage.set(options.name, Promise.resolve(namespace));
	}
}
