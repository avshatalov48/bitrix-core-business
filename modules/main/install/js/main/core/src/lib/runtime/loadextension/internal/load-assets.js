const ajaxController: string = 'main.bitrix.main.controller.loadext.getextensions';

export default function loadAssets(options: {extension: Array<string>}): Promise<any>
{
	return new Promise((resolve) => {
		// eslint-disable-next-line
		BX.ajax.runAction(ajaxController, {data: options}).then(resolve);
	});
}
