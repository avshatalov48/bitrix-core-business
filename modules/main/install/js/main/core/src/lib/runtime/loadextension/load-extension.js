import Reflection from '../../reflection';
import Type from '../../type';
import extensionsStorage from './internal/extensions-storage';
import { processExtensions } from './internal/process-extensions';

export type ExtensionExports = {[key: string]: any};
export type LoadExtensionResult = Promise<Array<ExtensionExports>>;
export type LoadableExtensionEntry = {
	promise: Promise<ExtensionExports>,
	resolve: (namespace: string) => void,
	reject: (error: Error) => void,
};

const queue: Map<string, LoadableExtensionEntry> = new Map();
let timerId: number | null = null;

export default async function loadExtension(...name: Array<string | Array<string>>): LoadExtensionResult
{
	if (Type.isNumber(timerId))
	{
		clearTimeout(timerId);
	}

	const requestedNames: Array<string> = name.flat();
	const extensionsToLoad: Array<string> = requestedNames.filter((extensionName: string) => {
		return !extensionsStorage.has(extensionName);
	});

	extensionsToLoad.forEach((extensionName: string) => {
		let resolve: ((namespace: string) => void) | null = null;
		let reject: ((error: Error) => void) | null = null;
		const loadableExtensionEntry: LoadableExtensionEntry = {
			// eslint-disable-next-line promise/param-names
			promise: new Promise((sourceResolve, sourceReject) => {
				resolve = sourceResolve;
				reject = sourceReject;
			}),
			resolve,
			reject,
		};

		extensionsStorage.set(extensionName, loadableExtensionEntry.promise);
		queue.set(extensionName, loadableExtensionEntry);
	});

	timerId = setTimeout(() => {
		if (queue.size > 0)
		{
			void processExtensions(new Map(queue.entries()));
			queue.clear();
		}
	});

	const namespaces: Array<string> = await Promise.all(
		requestedNames.map((extensionName: string) => {
			return extensionsStorage.get(extensionName);
		}),
	);

	return namespaces.reduce((acc, namespace: string) => {
		return { ...acc, ...Reflection.getClass(namespace) };
	}, {});
}
