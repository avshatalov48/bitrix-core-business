import {rest as RestClient} from 'rest.client';

import {Logger} from 'im.v2.lib.logger';
import {RestMethod} from 'im.v2.const';

import type {BackgroundListRestResult} from '../types/rest';

type ElementsListRestResult = {
	[RestMethod.imCallBackgroundGet]: RestResult,
	[RestMethod.imCallMaskGet]: RestResult
};

export class BackgroundService
{
	getElementsList(): Promise<BackgroundListRestResult>
	{
		const query = {
			[RestMethod.imCallBackgroundGet]: [RestMethod.imCallBackgroundGet],
			[RestMethod.imCallMaskGet]: [RestMethod.imCallMaskGet]
		};

		return new Promise((resolve, reject) => {
			RestClient.callBatch(query, (response: ElementsListRestResult) => {
				Logger.warn('BackgroundService: getElementsList result', response);
				const backgroundResult: RestResult = response[RestMethod.imCallBackgroundGet];
				const maskResult: RestResult = response[RestMethod.imCallMaskGet];
				if (backgroundResult.error())
				{
					console.error('BackgroundService: error getting background list', backgroundResult.error());
					return reject('Error getting background list');
				}
				if (maskResult.error())
				{
					console.error('BackgroundService: error getting mask list', maskResult.error());
					return reject('Error getting mask list');
				}

				return resolve({
					backgroundResult: backgroundResult.data(),
					maskResult: maskResult.data()
				});
			});
		});
	}

	commitBackground(fileId: string): Promise
	{
		return RestClient.callMethod(RestMethod.imCallBackgroundCommit, {
			fileId
		});
	}

	deleteFile(fileId: string): Promise
	{
		return RestClient.callMethod(RestMethod.imCallBackgroundDelete, {
			fileId
		});
	}
}