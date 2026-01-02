import { Loc } from 'main.core';

import { showNotification } from '../utils/notification';

export const SpeechNotifier = {
	onRecognitionError(): void
	{
		showNotification(Loc.getMessage('IM_NOTIFIER_AUDIO_INPUT_ERROR'));
	},
};
