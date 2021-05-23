this.BX = this.BX || {};
(function (exports,ui_dialogs_messagebox,im_view_textarea,im_component_dialog,ui_switcher,ui_vue_components_smiles,main_core,im_lib_logger,ui_forms,ui_vue,im_const,im_lib_cookie,im_lib_utils,ui_vue_vuex) {
	'use strict';

	/**
	 * Bitrix Videoconf
	 * Smiles component (Vue component)
	 *
	 * @package bitrix
	 * @subpackage imopenlines
	 * @copyright 2001-2019 Bitrix
	 */
	ui_vue.Vue.cloneComponent('bx-im-component-call-smiles', 'bx-smiles', {
	  methods: {
	    hideForm: function hideForm(event) {
	      this.$parent.hideSmiles();
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-component-smiles-box\">\n\t\t\t<div class=\"bx-im-component-smiles-box-close\" @click=\"hideForm\"></div>\n\t\t\t<div class=\"bx-livechat-alert-smiles-box\">\n\t\t\t\t#PARENT_TEMPLATE#\n\t\t\t</div>\n\t\t</div>\n\t"
	});

	var MicLevel = {
	  props: ['localStream'],
	  data: function data() {
	    return {
	      bars: [],
	      barDisabledColor: 'rgba(255,255,255,0.42)',
	      barEnabledColor: '#B3E600'
	    };
	  },
	  watch: {
	    localStream: function localStream(stream) {
	      if (!main_core.Type.isNil(stream)) {
	        this.startAudioCheck();
	      }
	    }
	  },
	  mounted: function mounted() {
	    this.bars = babelHelpers.toConsumableArray(document.querySelectorAll('.bx-im-component-call-check-devices-micro-level-item'));
	  },
	  computed: {
	    localize: function localize() {
	      return ui_vue.Vue.getFilteredPhrases('BX_IM_COMPONENT_CALL_CHECK_DEVICES_', this.$root.$bitrixMessages);
	    }
	  },
	  methods: {
	    startAudioCheck: function startAudioCheck() {
	      this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
	      this.analyser = this.audioContext.createAnalyser();
	      this.microphone = this.audioContext.createMediaStreamSource(this.localStream);
	      this.scriptNode = this.audioContext.createScriptProcessor(2048, 1, 1);
	      this.analyser.smoothingTimeConstant = 0.8;
	      this.analyser.fftSize = 1024;
	      this.microphone.connect(this.analyser);
	      this.analyser.connect(this.scriptNode);
	      this.scriptNode.connect(this.audioContext.destination);
	      this.scriptNode.onaudioprocess = this.processVolume;
	    },
	    processVolume: function processVolume() {
	      var _this = this;

	      var arr = new Uint8Array(this.analyser.frequencyBinCount);
	      this.analyser.getByteFrequencyData(arr);
	      var values = 0;

	      for (var i = 0; i < arr.length; i++) {
	        values += arr[i];
	      }

	      var average = values / arr.length;
	      var oneBarValue = 100 / this.bars.length;
	      var barsToColor = Math.round(average / oneBarValue);
	      var elementsToColor = this.bars.slice(0, barsToColor);
	      this.bars.forEach(function (elem) {
	        elem.style.backgroundColor = _this.barDisabledColor;
	      });
	      elementsToColor.forEach(function (elem) {
	        elem.style.backgroundColor = _this.barEnabledColor;
	      });
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-component-call-check-devices-row\">\n\t\t\t<div class=\"bx-im-component-call-check-devices-micro-icon\"></div>\n\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level\">\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	var CheckDevices = {
	  data: function data() {
	    return {
	      noVideo: true,
	      selectedCamera: null,
	      selectedMic: null,
	      mediaStream: null,
	      showMic: true,
	      userDisabledCamera: false,
	      gettingVideo: false
	    };
	  },
	  created: function created() {
	    var _this = this;

	    this.$root.$on('setCameraState', function (state) {
	      _this.onCameraStateChange(state);
	    });
	    this.$root.$on('setMicState', function (state) {
	      _this.onMicStateChange(state);
	    });
	    this.$root.$on('callLocalMediaReceived', function () {
	      _this.stopLocalVideo();
	    });
	    this.$root.$on('cameraSelected', function (cameraId) {
	      _this.onCameraSelected(cameraId);
	    });
	    this.$root.$on('micSelected', function (micId) {
	      _this.onMicSelected(micId);
	    });
	    this.getDefaultDevices();
	  },
	  destroyed: function destroyed() {
	    // do not stop local media stream, because it is required in the controller
	    this.mediaStream = null;
	  },
	  computed: {
	    noVideoText: function noVideoText() {
	      if (this.gettingVideo) {
	        return this.localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_GETTING_CAMERA'];
	      }

	      if (this.userDisabledCamera) {
	        return this.localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_DISABLED_CAMERA'];
	      }

	      return this.localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_NO_VIDEO'];
	    },
	    localize: function localize() {
	      return ui_vue.Vue.getFilteredPhrases('BX_IM_COMPONENT_CALL_CHECK_DEVICES_', this.$root.$bitrixMessages);
	    }
	  },
	  methods: {
	    getDefaultDevices: function getDefaultDevices() {
	      var _this2 = this;

	      this.gettingVideo = true;
	      var constraints = {
	        audio: true,
	        video: true
	      };

	      if (!im_lib_utils.Utils.device.isMobile()) {
	        constraints.video = {};
	        constraints.video.width = {
	          ideal:
	          /*BX.Call.Hardware.preferHdQuality*/
	          1280
	        };
	        constraints.video.height = {
	          ideal:
	          /*BX.Call.Hardware.preferHdQuality*/
	          720
	        };
	      }

	      if (BX.Call.Hardware.defaultCamera) {
	        this.selectedCamera = BX.Call.Hardware.defaultCamera;
	        constraints.video = {
	          deviceId: {
	            exact: this.selectedCamera
	          }
	        };
	      } else if (Object.keys(BX.Call.Hardware.cameraList).length === 0) {
	        constraints.video = false;
	      }

	      if (BX.Call.Hardware.defaultMicrophone) {
	        this.selectedMic = BX.Call.Hardware.defaultMicrophone;
	        constraints.audio = {
	          deviceId: {
	            exact: this.selectedMic
	          }
	        };
	      }

	      navigator.mediaDevices.getUserMedia(constraints).then(function (stream) {
	        _this2.gettingVideo = false;

	        _this2.setLocalStream(stream);

	        if (stream.getVideoTracks().length > 0) {
	          if (!_this2.selectedCamera) {
	            _this2.selectedCamera = stream.getVideoTracks()[0].getSettings().deviceId;
	          }

	          _this2.noVideo = false;

	          _this2.playLocalVideo();

	          _this2.getApplication().setSelectedCamera(_this2.selectedCamera);
	        }

	        if (stream.getAudioTracks().length > 0) {
	          if (!_this2.selectedMic) {
	            _this2.selectedMic = stream.getAudioTracks()[0].getSettings().deviceId;
	          }

	          _this2.getApplication().setSelectedMic(_this2.selectedMic);
	        }
	      }).catch(function (e) {
	        _this2.gettingVideo = false;
	        im_lib_logger.Logger.warn('Error getting default media stream', e);
	      });
	    },
	    getLocalStream: function getLocalStream() {
	      var _this3 = this;

	      this.gettingVideo = true;

	      if (main_core.Type.isNil(this.selectedCamera) && main_core.Type.isNil(this.selectedMic)) {
	        return false;
	      }

	      var constraints = {
	        video: false,
	        audio: false
	      };

	      if (this.selectedCamera && !this.noVideo) {
	        constraints.video = {
	          deviceId: {
	            exact: this.selectedCamera
	          }
	        };

	        if (!im_lib_utils.Utils.device.isMobile()) {
	          constraints.video.width = {
	            ideal:
	            /*BX.Call.Hardware.preferHdQuality*/
	            1280
	          };
	          constraints.video.height = {
	            ideal:
	            /*BX.Call.Hardware.preferHdQuality*/
	            720
	          };
	        }
	      }

	      if (this.selectedMic) {
	        constraints.audio = {
	          deviceId: {
	            exact: this.selectedMic
	          }
	        };
	      }

	      navigator.mediaDevices.getUserMedia(constraints).then(function (stream) {
	        _this3.gettingVideo = false;

	        _this3.setLocalStream(stream);

	        if (stream.getVideoTracks().length > 0) {
	          _this3.playLocalVideo();
	        }
	      }).catch(function (error) {
	        _this3.gettingVideo = false;
	        im_lib_logger.Logger.warn('Getting video from camera error', error);
	        _this3.noVideo = true;

	        _this3.getApplication().setCameraState(false);
	      });
	    },
	    setLocalStream: function setLocalStream(stream) {
	      this.mediaStream = stream;
	      this.getApplication().setLocalVideoStream(this.mediaStream);
	    },
	    playLocalVideo: function playLocalVideo() {
	      im_lib_logger.Logger.warn('playing local video');
	      this.noVideo = false;
	      this.userDisabledCamera = false;
	      this.getApplication().setCameraState(true);
	      this.$refs['video'].volume = 0;
	      this.$refs['video'].srcObject = this.mediaStream;
	      this.$refs['video'].play();
	    },
	    stopLocalVideo: function stopLocalVideo() {
	      if (!this.mediaStream) {
	        return;
	      }

	      this.mediaStream.getTracks().forEach(function (tr) {
	        return tr.stop();
	      });
	      this.mediaStream = null;
	      this.getApplication().stopLocalVideoStream();
	    },
	    onCameraSelected: function onCameraSelected(cameraId) {
	      this.stopLocalVideo();
	      this.selectedCamera = cameraId;
	      this.getLocalStream();
	    },
	    onMicSelected: function onMicSelected(micId) {
	      /*this.stopLocalVideo();
	      this.selectedMic = micId;
	      this.getLocalStream();*/
	    },
	    onCameraStateChange: function onCameraStateChange(state) {
	      if (state) {
	        this.noVideo = false;
	        this.getLocalStream();
	      } else {
	        this.stopLocalVideo();
	        this.userDisabledCamera = true;
	        this.noVideo = true;
	        this.$root.$bitrixApplication.setCameraState(false);
	      }
	    },
	    onMicStateChange: function onMicStateChange(state) {
	      if (state) {
	        this.getLocalStream();
	      }

	      this.showMic = state;
	    },
	    isMobile: function isMobile() {
	      return im_lib_utils.Utils.device.isMobile();
	    },
	    getApplication: function getApplication() {
	      return this.$root.$bitrixApplication;
	    }
	  },
	  components: {
	    MicLevel: MicLevel
	  },
	  template: "\n\t<div class=\"bx-im-component-call-check-devices\">\n\t\t<div v-show=\"noVideo\">\n\t\t\t<div class=\"bx-im-component-call-check-devices-camera-no-video\">\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-camera-no-video-icon\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-camera-no-video-text\">{{ noVideoText }}</div>\n\t\t\t</div>\n\t\t</div>\n\t\t<div v-show=\"!noVideo\">\n\t\t\t<div class=\"bx-im-component-call-check-devices-camera-video-container\">\n\t\t\t\t<video ref=\"video\" class=\"bx-im-component-call-check-devices-camera-video\" muted autoplay playsinline></video>\n\t\t\t</div>\n\t\t</div>\n\t\t<template v-if=\"!isMobile()\">\n\t\t\t<mic-level v-show=\"showMic\" :localStream=\"mediaStream\"/>\n\t\t</template>\n\t</div>\n\t"
	};

	var ErrorComponent = {
	  props: ['errorCode'],
	  data: function data() {
	    return {
	      downloadAppArticleCode: 11387752
	    };
	  },
	  computed: babelHelpers.objectSpread({
	    bitrix24only: function bitrix24only() {
	      return this.errorCode === im_const.CallApplicationErrorCode.bitrix24only;
	    },
	    detectIntranetUser: function detectIntranetUser() {
	      return this.errorCode === im_const.CallApplicationErrorCode.detectIntranetUser;
	    },
	    userLimitReached: function userLimitReached() {
	      return this.errorCode === im_const.CallApplicationErrorCode.userLimitReached;
	    },
	    kickedFromCall: function kickedFromCall() {
	      return this.errorCode === im_const.CallApplicationErrorCode.kickedFromCall;
	    },
	    wrongAlias: function wrongAlias() {
	      return this.errorCode === im_const.CallApplicationErrorCode.wrongAlias;
	    },
	    conferenceFinished: function conferenceFinished() {
	      return this.errorCode === im_const.CallApplicationErrorCode.finished;
	    },
	    unsupportedBrowser: function unsupportedBrowser() {
	      return this.errorCode === im_const.CallApplicationErrorCode.unsupportedBrowser;
	    },
	    missingMicrophone: function missingMicrophone() {
	      return this.errorCode === im_const.CallApplicationErrorCode.missingMicrophone;
	    },
	    unsafeConnection: function unsafeConnection() {
	      return this.errorCode === im_const.CallApplicationErrorCode.unsafeConnection;
	    },
	    noSignalFromCamera: function noSignalFromCamera() {
	      return this.errorCode === im_const.CallErrorCode.noSignalFromCamera;
	    },
	    userLeftCall: function userLeftCall() {
	      return this.errorCode === im_const.CallApplicationErrorCode.userLeftCall;
	    },
	    localize: function localize() {
	      return ui_vue.Vue.getFilteredPhrases('BX_IM_COMPONENT_CALL_', this.$root.$bitrixMessages);
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    callApplication: function callApplication(state) {
	      return state.callApplication;
	    }
	  })),
	  methods: {
	    reloadPage: function reloadPage() {
	      location.reload();
	    },
	    redirectToAuthorize: function redirectToAuthorize() {
	      location.href = location.origin + '/auth/?backurl=' + location.pathname;
	    },
	    continueAsGuest: function continueAsGuest() {
	      im_lib_cookie.Cookie.set(null, "VIDEOCONF_GUEST_".concat(this.callApplication.common.alias), '', {
	        path: '/'
	      });
	      location.reload(true);
	    },
	    getBxLink: function getBxLink() {
	      return "bx://videoconf/code/".concat(this.$root.$bitrixApplication.getAlias());
	    },
	    openHelpArticle: function openHelpArticle() {
	      if (BX.Helper) {
	        BX.Helper.show("redirect=detail&code=" + this.downloadAppArticleCode);
	      }
	    },
	    isMobile: function isMobile() {
	      return im_lib_utils.Utils.device.isMobile();
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-component-call-error-wrap\">\n\t\t\t<template v-if=\"bitrix24only\">\n\t\t\t\t<div class=\"bx-im-component-call-error-container\">\n\t\t\t\t\t<div class=\"bx-im-component-call-error-icon bx-im-component-call-error-icon-b24only\"></div>\n\t\t\t\t\t<div class=\"bx-im-component-call-error-content\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-text\">{{ localize['BX_IM_COMPONENT_CALL_ERROR_MESSAGE_B24_ONLY'] }}</div>\n\t\t\t\t\t\t<template v-if=\"!isMobile()\">\n\t\t\t\t\t\t\t<a @click.prevent=\"openHelpArticle\" class=\"bx-im-component-call-error-more-link\">{{ localize['BX_IM_COMPONENT_CALL_BUTTON_CREATE_OWN'] }}</a>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-if=\"detectIntranetUser\">\n\t\t\t\t<div class=\"bx-im-component-call-error-container\">\n\t\t\t\t\t<div class=\"bx-im-component-call-error-icon bx-im-component-call-error-icon-intranet\"></div>\n\t\t\t\t\t<div class=\"bx-im-component-call-error-content\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-text\">{{ localize['BX_IM_COMPONENT_CALL_ERROR_MESSAGE_PLEASE_LOG_IN'] }}</div>\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-buttons\">\n\t\t\t\t\t\t\t<button @click=\"redirectToAuthorize\" class=\"ui-btn ui-btn-sm ui-btn-primary bx-im-component-call-error-button-authorize\">{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_AUTHORIZE'] }}</button>\n\t\t\t\t\t\t\t<button @click=\"continueAsGuest\" class=\"ui-btn ui-btn-sm bx-im-component-call-error-button-as-guest\">{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_AS_GUEST'] }}</button>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-if=\"userLimitReached\">\n\t\t\t\t<div class=\"bx-im-component-call-error-container\">\n\t\t\t\t\t<div class=\"bx-im-component-call-error-icon bx-im-component-call-error-icon-full\"></div>\n\t\t\t\t\t<div class=\"bx-im-component-call-error-content\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-text\">{{ localize['BX_IM_COMPONENT_CALL_ERROR_MESSAGE_USER_LIMIT'] }}</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-if=\"kickedFromCall\">\n\t\t\t\t<div class=\"bx-im-component-call-error-container\">\n\t\t\t\t\t<div class=\"bx-im-component-call-error-icon bx-im-component-call-error-icon-kicked\"></div>\n\t\t\t\t\t<div class=\"bx-im-component-call-error-content\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-text\">{{ localize['BX_IM_COMPONENT_CALL_ERROR_MESSAGE_KICKED'] }}</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-if=\"wrongAlias || conferenceFinished\">\n\t\t\t\t<div class=\"bx-im-component-call-error-container\">\n\t\t\t\t\t<div class=\"bx-im-component-call-error-icon bx-im-component-call-error-icon-finished\"></div>\n\t\t\t\t\t<div class=\"bx-im-component-call-error-content\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-text\">{{ localize['BX_IM_COMPONENT_CALL_ERROR_FINISHED'] }}</div>\n\t\t\t\t\t\t<template v-if=\"!isMobile()\">\n\t\t\t\t\t\t\t<a @click.prevent=\"openHelpArticle\" class=\"bx-im-component-call-error-more-link\">{{ localize['BX_IM_COMPONENT_CALL_BUTTON_CREATE_OWN'] }}</a>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-if=\"unsupportedBrowser\">\n\t\t\t\t<div class=\"bx-im-component-call-error-container\">\n\t\t\t\t\t<div class=\"bx-im-component-call-error-icon bx-im-component-call-error-icon-browser\"></div>\n\t\t\t\t\t<div class=\"bx-im-component-call-error-content\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-text\">{{ localize['BX_IM_COMPONENT_CALL_ERROR_UNSUPPORTED_BROWSER'] }}</div>\n\t\t\t\t\t\t<template v-if=\"!isMobile()\">\n\t\t\t\t\t\t\t<a @click.prevent=\"openHelpArticle\" class=\"bx-im-component-call-error-more-link\">{{ localize['BX_IM_COMPONENT_CALL_BUTTON_DETAILS'] }}</a>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-if=\"missingMicrophone\">\n\t\t\t\t<div class=\"bx-im-component-call-error-container\">\n\t\t\t\t\t<div class=\"bx-im-component-call-error-content\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-text\">{{ localize['BX_IM_COMPONENT_CALL_ERROR_NO_MIC'] }}</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-if=\"unsafeConnection\">\n\t\t\t\t<div class=\"bx-im-component-call-error-container\">\n\t\t\t\t\t<div class=\"bx-im-component-call-error-icon bx-im-component-call-error-icon-https\"></div>\n\t\t\t\t\t<div class=\"bx-im-component-call-error-content\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-text\">{{ localize['BX_IM_COMPONENT_CALL_ERROR_NO_HTTPS'] }}</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-if=\"noSignalFromCamera\">\n\t\t\t\t<div class=\"bx-im-component-call-error-container\">\n\t\t\t\t\t<div class=\"bx-im-component-call-error-content\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-text\">{{ localize['BX_IM_COMPONENT_CALL_ERROR_NO_SIGNAL_FROM_CAMERA'] }}</div>\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-buttons\">\n\t\t\t\t\t\t\t<button @click=\"reloadPage\" class=\"ui-btn ui-btn-sm ui-btn-no-caps\">{{ localize['BX_IM_COMPONENT_CALL_BUTTON_RELOAD'] }}</button>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-if=\"userLeftCall\">\n\t\t\t\t<div class=\"bx-im-component-call-error-container\">\n\t\t\t\t\t<div class=\"bx-im-component-call-error-content\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-text\">{{ localize['BX_IM_COMPONENT_CALL_ERROR_USER_LEFT_THE_CALL'] }}</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t</div>\n\t"
	};

	var OrientationDisabled = {
	  computed: {
	    localize: function localize() {
	      return Object.freeze({
	        BX_IM_COMPONENT_CALL_ROTATE_DEVICE: this.$root.$bitrixMessages.BX_IM_COMPONENT_CALL_ROTATE_DEVICE
	      });
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-component-call-orientation-disabled-wrap\">\n\t\t\t<div class=\"bx-im-component-call-orientation-disabled-icon\"></div>\n\t\t\t<div class=\"bx-im-component-call-orientation-disabled-text\">\n\t\t\t\t{{ localize.BX_IM_COMPONENT_CALL_ROTATE_DEVICE }}\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	/**
	 * Bitrix im
	 * Pubic call vue component
	 *
	 * @package bitrix
	 * @subpackage mobile
	 * @copyright 2001-2019 Bitrix
	 */
	var popupModes = Object.freeze({
	  preparation: 'preparation'
	});
	/**
	 * @notice Do not mutate or clone this component! It is under development.
	 */

	ui_vue.Vue.component('bx-im-component-call', {
	  props: ['chatId'],
	  data: function data() {
	    return {
	      userNewName: '',
	      password: '',
	      checkingPassword: false,
	      wrongPassword: false,
	      permissionsRequested: false,
	      waitingForStart: false,
	      popupMode: popupModes.preparation,
	      viewPortMetaNode: null,
	      conferenceDuration: '',
	      durationInterval: null
	    };
	  },
	  created: function created() {
	    if (this.isMobile()) {
	      this.setMobileMeta();
	    } else {
	      document.body.classList.add('bx-im-application-call-desktop-state');
	    }

	    if (!this.isDesktop()) {
	      window.addEventListener('beforeunload', this.onBeforeUnload.bind(this));
	    }
	  },
	  mounted: function mounted() {
	    if (!this.isHttps()) {
	      this.getApplication().setError(im_const.CallApplicationErrorCode.unsafeConnection);
	    }

	    if (!this.passwordChecked) {
	      this.$refs['passwordInput'].focus();
	    }
	  },
	  destroyed: function destroyed() {
	    clearInterval(this.durationInterval);
	  },
	  watch: {
	    showChat: function showChat(newValue) {
	      var _this = this;

	      if (this.isMobile()) {
	        return false;
	      }

	      if (newValue === true) {
	        this.$nextTick(function () {
	          _this.$root.$emit(im_const.EventType.textarea.focus);

	          _this.$root.$emit(im_const.EventType.dialog.scrollToBottom);
	        });
	      }
	    },
	    dialogInited: function dialogInited(newValue) {
	      if (newValue === true) {
	        this.getApplication().setDialogInited();
	      }
	    },
	    conferenceStarted: function conferenceStarted(newValue) {
	      var _this2 = this;

	      if (newValue === true) {
	        this.durationInterval = setInterval(function () {
	          _this2.updateConferenceDuration();
	        }, 1000);
	      }

	      this.updateConferenceDuration();
	    },
	    userInited: function userInited(newValue) {
	      if (newValue === true && this.isDesktop() && this.passwordChecked) {
	        this.requestPermissions();
	      }
	    }
	  },
	  computed: babelHelpers.objectSpread({
	    EventType: function EventType() {
	      return im_const.EventType;
	    },
	    userId: function userId() {
	      return this.application.common.userId;
	    },
	    dialogId: function dialogId() {
	      return this.application.dialog.dialogId;
	    },
	    conferenceTitle: function conferenceTitle() {
	      return this.callApplication.common.conferenceTitle;
	    },
	    conferenceStarted: function conferenceStarted() {
	      return this.callApplication.common.conferenceStarted;
	    },
	    conferenceStartDate: function conferenceStartDate() {
	      return this.callApplication.common.conferenceStartDate;
	    },
	    conferenceStatusClasses: function conferenceStatusClasses() {
	      var classes = ['bx-im-component-call-info-status'];

	      if (this.conferenceStarted === true) {
	        classes.push('bx-im-component-call-info-status-active');
	      } else {
	        classes.push('bx-im-component-call-info-status-not-active');
	      }

	      return classes;
	    },
	    conferenceStatusText: function conferenceStatusText() {
	      if (this.conferenceStarted === true) {
	        return "".concat(this.localize['BX_IM_COMPONENT_CALL_STATUS_STARTED'], ", ").concat(this.conferenceDuration);
	      } else if (this.conferenceStarted === false) {
	        return this.localize['BX_IM_COMPONENT_CALL_STATUS_NOT_STARTED'];
	      } else if (this.conferenceStarted === null) {
	        return this.localize['BX_IM_COMPONENT_CALL_STATUS_LOADING'];
	      }
	    },
	    intranetAvatarStyle: function intranetAvatarStyle() {
	      if (this.user && !this.user.extranet && this.user.avatar) {
	        return {
	          backgroundImage: "url('".concat(this.user.avatar, "')")
	        };
	      }

	      return '';
	    },
	    dialogInited: function dialogInited() {
	      if (this.dialog) {
	        return this.dialog.init;
	      }
	    },
	    dialogName: function dialogName() {
	      if (this.dialog) {
	        return this.dialog.name;
	      }
	    },
	    dialogCounter: function dialogCounter() {
	      if (this.dialog) {
	        return this.dialog.counter;
	      }
	    },
	    publicLink: function publicLink() {
	      if (this.dialog) {
	        return this.dialog.public.link;
	      }
	    },
	    userInited: function userInited() {
	      return this.callApplication.common.inited;
	    },
	    userHasRealName: function userHasRealName() {
	      if (this.user) {
	        return this.user.name !== this.localize['BX_IM_COMPONENT_CALL_DEFAULT_USER_NAME'];
	      }

	      return false;
	    },
	    showChat: function showChat() {
	      return this.callApplication.common.showChat;
	    },
	    userCounter: function userCounter() {
	      return this.dialog.userCounter;
	    },
	    userInCallCounter: function userInCallCounter() {
	      return this.callApplication.common.userInCallCount;
	    },
	    isPreparationStep: function isPreparationStep() {
	      return this.callApplication.common.state === im_const.CallStateType.preparation;
	    },
	    error: function error() {
	      return this.callApplication.common.error;
	    },
	    passwordChecked: function passwordChecked() {
	      return this.callApplication.common.passChecked;
	    },
	    mobileDisabled: function mobileDisabled() {
	      return false;

	      if (this.application.device.type === im_const.DeviceType.mobile) {
	        if (navigator.userAgent.toString().includes('iPad')) ; else if (this.application.device.orientation === im_const.DeviceOrientation.horizontal) {
	          if (navigator.userAgent.toString().includes('iPhone')) {
	            return true;
	          } else {
	            return !(babelHelpers.typeof(window.screen) === 'object' && window.screen.availHeight >= 800);
	          }
	        }
	      }

	      return false;
	    },
	    logoutLink: function logoutLink() {
	      return "".concat(this.publicLink, "?logout=yes&sessid=").concat(BX.bitrix_sessid());
	    },
	    localize: function localize() {
	      return ui_vue.Vue.getFilteredPhrases('BX_IM_COMPONENT_CALL_', this.$root.$bitrixMessages);
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    callApplication: function callApplication(state) {
	      return state.callApplication;
	    },
	    application: function application(state) {
	      return state.application;
	    },
	    user: function user(state) {
	      return state.users.collection[state.application.common.userId];
	    },
	    dialog: function dialog(state) {
	      return state.dialogues.collection[state.application.dialog.dialogId];
	    }
	  })),
	  methods: {
	    /* region 01. Actions */
	    setNewName: function setNewName() {
	      if (this.userNewName.length > 0) {
	        this.getApplication().setUserName(this.userNewName.trim());
	      }
	    },
	    startCall: function startCall() {
	      this.getApplication().startCall();
	    },
	    hideSmiles: function hideSmiles() {
	      this.getApplication().toggleSmiles();
	    },
	    checkPassword: function checkPassword() {
	      var _this3 = this;

	      if (!this.password || this.checkingPassword) {
	        this.wrongPassword = true;
	        return false;
	      }

	      this.checkingPassword = true;
	      this.wrongPassword = false;
	      this.getApplication().checkPassword(this.password).catch(function (checkResult) {
	        _this3.wrongPassword = true;
	      }).finally(function () {
	        _this3.checkingPassword = false;
	      });
	    },
	    requestPermissions: function requestPermissions() {
	      var _this4 = this;

	      this.getApplication().initHardware().then(function () {
	        _this4.$nextTick(function () {
	          _this4.permissionsRequested = true;
	        });
	      }).catch(function (error) {
	        ui_dialogs_messagebox.MessageBox.show({
	          message: _this4.localize['BX_IM_COMPONENT_CALL_HARDWARE_ERROR'],
	          modal: true,
	          buttons: ui_dialogs_messagebox.MessageBoxButtons.OK
	        });
	      });
	    },
	    startConference: function startConference(_ref) {
	      var video = _ref.video;
	      this.getApplication().startCall(video);
	    },
	    joinConference: function joinConference(_ref2) {
	      var video = _ref2.video;

	      if (this.user.extranet && !this.userHasRealName) {
	        this.setNewName();
	      }

	      if (!this.conferenceStarted) {
	        this.waitingForStart = true;
	        this.getApplication().setUserReadyToJoin();
	        this.getApplication().setJoinType(video);
	      } else {
	        this.getApplication().startCall(video);
	      }
	    },
	    openChat: function openChat() {
	      this.getApplication().toggleChat();
	    },

	    /* endregion 01. Actions */

	    /* region 02. Handlers */
	    onCloseChat: function onCloseChat() {
	      this.getApplication().toggleChat();
	    },
	    onTextareaSend: function onTextareaSend(event) {
	      if (!event.text) {
	        return false;
	      }

	      if (this.callApplication.common.showSmiles) {
	        this.getApplication().toggleSmiles();
	      }

	      this.getApplication().addMessage(event.text);
	    },
	    onTextareaFileSelected: function onTextareaFileSelected(event) {
	      var fileInput = event && event.fileChangeEvent && event.fileChangeEvent.target.files.length > 0 ? event.fileChangeEvent : '';

	      if (!fileInput) {
	        return false;
	      }

	      this.getApplication().uploadFile(fileInput);
	    },
	    onTextareaWrites: function onTextareaWrites(event) {
	      this.getController().application.startWriting();
	    },
	    onTextareaAppButtonClick: function onTextareaAppButtonClick(event) {
	      if (event.appId === 'smile') {
	        this.getApplication().toggleSmiles();
	      }
	    },
	    onBeforeUnload: function onBeforeUnload(event) {
	      if (!this.getApplication().callView) {
	        return;
	      }

	      if (!this.isPreparationStep) {
	        event.preventDefault();
	        event.returnValue = '';
	      }
	    },
	    onSmilesSelectSmile: function onSmilesSelectSmile(event) {
	      this.$root.$emit(im_const.EventType.textarea.insertText, {
	        text: event.text
	      });
	    },
	    onSmilesSelectSet: function onSmilesSelectSet() {
	      this.$root.$emit(im_const.EventType.textarea.focus);
	    },

	    /* endregion 02. Handlers */

	    /* region 03. Helpers */
	    isMobile: function isMobile() {
	      return im_lib_utils.Utils.device.isMobile();
	    },
	    isDesktop: function isDesktop() {
	      return im_lib_utils.Utils.platform.isBitrixDesktop();
	    },
	    setMobileMeta: function setMobileMeta() {
	      if (!this.viewPortMetaNode) {
	        this.viewPortMetaNode = document.createElement('meta');
	        this.viewPortMetaNode.setAttribute('name', 'viewport');
	        this.viewPortMetaNode.setAttribute("content", "width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0");
	        document.head.appendChild(this.viewPortMetaNode);
	      }

	      document.body.classList.add('bx-im-application-call-mobile-state');

	      if (im_lib_utils.Utils.browser.isSafariBased()) {
	        document.body.classList.add('bx-im-application-call-mobile-safari-based');
	      }
	    },
	    getApplication: function getApplication() {
	      return this.$root.$bitrixApplication;
	    },
	    getController: function getController() {
	      return this.$root.$bitrixController;
	    },
	    updateConferenceDuration: function updateConferenceDuration() {
	      if (!this.conferenceStartDate) {
	        return false;
	      }

	      var startDate = this.conferenceStartDate;
	      var currentDate = new Date();
	      var durationInSeconds = Math.floor((currentDate - startDate) / 1000);
	      var minutes = 0;

	      if (durationInSeconds > 60) {
	        minutes = Math.floor(durationInSeconds / 60);

	        if (minutes < 10) {
	          minutes = '0' + minutes;
	        }
	      }

	      var seconds = durationInSeconds - minutes * 60;

	      if (seconds < 10) {
	        seconds = '0' + seconds;
	      }

	      this.conferenceDuration = "".concat(minutes, ":").concat(seconds);
	      return true;
	    },
	    isHttps: function isHttps() {
	      return location.protocol === 'https:';
	    }
	    /* endregion 03. Helpers */

	  },
	  components: {
	    ErrorComponent: ErrorComponent,
	    CheckDevices: CheckDevices,
	    OrientationDisabled: OrientationDisabled
	  },
	  // language=Vue
	  template: "\n\t\t<div :class=\"['bx-im-component-call-wrap', {'bx-im-component-call-wrap-with-chat': showChat}]\">\n\t\t\t<div v-show=\"mobileDisabled\">\n\t\t\t\t<orientation-disabled/>\n\t\t\t</div>\n\t\t\t<div v-show=\"!mobileDisabled\" class=\"bx-im-component-call\">\n\t\t\t\t<div class=\"bx-im-component-call-left\">\n\t\t\t\t\t<div id=\"bx-im-component-call-container\"></div>\n\t\t\t\t\t<div v-if=\"isPreparationStep\" class=\"bx-im-component-call-left-preparation\">\n\t\t\t\t\t\t<!-- Step 1: Errors -->\n\t\t\t\t\t\t<template v-if=\"error\">\n\t\t\t\t\t\t\t<error-component :errorCode=\"error\" />\n\t\t\t\t\t\t</template>\n\t\t\t\t\t\t<!-- Step 2: Password check -->\n\t\t\t\t\t\t<template v-else-if=\"!passwordChecked\">\n\t\t\t\t\t\t\t<div class=\"bx-im-component-call-info-container\">\n\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-info-logo\"></div>\n\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-info-title\">{{ conferenceTitle }}</div>\n\t<!--\t\t\t\t\t\t<div class=\"bx-im-component-call-info-date\">26.08.2020, 12:00 - 13:00</div>-->\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"bx-im-component-call-password-container\">\n\t\t\t\t\t\t\t\t<template v-if=\"wrongPassword\">\n\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-password-error\">\n\t\t\t\t\t\t\t\t\t\t{{ localize['BX_IM_COMPONENT_CALL_PASSWORD_WRONG'] }}\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-password-title\">\n\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-password-title-logo\"></div>\n\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-password-title-text\">{{ localize['BX_IM_COMPONENT_CALL_PASSWORD_TITLE'] }}</div>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t<input @keyup.enter=\"checkPassword\" type=\"text\" v-model=\"password\" class=\"bx-im-component-call-password-input\" :placeholder=\"localize['BX_IM_COMPONENT_CALL_PASSWORD_PLACEHOLDER']\" ref=\"passwordInput\"/>\n\t\t\t\t\t\t\t\t<button @click=\"checkPassword\" class=\"ui-btn ui-btn-sm ui-btn-primary bx-im-component-call-password-button\">{{ localize['BX_IM_COMPONENT_CALL_PASSWORD_JOIN'] }}</button>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t\t<template v-else-if=\"!error && passwordChecked\">\n\t\t\t\t\t\t\t<!-- Step 3: Loading -->\n\t\t\t\t\t\t\t<template v-if=\"!userInited\">\n\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-loading\">\n\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-loading-text\">{{ localize['BX_IM_COMPONENT_CALL_LOADING'] }}</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t\t\t<!-- Step 4: Permissions -->\n\t\t\t\t\t\t\t\t<template v-if=\"!isDesktop() && !permissionsRequested\">\n\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-info-container\">\n\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-info-logo\"></div>\n\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-info-title\">{{ conferenceTitle }}</div>\n\t<!--\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-info-date\">26.08.2020, 12:00 - 13:00</div>-->\n\t\t\t\t\t\t\t\t\t\t<div :class=\"conferenceStatusClasses\">{{ conferenceStatusText }}</div>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-permissions-container\">\n\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-permissions-text\">{{ localize['BX_IM_COMPONENT_CALL_PERMISSIONS_TEXT'] }}</div>\n\t\t\t\t\t\t\t\t\t\t<button @click=\"requestPermissions\" class=\"ui-btn ui-btn-sm ui-btn-primary bx-im-component-call-permissions-button\">{{ localize['BX_IM_COMPONENT_CALL_PERMISSIONS_BUTTON'] }}</button>\n\t\t\t\t\t\t\t\t\t\t<template v-if=\"isMobile()\">\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-open-chat-button-container\">\n\t\t\t\t\t\t\t\t\t\t\t\t<button @click=\"openChat\" class=\"ui-btn ui-btn-sm ui-btn-icon-chat bx-im-component-call-open-chat-button\">{{ localize['BX_IM_COMPONENT_CALL_OPEN_CHAT'] }}</button>\n\t\t\t\t\t\t\t\t\t\t\t\t<template v-if=\"dialogCounter > 0\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-open-chat-button-counter\">{{ dialogCounter }}</div>\n\t\t\t\t\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t<template v-if=\"isDesktop() && (!permissionsRequested || !user)\">\n\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-info-container\">\n\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-info-logo\"></div>\n\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-info-title\">{{ conferenceTitle }}</div>\n\t<!--\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-info-date\">26.08.2020, 12:00 - 13:00</div>-->\n\t\t\t\t\t\t\t\t\t\t<div :class=\"conferenceStatusClasses\">{{ conferenceStatusText }}</div>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-permissions-container\">\n\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-permissions-text\">{{ localize['BX_IM_COMPONENT_CALL_PERMISSIONS_LOADING'] }}</div>\n\t\t\t\t\t\t\t\t\t\t<button class=\"ui-btn ui-btn-sm ui-btn-wait bx-im-component-call-permissions-button\">{{ localize['BX_IM_COMPONENT_CALL_PERMISSIONS_BUTTON'] }}</button>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t<!-- Step 5: Usual interface with video and mic check -->\n\t\t\t\t\t\t\t\t<template v-else-if=\"permissionsRequested\">\n\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-video-step-container\">\n\t\t\t\t\t\t\t\t\t\t<!-- Compact conference info -->\n\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-info-container-compact\">\n\t\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-info-title-container\">\n\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-info-logo\"></div>\n\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-info-title\">{{ conferenceTitle }}</div>\n\t\t\t\t\t\t\t\t\t\t\t</div>\n\t<!--\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-info-date\">26.08.2020, 12:00 - 13:00</div>-->\n\t\t\t\t\t\t\t\t\t\t\t<div :class=\"conferenceStatusClasses\">{{ conferenceStatusText }}</div>\n\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t<!-- Video and mic check -->\n\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-device-check-container\">\n\t\t\t\t\t\t\t\t\t\t\t<check-devices />\n\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-bottom-container\">\n\t\t\t\t\t\t\t\t\t\t\t<template v-if=\"!waitingForStart\">\n\t\t\t\t\t\t\t\t\t\t\t\t<!-- If we know user name -->\n\t\t\t\t\t\t\t\t\t\t\t\t<template v-if=\"user && userHasRealName\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t<template v-if=\"!user.extranet\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-intranet-name-container\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-intranet-name-title\">{{ localize['BX_IM_COMPONENT_CALL_INTRANET_NAME_TITLE'] }}</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-intranet-name-content\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-intranet-name-content-left\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div :style=\"intranetAvatarStyle\" class=\"bx-im-component-call-intranet-name-avatar\"></div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-intranet-name-text\">{{ user.name }}</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<template v-if=\"!isDesktop()\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<a :href=\"logoutLink\" class=\"bx-im-component-call-intranet-name-logout\">{{ localize['BX_IM_COMPONENT_CALL_INTRANET_LOGOUT'] }}</a>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t\t\t\t\t\t<template v-else-if=\"user.extranet\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-guest-name-container\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-guest-name-text\">{{ user.name }}</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t\t\t\t\t<!-- New guest, need to specify name -->\n\t\t\t\t\t\t\t\t\t\t\t\t<template v-else-if=\"user && !userHasRealName\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t<input\n\t\t\t\t\t\t\t\t\t\t\t\t\t\tv-model=\"userNewName\"\n\t\t\t\t\t\t\t\t\t\t\t\t\t\ttype=\"text\"\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t:placeholder=\"localize['BX_IM_COMPONENT_CALL_NAME_PLACEHOLDER']\"\n\t\t\t\t\t\t\t\t\t\t\t\t\t\tclass=\"bx-im-component-call-name-input\"\n\t\t\t\t\t\t\t\t\t\t\t\t\t\tref=\"nameInput\"\n\t\t\t\t\t\t\t\t\t\t\t\t\t/>\n\t\t\t\t\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t\t\t\t\t<!-- Action buttons -->\n\t\t\t\t\t\t\t\t\t\t\t\t<!-- Intranet user can start conference -->\n\t\t\t\t\t\t\t\t\t\t\t\t<template v-if=\"user\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t<template v-if=\"!user.extranet && !conferenceStarted\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t<button @click=\"startConference({video: true})\" class=\"ui-btn ui-btn-sm ui-btn-primary bx-im-component-call-join-video\">{{ localize['BX_IM_COMPONENT_CALL_START_WITH_VIDEO'] }}</button>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t<button @click=\"startConference({video: false})\" class=\"ui-btn ui-btn-sm bx-im-component-call-join-audio\">{{ localize['BX_IM_COMPONENT_CALL_START_WITH_AUDIO'] }}</button>\n\t\t\t\t\t\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t\t\t\t\t\t<!-- Others can join -->\n\t\t\t\t\t\t\t\t\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t<button @click=\"joinConference({video: true})\" class=\"ui-btn ui-btn-sm ui-btn-primary bx-im-component-call-join-video\">{{ localize['BX_IM_COMPONENT_CALL_JOIN_WITH_VIDEO'] }}</button>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t<button @click=\"joinConference({video: false})\" class=\"ui-btn ui-btn-sm bx-im-component-call-join-audio\">{{ localize['BX_IM_COMPONENT_CALL_JOIN_WITH_AUDIO'] }}</button>\n\t\t\t\t\t\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t\t\t\t<!-- Waiting for start-->\n\t\t\t\t\t\t\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-wait-container\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-wait-logo\"></div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-wait-title\">{{ localize['BX_IM_COMPONENT_CALL_WAIT_START_TITLE'] }}</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-wait-user-counter\">{{ localize['BX_IM_COMPONENT_CALL_WAIT_START_USER_COUNT'] }} {{ userCounter }}</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t<template v-if=\"isMobile()\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-open-chat-button-container\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<button @click=\"openChat\" class=\"ui-btn ui-btn-sm ui-btn-icon-chat bx-im-component-call-open-chat-button\">{{ localize['BX_IM_COMPONENT_CALL_OPEN_CHAT'] }}</button>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<template v-if=\"dialogCounter > 0\">\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-open-chat-button-counter\">{{ dialogCounter }}</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<template v-if=\"userInited && !error\">\n\t\t\t\t\t<transition :name=\"!isMobile()? 'videoconf-chat-slide': ''\">\n\t\t\t\t\t\t<div v-show=\"showChat\" class=\"bx-im-component-call-right\">\n\t\t\t\t\t\t\t<div class=\"bx-im-component-call-right-header\">\n\t\t\t\t\t\t\t\t<div @click=\"onCloseChat\" class=\"bx-im-component-call-right-header-close\" :title=\"localize['BX_IM_COMPONENT_CALL_CHAT_CLOSE_TITLE']\"></div>\n\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-right-header-title\">{{ localize['BX_IM_COMPONENT_CALL_CHAT_TITLE'] }}</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div class=\"bx-im-component-call-right-chat\">\n\t\t\t\t\t\t\t\t<bx-im-component-dialog\n\t\t\t\t\t\t\t\t\t:userId=\"userId\"\n\t\t\t\t\t\t\t\t\t:dialogId=\"dialogId\"\n\t\t\t\t\t\t\t\t\t:listenEventScrollToBottom=\"EventType.dialog.scrollToBottom\"\n\t\t\t\t\t\t\t\t/>\n\t\t\t\t\t\t\t\t<keep-alive include=\"bx-im-component-call-smiles\">\n\t\t\t\t\t\t\t\t\t<template v-if=\"callApplication.common.showSmiles\">\n\t\t\t\t\t\t\t\t\t\t<bx-im-component-call-smiles @selectSmile=\"onSmilesSelectSmile\" @selectSet=\"onSmilesSelectSet\"/>\t\n\t\t\t\t\t\t\t\t\t</template>\t\n\t\t\t\t\t\t\t\t</keep-alive>\n\t\t\t\t\t\t\t\t<div v-if=\"user\" class=\"bx-im-component-call-textarea\">\n\t\t\t\t\t\t\t\t\t<bx-im-view-textarea\n\t\t\t\t\t\t\t\t\t\t:userId=\"userId\"\n\t\t\t\t\t\t\t\t\t\t:dialogId=\"dialogId\" \n\t\t\t\t\t\t\t\t\t\t:writesEventLetter=\"3\"\n\t\t\t\t\t\t\t\t\t\t:enableFile=\"true\"\n\t\t\t\t\t\t\t\t\t\t:enableEdit=\"true\"\n\t\t\t\t\t\t\t\t\t\t:enableCommand=\"false\"\n\t\t\t\t\t\t\t\t\t\t:enableMention=\"false\"\n\t\t\t\t\t\t\t\t\t\t:autoFocus=\"true\"\n\t\t\t\t\t\t\t\t\t\t:listenEventInsertText=\"EventType.textarea.insertText\"\n\t\t\t\t\t\t\t\t\t\t:listenEventFocus=\"EventType.textarea.focus\"\n\t\t\t\t\t\t\t\t\t\t:listenEventBlur=\"EventType.textarea.blur\"\n\t\t\t\t\t\t\t\t\t\t@send=\"onTextareaSend\"\n\t\t\t\t\t\t\t\t\t\t@fileSelected=\"onTextareaFileSelected\"\n\t\t\t\t\t\t\t\t\t\t@writes=\"onTextareaWrites\"\n\t\t\t\t\t\t\t\t\t\t@appButtonClick=\"onTextareaAppButtonClick\"\n\t\t\t\t\t\t\t\t\t/>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</transition>\n\t\t\t\t</template>\n\t\t\t</div>\n\t\t</div>\n\t"
	});

}((this.BX.Messenger = this.BX.Messenger || {}),BX.UI.Dialogs,window,BX.Messenger,BX,window,BX,BX.Messenger.Lib,BX,BX,BX.Messenger.Const,BX.Messenger.Lib,BX.Messenger.Lib,BX));
//# sourceMappingURL=call.bundle.js.map
