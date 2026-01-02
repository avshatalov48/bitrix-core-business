/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,ui_fonts_opensans,main_polyfill_intersectionobserver,main_core_events,im_v2_const,im_v2_lib_localStorage,im_v2_lib_utils,im_v2_component_elements_avatar,im_v2_lib_analytics) {
	'use strict';

	const ID_KEY = 'im:audioplayer:id';

	// @vue/component
	const AudioPlayer = {
	  name: 'AudioPlayer',
	  components: {
	    MessageAvatar: im_v2_component_elements_avatar.MessageAvatar
	  },
	  props: {
	    id: {
	      type: Number,
	      default: 0
	    },
	    src: {
	      type: String,
	      default: ''
	    },
	    file: {
	      type: Object,
	      required: true
	    },
	    authorId: {
	      type: Number,
	      required: true
	    },
	    messageId: {
	      type: [String, Number],
	      required: true
	    },
	    timelineType: {
	      type: Number,
	      required: true
	    },
	    withContextMenu: {
	      type: Boolean,
	      default: true
	    },
	    withAvatar: {
	      type: Boolean,
	      default: true
	    },
	    withPlaybackRateControl: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data() {
	    return {
	      preload: 'none',
	      loaded: false,
	      loading: false,
	      state: im_v2_const.AudioPlaybackState.none,
	      progress: 0,
	      progressInPixel: 0,
	      seek: 0,
	      timeCurrent: 0,
	      timeTotal: 0,
	      showContextButton: false,
	      currentRate: im_v2_const.AudioPlaybackRate['1']
	    };
	  },
	  computed: {
	    State: () => im_v2_const.AudioPlaybackState,
	    seekPosition() {
	      if (!this.loaded && !this.seek) {
	        return 'display: none';
	      }
	      return `left: ${this.seek}px;`;
	    },
	    isPlaying() {
	      return this.state === im_v2_const.AudioPlaybackState.play;
	    },
	    labelTime() {
	      if (!this.loaded && !this.timeTotal) {
	        return '--:--';
	      }
	      let time = 0;
	      if (this.isPlaying) {
	        time = this.timeTotal - this.timeCurrent;
	      } else {
	        time = this.timeTotal;
	      }
	      return im_v2_lib_utils.Utils.date.formatMediaDurationTime(time);
	    },
	    AvatarSize: () => im_v2_component_elements_avatar.AvatarSize,
	    fileSize() {
	      return im_v2_lib_utils.Utils.file.formatFileSize(this.file.size);
	    },
	    progressPosition() {
	      if (!this.loaded || this.state === im_v2_const.AudioPlaybackState.none) {
	        return {
	          width: '100%'
	        };
	      }
	      return {
	        width: `${this.progressInPixel}px`
	      };
	    },
	    activeTimelineStyles() {
	      const TIMELINE_VERTICAL_SHIFT = 44;
	      const ACTIVE_TIMELINE_VERTICAL_SHIFT = 19;
	      const shift = this.timelineType * TIMELINE_VERTICAL_SHIFT + ACTIVE_TIMELINE_VERTICAL_SHIFT;
	      return {
	        ...this.progressPosition,
	        'background-position-y': `-${shift}px`
	      };
	    },
	    timelineStyles() {
	      const TIMELINE_VERTICAL_SHIFT = 44;
	      const shift = this.timelineType * TIMELINE_VERTICAL_SHIFT;
	      return {
	        'background-position-y': `-${shift}px`
	      };
	    },
	    getAudioPlayerIds() {
	      return this.$Bitrix.Data.get(ID_KEY, []);
	    },
	    currentRateLabel() {
	      return this.isPlaying ? `${this.currentRate}x` : '';
	    },
	    metaInfo() {
	      return `${this.fileSize}, ${this.labelTime}`;
	    }
	  },
	  watch: {
	    id(value) {
	      this.registerPlayer(value);
	    },
	    progress(value) {
	      if (value > 70) {
	        this.preloadNext();
	      }
	    }
	  },
	  created() {
	    this.localStorageInst = im_v2_lib_localStorage.LocalStorageManager.getInstance();
	    this.currentRate = this.getRateFromLS();
	    this.preloadRequestSent = false;
	    this.registeredId = 0;
	    this.registerPlayer(this.id);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.audioPlayer.play, this.onPlay);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.audioPlayer.stop, this.onStop);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.audioPlayer.pause, this.onPause);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.audioPlayer.preload, this.onPreload);
	  },
	  mounted() {
	    this.getObserver().observe(this.$refs.body);
	  },
	  beforeUnmount() {
	    this.unregisterPlayer();
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.audioPlayer.play, this.onPlay);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.audioPlayer.stop, this.onStop);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.audioPlayer.pause, this.onPause);
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.audioPlayer.preload, this.onPreload);
	    this.getObserver().unobserve(this.$refs.body);
	  },
	  methods: {
	    loadFile(play = false) {
	      if (this.loaded || this.loading && !play) {
	        return;
	      }
	      this.preload = 'auto';
	      if (!play) {
	        return;
	      }
	      this.loading = true;
	      if (this.source()) {
	        void this.source().play();
	      }
	    },
	    clickToButton() {
	      if (!this.src) {
	        return;
	      }
	      if (this.isPlaying) {
	        this.pause();
	      } else {
	        this.play();
	      }
	    },
	    play() {
	      this.updateRate(this.getRateFromLS());
	      if (!this.loaded) {
	        this.loadFile(true);
	        return;
	      }
	      void this.source().play();
	    },
	    pause() {
	      this.source().pause();
	    },
	    stop() {
	      this.state = im_v2_const.AudioPlaybackState.stop;
	      this.source().pause();
	    },
	    setPosition() {
	      if (!this.loaded) {
	        this.loadFile(true);
	        return;
	      }
	      const pixelPerPercent = this.$refs.track.offsetWidth / 100;
	      this.setProgress(this.seek / pixelPerPercent, this.seek);
	      if (this.state !== im_v2_const.AudioPlaybackState.play) {
	        this.state = im_v2_const.AudioPlaybackState.pause;
	      }
	      this.play();
	      this.source().currentTime = this.timeTotal / 100 * this.progress;
	    },
	    getRateFromLS() {
	      return this.localStorageInst.get(im_v2_const.LocalStorageKey.audioPlaybackRate) || im_v2_const.AudioPlaybackRate['1'];
	    },
	    setRateInLS(newRate) {
	      this.localStorageInst.set(im_v2_const.LocalStorageKey.audioPlaybackRate, newRate);
	    },
	    getNextPlaybackRate(currentRate) {
	      const rates = Object.values(im_v2_const.AudioPlaybackRate).sort();
	      const currentIndex = rates.indexOf(currentRate);
	      const nextIndex = (currentIndex + 1) % rates.length;
	      return rates[nextIndex];
	    },
	    changeRate() {
	      if ([im_v2_const.AudioPlaybackState.pause, im_v2_const.AudioPlaybackState.none].includes(this.state)) {
	        return;
	      }
	      const commonCurrentRate = this.getRateFromLS();
	      const newRate = this.getNextPlaybackRate(commonCurrentRate);
	      im_v2_lib_analytics.Analytics.getInstance().audioMessage.onChangeRate(this.file.chatId, newRate);
	      this.setRateInLS(newRate);
	      this.updateRate(newRate);
	    },
	    updateRate(newRate) {
	      this.currentRate = newRate;
	      this.source().playbackRate = newRate;
	    },
	    seeking(event) {
	      if (!this.loaded) {
	        return;
	      }
	      this.seek = event.offsetX > 0 ? event.offsetX : 0;
	    },
	    setProgress(percent, pixel = -1) {
	      this.progress = percent;
	      this.progressInPixel = pixel > 0 ? pixel : Math.round(this.$refs.track.offsetWidth / 100 * percent);
	    },
	    registerPlayer(id) {
	      if (id <= 0) {
	        return;
	      }
	      this.unregisterPlayer();
	      const audioIdArray = [...new Set([...this.getAudioPlayerIds, id])];
	      this.$Bitrix.Data.set(ID_KEY, audioIdArray.sort((a, b) => a - b));
	      this.registeredId = id;
	    },
	    unregisterPlayer() {
	      if (!this.registeredId) {
	        return;
	      }
	      this.$Bitrix.Data.get(ID_KEY, this.getAudioPlayerIds.filter(id => id !== this.registeredId));
	      this.registeredId = 0;
	    },
	    playNext() {
	      if (!this.registeredId) {
	        return;
	      }
	      const nextId = this.getAudioPlayerIds.filter(id => id > this.registeredId).slice(0, 1)[0];
	      if (nextId) {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.audioPlayer.play, {
	          id: nextId,
	          start: true
	        });
	      }
	    },
	    preloadNext() {
	      if (this.preloadRequestSent || !this.registeredId) {
	        return;
	      }
	      this.preloadRequestSent = true;
	      const nextId = this.getAudioPlayerIds.filter(id => id > this.registeredId).slice(0, 1)[0];
	      if (nextId) {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.audioPlayer.preload, {
	          id: nextId
	        });
	      }
	    },
	    onPlay(event) {
	      const data = event.getData();
	      if (data.id !== this.id) {
	        return;
	      }
	      if (data.start) {
	        this.stop();
	      }
	      this.play();
	    },
	    onStop(event) {
	      const data = event.getData();
	      if (data.initiator === this.id) {
	        return;
	      }
	      this.stop();
	    },
	    onPause(event) {
	      const data = event.getData();
	      if (data.initiator === this.id) {
	        return;
	      }
	      this.pause();
	    },
	    onPreload(event) {
	      const data = event.getData();
	      if (data.id !== this.id) {
	        return;
	      }
	      this.loadFile();
	    },
	    source() {
	      return this.$refs.source;
	    },
	    audioEventRouter(eventName, event) {
	      // eslint-disable-next-line default-case
	      switch (eventName) {
	        case 'durationchange':
	        case 'loadeddata':
	        case 'loadedmetadata':
	          if (!this.source()) {
	            return;
	          }
	          this.timeTotal = this.source().duration;
	          break;
	        case 'abort':
	        case 'error':
	          console.error('BxAudioPlayer: load failed', this.id, event);
	          this.loading = false;
	          this.state = im_v2_const.AudioPlaybackState.none;
	          this.timeTotal = 0;
	          this.preload = 'none';
	          break;
	        case 'canplaythrough':
	          this.loading = false;
	          this.loaded = true;
	          break;
	        case 'timeupdate':
	          if (!this.source()) {
	            return;
	          }
	          this.timeCurrent = this.source().currentTime;
	          this.setProgress(Math.round(100 / this.timeTotal * this.timeCurrent));
	          if (this.isPlaying && this.timeCurrent >= this.timeTotal) {
	            this.playNext();
	          }
	          break;
	        case 'pause':
	          im_v2_lib_analytics.Analytics.getInstance().audioMessage.onPause(this.file.chatId);
	          if (this.state !== im_v2_const.AudioPlaybackState.stop) {
	            this.state = im_v2_const.AudioPlaybackState.pause;
	          }
	          break;
	        case 'play':
	          im_v2_lib_analytics.Analytics.getInstance().audioMessage.onPlay(this.file.chatId);
	          this.state = im_v2_const.AudioPlaybackState.play;
	          if (this.state === im_v2_const.AudioPlaybackState.stop) {
	            this.progress = 0;
	            this.timeCurrent = 0;
	          }
	          if (this.id > 0) {
	            main_core_events.EventEmitter.emit(im_v2_const.EventType.audioPlayer.pause, {
	              initiator: this.id
	            });
	          }
	          break;
	        // No default
	      }
	    },

	    getObserver() {
	      if (this.observer) {
	        return this.observer;
	      }
	      this.observer = new IntersectionObserver(entries => {
	        entries.forEach(entry => {
	          if (entry.isIntersecting && this.preload === 'none') {
	            this.preload = 'metadata';
	            this.observer.unobserve(entry.target);
	          }
	        });
	      }, {
	        threshold: [0, 1]
	      });
	      return this.observer;
	    }
	  },
	  template: `
		<div 
			class="bx-im-audio-player__container bx-im-audio-player__scope" 
			ref="body"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"
		>
			<div class="bx-im-audio-player__control-container">
				<button
					class="bx-im-audio-player__control-button"
					:class="{
						'bx-im-audio-player__control-loader': loading,
						'bx-im-audio-player__control-play': !loading && !this.isPlaying,
						'bx-im-audio-player__control-pause': !loading && this.isPlaying,
					}"
					@click="clickToButton"
				></button>
				<div v-if="withAvatar" class="bx-im-audio-player__author-avatar-container">
					<MessageAvatar
						:messageId="messageId"
						:authorId="authorId"
						:size="AvatarSize.XS" 
					/>
				</div>
			</div>
			<div class="bx-im-audio-player__timeline-container">
				<div class="bx-im-audio-player__track-container" @click="setPosition" ref="track">
					<div class="bx-im-audio-player__track-mask" :style="timelineStyles"></div>
					<div class="bx-im-audio-player__track-mask --active" :style="activeTimelineStyles"></div>
					<div class="bx-im-audio-player__track-seek" :style="seekPosition"></div>
					<div class="bx-im-audio-player__track-event" @mousemove="seeking"></div>
				</div>
				<div class="bx-im-audio-player__timer-container --ellipsis">
					{{ metaInfo }}
				</div>
			</div>
			<slot name="transcription-control"></slot>
			<div
				v-if="!withPlaybackRateControl"
				class="bx-im-audio-player__rate-button-container"
			>
				<button
					:class="{'--active': isPlaying}"
					@click="changeRate"
				>
					<span :class="{'bx-im-audio-player__rate-icon': !isPlaying}">
						{{ currentRateLabel }}
					</span>
				</button>
			</div>
			<button
				v-if="showContextButton && withContextMenu"
				class="bx-im-messenger__context-menu-icon bx-im-audio-player__context-menu-button"
				@click="$emit('contextMenuClick', $event)"
			></button>
			<audio 
				v-if="src" 
				:src="src" 
				class="bx-im-audio-player__audio-source" 
				ref="source" 
				:preload="preload"
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
	};

	exports.AudioPlayer = AudioPlayer;

}((this.BX.Messenger.v2.Component.Elements = this.BX.Messenger.v2.Component.Elements || {}),BX,BX,BX.Event,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Lib));
//# sourceMappingURL=audioplayer.bundle.js.map
