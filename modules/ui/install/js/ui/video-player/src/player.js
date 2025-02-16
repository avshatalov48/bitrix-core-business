/* eslint-disable @bitrix24/bitrix24-rules/no-native-dom-methods */
import { Dom, Type, Event, Reflection, Loc, type JsonObject } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { videojs } from 'ui.video-js';
import { GlobalSettings } from './global-settings';
import { PlayerManager } from './player-manager';

let langSetup = false;
videojs.hook('beforesetup', (videoEl, options) => {
	Dom.addClass(videoEl, 'ui-video-player ui-icon-set__scope');
	if (videoEl.tagName.toLowerCase() === 'audio')
	{
		Dom.addClass(videoEl, 'vjs-audio-only-mode');
	}

	if (langSetup === false)
	{
		videojs.addLanguage('video-player', {
			Play: Loc.getMessage('VIDEO_PLAYER_PLAY'),
			Pause: Loc.getMessage('VIDEO_PLAYER_PAUSE'),
			Replay: Loc.getMessage('VIDEO_PLAYER_REPLAY'),
			'Current Time': Loc.getMessage('VIDEO_PLAYER_CURRENT_TIME'),
			Duration: Loc.getMessage('VIDEO_PLAYER_DURATION'),
			'Remaining Time': Loc.getMessage('VIDEO_PLAYER_REMAINING_TIME'),
			Loaded: Loc.getMessage('VIDEO_PLAYER_LOADED'),
			Progress: Loc.getMessage('VIDEO_PLAYER_PROGRESS'),
			'Progress Bar': Loc.getMessage('VIDEO_PLAYER_PROGRESS_BAR'),
			Fullscreen: Loc.getMessage('VIDEO_PLAYER_FULLSCREEN'),
			'Exit Fullscreen': Loc.getMessage('VIDEO_PLAYER_EXIT_FULLSCREEN'),
			Mute: Loc.getMessage('VIDEO_PLAYER_MUTE'),
			Unmute: Loc.getMessage('VIDEO_PLAYER_UNMUTE'),
			'Playback Rate': Loc.getMessage('VIDEO_PLAYER_PLAYBACK_RATE'),
			'Volume Level': Loc.getMessage('VIDEO_PLAYER_VOLUME_LEVEL'),
			'You aborted the media playback': Loc.getMessage('VIDEO_PLAYER_ABORTED_PLAYBACK'),
			'A network error caused the media download to fail part-way.': Loc.getMessage('VIDEO_PLAYER_NETWORK_ERROR'),
			'The media could not be loaded, either because the server or network failed or because the format is not supported.': Loc.getMessage('VIDEO_PLAYER_FORMAT_NOT_SUPPORTED'),
			'The media playback was aborted due to a corruption problem or because the media used features your browser did not support.': Loc.getMessage('VIDEO_PLAYER_PLAYBACK_WAS_ABORTED'),
			'No compatible source was found for this media.': Loc.getMessage('VIDEO_PLAYER_NO_COMPATIBLE_SOURCE'),
			'The media is encrypted and we do not have the keys to decrypt it.': Loc.getMessage('VIDEO_PLAYER_MEDIA_IS_ENCRYPTED'),
			'Play Video': Loc.getMessage('VIDEO_PLAYER_PLAY_VIDEO'),
			'Exit Picture-in-Picture': Loc.getMessage('VIDEO_PLAYER_EXIT_PICTURE_IN_PICTURE'),
			'Picture-in-Picture': Loc.getMessage('VIDEO_PLAYER_PICTURE_IN_PICTURE'),
		});

		langSetup = true;
	}

	return options;
});

export class Player
{
	id: string = null;
	muted: boolean = false;
	hasStarted: boolean = false;
	vjsPlayer: boolean = null;
	isAudio: boolean = false;

	static #globalSettings = new GlobalSettings('bx-video-player-settings');

	constructor(id, params)
	{
		this.id = id;
		this.#fillParameters(params);

		PlayerManager.addPlayer(this);

		this.#fireEvent('onCreate');
	}

	isReady(): boolean
	{
		// eslint-disable-next-line no-underscore-dangle
		return this.vjsPlayer && this.vjsPlayer.isReady_;
	}

	play(): void
	{
		this.setPlayedState();
		this.hasStarted = true;
		try
		{
			this.vjsPlayer.play();
		}
		catch
		{
			// fail silently
		}

		this.#fireEvent('onPlay');
	}

	pause(): void
	{
		try
		{
			this.vjsPlayer.pause();
		}
		catch
		{
			// fail silently
		}

		this.#fireEvent('onPause');
	}

	toggle(): void
	{
		if (this.isPlaying())
		{
			this.pause();
		}
		else
		{
			this.play();
		}
	}

	isPlaying(): boolean
	{
		if (this.vjsPlayer)
		{
			return this.isReady() && !this.vjsPlayer.paused();
		}

		return false;
	}

	isEnded(): boolean
	{
		if (this.vjsPlayer)
		{
			return this.vjsPlayer.ended();
		}

		return false;
	}

	setPlayedState(): void
	{
		const storageHash = this.#getStorageHash();

		const localStorage = Reflection.getClass('BX.localStorage');
		if (localStorage)
		{
			localStorage.set(storageHash, 'played', 14 * 24 * 3600);
		}
	}

	isPlayed(): boolean
	{
		const storageHash = this.#getStorageHash();
		/** @type {BX.localStorage} */
		const localStorage = Reflection.getClass('BX.localStorage');
		if (localStorage)
		{
			return localStorage.get(storageHash) === 'played';
		}

		return true;
	}

	#getStorageHash(): string
	{
		let storageHash = this.id;
		if (Type.isArrayFilled(this.params.sources) && this.params.sources[0].src)
		{
			storageHash = this.params.sources[0].src;
		}

		return `player_${storageHash}`;
	}

	getElement(): HTMLElement | null
	{
		return document.getElementById(this.id);
	}

	createElement(): HTMLElement | null
	{
		let node = this.getElement();
		if (node)
		{
			return node;
		}

		if (!this.id)
		{
			return null;
		}

		let tagName = 'video';

		const classes = ['video-js', 'ui-video-player', 'ui-icon-set__scope'];
		if (this.isAudio)
		{
			tagName = 'audio';
			classes.push('vjs-audio-only-mode');
		}

		let className = classes.join(' ');

		if (this.skin)
		{
			className += ` ${this.skin}`;
		}

		const attrs = {
			id: this.id,
			className,
			width: this.width,
			height: this.height,
			controls: true,
		};

		if (this.muted)
		{
			attrs.muted = true;
		}

		node = Dom.create(tagName, { attrs });

		if (Type.isArrayFilled(this.params.sources))
		{
			for (const source of this.params.sources)
			{
				if (!source.src || !source.type)
				{
					continue;
				}

				const sourceTag = Dom.create('source', {
					attrs: {
						src: source.src,
						type: source.type,
					},
				});

				Dom.append(sourceTag, node);
			}
		}

		return node;
	}

	#fillParameters(options: JsonObject)
	{
		const defaults = this.#getDefaultOptions();
		const params = Type.isPlainObject(options) ? { ...defaults, ...options } : defaults;
		if (Type.isArrayFilled(params.techOrder))
		{
			// Compatibility
			params.techOrder = params.techOrder.filter((tech: string) => tech !== 'flash');
		}

		this.autostart = params.autostart || false;

		if (params.playbackRate)
		{
			params.playbackRate = parseFloat(params.playbackRate);
			if (params.playbackRate !== 1)
			{
				if (params.playbackRate <= 0)
				{
					params.playbackRate = 1;
				}

				if (params.playbackRate > 3)
				{
					params.playbackRate = 3;
				}
			}

			if (params.playbackRate !== 1)
			{
				this.playbackRate = params.playbackRate;
			}
		}

		this.volume = BX.Type.isNumber(params.volume) ? params.volume : null;

		this.startTime = params.startTime || 0;
		this.onInit = params.onInit;
		this.lazyload = params.lazyload;
		this.skin = params.skin || '';
		this.isAudio = params.isAudio || false;

		if (this.isAudio)
		{
			params.width = params.width || 400;
			params.height = params.height || 30;
			params.audioOnlyMode = true;
		}
		else
		{
			params.width = Math.max(params.width || 560, 400);
			params.height = Math.max(params.height || 315, 130);
		}

		this.width = params.width;
		this.height = params.height;
		this.duration = params.duration || null;
		this.muted = params.muted || false;

		this.params = params;
	}

	#getDefaultOptions(): JsonObject
	{
		return {
			controls: true,
			playbackRates: [0.5, 1, 1.25, 1.5, 1.75, 2],
			language: 'video-player',
			userActions: {
				click: this.#handleClick.bind(this),
				hotkeys: this.#handleKeyDown.bind(this),
			},
		};
	}

	setSource(source): void
	{
		if (!source)
		{
			return;
		}

		this.vjsPlayer.src(source);
		this.#fireEvent('onSetSource');
	}

	getSource(): string
	{
		return this.vjsPlayer.src();
	}

	init(): void
	{
		if (this.vjsPlayer !== null)
		{
			return;
		}

		this.#fireEvent('onBeforeInit');

		this.vjsPlayer = videojs(this.id, this.params);

		if (this.isAudio)
		{
			this.#hideAudioControls();
			this.#setInitialVolume();
		}

		this.vjsPlayer.one('loadedmetadata', (event) => {
			if (!this.isAudio && !(this.vjsPlayer.videoWidth() > 0 && this.vjsPlayer.videoHeight() > 0))
			{
				// Throw an error if a video doesn't have dimensions
				event.stopPropagation();
				event.stopImmediatePropagation();

				setTimeout(() => {
					this.vjsPlayer.error(4);
				}, 0);
			}
			else if (this.duration > 0)
			{
				this.vjsPlayer.duration(this.duration);
			}
		});

		this.vjsPlayer.on('fullscreenchange', () => {
			this.vjsPlayer.focus();
		});

		this.#proxyEvents();

		this.vjsPlayer.ready(() => {
			const controlBar = this.vjsPlayer.getChild('ControlBar');
			const playbackButton = controlBar.getChild('PlaybackRateMenuButton');
			if (playbackButton)
			{
				// eslint-disable-next-line no-underscore-dangle
				videojs.off(playbackButton.menuButton_.el(), 'mouseenter');
				videojs.off(playbackButton.el(), 'mouseleave');
			}

			this.vjsPlayer.one('play', this.#handlePlayOnce.bind(this));

			if (Type.isFunction(this.onInit))
			{
				this.onInit(this);
			}

			this.#fireEvent('onAfterInit');
		});

		if (this.autostart && !this.lazyload)
		{
			this.vjsPlayer.one('canplay', () => {
				if (!this.hasStarted)
				{
					this.play();
				}
			});
		}
	}

	isInited(): boolean
	{
		return this.vjsPlayer !== null;
	}

	getEventList(): Array<string>
	{
		return [
			'Player:onBeforeInit',
			'Player:onAfterInit',
			'Player:onCreate',
			'Player:onSetSource',
			'Player:onKeyDown',
			'Player:onPlay',
			'Player:onPause',
			'Player:onClick',
			'Player:onError',
			'Player:onEnded',
			'Player:onEnterPictureInPicture',
			'Player:onLeavePictureInPicture',
		];
	}

	mute(mute: boolean | undefined): boolean
	{
		return this.vjsPlayer?.muted(mute);
	}

	isMuted(): boolean
	{
		return this.vjsPlayer?.muted();
	}

	focus(): void
	{
		this.vjsPlayer?.focus();
	}

	moveBackward(skipTime: number): void
	{
		const currentVideoTime = this.vjsPlayer.currentTime();
		const liveTracker = this.vjsPlayer.liveTracker;

		const seekableStart = liveTracker && liveTracker.isLive() && liveTracker.seekableStart();
		let newTime = 0;

		if (seekableStart && (currentVideoTime - skipTime <= seekableStart))
		{
			newTime = seekableStart;
		}
		else if (currentVideoTime >= skipTime)
		{
			newTime = currentVideoTime - skipTime;
		}

		this.vjsPlayer.currentTime(newTime);
	}

	moveForward(skipTime: number): void
	{
		if (!Type.isNumber(this.vjsPlayer.duration()))
		{
			return;
		}

		const currentVideoTime = this.vjsPlayer.currentTime();
		const liveTracker = this.vjsPlayer.liveTracker;
		const duration = (liveTracker && liveTracker.isLive()) ? liveTracker.seekableEnd() : this.vjsPlayer.duration();
		const newTime = currentVideoTime + skipTime <= duration ? currentVideoTime + skipTime : duration;

		this.vjsPlayer.currentTime(newTime);
	}

	increasePlaybackRate(): void
	{
		const playbackRates: Number[] = this.vjsPlayer.playbackRates();
		const currentPlayback = this.vjsPlayer.playbackRate();

		const nextPlayback = playbackRates.find((value) => {
			return value > currentPlayback;
		});

		if (nextPlayback)
		{
			this.vjsPlayer.playbackRate(nextPlayback);
		}
	}

	decreasePlaybackRate(): void
	{
		const playbackRates = [...this.vjsPlayer.playbackRates()].reverse();
		const currentPlayback = this.vjsPlayer.playbackRate();

		const prevPlayback = playbackRates.find((value) => {
			return value < currentPlayback;
		});

		if (prevPlayback)
		{
			this.vjsPlayer.playbackRate(prevPlayback);
		}
	}

	#hideAudioControls(): void
	{
		this.vjsPlayer.removeChild('BigPlayButton');
		this.vjsPlayer.removeChild('TextTrackSettings');
		this.vjsPlayer.removeChild('PosterImage');
		this.vjsPlayer.controlBar.removeChild('FullscreenToggle');
		this.vjsPlayer.controlBar.removeChild('PictureInPictureToggle');
		this.vjsPlayer.controlBar.removeChild('ChaptersButton');
		this.vjsPlayer.controlBar.removeChild('DescriptionsButton');

		if (this.skin === 'vjs-audio-wave-skin' || this.skin === 'vjs-viewer-audio-player-skin')
		{
			this.vjsPlayer.removeChild('VolumePanel');
			this.vjsPlayer.controlBar.removeChild('VolumePanel');
			this.vjsPlayer.controlBar.removeChild('CurrentTimeDisplay');
			this.vjsPlayer.controlBar.removeChild('PlaybackRateMenuButton');
		}
	}

	#handlePlayOnce(): void
	{
		if (this.playbackRate !== 1)
		{
			this.vjsPlayer.playbackRate(this.playbackRate);
		}

		this.#setInitialVolume();

		if (this.startTime > 0)
		{
			try
			{
				this.vjsPlayer.currentTime(this.startTime);
			}
			catch
			{
				// Fail silently
			}
		}

		this.vjsPlayer.on('volumechange', () => {
			this.constructor.#globalSettings.set('volume', this.vjsPlayer.volume());
		});
	}

	#setInitialVolume(): void
	{
		const hasVolumePanel = !BX.Type.isNil(this.vjsPlayer.controlBar.getChild('VolumePanel'));
		if (hasVolumePanel)
		{
			const volume = this.volume === null ? this.constructor.#globalSettings.get('volume', 0.8) : this.volume;
			this.vjsPlayer.volume(volume);
		}
		else
		{
			const volume = this.volume === null ? 0.8 : this.volume;
			this.vjsPlayer.volume(volume);
		}
	}

	#handleClick(event: MouseEvent): void
	{
		this.toggle();

		event.preventDefault();
		event.stopPropagation();
	}

	#handleKeyDown(event: KeyboardEvent): void
	{
		const beforeKeyDownEvent = new BaseEvent({ event });
		this.#fireEvent('onBeforeKeyDown', beforeKeyDownEvent);
		if (beforeKeyDownEvent.isDefaultPrevented())
		{
			return;
		}

		switch (event.code)
		{
			case 'KeyK':
			case 'Space':
			{
				this.toggle();
				event.preventDefault();
				event.stopPropagation();

				break;
			}

			case 'KeyF':
			{
				if (!this.isAudio)
				{
					if (this.vjsPlayer.isFullscreen())
					{
						this.vjsPlayer.exitFullscreen();
					}
					else
					{
						this.vjsPlayer.requestFullscreen();
					}

					event.preventDefault();
					event.stopPropagation();
				}

				break;
			}

			case 'KeyJ':
			{
				this.moveBackward(10);
				event.preventDefault();
				event.stopPropagation();

				break;
			}

			case 'KeyL':
			{
				this.moveForward(10);
				event.preventDefault();
				event.stopPropagation();

				break;
			}

			case 'ArrowLeft':
			{
				this.moveBackward(5);
				event.preventDefault();
				event.stopPropagation();

				break;
			}

			case 'ArrowRight':
			{
				this.moveForward(5);
				event.preventDefault();
				event.stopPropagation();

				break;
			}

			case 'KeyM':
			{
				if (this.isMuted())
				{
					this.mute(false);
				}
				else
				{
					this.mute(true);
				}

				event.preventDefault();
				event.stopPropagation();

				break;
			}

			case 'Comma':
			{
				this.decreasePlaybackRate();
				event.preventDefault();
				event.stopPropagation();

				break;
			}

			case 'Period':
			{
				this.increasePlaybackRate();
				event.preventDefault();
				event.stopPropagation();

				break;
			}

			default: {
				// nothing
			}
		}

		this.#fireEvent('onKeyDown', new BaseEvent({ event }));
	}

	#fireEvent(eventName: string, event): void
	{
		if (Type.isStringFilled(eventName))
		{
			const fullName = `Player:${eventName}`;

			const compatEvent = event || new BaseEvent();
			compatEvent.setCompatData([this, fullName]);

			Event.EventEmitter.emit(this, fullName, compatEvent);
		}
	}

	#proxyEvents()
	{
		this.vjsPlayer.on('play', () => {
			this.#fireEvent('onPlay');
			this.hasStarted = true;
		});

		this.vjsPlayer.on('pause', () => {
			this.#fireEvent('onPause');
		});

		this.vjsPlayer.on('click', () => {
			this.#fireEvent('onClick');
		});

		this.vjsPlayer.on('ended', () => {
			this.#fireEvent('onEnded');
		});

		this.vjsPlayer.on('loadedmetadata', () => {
			this.#fireEvent('onLoadedMetadata');
		});

		this.vjsPlayer.on('error', () => {
			this.#fireEvent('onError');
		});

		this.vjsPlayer.on('enterpictureinpicture', () => {
			this.#fireEvent('onEnterPictureInPicture');
		});

		this.vjsPlayer.on('leavepictureinpicture', () => {
			const event = new BaseEvent();
			this.#fireEvent('onLeavePictureInPicture', event);

			if (!event.isDefaultPrevented())
			{
				const visible = PlayerManager.isVisibleOnScreen(this.id, 1);
				if (!visible)
				{
					this.pause();
				}
			}
		});
	}

	destroy()
	{
		PlayerManager.removePlayer(this);

		if (this.vjsPlayer !== null)
		{
			this.vjsPlayer.dispose();
		}

		this.vjsPlayer = null;
	}
}
