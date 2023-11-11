import { Loc } from 'main.core';

import { DiskService } from 'im.v2.provider.service';
import { BaseMenu } from 'im.v2.lib.menu';
import { Utils } from 'im.v2.lib.utils';
import { PopupType } from 'im.v2.const';

import type { MenuItem } from 'im.v2.lib.menu';
import type { ImModelFile } from 'im.v2.model';

export class BaseFileContextMenu extends BaseMenu
{
	id: String = PopupType.messageBaseFileMenu;
	diskService: DiskService;

	constructor()
	{
		super();

		this.id = 'bx-im-message-file-context-menu';
		this.diskService = new DiskService();
	}

	getMenuItems(): Array
	{
		return [
			this.getDownloadFileItem(),
			this.getSaveToDisk(),
		];
	}

	getDownloadFileItem(): ?MenuItem
	{
		const file = this.#getMessageFile();
		if (!file)
		{
			return null;
		}

		return {
			html: Utils.file.createDownloadLink(
				Loc.getMessage('IM_MESSAGE_FILE_MENU_DOWNLOAD_FILE'),
				file.urlDownload,
				file.name,
			),
			onclick: function() {
				this.menuInstance.close();
			}.bind(this),
		};
	}

	getSaveToDisk(): ?MenuItem
	{
		const file = this.#getMessageFile();
		if (!file)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_MESSAGE_FILE_MENU_SAVE_ON_DISK'),
			onclick: function() {
				void this.diskService.save(file.id).then(() => {
					BX.UI.Notification.Center.notify({
						content: Loc.getMessage('IM_MESSAGE_FILE_MENU_SAVE_ON_DISK_SUCCESS'),
					});
				});
				this.menuInstance.close();
			}.bind(this),
		};
	}

	#getMessageFile(): ?ImModelFile
	{
		if (this.context.files.length === 0)
		{
			return null;
		}

		// for now, we have only one file in one message. In the future we need to change this logic.
		return this.store.getters['files/get'](this.context.files[0]);
	}
}
