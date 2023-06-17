import {EventEmitter} from 'main.core.events';

import {SoundType} from 'im.v2.const';

const SoundFile = {
	[SoundType.reminder]: '/bitrix/js/im/audio/reminder.mp3',
	[SoundType.newMessage1]: '/bitrix/js/im/audio/new-message-1.mp3',
	[SoundType.newMessage2]: '/bitrix/js/im/audio/new-message-2.mp3',
	[SoundType.send]: '/bitrix/js/im/audio/send.mp3',
	[SoundType.dialtone]: '/bitrix/js/im/audio/video-dialtone.mp3',
	[SoundType.ringtone]: '/bitrix/js/im/audio/video-ringtone.mp3',
	[SoundType.start]: '/bitrix/js/im/audio/video-start.mp3',
	[SoundType.stop]: '/bitrix/js/im/audio/video-stop.mp3',
	[SoundType.error]: '/bitrix/js/im/audio/video-error.mp3',
};

export class SoundPlayer
{
	static syncEvent = 'im-sound-stop';

	#isPlayingLoop: boolean = false;
	#currentPlayingSound: ?HTMLAudioElement;
	#loopTimers: {[type: string]: number} = {};

	constructor()
	{
		EventEmitter.subscribe('onLocalStorageSet', (event) => {
			const [changedLocalStorageData] = event.getData();
			if (changedLocalStorageData.key !== SoundPlayer.syncEvent)
			{
				return;
			}

			this.stop(changedLocalStorageData.value.soundType, true);
		});
	}

	playSingle(type: $Keys<typeof SoundFile>)
	{
		if (this.#isPlayingLoop)
		{
			return;
		}

		if (this.#currentPlayingSound)
		{
			this.stop(type);
		}

		this.#notifyOtherTabs(type);

		this.#currentPlayingSound = new Audio(SoundFile[type]);
		this.#currentPlayingSound.play().catch(() => {
			this.#currentPlayingSound = null;
		});
	}

	playLoop(type: $Keys<typeof SoundFile>, timeout: number = 5000)
	{
		if (this.#currentPlayingSound)
		{
			this.stop(type);
		}

		this.#isPlayingLoop = false;
		this.playSingle(type);

		this.#isPlayingLoop = true;
		this.#loopTimers[type] = setTimeout(() => {
			this.playLoop(type, timeout);
		}, timeout);
	}

	stop(type, skip = false)
	{
		if (!skip)
		{
			this.#notifyOtherTabs(type);
		}

		if (this.#loopTimers[type])
		{
			this.#isPlayingLoop = false;
			clearTimeout(this.#loopTimers[type]);
		}

		if (!this.#currentPlayingSound)
		{
			return;
		}

		this.#currentPlayingSound.pause();
		this.#currentPlayingSound.currentTime = 0;
		this.#currentPlayingSound = null;
	}

	#notifyOtherTabs(soundType: $Keys<typeof SoundFile>)
	{
		const localStorageTtl = 1;

		BX.localStorage.set(SoundPlayer.syncEvent, {soundType}, localStorageTtl);
	}
}