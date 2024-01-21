import { Event } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { DesktopApi } from 'im.v2.lib.desktop-api';

const RecognizerEvent = {
	audioend: 'audioend',
	audiostart: 'audiostart',
	end: 'end',
	error: 'error',
	nomatch: 'nomatch',
	result: 'result',
	soundend: 'soundend',
	soundstart: 'soundstart',
	speechend: 'speechend',
	speechstart: 'speechstart',
	start: 'start',
};

const EVENT_NAMESPACE = 'BX.Messenger.v2.CopilotAudioManager';

export class AudioManager extends EventEmitter
{
	static events = {
		recognitionStart: 'recognitionStart',
		recognitionError: 'recognitionError',
		recognitionEnd: 'recognitionEnd',
		recognitionResult: 'recognitionResult',
	};

	recognizer: SpeechRecognition | null = null;

	static isAvailable(): boolean
	{
		if (DesktopApi.isDesktop())
		{
			return DesktopApi.getApiVersion() > 74;
		}

		return Boolean(window.SpeechRecognition || window.webkitSpeechRecognition);
	}

	constructor()
	{
		super();
		this.setEventNamespace(EVENT_NAMESPACE);

		this.recognizer = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
		this.#initSettings();
		this.#bindEvents();
	}

	startRecognition()
	{
		this.recognizer.start();
	}

	stopRecognition()
	{
		this.recognizer.stop();
	}

	#bindEvents()
	{
		Event.bind(this.recognizer, RecognizerEvent.start, () => {
			this.lastRecognizedText = '';
			this.emit(AudioManager.events.recognitionStart);
		});
		Event.bind(this.recognizer, RecognizerEvent.error, (event: SpeechRecognitionErrorEvent) => {
			this.emit(AudioManager.events.recognitionError, event.error);
			// eslint-disable-next-line no-console
			console.error('Copilot: AudioManager: error', event.error);
		});
		Event.bind(this.recognizer, RecognizerEvent.end, () => {
			this.lastRecognizedText = '';
			this.emit(AudioManager.events.recognitionEnd);
		});
		Event.bind(this.recognizer, RecognizerEvent.result, (event: SpeechRecognitionEvent) => {
			const recognizedText = this.#getRecognizedText(event);
			const newText = this.#getNewText(recognizedText);

			if (newText !== '')
			{
				this.emit(AudioManager.events.recognitionResult, newText);
			}

			this.lastRecognizedText = recognizedText;
		});
	}

	#getRecognizedText(event: SpeechRecognitionEvent): string
	{
		let recognizedChunk = '';
		Object.values(event.results).forEach((result: SpeechRecognitionResult) => {
			if (result.isFinal)
			{
				return;
			}
			const [alternative] = result;
			const { transcript } = alternative;
			recognizedChunk += transcript;
		});

		return recognizedChunk;
	}

	#getNewText(fullText: string): string
	{
		let additionalText = '';
		const lastChunkLength = this.lastRecognizedText.length;
		if (fullText.length > lastChunkLength)
		{
			additionalText = fullText.slice(lastChunkLength);
		}

		return additionalText;
	}

	#initSettings()
	{
		this.recognizer.continuous = true;
		this.recognizer.interimResults = true;
	}
}
