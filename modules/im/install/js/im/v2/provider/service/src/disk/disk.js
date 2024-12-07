import { RestClient } from 'rest.client';

import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';

export class DiskService
{
	#restClient: RestClient;

	constructor()
	{
		this.#restClient = Core.getRestClient();
	}

	delete({ chatId, fileId }): Promise
	{
		const queryParams = { chat_id: chatId, file_id: fileId };

		return this.#restClient.callMethod(RestMethod.imDiskFileDelete, queryParams).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('DiskService: error deleting file', error);
		});
	}

	save(fileId: number): Promise
	{
		const queryParams = { file_id: fileId };

		return this.#restClient.callMethod(RestMethod.imDiskFileSave, queryParams).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('DiskService: error saving file on disk', error);
		});
	}
}
