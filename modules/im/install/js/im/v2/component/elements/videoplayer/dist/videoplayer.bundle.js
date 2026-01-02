/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,ui_fonts_opensans,main_polyfill_intersectionobserver,ui_vue3_directives_lazyload,im_v2_component_animation,im_v2_lib_utils) {
	'use strict';

	const State = Object.freeze({
	  play: 'play',
	  pause: 'pause',
	  stop: 'stop',
	  none: 'none'
	});
	const PreloadAttribute = Object.freeze({
	  none: 'none',
	  metadata: 'metadata',
	  auto: 'auto'
	});

	// @vue/component
	const VideoPlayer = {
	  name: 'VideoPlayer',
	  directives: {
	    lazyload: ui_vue3_directives_lazyload.lazyload
	  },
	  components: {
	    FadeAnimation: im_v2_component_animation.FadeAnimation
	  },
	  props: {
	    fileId: {
	      type: [Number, String],
	      default: 0
	    },
	    src: {
	      type: String,
	      required: true
	    },
	    previewImageUrl: {
	      type: String,
	      default: ''
	    },
	    withAutoplay: {
	      type: Boolean,
	      default: true
	    },
	    elementStyle: {
	      type: Object,
	      default: null
	    },
	    withPlayerControls: {
	      type: Boolean,
	      default: true
	    },
	    viewerAttributes: {
	      type: Object,
	      default: () => {}
	    }
	  },
	  data() {
	    return {
	      preloadAttribute: PreloadAttribute.none,
	      loaded: false,
	      loading: false,
	      state: State.none,
	      timeCurrent: 0,
	      timeTotal: 0,
	      isMuted: true
	    };
	  },
	  computed: {
	    State: () => State,
	    isAutoPlayDisabled() {
	      return !this.withAutoplay && this.state === State.none;
	    },
	    showStartButton() {
	      return this.withPlayerControls && this.isAutoPlayDisabled;
	    },
	    showInterface() {
	      return this.withPlayerControls && !this.showStartButton;
	    },
	    formattedTime() {
	      if (!this.loaded && !this.timeTotal) {
	        return '--:--';
	      }
	      let time = 0;
	      if (this.state === State.play) {
	        time = this.timeTotal - this.timeCurrent;
	      } else {
	        time = this.timeTotal;
	      }
	      return im_v2_lib_utils.Utils.date.formatMediaDurationTime(time);
	    },
	    controlButtonClass() {
	      if (this.loading) {
	        return '--loading';
	      }
	      return this.state === State.play ? '--pause' : '--play';
	    },
	    source() {
	      return this.$refs.source;
	    },
	    hasViewerAttributes() {
	      return Object.keys(this.viewerAttributes).length > 0;
	    }
	  },
	  created() {
	    if (!this.previewImageUrl) {
	      this.preloadAttribute = PreloadAttribute.metadata;
	    }
	  },
	  mounted() {
	    this.getObserver().observe(this.$refs.body);
	  },
	  beforeUnmount() {
	    this.getObserver().unobserve(this.$refs.body);
	  },
	  methods: {
	    loadFile() {
	      if (this.loaded || this.loading) {
	        return;
	      }
	      this.preloadAttribute = PreloadAttribute.auto;
	      this.loading = true;
	      this.playAfterLoad = true;
	    },
	    handleControlClick() {
	      if (this.state === State.play) {
	        this.getObserver().unobserve(this.$refs.body);
	        this.pause();
	      } else {
	        this.play();
	      }
	    },
	    handleMuteClick() {
	      if (this.isMuted) {
	        this.unmute();
	      } else {
	        this.mute();
	      }
	    },
	    handleContainerClick() {
	      if (this.hasViewerAttributes) {
	        this.pause();
	        return;
	      }
	      this.handleControlClick();
	    },
	    play() {
	      if (!this.loaded) {
	        this.loadFile();
	        return;
	      }
	      void this.source.play();
	    },
	    pause() {
	      this.source.pause();
	    },
	    mute() {
	      this.isMuted = true;
	      this.source.muted = true;
	    },
	    unmute() {
	      this.isMuted = false;
	      this.source.muted = false;
	    },
	    handleError(event) {
	      console.error('Im.VideoPlayer: loading failed', this.fileId, event);
	      this.loading = false;
	      this.state = State.none;
	      this.timeTotal = 0;
	      this.preloadAttribute = PreloadAttribute.none;
	    },
	    handleAbort(event) {
	      this.handleError(event);
	    },
	    handlePlay() {
	      this.state = State.play;
	    },
	    handleLoadedData() {
	      this.timeTotal = this.source.duration;
	    },
	    handleDurationChange() {
	      this.handleLoadedData();
	    },
	    handleLoadedMetaData() {
	      this.timeTotal = this.source.duration;
	      this.loaded = true;
	      this.play();
	    },
	    handleCanPlayThrough() {
	      this.loading = false;
	      this.loaded = true;
	      this.play();
	    },
	    handlePause() {
	      if (this.state === State.stop) {
	        return;
	      }
	      this.state = State.pause;
	    },
	    handleVolumeChange() {
	      if (this.source.muted) {
	        this.mute();
	      } else {
	        this.unmute();
	      }
	    },
	    handleTimeUpdate() {
	      this.timeCurrent = this.source.currentTime;
	    },
	    getObserver() {
	      if (this.observer) {
	        return this.observer;
	      }
	      this.observer = new IntersectionObserver(entries => {
	        if (this.isAutoPlayDisabled) {
	          return;
	        }
	        entries.forEach(entry => {
	          if (entry.isIntersecting) {
	            this.play();
	          } else {
	            this.pause();
	          }
	        });
	      }, {
	        threshold: [0, 1]
	      });
	      return this.observer;
	    }
	  },
	  template: `
		<div class="bx-im-video-player__container" @click.stop="handleContainerClick">
			<FadeAnimation :duration="500">
				<div v-if="showStartButton" class="bx-im-video-player__start-play_button" @click.stop="handleControlClick">
					<span class="bx-im-video-player__start-play_icon"></span>
				</div>
			</FadeAnimation>
			<FadeAnimation :duration="500">
				<div v-if="showInterface" class="bx-im-video-player__control-button_container" @click.stop="handleControlClick">
					<button class="bx-im-video-player__control-button" :class="controlButtonClass"></button>
				</div>
			</FadeAnimation>
			<FadeAnimation :duration="500">
				<div 
					v-if="showInterface" 
					class="bx-im-video-player__info-container" 
					@click.stop="handleMuteClick"
				>
					<span class="bx-im-video-player__time">{{ formattedTime }}</span>
					<span class="bx-im-video-player__sound" :class="{'--muted': isMuted}"></span>
				</div>
			</FadeAnimation>
			<div class="bx-im-video-player__video-container" ref="body">
				<video
					v-bind="viewerAttributes"
					:src="src"
					class="bx-im-video-player__video"
					:poster="previewImageUrl"
					ref="source"
					:preload="preloadAttribute"
					playsinline
					loop
					muted
					:style="elementStyle"
					@abort="handleAbort"
					@error="handleError"
					@canplaythrough="handleCanPlayThrough"
					@durationchange="handleDurationChange"
					@loadeddata="handleLoadedData"
					@loadedmetadata="handleLoadedMetaData"
					@volumechange="handleVolumeChange"
					@timeupdate="handleTimeUpdate"
					@play="handlePlay"
					@pause="handlePause"
				></video>
			</div>
		</div>
	`
	};

	exports.VideoPlayer = VideoPlayer;

}((this.BX.Messenger.v2.Component.Elements = this.BX.Messenger.v2.Component.Elements || {}),BX,BX,BX.Vue3.Directives,BX.Messenger.v2.Component.Animation,BX.Messenger.v2.Lib));
//# sourceMappingURL=videoplayer.bundle.js.map
