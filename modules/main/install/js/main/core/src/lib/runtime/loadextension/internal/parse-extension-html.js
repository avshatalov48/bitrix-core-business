import { fetchExtensionSettings, fetchExternalScripts, fetchExternalStyles, fetchInlineScripts } from './utils';

export type ExtensionAssets = {
	inlinePreScripts: Array<string>,
	inlineAfterScripts: Array<string>,
	externalScripts: Array<string>,
	externalStyles: Array<string>,
	settingsScripts: Array<string>,
};

export function parseExtensionHtml(html: string): ExtensionAssets
{
	const result = window.BX.processHTML(html);
	const inlinePreScripts: Array<string> = [];
	const inlineAfterScripts: Array<string> = [];
	result.SCRIPT
		.reduce((accumulator, element) => {
			return fetchInlineScripts(accumulator, element);
		}, [])
		.forEach((script: string) => {
			if (script.startsWith('BX.Runtime.registerExtension'))
			{
				inlineAfterScripts.push(script);
			}
			else
			{
				inlinePreScripts.push(script);
			}
		});

	return {
		inlinePreScripts,
		inlineAfterScripts,
		externalScripts: result.SCRIPT.reduce((accumulator, element) => {
			return fetchExternalScripts(accumulator, element);
		}, []),
		externalStyles: result.STYLE.reduce((accumulator, element) => {
			return fetchExternalStyles(accumulator, element);
		}, []),
		settingsScripts: fetchExtensionSettings(result.HTML),
	};
}
