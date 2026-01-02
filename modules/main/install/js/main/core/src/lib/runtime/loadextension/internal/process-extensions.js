import { type LoadableExtensionEntry } from '../load-extension';
import { type RawAsset, loadAssets } from './load-assets';
import { type ExtensionAssets, parseExtensionHtml } from './parse-extension-html';
import { loadAll } from './utils';

export async function processExtensions(map: Map<string, LoadableExtensionEntry>)
{
	const loadableExtensions: Array<string> = [...map.keys()];
	const rawAssets: Array<RawAsset> = await loadAssets({ extension: loadableExtensions });

	rawAssets.forEach((rawAsset: RawAsset) => {
		const preparedHtml = rawAsset.html ?? '';
		const extensionAssets: ExtensionAssets = parseExtensionHtml(preparedHtml);

		extensionAssets.settingsScripts.forEach(({ script }) => {
			document.body.insertAdjacentHTML('beforeend', script);
		});

		extensionAssets.inlinePreScripts.forEach((script: string) => {
			window.BX.evalGlobal(script);
		});

		const loadableExtensionEntry: LoadableExtensionEntry = map.get(rawAsset.extension);

		void Promise
			.all([
				loadAll(extensionAssets.externalScripts),
				loadAll(extensionAssets.externalStyles),
			])
			.then(() => {
				extensionAssets.inlineAfterScripts.forEach((script: string) => {
					window.BX.evalGlobal(script);
				});

				const namespace = rawAsset?.config?.namespace ?? 'window';
				loadableExtensionEntry.resolve(namespace);
			})
			.catch((error: Error) => {
				loadableExtensionEntry.reject(error);
			});
	});
}
