import { Dom, Tag, Text, Type } from 'main.core';
import { Loader } from 'main.loader';
import { ProgressRound } from 'ui.progressround'
import './player.css';

export type PlayerOptions = {
	wrapper: HTMLElement,
	width: number,
	scale: ?number,
	posterUrl: ?string,
	videos: Array<{url: string, type: string}>,
	loop: boolean,
	autoplay: boolean,
	muted: boolean,
	analyticsCallback: ?Function,
}

export class RoundPlayer
{
	static PLAY_STATE_BACKGROUND = 'background';
	static PLAY_STATE_USER = 'user';

	#pausePlayerWidth: number;
	#scale: number;
	#videos: Array;
	#loop: true;
	#autoplay: false;
	#muted: true;
	#content: HTMLElement;
	#videoNode: HTMLVideoElement;
	#playerNode: HTMLElement;
	#progressBar: ProgressRound;
	#barPadding: number = 3;
	#posterUrl: string;
	#loader: Loader;
	#currentPlayState: string = RoundPlayer.PLAY_STATE_BACKGROUND;
	#playButton: HTMLElement;
	#stopButton: HTMLElement;
	#wrapper: HTMLElement;
	#hasAutoPlayed: boolean = false;
	#analyticsCallback: ?Function;

	constructor(options: PlayerOptions)
	{
		this.#wrapper = options.wrapper;
		this.#pausePlayerWidth = options.width ?? 86;
		this.#scale = Type.isNumber(options.scale) ? options.scale : 1;
		this.#videos = Type.isArrayFilled(options.videos) ? options.videos : [];
		this.#loop = Type.isBoolean(options.loop) ? options.loop : true;
		this.#autoplay = Type.isBoolean(options.autoplay) ? options.autoplay : true;
		this.#muted = Type.isBoolean(options.muted) ? options.muted : true;
		this.#posterUrl = options.posterUrl;
		this.#analyticsCallback = Type.isFunction(options.analyticsCallback) ? options.analyticsCallback : null;

		this.#playerNode = Tag.render`<div class="ui-popupcomponentmaker__round-player"></div>`;
		this.#playButton = Tag.render`<div class="ui-popupcomponentmaker__round-player-btn"></div>`;
		this.#stopButton = Tag.render`<div class="ui-popupcomponentmaker__round-player-btn --stop-btn"></div>`;
		let poster = '';
		if (Type.isStringFilled(this.#posterUrl))
		{
			this.#playerNode.style.backgroundImage = 'url("'+Text.encode(this.#posterUrl)+'")';
			poster = 'poster="'+Text.encode(this.#posterUrl)+'"';
		}

		const autoplay = this.#autoplay ? 'autoplay' : '';
		const muted = this.#muted ? 'muted' : '';
		this.#videoNode = Tag.render`<video ${poster} ${autoplay} ${muted}></video>`;
		this.#videoNode.muted = this.#muted;
		this.#videoNode.autoplay = this.#autoplay;
		// this.#videoNode.loop = this.#loop;

		this.#loader = new Loader({
			size: 40
		});
		this.#playerNode.style.width = this.#pausePlayerWidth + 'px';

		this.#videoNode.addEventListener('timeupdate', this.#onTick.bind(this));
		this.#videoNode.addEventListener('loadedmetadata', this.#onInitVideoMetadata.bind(this));
		this.#playerNode.addEventListener('click', this.#onClickPlayer.bind(this));
		this.#videoNode.addEventListener('ended', this.#onVideoEnded.bind(this));
		this.#videoNode.addEventListener('play', this.#onPlay.bind(this));
		this.#videoNode.addEventListener('pause', this.#onPause.bind(this));
		this.#playButton.addEventListener('click', this.#onClickPlayer.bind(this));
		this.#stopButton.addEventListener('click', this.#onClickStopButton.bind(this));

		this.#videoNode.addEventListener('canplay', () => {
			this.#loader.hide();
		});
		this.#videoNode.addEventListener('waiting', () => {
			this.#loader.show(this.#playerNode);
		});
	}

	render()
	{
		if (this.#content)
		{
			return this.#content;
		}

		this.#videos.forEach((video) => {
			Dom.append(Tag.render`<source src="${video.url}" type="${video.type}">`, this.#videoNode);
		});

		Dom.append(this.#videoNode, this.#playerNode);
		Dom.append(this.#playButton, this.#wrapper);
		Dom.append(this.#stopButton, this.#wrapper);
		Dom.append(this.#playerNode, this.#wrapper);

		this.#content = this.#wrapper;

		return this.#content;
	}

	renderTo(wrapper: HTMLElement): HTMLElement
	{
		Dom.append(wrapper, this.render());

		return wrapper;
	}

	#onInitVideoMetadata(event): void
	{
		this.#progressBar = new ProgressRound({
			width: this.#pausePlayerWidth - 2 * this.#barPadding,
			lineSize: 2,
			maxValue: this.#videoNode.duration,
			value: this.#videoNode.currentTime,
			colorBar: '#fff',
			colorTrack: 'rgba(0, 0, 0, 0)',
		});

		if (this.#autoplay)
		{
			this.play();
		}
	}

	#onTick(): void
	{
		this.#progressBar.update(this.#videoNode.currentTime);
	}

	#onClickPlayer(): void
	{
		if (this.#currentPlayState === RoundPlayer.PLAY_STATE_BACKGROUND)
		{
			this.userPlay();
			Dom.removeClass(this.render(), '--stop');
		}
		else
		{
			this.#videoNode.paused ? this.play() : this.pause();
		}

		if (this.#analyticsCallback)
		{
			this.#analyticsCallback('click-player');
		}
	}

	#onClickStopButton(): void
	{
		if (this.#analyticsCallback)
		{
			this.#analyticsCallback('click-player');
		}

		this.stop();
	}

	#onVideoEnded(): void
	{
		if (this.#analyticsCallback && (!this.#videoNode.muted || !this.#hasAutoPlayed))
		{
			this.#analyticsCallback('video_finished', `isMuted_${this.#videoNode.muted ? 'Y' : 'N'}`);
		}

		if (!this.#hasAutoPlayed)
		{
			this.#hasAutoPlayed = this.#videoNode.muted;
		}

		this.stop();
		Dom.remove(this.#progressBar.getContainer());
		this.setMute(true);

		if (this.#loop)
		{
			this.play();
		}
	}

	#scaleTo(x: number)
	{
		this.#playerNode.style.transform = `scale(${x})`;
	}

	#onPause(): void
	{
		this.#scaleTo(1);

		if (this.#analyticsCallback && (!this.#videoNode.muted || !this.#hasAutoPlayed))
		{
			this.#analyticsCallback('on-pause');
		}
	}

	#onPlay(): void
	{
		this.#scaleTo(this.#scale);

		if (this.#analyticsCallback && (!this.#videoNode.muted || !this.#hasAutoPlayed))
		{
			this.#analyticsCallback('on-play');
		}
	}

	play(): void
	{
		this.#videoNode.play();
		Dom.removeClass(this.render(), '--stop');
	}

	setMute(mute: boolean): void
	{
		this.#videoNode.muted = mute;
	}

	getPlayState(): string
	{
		return this.#currentPlayState;
	}

	pause(): void
	{
		this.#videoNode.pause();
	}

	stop(): void
	{
		this.pause();
		this.#currentPlayState = RoundPlayer.PLAY_STATE_BACKGROUND;
		this.#videoNode.currentTime = 0;
		Dom.addClass(this.render(), '--stop');
	}

	userPlay()
	{
		this.stop();
		this.#currentPlayState = RoundPlayer.PLAY_STATE_USER;
		this.#progressBar.setValue(0);
		Dom.remove(this.#progressBar.getContainer());
		this.#progressBar.renderTo(this.#playerNode);
		this.setMute(false);
		this.play();
	}
}
