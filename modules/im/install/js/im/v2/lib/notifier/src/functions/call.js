import { Loc } from 'main.core';

import { showNotification } from '../utils/notification';

export const CallNotifier = {
	onBackgroundFileSizeError(payload: { fileName: string, fileSizeLimit: number }): void
	{
		const { fileName, fileSizeLimit } = payload;
		const phrase = Loc.getMessage('IM_NOTIFIER_CALL_BACKGROUND_FILE_SIZE_ERROR', {
			'#LIMIT#': fileSizeLimit,
			'#FILE_NAME#': fileName,
		});

		showNotification(phrase);
	},

	onBackgroundUnsupportedError(fileName: string): void
	{
		const phrase = Loc.getMessage('IM_NOTIFIER_CALL_BACKGROUND_FILE_UNSUPPORTED_ERROR', {
			'#FILE_NAME#': fileName,
		});

		showNotification(phrase);
	},
};
