import { RestClient } from 'rest.client';

import { runAction } from 'im.v2.lib.rest';
import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';

type FileId = number | string;

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
			console.error('DiskService: error deleting file', error);
		});
	}

	async save(fileIds: FileId[]): Promise
	{
		try
		{
			const normalizedIds = fileIds.map((id) => Number.parseInt(id, 10));

			await runAction(RestMethod.imV2DiskFileSave, {
				data: { ids: normalizedIds },
			});
		}
		catch (error)
		{
			console.error('DiskService: error saving file on disk', error);
		}
	}
}
