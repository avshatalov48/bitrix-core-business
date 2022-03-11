(function (exports,ui_vue_directives_lazyload,main_polyfill_intersectionobserver,ui_vue,main_core_events) {
	'use strict';

	/**
	 * Bitrix UI
	 * Social Video Vue component
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2021 Bitrix
	 */

	var _State = Object.freeze({
	  play: 'play',
	  pause: 'pause',
	  stop: 'stop',
	  none: 'none'
	});

	ui_vue.BitrixVue.component('bx-socialvideo', {
	  props: {
	    id: {
	      "default": 0
	    },
	    src: {
	      "default": ''
	    },
	    preview: {
	      "default": ''
	    },
	    autoplay: {
	      "default": true
	    },
	    containerClass: {
	      "default": null
	    },
	    containerStyle: {
	      "default": null
	    },
	    elementStyle: {
	      "default": null
	    },
	    showControls: {
	      "default": true
	    }
	  },
	  data: function data() {
	    return {
	      preload: "none",
	      previewLoaded: false,
	      loaded: false,
	      loading: false,
	      playAfterLoad: false,
	      enterFullscreen: false,
	      playBeforeMute: 2,
	      state: _State.none,
	      progress: 0,
	      timeCurrent: 0,
	      timeTotal: 0,
	      muteFlag: true
	    };
	  },
	  created: function created() {
	    if (!this.preview) {
	      this.previewLoaded = true;
	      this.preload = 'metadata';
	    }

	    this.$Bitrix.eventEmitter.subscribe('ui:socialvideo:unmute', this.onUnmute);
	  },
	  mounted: function mounted() {
	    this.getObserver().observe(this.$refs.body);
	  },
	  beforeDestroy: function beforeDestroy() {
	    this.$Bitrix.eventEmitter.unsubscribe('ui:socialvideo:unmute', this.onUnmute);
	    this.getObserver().unobserve(this.$refs.body);
	  },
	  watch: {
	    id: function id(value) {
	      this.registeredId = value;
	    }
	  },
	  methods: {
	    loadFile: function loadFile() {
	      var play = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

	      if (this.loaded) {
	        return true;
	      }

	      if (this.loading) {
	        return true;
	      }

	      this.preload = 'auto';
	      this.loading = true;
	      this.playAfterLoad = play;
	      return true;
	    },
	    clickToButton: function clickToButton(event) {
	      if (!this.src) {
	        return false;
	      }

	      if (this.state === _State.play) {
	        this.getObserver().unobserve(this.$refs.body);
	        this.pause();
	      } else {
	        this.play();
	      }

	      event.stopPropagation();
	    },
	    clickToMute: function clickToMute() {
	      if (!this.src) {
	        return false;
	      }

	      if (!this.muteFlag) {
	        this.mute();
	      } else {
	        this.unmute();
	      }

	      event.stopPropagation();
	    },
	    click: function click(event) {
	      if (this.autoPlayDisabled) {
	        this.play();
	        event.stopPropagation();
	        return false;
	      }

	      if (this.isMobile) {
	        if (this.source().webkitEnterFullscreen) {
	          this.unmute();
	          this.enterFullscreen = true;
	          this.source().webkitEnterFullscreen();
	        } else if (this.source().requestFullscreen) {
	          this.unmute();
	          this.enterFullscreen = true;
	          this.source().requestFullscreen();
	        } else {
	          this.$emit('click', event);
	        }
	      } else {
	        this.$emit('click', event);
	      }

	      event.stopPropagation();
	    },
	    play: function play(event) {
	      if (!this.loaded) {
	        this.loadFile(true);
	        return false;
	      }

	      if (!this.source()) {
	        return false;
	      }

	      this.source().play();
	    },
	    pause: function pause() {
	      if (!this.source()) {
	        return false;
	      }

	      this.playAfterLoad = false;
	      this.source().pause();
	    },
	    stop: function stop() {
	      if (!this.source()) {
	        return false;
	      }

	      this.state = _State.stop;
	      this.source().pause();
	    },
	    mute: function mute() {
	      if (!this.source()) {
	        return false;
	      }

	      this.muteFlag = true;
	      this.playBeforeMute = 2;
	      this.source().muted = true;
	    },
	    unmute: function unmute() {
	      if (!this.source()) {
	        return false;
	      }

	      this.muteFlag = false;
	      this.source().muted = false;

	      if (this.id > 0) {
	        this.$Bitrix.eventEmitter.emit('ui:socialvideo:unmute', {
	          initiator: this.id
	        });
	      }
	    },
	    setProgress: function setProgress(percent) {
	      this.progress = percent;
	    },
	    formatTime: function formatTime(second) {
	      second = Math.floor(second);
	      var hour = Math.floor(second / 60 / 60);

	      if (hour > 0) {
	        second -= hour * 60 * 60;
	      }

	      var minute = Math.floor(second / 60);

	      if (minute > 0) {
	        second -= minute * 60;
	      }

	      return (hour > 0 ? hour + ':' : '') + (hour > 0 ? minute.toString().padStart(2, "0") + ':' : minute + ':') + second.toString().padStart(2, "0");
	    },
	    onUnmute: function onUnmute(event) {
	      event = event.getData();

	      if (event.initiator === this.id) {
	        return false;
	      }

	      this.mute();
	    },
	    source: function source() {
	      return this.$refs.source;
	    },
	    videoEventRouter: function videoEventRouter(eventName, event) {
	      if (eventName === 'durationchange' || eventName === 'loadeddata') {
	        if (!this.source()) {
	          return false;
	        }

	        this.timeTotal = this.source().duration;
	      } else if (eventName === 'loadedmetadata') {
	        if (!this.source()) {
	          return false;
	        }

	        this.timeTotal = this.source().duration;
	        this.loaded = true;

	        if (this.playAfterLoad) {
	          this.play();
	        }
	      } else if (eventName === 'abort' || eventName === 'error') {
	        console.error('BxSocialVideo: load failed', this.id, event);
	        this.loading = false;
	        this.state = _State.none;
	        this.timeTotal = 0;
	        this.preload = 'none';
	      } else if (eventName === 'canplaythrough') {
	        this.loading = false;
	        this.loaded = true;

	        if (this.playAfterLoad) {
	          this.play();
	        }
	      } else if (eventName === 'volumechange') {
	        if (!this.source()) {
	          return false;
	        }

	        if (this.source().muted) {
	          this.mute();
	        } else {
	          this.unmute();
	        }
	      } else if (eventName === 'timeupdate') {
	        if (!this.source()) {
	          return false;
	        }

	        this.timeCurrent = this.source().currentTime;

	        if (!this.muteFlag && !this.enterFullscreen && this.timeCurrent === 0) {
	          if (this.playBeforeMute <= 0) {
	            this.mute();
	          }

	          this.playBeforeMute -= 1;
	        }

	        this.setProgress(Math.round(100 / this.timeTotal * this.timeCurrent));
	      } else if (eventName === 'pause') {
	        if (this.state !== _State.stop) {
	          this.state = _State.pause;
	        }

	        if (this.enterFullscreen) {
	          this.enterFullscreen = false;
	          this.mute();
	          this.play();
	        }
	      } else if (eventName === 'play') {
	        this.state = _State.play;

	        if (this.state === _State.stop) {
	          this.progress = 0;
	          this.timeCurrent = 0;
	        }

	        if (this.enterFullscreen) {
	          this.enterFullscreen = false;
	        }
	      }
	    },
	    getObserver: function getObserver() {
	      var _this = this;

	      if (this.observer) {
	        return this.observer;
	      }

	      this.observer = new IntersectionObserver(function (entries, observer) {
	        if (_this.autoPlayDisabled) {
	          return false;
	        }

	        entries.forEach(function (entry) {
	          if (entry.isIntersecting) {
	            _this.play();
	          } else {
	            _this.pause();
	          }
	        });
	      }, {
	        threshold: [0, 1]
	      });
	      return this.observer;
	    },
	    lazyLoadCallback: function lazyLoadCallback(element) {
	      this.previewLoaded = element.state === 'success';
	    }
	  },
	  computed: {
	    State: function State() {
	      return _State;
	    },
	    autoPlayDisabled: function autoPlayDisabled() {
	      return !this.autoplay && this.state === _State.none;
	    },
	    showStartButton: function showStartButton() {
	      return this.autoPlayDisabled && this.previewLoaded;
	    },
	    showInterface: function showInterface() {
	      return this.previewLoaded && !this.showStartButton;
	    },
	    labelTime: function labelTime() {
	      if (!this.loaded && !this.timeTotal) {
	        return '--:--';
	      }

	      var time;

	      if (this.state === _State.play) {
	        time = this.timeTotal - this.timeCurrent;
	      } else {
	        time = this.timeTotal;
	      }

	      return this.formatTime(time);
	    },
	    isMobile: function isMobile() {
	      var UA = navigator.userAgent.toLowerCase();
	      return UA.includes('android') || UA.includes('iphone') || UA.includes('ipad') || UA.includes('bitrixmobile');
	    }
	  },
	  template: "\n\t\t<div :class=\"['ui-vue-socialvideo', containerClass, {\n\t\t\t\t'ui-vue-socialvideo-mobile': isMobile,\n\t\t\t}]\" :style=\"containerStyle\" @click=\"click\">\n\t\t\t<transition name=\"ui-vue-socialvideo-animation-fade\">\n\t\t\t\t<div v-if=\"showStartButton && showControls\" class=\"ui-vue-socialvideo-button-start\">\n\t\t\t\t\t<span class=\"ui-vue-socialvideo-button-start-icon\"></span>\n\t\t\t\t</div>\n\t\t\t</transition>\n\t\t\t<transition name=\"ui-vue-socialvideo-animation-fade\">\n\t\t\t\t<div v-if=\"showInterface && showControls\" class=\"ui-vue-socialvideo-overlay-container\">\n\t\t\t\t\t<div class=\"ui-vue-socialvideo-controls-container\" @click=\"clickToButton\">\n\t\t\t\t\t\t<button :class=\"['ui-vue-socialvideo-control', {\n\t\t\t\t\t\t\t'ui-vue-socialvideo-control-loader': loading,\n\t\t\t\t\t\t\t'ui-vue-socialvideo-control-play': !loading && state !== State.play,\n\t\t\t\t\t\t\t'ui-vue-socialvideo-control-pause': !loading && state === State.play,\n\t\t\t\t\t\t}]\"></button>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"ui-vue-socialvideo-info-container\" @click=\"clickToMute\">\n\t\t\t\t\t\t<span class=\"ui-vue-socialvideo-time-current\">{{labelTime}}</span>\n\t\t\t\t\t\t<span :class=\"['ui-vue-socialvideo-sound', {\n\t\t\t\t\t\t\t'ui-vue-socialvideo-sound-on': state !== State.none && !muteFlag,\n\t\t\t\t\t\t\t'ui-vue-socialvideo-sound-off': state !== State.none && muteFlag\n\t\t\t\t\t\t}]\"></span>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</transition>\n\t\t\t<div v-if=\"!preview\" class=\"ui-vue-socialvideo-background\" :style=\"{position: (src? 'absolute': 'relative')}\"></div>\n\t\t\t<div class=\"ui-vue-socialvideo-container\" ref=\"body\">\n\t\t\t\t<img \n\t\t\t\t\tv-bx-lazyload=\"{callback: lazyLoadCallback}\"\n\t\t\t\t\tdata-lazyload-dont-hide\n\t\t\t\t\tv-if=\"preview\"\n\t\t\t\t\tclass=\"ui-vue-socialvideo-image-source\"\n\t\t\t\t\t:data-lazyload-src=\"preview\"\n\t\t\t\t\t:style=\"{position: (src? 'absolute': 'relative'), ...elementStyle}\"\n\t\t\t\t/>\n\t\t\t\t<video \n\t\t\t\t\tv-if=\"src\" :src=\"src\" \n\t\t\t\t\tclass=\"ui-vue-socialvideo-source\" \n\t\t\t\t\tref=\"source\"\n\t\t\t\t\t:preload=\"preload\" \n\t\t\t\t\tplaysinline\n\t\t\t\t\tloop \n\t\t\t\t\tmuted\n\t\t\t\t\t:style=\"{opacity: (loaded? 1: 0), ...elementStyle}\"\n\t\t\t\t\t@abort=\"videoEventRouter('abort', $event)\"\n\t\t\t\t\t@error=\"videoEventRouter('error', $event)\"\n\t\t\t\t\t@suspend=\"videoEventRouter('suspend', $event)\"\n\t\t\t\t\t@canplay=\"videoEventRouter('canplay', $event)\"\n\t\t\t\t\t@canplaythrough=\"videoEventRouter('canplaythrough', $event)\"\n\t\t\t\t\t@durationchange=\"videoEventRouter('durationchange', $event)\"\n\t\t\t\t\t@loadeddata=\"videoEventRouter('loadeddata', $event)\"\n\t\t\t\t\t@loadedmetadata=\"videoEventRouter('loadedmetadata', $event)\"\n\t\t\t\t\t@volumechange=\"videoEventRouter('volumechange', $event)\"\n\t\t\t\t\t@timeupdate=\"videoEventRouter('timeupdate', $event)\"\n\t\t\t\t\t@play=\"videoEventRouter('play', $event)\"\n\t\t\t\t\t@playing=\"videoEventRouter('playing', $event)\"\n\t\t\t\t\t@pause=\"videoEventRouter('pause', $event)\"\n\t\t\t\t></video>\n\t\t\t</div>\n\t\t</div>\t\n\t"
	});

}((this.window = this.window || {}),window,BX,BX,BX.Event));
//# sourceMappingURL=socialvideo.bundle.js.map
