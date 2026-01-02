import 'ui.viewer';
import { Utils } from 'im.v2.lib.utils';
import { Loc, Dom } from 'main.core';

import { SidebarMenu } from '../sidebar-base-menu';
import { FileManager } from './file-manager';

import { Notifier } from 'im.v2.lib.notifier';

import type { MenuItemOptions } from 'ui.system.menu';
import type { ImModelFile, ImModelSidebarFileItem } from 'im.v2.model';

type MediaMenuContext = {
	sidebarFile: ImModelSidebarFileItem,
	file: ImModelFile,
	messageId: number,
	dialogId: string,
}

export class FileMenu extends SidebarMenu
{
	context: MediaMenuContext;

	constructor()
	{
		super();

		this.id = 'im-sidebar-context-menu';
		this.mediaManager = new FileManager();
	}

	getMenuItems(): MenuItemOptions | null[]
	{
		return [
			this.getOpenContextMessageItem(),
			this.getDownloadFileItem(),
			this.getSaveFileOnDiskItem(),
			this.getDeleteFileItem(),
		];
	}

	getDownloadFileItem(): ?MenuItemOptions
	{
		if (!this.context.file.urlDownload)
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_SIDEBAR_MENU_DOWNLOAD_FILE'),
			onClick: function() {
				Utils.file.downloadFiles([this.context.file]);
				this.menuInstance.close();
			}.bind(this),
		};
	}

	getSaveFileOnDiskItem(): ?MenuItemOptions
	{
		if (!this.context.sidebarFile.fileId)
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_SIDEBAR_MENU_SAVE_FILE_ON_DISK_MSGVER_1'),
			onClick: async function() {
				this.menuInstance.close();
				await this.mediaManager.saveOnDisk([this.context.sidebarFile.fileId]);
				Notifier.file.onDiskSaveComplete();
			}.bind(this),
		};
	}

	getDeleteFileItem(): ?MenuItemOptions
	{
		if (this.getCurrentUserId() !== this.context.sidebarFile.authorId)
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_SIDEBAR_MENU_DELETE_FILE'),
			onClick: function() {
				this.mediaManager.delete(this.context.sidebarFile);
				this.menuInstance.close();
			}.bind(this),
		};
	}
}
