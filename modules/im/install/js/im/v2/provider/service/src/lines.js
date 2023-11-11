import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';

export class LinesService
{
	getDialogIdByUserCode(userCode: string): Promise<string>
	{
		return Core.getRestClient().callMethod(RestMethod.linesDialogGet, {
			USER_CODE: userCode,
		}).then((result) => {
			const { dialog_id: dialogId } = result.data();

			return dialogId;
		}).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('LinesService: error getting dialog id', error);
		});
	}
}
