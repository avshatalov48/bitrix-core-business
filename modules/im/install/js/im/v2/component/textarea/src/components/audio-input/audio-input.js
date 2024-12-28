import { BaseEvent, EventEmitter } from 'main.core.events';

import { EventType } from 'im.v2.const';
import { Analytics } from 'im.v2.lib.analytics';

import { AudioManager } from './classes/audio-manager';

import './css/audio-input.css';

import type { JsonObject } from 'main.core';

// @vue/component
export const AudioInput = {
	name: 'AudioInput',
	emits: ['inputStart', 'inputResult'],
	data(): JsonObject
	{
		return {
			audioMode: false,
			audioUsed: false,
		};
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
	created()
	{
		EventEmitter.subscribe(EventType.textarea.onAfterSendMessage, this.handleOnAfterSendMessage);
	},
	beforeUnmount()
	{
		EventEmitter.unsubscribe(EventType.textarea.onAfterSendMessage, this.handleOnAfterSendMessage);
	},
	methods:
	{
		onClick()
		{
			if (this.audioMode)
			{
				this.audioMode = false;

				return;
			}

			this.audioMode = true;
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
				this.audioUsed = true;
			});
			this.getAudioManager().subscribe(AudioManager.events.recognitionStart, () => {
				this.$emit('inputStart');
			});
			this.getAudioManager().subscribe(AudioManager.events.recognitionEnd, () => {
				this.audioMode = false;
			});
			this.getAudioManager().subscribe(AudioManager.events.recognitionError, () => {
				this.audioMode = false;
				BX.UI.Notification.Center.notify({ content: this.loc('IM_TEXTAREA_AUDIO_INPUT_ERROR') });
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
		handleOnAfterSendMessage()
		{
			if (this.audioUsed)
			{
				Analytics.getInstance().copilot.onUseAudioInput();
				this.audioUsed = false;
			}

			this.audioMode = false;
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
