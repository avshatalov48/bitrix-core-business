import { Store } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { RestMethod, TranscriptionStatus } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { runAction } from 'im.v2.lib.rest';

import type { TranscriptionResponse } from '../types/message';

export class TranscribeService
{
	#chatId: number;
	#store: Store;

	constructor(chatId: number)
	{
		this.#chatId = chatId;
		this.#store = Core.getStore();
	}

	transcribe(fileId: number): Promise<TranscriptionResponse>
	{
		Logger.warn('TranscribeService: transcribe:', fileId);

		const payload = {
			data: {
				chatId: this.#chatId,
				fileId,
			},
		};

		void this.#store.dispatch('files/setTranscription', {
			fileId,
			status: TranscriptionStatus.PENDING,
			transcriptText: null,
		});

		return runAction(RestMethod.imV2DiskFileTranscribe, payload)
			.then((result) => {
				void this.#store.dispatch('files/setTranscription', result);
			})
			.catch((error) => {
				void this.#store.dispatch('files/setTranscription', {
					fileId,
					status: TranscriptionStatus.ERROR,
					transcriptText: null,
				});

				console.error('TranscribeService: transcribe error:', error);
			});
	}
}
