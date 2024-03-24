import { Store } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { DiskService } from 'im.v2.provider.service';

import type { ImModelSidebarFileItem } from 'im.v2.model';

export class FileManager
{
	store: Store;

	constructor()
	{
		this.store = Core.getStore();
		this.diskService = new DiskService();
	}

	delete(sidebarFile: ImModelSidebarFileItem)
	{
		this.store.dispatch('sidebar/files/delete', {
			dialogId: sidebarFile.chatId,
			id: sidebarFile.id,
		});

		this.diskService.delete({ chatId: sidebarFile.chatId, fileId: sidebarFile.fileId });
	}

	saveOnDisk(fileId: number): Promise
	{
		return this.diskService.save(fileId);
	}
}
