(function (exports,main_polyfill_intersectionobserver,ui_vue) {
	'use strict';

	/**
	 * Bitrix UI
	 * Audio player Vue component
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2019 Bitrix
	 */

	var _State = Object.freeze({
	  play: 'play',
	  pause: 'pause',
	  stop: 'stop',
	  none: 'none'
	});

	ui_vue.Vue.component('bx-audioplayer', {
	  props: {
	    id: {
	      default: 0
	    },
	    src: {
	      default: ''
	    },
	    autoPlayNext: {
	      default: true
	    },
	    background: {
	      default: 'light'
	    }
	  },
	  data: function data() {
	    return {
	      isDark: false,
	      preload: "none",
	      loaded: false,
	      loading: false,
	      playAfterLoad: false,
	      state: _State.none,
	      progress: 0,
	      progressInPixel: 0,
	      seek: 0,
	      timeCurrent: 0,
	      timeTotal: 0
	    };
	  },
	  created: function created() {
	    this.preloadRequestSent = false;
	    this.registeredId = 0;
	    this.registerPlayer(this.id);
	    this.$root.$on('ui:audioplayer:play', this.onPlay);
	    ui_vue.Vue.event.$on('ui:audioplayer:play', this.onPlay);
	    this.$root.$on('ui:audioplayer:stop', this.onStop);
	    ui_vue.Vue.event.$on('ui:audioplayer:stop', this.onStop);
	    this.$root.$on('ui:audioplayer:pause', this.onPause);
	    ui_vue.Vue.event.$on('ui:audioplayer:pause', this.onPause);
	    this.$root.$on('ui:audioplayer:preload', this.onPreload);
	    this.isDark = this.background === 'dark';
	  },
	  mounted: function mounted() {
	    this.getObserver().observe(this.$refs.body);
	  },
	  beforeDestroy: function beforeDestroy() {
	    this.unregisterPlayer();
	    this.$root.$off('ui:audioplayer:play', this.onPlay);
	    ui_vue.Vue.event.$off('ui:audioplayer:play', this.onPlay);
	    this.$root.$off('ui:audioplayer:stop', this.onStop);
	    ui_vue.Vue.event.$off('ui:audioplayer:stop', this.onStop);
	    this.$root.$off('ui:audioplayer:pause', this.onPause);
	    ui_vue.Vue.event.$off('ui:audioplayer:pause', this.onPause);
	    this.$root.$off('ui:audioplayer:preload', this.onPreload);
	    this.getObserver().unobserve(this.$refs.body);
	  },
	  watch: {
	    id: function id(value) {
	      this.registerPlayer(value);
	    },
	    progress: function progress(value) {
	      if (value > 70) {
	        this.preloadNext();
	      }
	    }
	  },
	  methods: {
	    loadFile: function loadFile() {
	      var play = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;

	      if (this.loaded) {
	        return true;
	      }

	      if (this.loading && !play) {
	        return true;
	      }

	      this.preload = 'auto';

	      if (play) {
	        this.loading = true;

	        if (this.source()) {
	          this.source().play();
	        }
	      }

	      return true;
	    },
	    clickToButton: function clickToButton() {
	      if (!this.src) {
	        return false;
	      }

	      if (this.state === _State.play) {
	        this.pause();
	      } else {
	        this.play();
	      }
	    },
	    play: function play() {
	      if (!this.loaded) {
	        this.loadFile(true);
	        return false;
	      }

	      this.source().play();
	    },
	    pause: function pause() {
	      this.source().pause();
	    },
	    stop: function stop() {
	      this.state = _State.stop;
	      this.source().pause();
	    },
	    setPosition: function setPosition(event) {
	      if (!this.loaded) {
	        this.loadFile(true);
	        return false;
	      }

	      var pixelPerPercent = this.$refs.track.offsetWidth / 100;
	      this.setProgress(this.seek / pixelPerPercent, this.seek);

	      if (this.state !== _State.play) {
	        this.state = _State.pause;
	      }

	      this.play();
	      this.source().currentTime = this.timeTotal / 100 * this.progress;
	    },
	    seeking: function seeking(event) {
	      if (!this.loaded) {
	        return false;
	      }

	      this.seek = event.offsetX > 0 ? event.offsetX : 0;
	      return true;
	    },
	    setProgress: function setProgress(percent) {
	      var pixel = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : -1;
	      this.progress = percent;
	      this.progressInPixel = pixel > 0 ? pixel : Math.round(this.$refs.track.offsetWidth / 100 * percent);
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
	    registerPlayer: function registerPlayer(id) {
	      if (id <= 0) {
	        return false;
	      }

	      if (typeof this.$root.$uiAudioPlayerId === 'undefined') {
	        this.$root.$uiAudioPlayerId = [];
	      }

	      this.unregisterPlayer();
	      this.$root.$uiAudioPlayerId = babelHelpers.toConsumableArray(new Set([].concat(babelHelpers.toConsumableArray(this.$root.$uiAudioPlayerId), [id]))).sort(function (a, b) {
	        if (a > b) return 1;else if (a < b) return -1;else return 0;
	      });
	      this.registeredId = id;
	      return true;
	    },
	    unregisterPlayer: function unregisterPlayer() {
	      var _this = this;

	      if (!this.registeredId) {
	        return true;
	      }

	      this.$root.$uiAudioPlayerId = this.$root.$uiAudioPlayerId.filter(function (id) {
	        return id !== _this.registeredId;
	      });
	      this.registeredId = 0;
	      return true;
	    },
	    playNext: function playNext() {
	      var _this2 = this;

	      if (!this.registeredId || !this.autoPlayNext) {
	        return false;
	      }

	      var nextId = this.$root.$uiAudioPlayerId.filter(function (id) {
	        return id > _this2.registeredId;
	      }).slice(0, 1)[0];

	      if (nextId) {
	        this.$root.$emit('ui:audioplayer:play', {
	          id: nextId,
	          start: true
	        });
	      }

	      return true;
	    },
	    preloadNext: function preloadNext() {
	      var _this3 = this;

	      if (this.preloadRequestSent) {
	        return true;
	      }

	      if (!this.registeredId || !this.autoPlayNext) {
	        return false;
	      }

	      this.preloadRequestSent = true;
	      var nextId = this.$root.$uiAudioPlayerId.filter(function (id) {
	        return id > _this3.registeredId;
	      }).slice(0, 1)[0];

	      if (nextId) {
	        this.$root.$emit('ui:audioplayer:preload', {
	          id: nextId
	        });
	      }

	      return true;
	    },
	    onPlay: function onPlay(event) {
	      if (event.id !== this.id) {
	        return false;
	      }

	      if (event.start) {
	        this.stop();
	      }

	      this.play();
	    },
	    onStop: function onStop(event) {
	      if (event.initiator === this.id) {
	        return false;
	      }

	      this.stop();
	    },
	    onPause: function onPause(event) {
	      if (event.initiator === this.id) {
	        return false;
	      }

	      this.pause();
	    },
	    onPreload: function onPreload(event) {
	      if (event.id !== this.id) {
	        return false;
	      }

	      this.loadFile();
	    },
	    source: function source() {
	      return this.$refs.source;
	    },
	    audioEventRouter: function audioEventRouter(eventName, event) {
	      if (eventName === 'durationchange' || eventName === 'loadeddata' || eventName === 'loadedmetadata') {
	        this.timeTotal = this.source().duration;
	      } else if (eventName === 'abort' || eventName === 'error') {
	        console.error('BxAudioPlayer: load failed', this.id, event);
	        this.loading = false;
	        this.state = _State.none;
	        this.timeTotal = 0;
	        this.preload = 'none';
	      } else if (eventName === 'canplaythrough') {
	        this.loading = false;
	        this.loaded = true;
	      } else if (eventName === 'timeupdate') {
	        if (!this.source()) {
	          return;
	        }

	        this.timeCurrent = this.source().currentTime;
	        this.setProgress(Math.round(100 / this.timeTotal * this.timeCurrent));

	        if (this.state === _State.play && this.timeCurrent >= this.timeTotal) {
	          this.playNext();
	        }
	      } else if (eventName === 'pause') {
	        if (this.state !== _State.stop) {
	          this.state = _State.pause;
	        }
	      } else if (eventName === 'play') {
	        this.state = _State.play;

	        if (this.state === _State.stop) {
	          this.progress = 0;
	          this.timeCurrent = 0;
	        }

	        if (this.id > 0) {
	          this.$root.$emit('ui:audioplayer:pause', {
	            initiator: this.id
	          });
	          ui_vue.Vue.event.$emit('ui:audioplayer:pause', {
	            initiator: this.id
	          });
	        }
	      }
	    },
	    getObserver: function getObserver() {
	      var _this4 = this;

	      if (this.observer) {
	        return this.observer;
	      }

	      this.observer = new IntersectionObserver(function (entries, observer) {
	        entries.forEach(function (entry) {
	          if (entry.isIntersecting) {
	            if (_this4.preload === "none") {
	              _this4.preload = "metadata";

	              _this4.observer.unobserve(entry.target);
	            }
	          }
	        });
	      }, {
	        threshold: [0, 1]
	      });
	      return this.observer;
	    }
	  },
	  computed: {
	    State: function State() {
	      return _State;
	    },
	    seekPosition: function seekPosition() {
	      if (!this.loaded && !this.seek || this.isMobile) {
	        return 'display: none';
	      }

	      return "left: ".concat(this.seek, "px;");
	    },
	    progressPosition: function progressPosition() {
	      if (!this.loaded || this.state === _State.none) {
	        return "width: 100%;";
	      }

	      return "width: ".concat(this.progressInPixel, "px;");
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
	  template: "\n\t\t<div :class=\"['ui-vue-audioplayer-container', {\n\t\t\t\t'ui-vue-audioplayer-container-dark': isDark,\n\t\t\t\t'ui-vue-audioplayer-container-mobile': isMobile,\n\t\t\t}]\" ref=\"body\">\n\t\t\t<div class=\"ui-vue-audioplayer-controls-container\">\n\t\t\t\t<button :class=\"['ui-vue-audioplayer-control', {\n\t\t\t\t\t'ui-vue-audioplayer-control-loader': loading,\n\t\t\t\t\t'ui-vue-audioplayer-control-play': !loading && state !== State.play,\n\t\t\t\t\t'ui-vue-audioplayer-control-pause': !loading && state === State.play,\n\t\t\t\t}]\" @click=\"clickToButton\"></button>\n\t\t\t</div>\n\t\t\t<div class=\"ui-vue-audioplayer-timeline-container\">\n\t\t\t\t<div class=\"ui-vue-audioplayer-track-container\" @click=\"setPosition\" ref=\"track\">\n\t\t\t\t\t<div class=\"ui-vue-audioplayer-track-mask\"></div>\n\t\t\t\t\t<div class=\"ui-vue-audioplayer-track\" :style=\"progressPosition\"></div>\n\t\t\t\t\t<div class=\"ui-vue-audioplayer-track-seek\" :style=\"seekPosition\"></div>\n\t\t\t\t\t<div class=\"ui-vue-audioplayer-track-event\" @mousemove=\"seeking\"></div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"ui-vue-audioplayer-timers-container\">\n\t\t\t\t\t<div class=\"ui-vue-audioplayer-time-current\">{{labelTime}}</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<audio v-if=\"src\" :src=\"src\" class=\"ui-vue-audioplayer-source\" ref=\"source\" :preload=\"preload\"\n\t\t\t\t@abort=\"audioEventRouter('abort', $event)\"\n\t\t\t\t@error=\"audioEventRouter('error', $event)\"\n\t\t\t\t@suspend=\"audioEventRouter('suspend', $event)\"\n\t\t\t\t@canplay=\"audioEventRouter('canplay', $event)\"\n\t\t\t\t@canplaythrough=\"audioEventRouter('canplaythrough', $event)\"\n\t\t\t\t@durationchange=\"audioEventRouter('durationchange', $event)\"\n\t\t\t\t@loadeddata=\"audioEventRouter('loadeddata', $event)\"\n\t\t\t\t@loadedmetadata=\"audioEventRouter('loadedmetadata', $event)\"\n\t\t\t\t@timeupdate=\"audioEventRouter('timeupdate', $event)\"\n\t\t\t\t@play=\"audioEventRouter('play', $event)\"\n\t\t\t\t@playing=\"audioEventRouter('playing', $event)\"\n\t\t\t\t@pause=\"audioEventRouter('pause', $event)\"\n\t\t\t></audio>\n\t\t</div>\n\t"
	});

}((this.window = this.window || {}),BX,BX));
//# sourceMappingURL=audioplayer.bundle.js.map
