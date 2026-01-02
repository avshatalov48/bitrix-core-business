import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';

export class LinesService
{
	async getDialogIdByUserCode(userCode: string): Promise<string>
	{
		const result: RestResult = await Core.getRestClient().callMethod(RestMethod.linesDialogGet, {
			USER_CODE: userCode,
		}).catch((errorResult: RestResult) => {
			console.error('LinesService: error getting dialog id', errorResult.error());
		});

		const { dialog_id: dialogId } = result.data();

		return dialogId;
	}
}
