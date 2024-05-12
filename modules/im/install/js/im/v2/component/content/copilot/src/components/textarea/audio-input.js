import { BaseEvent } from 'main.core.events';

import { AudioManager } from './classes/audio-manager';

import './css/audio-input.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const AudioInput = {
	name: 'AudioInput',
	props:
	{
		audioMode: {
			type: Boolean,
			required: true,
		},
	},
	emits: ['start', 'stop', 'inputStart', 'inputResult', 'error'],
	data(): JsonObject
	{
		return {};
	},
	watch:
	{
		audioMode(newValue, oldValue)
		{
			if (oldValue === false && newValue === true)
			{
				this.startAudio();
			}

			if (oldValue === true && newValue === false)
			{
				this.stopAudio();
			}
		},
	},
	methods:
	{
		onClick()
		{
			if (this.audioMode)
			{
				this.$emit('stop');

				return;
			}

			this.$emit('start');
		},
		startAudio()
		{
			this.getAudioManager().startRecognition();
			this.bindAudioEvents();
		},
		stopAudio()
		{
			this.getAudioManager().stopRecognition();
			this.unbindAudioEvents();
		},
		bindAudioEvents()
		{
			this.getAudioManager().subscribe(AudioManager.events.recognitionResult, (event: BaseEvent) => {
				const text: string = event.getData();
				this.$emit('inputResult', text);
			});
			this.getAudioManager().subscribe(AudioManager.events.recognitionStart, () => {
				this.$emit('inputStart');
			});
			this.getAudioManager().subscribe(AudioManager.events.recognitionError, () => {
				this.$emit('error');
				BX.UI.Notification.Center.notify({ content: this.loc('IM_CONTENT_COPILOT_TEXTAREA_AUDIO_INPUT_ERROR') });
			});
		},
		unbindAudioEvents()
		{
			this.getAudioManager().unsubscribeAll(AudioManager.events.recognitionResult);
			this.getAudioManager().unsubscribeAll(AudioManager.events.recognitionStart);
			this.getAudioManager().unsubscribeAll(AudioManager.events.recognitionEnd);
			this.getAudioManager().unsubscribeAll(AudioManager.events.recognitionError);
		},
		isAudioModeAvailable(): boolean
		{
			return AudioManager.isAvailable();
		},
		getAudioManager(): AudioManager
		{
			if (!this.audioManager)
			{
				this.audioManager = new AudioManager();
			}

			return this.audioManager;
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div
			v-if="isAudioModeAvailable()"
			@click="onClick"
			class="bx-im-copilot-audio-input__container"
			:class="{'--active': audioMode}"
		></div>
	`,
};
