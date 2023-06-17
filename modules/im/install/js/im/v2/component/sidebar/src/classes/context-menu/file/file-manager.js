import {RestClient} from 'rest.client';
import {Store} from 'ui.vue3.vuex';

import {Core} from 'im.v2.application.core';
import {RestMethod} from 'im.v2.const';

import type {ImModelSidebarFileItem} from 'im.v2.model';

export class FileManager
{
	store: Store;
	restClient: RestClient;

	constructor()
	{
		this.store = Core.getStore();
		this.restClient = Core.getRestClient();
	}

	delete(sidebarFile: ImModelSidebarFileItem)
	{
		this.store.dispatch('sidebar/files/delete', {
			chatId: sidebarFile.chatId,
			id: sidebarFile.id
		});

		const queryParams = {chat_id: sidebarFile.chatId, file_id: sidebarFile.fileId};
		this.restClient.callMethod(RestMethod.imDiskFileDelete, queryParams).catch(error => {
			console.error('Im.Sidebar: error deleting file', error);
		});
	}

	saveOnDisk(fileId: number): Promise
	{
		const queryParams = {file_id: fileId};
		return this.restClient.callMethod(RestMethod.imDiskFileSave, queryParams).catch(error => {
			console.error('Im.Sidebar: error saving file on disk', error);
		});
	}
}