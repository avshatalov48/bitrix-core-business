const ajaxController: string = 'main.bitrix.main.controller.loadext.getextensions';

export type RawAsset = {
	config: null | { namespace: string },
	extension: string,
	html: string,
};
export type LoadAssetsResult = Promise<Array<RawAsset>>;

export function loadAssets(options: {extension: Array<string>}): LoadAssetsResult
{
	return new Promise((resolve, reject) => {
		const getParameters = {
			e: (options?.extension || []).join(','),
		};

		BX.ajax.runAction(ajaxController, { data: options, getParameters })
			.then((result) => {
				resolve(result?.data);
			})
			.catch((err) => {
				reject(err);
			});
	});
}
