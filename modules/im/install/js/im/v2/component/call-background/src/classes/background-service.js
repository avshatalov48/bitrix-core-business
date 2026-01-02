import { rest as RestClient } from 'rest.client';

import { Logger } from 'im.v2.lib.logger';
import { RestMethod } from 'im.v2.const';

import type { BackgroundListRestResult } from '../types/rest';

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
			[RestMethod.imCallMaskGet]: [RestMethod.imCallMaskGet],
		};

		return new Promise((resolve, reject) => {
			RestClient.callBatch(query, (response: ElementsListRestResult) => {
				Logger.warn('BackgroundService: getElementsList result', response);
				const backgroundResult: RestResult = response[RestMethod.imCallBackgroundGet];
				const maskResult: RestResult = response[RestMethod.imCallMaskGet];
				if (backgroundResult.error())
				{
					console.error('BackgroundService: error getting background list', backgroundResult.error());

					return reject(backgroundResult.error());
				}

				if (maskResult.error())
				{
					console.error('BackgroundService: error getting mask list', maskResult.error());

					return reject(maskResult.error());
				}

				return resolve({
					backgroundResult: backgroundResult.data(),
					maskResult: maskResult.data(),
				});
			});
		});
	}

	commitBackground(fileId: string): void
	{
		RestClient.callMethod(RestMethod.imCallBackgroundCommit, { fileId })
			.catch((result: RestResult) => {
				console.error('BackgroundService: commitBackground error', result.error());
			});
	}

	deleteFile(fileId: string): void
	{
		RestClient.callMethod(RestMethod.imCallBackgroundDelete, { fileId })
			.catch((result: RestResult) => {
				console.error('BackgroundService: deleteFile error', result.error());
			});
	}
}
