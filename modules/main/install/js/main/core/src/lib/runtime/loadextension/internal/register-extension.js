import extensionsStorage from './extensions-storage';
import Extension from './extension';

export default function registerExtension(options): void
{
	if (!extensionsStorage.has(options.name))
	{
		extensionsStorage.set(options.name, new Extension(options));
	}
}
