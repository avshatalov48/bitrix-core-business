/**
 * Bitrix UI
 * Social Video Vue component
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2021 Bitrix
 */

import 'ui.fonts.opensans';
import 'main.polyfill.intersectionobserver';
import { lazyload } from 'ui.vue3.directives.lazyload';
import { BaseEvent, EventEmitter } from 'main.core.events';

import './socialvideo.css';

import type { JsonObject } from 'main.core';

const State = Object.freeze({
	play: 'play',
	pause: 'pause',
	stop: 'stop',
	none: 'none',
});
export { State as SocialVideoState };

// @vue/component
export const SocialVideo = {
	name: 'SocialVideo',
	directives: { lazyload },
	props:
	{
		id: {
			type: [String, Number],
			default: 0,
		},
		src: {
			type: String,
			default: '',
		},
		preview: {
			type: String,
			default: '',
		},
		autoplay: {
			type: Boolean,
			default: true,
		},
		containerClass: {
			type: String,
			default: null,
		},
		containerStyle: {
			type: Object,
			default: null,
		},
		elementStyle: {
			type: Object,
			default: null,
		},
		showControls: {
			type: Boolean,
			default: true,
		},
		playCallback: {
			type: [Function, null],
			default: null,
		},
	},
	data(): JsonObject
	{
		return {
			preload: 'none',
			previewLoaded: false,
			loaded: false,
			loading: false,
			playAfterLoad: false,
			enterFullscreen: false,
			playBeforeMute: 2,
			state: State.none,
			progress: 0,
			timeCurrent: 0,
			timeTotal: 0,
			muteFlag: true,
		};
	},
	computed:
	{
		State: () => State,
		autoPlayDisabled(): boolean
		{
			return !this.autoplay && this.state === State.none;
		},
		showStartButton(): boolean
		{
			return this.autoPlayDisabled && this.previewLoaded;
		},
		showInterface(): boolean
		{
			return this.previewLoaded && !this.showStartButton;
		},
		labelTime(): string
		{
			if (!this.loaded && !this.timeTotal)
			{
				return '--:--';
			}

			let time = 0;
			if (this.state === State.play)
			{
				time = this.timeTotal - this.timeCurrent;
			}
			else
			{
				time = this.timeTotal;
			}

			return this.formatTime(time);
		},
		isMobile(): boolean
		{
			const UA = navigator.userAgent.toLowerCase();

			return (
				UA.includes('android')
				|| UA.includes('iphone')
				|| UA.includes('ipad')
				|| UA.includes('bitrixmobile')
			);
		},
		source(): HTMLVideoElement
		{
			return this.$refs.source;
		},
	},
	created()
	{
		if (!this.preview)
		{
			this.previewLoaded = true;
			this.preload = 'metadata';
		}

		EventEmitter.subscribe('ui:socialvideo:play', this.onPlay);
		EventEmitter.subscribe('ui:socialvideo:stop', this.onStop);
		EventEmitter.subscribe('ui:socialvideo:pause', this.onPause);
	},
	mounted()
	{
		this.getObserver().observe(this.$refs.body);
	},
	beforeUnmount()
	{
		EventEmitter.unsubscribe('ui:socialvideo:play', this.onPlay);
		EventEmitter.unsubscribe('ui:socialvideo:stop', this.onStop);
		EventEmitter.unsubscribe('ui:socialvideo:pause', this.onPause);

		this.getObserver().unobserve(this.$refs.body);
	},
	methods:
	{
		loadFile(play = false)
		{
			if (this.loaded)
			{
				return true;
			}

			if (this.loading)
			{
				return true;
			}

			this.preload = 'auto';

			this.loading = true;
			this.playAfterLoad = play;

			return true;
		},
		clickToButton(event)
		{
			if (!this.src)
			{
				return;
			}

			if (this.state === State.play)
			{
				this.getObserver().unobserve(this.$refs.body);
				this.pause();
			}
			else
			{
				this.play();
			}

			event.stopPropagation();
		},
		clickToMute()
		{
			if (!this.src)
			{
				return;
			}

			if (this.muteFlag)
			{
				this.unmute();
			}
			else
			{
				this.mute();
			}
		},
		click(event)
		{
			if (this.autoPlayDisabled)
			{
				this.play();

				event.stopPropagation();

				return;
			}

			if (this.isMobile)
			{
				if (this.source.webkitEnterFullscreen)
				{
					this.unmute();
					this.enterFullscreen = true;
					this.source.webkitEnterFullscreen();
				}
				else if (this.source.requestFullscreen)
				{
					this.unmute();
					this.enterFullscreen = true;
					this.source.requestFullscreen();
				}
				else
				{
					this.$emit('click', event);
				}
			}
			else
			{
				this.$emit('click', event);
			}

			event.stopPropagation();
		},
		play(event)
		{
			if (this.playCallback)
			{
				this.playCallback();

				return;
			}

			if (!this.loaded)
			{
				this.loadFile(true);

				return;
			}

			if (!this.source)
			{
				return;
			}

			this.source.play();
		},
		pause()
		{
			if (!this.source)
			{
				return;
			}

			this.playAfterLoad = false;

			this.source.pause();
		},
		stop()
		{
			if (!this.source)
			{
				return;
			}

			this.state = State.stop;
			this.source.pause();
		},
		mute()
		{
			if (!this.source)
			{
				return;
			}

			this.muteFlag = true;
			this.playBeforeMute = 2;
			this.source.muted = true;
		},
		unmute()
		{
			if (!this.source)
			{
				return;
			}

			this.muteFlag = false;
			this.source.muted = false;
		},
		setProgress(percent)
		{
			this.progress = percent;
		},
		formatTime(second): string
		{
			second = Math.floor(second);

			const hour = Math.floor(second / 60 / 60);
			if (hour > 0)
			{
				second -= hour * 60 * 60;
			}

			const minute = Math.floor(second / 60);
			if (minute > 0)
			{
				second -= minute * 60;
			}

			return (hour > 0? hour+':': '')
				+ (hour > 0? minute.toString().padStart(2, "0")+':': minute+':')
				+ second.toString().padStart(2, "0")
		},
		onPlay(event: BaseEvent)
		{
			const data = event.getData();

			if (data.id !== this.id)
			{
				return false;
			}

			if (data.start)
			{
				this.stop();
			}

			this.play();
		},
		onStop(event: BaseEvent)
		{
			const data = event.getData();

			if (data.initiator === this.id)
			{
				return false;
			}

			this.stop();
		},
		onPause(event: BaseEvent)
		{
			const data = event.getData();

			if (data.initiator === this.id)
			{
				return false;
			}

			this.pause();
		},

		videoEventRouter(eventName, event)
		{
			if (
				eventName === 'durationchange'
				|| eventName === 'loadeddata'
			)
			{
				if (!this.source)
				{
					return false;
				}
				this.timeTotal = this.source.duration;
			}
			else if (eventName === 'loadedmetadata')
			{
				if (!this.source)
				{
					return false;
				}
				this.timeTotal = this.source.duration;
				this.loaded = true;

				if (this.playAfterLoad)
				{
					this.play();
				}
			}
			else if (
				eventName === 'abort'
				|| eventName === 'error'
			)
			{
				console.error('BxSocialVideo: load failed', this.id, event);

				this.loading = false;
				this.state = State.none;
				this.timeTotal = 0;
				this.preload = 'none';
			}
			else if (
				eventName === 'canplaythrough'
			)
			{
				this.loading = false;
				this.loaded = true;

				if (this.playAfterLoad)
				{
					this.play();
				}
			}
			else if (eventName === 'volumechange')
			{
				if (!this.source)
				{
					return false;
				}

				if (this.source.muted)
				{
					this.mute();
				}
				else
				{
					this.unmute();
				}
			}
			else if (eventName === 'timeupdate')
			{
				if (!this.source)
				{
					return false;
				}

				this.timeCurrent = this.source.currentTime;

				if (!this.muteFlag && !this.enterFullscreen && this.timeCurrent === 0)
				{
					if (this.playBeforeMute <= 0)
					{
						this.mute();
					}

					this.playBeforeMute -= 1;
				}

				this.setProgress(Math.round(100 / this.timeTotal * this.timeCurrent));
			}
			else if (eventName === 'pause')
			{
				if (this.state !== State.stop)
				{
					this.state = State.pause;
				}

				if (this.enterFullscreen)
				{
					this.enterFullscreen = false;
					this.mute();
					this.play();
				}
			}
			else if (eventName === 'play')
			{
				this.state = State.play;

				if (this.state === State.stop)
				{
					this.progress = 0;
					this.timeCurrent = 0;
				}

				if (this.enterFullscreen)
				{
					this.enterFullscreen = false;
				}
			}
		},
		getObserver(): IntersectionObserver
		{
			if (this.observer)
			{
				return this.observer;
			}

			this.observer = new IntersectionObserver((entries, observer) => {
				if (this.autoPlayDisabled)
				{
					return;
				}

				entries.forEach((entry) => {
					if (entry.isIntersecting)
					{
						this.play();
					}
					else
					{
						this.pause();
					}
				});
			}, {
				threshold: [0, 1],
			});

			return this.observer;
		},
		lazyLoadCallback(element)
		{
			this.previewLoaded = element.state === 'success';
		},
	},
	template: `
		<div :class="['ui-vue-socialvideo', containerClass, {
				'ui-vue-socialvideo-mobile': isMobile,
			}]" :style="containerStyle" @click="click">
			<transition name="ui-vue-socialvideo-animation-fade">
				<div v-if="showStartButton && showControls" class="ui-vue-socialvideo-button-start">
					<span class="ui-vue-socialvideo-button-start-icon"></span>
				</div>
			</transition>
			<transition name="ui-vue-socialvideo-animation-fade">
				<div v-if="showInterface && showControls" class="ui-vue-socialvideo-overlay-container">
					<div class="ui-vue-socialvideo-controls-container" @click="clickToButton">
						<button :class="['ui-vue-socialvideo-control', {
							'ui-vue-socialvideo-control-loader': loading,
							'ui-vue-socialvideo-control-play': !loading && state !== State.play,
							'ui-vue-socialvideo-control-pause': !loading && state === State.play,
						}]"></button>
					</div>
					<div class="ui-vue-socialvideo-info-container" @click="clickToMute">
						<span class="ui-vue-socialvideo-time-current">{{labelTime}}</span>
						<span :class="['ui-vue-socialvideo-sound', {
							'ui-vue-socialvideo-sound-on': state !== State.none && !muteFlag,
							'ui-vue-socialvideo-sound-off': state !== State.none && muteFlag
						}]"></span>
					</div>
				</div>
			</transition>
			<div v-if="!preview" class="ui-vue-socialvideo-background" :style="{position: (src? 'absolute': 'relative')}"></div>
			<div class="ui-vue-socialvideo-container" ref="body">
				<img
					v-lazyload="{callback: lazyLoadCallback}"
					data-lazyload-dont-hide
					v-if="preview"
					class="ui-vue-socialvideo-image-source"
					:data-lazyload-src="preview"
					:style="{position: (src? 'absolute': 'relative'), ...elementStyle}"
				/>
				<video
					v-if="src" :src="src"
					class="ui-vue-socialvideo-source"
					ref="source"
					:preload="preload"
					playsinline
					loop
					muted
					:style="{opacity: (loaded? 1: 0), ...elementStyle}"
					@abort="videoEventRouter('abort', $event)"
					@error="videoEventRouter('error', $event)"
					@suspend="videoEventRouter('suspend', $event)"
					@canplay="videoEventRouter('canplay', $event)"
					@canplaythrough="videoEventRouter('canplaythrough', $event)"
					@durationchange="videoEventRouter('durationchange', $event)"
					@loadeddata="videoEventRouter('loadeddata', $event)"
					@loadedmetadata="videoEventRouter('loadedmetadata', $event)"
					@volumechange="videoEventRouter('volumechange', $event)"
					@timeupdate="videoEventRouter('timeupdate', $event)"
					@play="videoEventRouter('play', $event)"
					@playing="videoEventRouter('playing', $event)"
					@pause="videoEventRouter('pause', $event)"
				></video>
			</div>
		</div>
	`,
};
