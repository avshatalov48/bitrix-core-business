import { Loc } from 'main.core';

import { ErrorCode } from 'im.v2.const';

import { showNotification } from '../utils/notification';

import type { UploaderError } from 'ui.uploader.core';

export const FileNotifier = {
	onDiskSaveComplete(isSingleFile = true): void
	{
		if (isSingleFile)
		{
			showNotification(Loc.getMessage('IM_NOTIFIER_FILE_DISK_SAVE_COMPLETE'));

			return;
		}

		showNotification(Loc.getMessage('IM_NOTIFIER_FILES_DISK_SAVE_COMPLETE'));
	},

	onCopyComplete(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_FILE_COPY_COMPLETE'));
	},

	handleUploadError(error: UploaderError): void
	{
		if (error.getCode() === ErrorCode.file.maxFileSize)
		{
			showNotification(`${error.getMessage()}<br>${error.getDescription()}`);
		}
	},
};
