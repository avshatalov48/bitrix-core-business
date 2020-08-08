/**
 * Bitrix UI
 * Audio player Vue component
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2019 Bitrix
 */

import "./audioplayer.css";
import 'main.polyfill.intersectionobserver';

import {Vue} from 'ui.vue';

const State = Object.freeze({
	play: 'play',
	pause: 'pause',
	stop: 'stop',
	none: 'none',
});

Vue.component('bx-audioplayer',
{
	props:
	{
		id: { default: 0 },
		src: { default: '' },
		autoPlayNext: { default: true },
		background: { default: 'light' },
	},
	data()
	{
		return {
			isDark: false,
			preload: "none",
			loaded: false,
			loading: false,
			playAfterLoad: false,
			state: State.none,
			progress: 0,
			progressInPixel: 0,
			seek: 0,
			timeCurrent: 0,
			timeTotal: 0,
		}
	},
	created()
	{
		this.preloadRequestSent = false;
		this.registeredId = 0;

		this.registerPlayer(this.id);

		this.$root.$on('ui:audioplayer:play', this.onPlay);
		Vue.event.$on('ui:audioplayer:play', this.onPlay);
		this.$root.$on('ui:audioplayer:stop', this.onStop);
		Vue.event.$on('ui:audioplayer:stop', this.onStop);
		this.$root.$on('ui:audioplayer:pause', this.onPause);
		Vue.event.$on('ui:audioplayer:pause', this.onPause);
		this.$root.$on('ui:audioplayer:preload', this.onPreload);

		this.isDark = this.background === 'dark';
	},
	mounted()
	{
		this.getObserver().observe(this.$refs.body);
	},
	beforeDestroy()
	{
		this.unregisterPlayer();

		this.$root.$off('ui:audioplayer:play', this.onPlay);
		Vue.event.$off('ui:audioplayer:play', this.onPlay);
		this.$root.$off('ui:audioplayer:stop', this.onStop);
		Vue.event.$off('ui:audioplayer:stop', this.onStop);
		this.$root.$off('ui:audioplayer:pause', this.onPause);
		Vue.event.$off('ui:audioplayer:pause', this.onPause);
		this.$root.$off('ui:audioplayer:preload', this.onPreload);

		this.getObserver().unobserve(this.$refs.body);
	},
	watch:
	{
		id(value)
		{
			this.registerPlayer(value);
		},
		progress(value)
		{
			if (value > 70)
			{
				this.preloadNext();
			}
		},
	},
	methods:
	{
		loadFile(play = false)
		{
			if (this.loaded)
			{
				return true;
			}

			if (this.loading && !play)
			{
				return true;
			}

			this.preload = 'auto';

			if (play)
			{
				this.loading = true;

				if (this.source())
				{
					this.source().play();
				}
			}

			return true;
		},
		clickToButton()
		{
			if (!this.src)
			{
				return false;
			}

			if (this.state === State.play)
			{
				this.pause();
			}
			else
			{
				this.play();
			}
		},
		play()
		{
			if (!this.loaded)
			{
				this.loadFile(true);
				return false;
			}

			this.source().play();
		},
		pause()
		{
			this.source().pause();
		},
		stop()
		{
			this.state = State.stop;
			this.source().pause();
		},
		setPosition(event)
		{
			if (!this.loaded)
			{
				this.loadFile(true);
				return false;
			}

			let pixelPerPercent = this.$refs.track.offsetWidth / 100;

			this.setProgress(this.seek / pixelPerPercent, this.seek);

			if (this.state !== State.play)
			{
				this.state = State.pause;
			}

			this.play();
			this.source().currentTime = this.timeTotal/100*this.progress;
		},
		seeking(event)
		{
			if (!this.loaded)
			{
				return false;
			}

			this.seek = event.offsetX > 0? event.offsetX: 0;

			return true;
		},
		setProgress(percent, pixel = -1)
		{
			this.progress = percent;
			this.progressInPixel = pixel > 0? pixel: Math.round(this.$refs.track.offsetWidth / 100 * percent);
		},
		formatTime(second)
		{
			second = Math.floor(second);

			const hour = Math.floor(second/60/60);
			if (hour > 0)
			{
				second -= hour*60*60;
			}

			const minute = Math.floor(second/60);
			if (minute > 0)
			{
				second -= minute*60;
			}

			return (hour > 0? hour+':': '')
					+ (hour > 0? minute.toString().padStart(2, "0")+':': minute+':')
					+ second.toString().padStart(2, "0")
		},
		registerPlayer(id)
		{
			if (id <= 0)
			{
				return false;
			}

			if (typeof this.$root.$uiAudioPlayerId === 'undefined')
			{
				this.$root.$uiAudioPlayerId = [];
			}

			this.unregisterPlayer();

			this.$root.$uiAudioPlayerId = [...new Set([...this.$root.$uiAudioPlayerId, id])].sort((a, b) => {
				if (a > b)
					return 1;
				else if (a < b)
					return -1;
				else
					return 0;
			});

			this.registeredId = id;

			return true;
		},
		unregisterPlayer()
		{
			if (!this.registeredId)
			{
				return true;
			}

			this.$root.$uiAudioPlayerId = this.$root.$uiAudioPlayerId.filter(id => id !== this.registeredId);

			this.registeredId = 0;

			return true;
		},
		playNext()
		{
			if (!this.registeredId || !this.autoPlayNext)
			{
				return false;
			}

			const nextId = this.$root.$uiAudioPlayerId.filter(id => id > this.registeredId).slice(0, 1)[0];
			if (nextId)
			{
				this.$root.$emit('ui:audioplayer:play', {id: nextId, start: true});
			}

			return true;
		},
		preloadNext()
		{
			if (this.preloadRequestSent)
			{
				return true;
			}

			if (!this.registeredId || !this.autoPlayNext)
			{
				return false;
			}

			this.preloadRequestSent = true;

			const nextId = this.$root.$uiAudioPlayerId.filter(id => id > this.registeredId).slice(0, 1)[0];
			if (nextId)
			{
				this.$root.$emit('ui:audioplayer:preload', {id: nextId});
			}

			return true;
		},
		onPlay(event)
		{
			if (event.id !== this.id)
			{
				return false;
			}

			if (event.start)
			{
				this.stop();
			}

			this.play();
		},
		onStop(event)
		{
			if (event.initiator === this.id)
			{
				return false;
			}

			this.stop();
		},
		onPause(event)
		{
			if (event.initiator === this.id)
			{
				return false;
			}

			this.pause();
		},
		onPreload(event)
		{
			if (event.id !== this.id)
			{
				return false;
			}

			this.loadFile();
		},
		source()
		{
			return this.$refs.source;
		},
		audioEventRouter(eventName, event)
		{
			if (
				eventName === 'durationchange'
				|| eventName === 'loadeddata'
				|| eventName === 'loadedmetadata'
			)
			{
				this.timeTotal = this.source().duration;
			}
			else if (
				eventName === 'abort'
				|| eventName === 'error'
			)
			{
				console.error('BxAudioPlayer: load failed', this.id, event);

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
			}
			else if (eventName === 'timeupdate')
			{
				if (!this.source())
				{
					return;
				}

				this.timeCurrent = this.source().currentTime;

				this.setProgress(Math.round(100/this.timeTotal*this.timeCurrent));

				if (
					this.state === State.play
					&& this.timeCurrent >= this.timeTotal
				)
				{
					this.playNext();
				}
			}
			else if (eventName === 'pause')
			{
				if (this.state !== State.stop)
				{
					this.state = State.pause;
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

				if (this.id > 0)
				{
					this.$root.$emit('ui:audioplayer:pause', {initiator: this.id});
					Vue.event.$emit('ui:audioplayer:pause', {initiator: this.id});
				}
			}
		},
		getObserver()
		{
			if (this.observer)
			{
				return this.observer;
			}

			this.observer = new IntersectionObserver((entries, observer) =>
			{
				entries.forEach((entry) =>
				{
					if (entry.isIntersecting)
					{
						if (this.preload === "none")
						{
							this.preload = "metadata";
							this.observer.unobserve(entry.target);
						}
					}
				});
			},{
				threshold: [0, 1]
			});

			return this.observer;
		}
	},
	computed:
	{
		State: () => State,
		seekPosition()
		{
			if (!this.loaded && !this.seek || this.isMobile)
			{
				return 'display: none'
			}

			return `left: ${this.seek}px;`;
		},
		progressPosition()
		{
			if (!this.loaded || this.state === State.none)
			{
				return `width: 100%;`;
			}

			return `width: ${this.progressInPixel}px;`;
		},
		labelTime()
		{
			if (!this.loaded && !this.timeTotal)
			{
				return '--:--';
			}

			let time;
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
		isMobile()
		{
			const UA = navigator.userAgent.toLowerCase();

			return (
				UA.includes('android')
				|| UA.includes('iphone')
				|| UA.includes('ipad')
				|| UA.includes('bitrixmobile')
			)
		},
	},
	template: `
		<div :class="['ui-vue-audioplayer-container', {
				'ui-vue-audioplayer-container-dark': isDark,
				'ui-vue-audioplayer-container-mobile': isMobile,
			}]" ref="body">
			<div class="ui-vue-audioplayer-controls-container">
				<button :class="['ui-vue-audioplayer-control', {
					'ui-vue-audioplayer-control-loader': loading,
					'ui-vue-audioplayer-control-play': !loading && state !== State.play,
					'ui-vue-audioplayer-control-pause': !loading && state === State.play,
				}]" @click="clickToButton"></button>
			</div>
			<div class="ui-vue-audioplayer-timeline-container">
				<div class="ui-vue-audioplayer-track-container" @click="setPosition" ref="track">
					<div class="ui-vue-audioplayer-track-mask"></div>
					<div class="ui-vue-audioplayer-track" :style="progressPosition"></div>
					<div class="ui-vue-audioplayer-track-seek" :style="seekPosition"></div>
					<div class="ui-vue-audioplayer-track-event" @mousemove="seeking"></div>
				</div>
				<div class="ui-vue-audioplayer-timers-container">
					<div class="ui-vue-audioplayer-time-current">{{labelTime}}</div>
				</div>
			</div>
			<audio v-if="src" :src="src" class="ui-vue-audioplayer-source" ref="source" :preload="preload"
				@abort="audioEventRouter('abort', $event)"
				@error="audioEventRouter('error', $event)"
				@suspend="audioEventRouter('suspend', $event)"
				@canplay="audioEventRouter('canplay', $event)"
				@canplaythrough="audioEventRouter('canplaythrough', $event)"
				@durationchange="audioEventRouter('durationchange', $event)"
				@loadeddata="audioEventRouter('loadeddata', $event)"
				@loadedmetadata="audioEventRouter('loadedmetadata', $event)"
				@timeupdate="audioEventRouter('timeupdate', $event)"
				@play="audioEventRouter('play', $event)"
				@playing="audioEventRouter('playing', $event)"
				@pause="audioEventRouter('pause', $event)"
			></audio>
		</div>
	`
});
