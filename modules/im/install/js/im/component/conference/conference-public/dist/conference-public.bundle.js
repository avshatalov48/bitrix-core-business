this.BX = this.BX || {};
(function (exports,ui_designTokens,ui_fonts_opensans,im_eventHandler,im_component_dialog,im_component_textarea,ui_switcher,ui_vue_components_smiles,main_core,ui_forms,im_lib_cookie,im_component_callFeedback,im_lib_desktop,ui_vue,im_lib_logger,im_lib_utils,im_const,main_core_events,ui_vue_vuex,main_popup,im_lib_clipboard,ui_dialogs_messagebox) {
	'use strict';

	var ConferenceTextareaHandler = /*#__PURE__*/function (_TextareaHandler) {
	  babelHelpers.inherits(ConferenceTextareaHandler, _TextareaHandler);
	  function ConferenceTextareaHandler($Bitrix) {
	    var _this;
	    babelHelpers.classCallCheck(this, ConferenceTextareaHandler);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ConferenceTextareaHandler).call(this, $Bitrix));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "application", null);
	    _this.application = $Bitrix.Application.get();
	    return _this;
	  }
	  babelHelpers.createClass(ConferenceTextareaHandler, [{
	    key: "onAppButtonClick",
	    value: function onAppButtonClick(_ref) {
	      var event = _ref.data;
	      if (event.appId === 'smile') {
	        this.application.toggleSmiles();
	      }
	    }
	  }]);
	  return ConferenceTextareaHandler;
	}(im_eventHandler.TextareaHandler);

	var ConferenceTextareaUploadHandler = /*#__PURE__*/function (_TextareaUploadHandle) {
	  babelHelpers.inherits(ConferenceTextareaUploadHandler, _TextareaUploadHandle);
	  function ConferenceTextareaUploadHandler() {
	    babelHelpers.classCallCheck(this, ConferenceTextareaUploadHandler);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ConferenceTextareaUploadHandler).apply(this, arguments));
	  }
	  babelHelpers.createClass(ConferenceTextareaUploadHandler, [{
	    key: "addMessageWithFile",
	    value: function addMessageWithFile(event) {
	      var _this = this;
	      var message = event.getData();
	      if (!this.getDiskFolderId()) {
	        this.requestDiskFolderId(message.chatId).then(function () {
	          _this.addMessageWithFile(event);
	        })["catch"](function (error) {
	          im_lib_logger.Logger.error('addMessageWithFile error', error);
	          return false;
	        });
	        return false;
	      }
	      message.chatId = this.getChatId();
	      this.setUploaderCustomHeaders();
	      this.uploader.addTask({
	        taskId: message.file.id,
	        fileData: message.file.source.file,
	        fileName: message.file.source.file.name,
	        generateUniqueName: true,
	        diskFolderId: this.getDiskFolderId(),
	        previewBlob: message.file.previewBlob
	      });
	    }
	  }, {
	    key: "setUploaderCustomHeaders",
	    value: function setUploaderCustomHeaders() {
	      if (!this.uploader.senderOptions.customHeaders) {
	        this.uploader.senderOptions.customHeaders = {};
	      }
	      this.uploader.senderOptions.customHeaders['Call-Auth-Id'] = this.getUserHash();
	      this.uploader.senderOptions.customHeaders['Call-Chat-Id'] = this.getChatId();
	    }
	  }, {
	    key: "getUserHash",
	    value: function getUserHash() {
	      return this.controller.store.state.conference.user.hash;
	    }
	  }, {
	    key: "getActionCommitFile",
	    value: function getActionCommitFile() {
	      return 'im.call.disk.commit';
	    }
	  }, {
	    key: "getActionUploadChunk",
	    value: function getActionUploadChunk() {
	      return 'im.call.disk.upload';
	    }
	  }]);
	  return ConferenceTextareaUploadHandler;
	}(im_eventHandler.TextareaUploadHandler);

	var ConferenceSmiles = {
	  methods: {
	    onSelectSmile: function onSelectSmile(event) {
	      this.$emit('selectSmile', event);
	    },
	    onSelectSet: function onSelectSet(event) {
	      this.$emit('selectSet', event);
	    },
	    hideSmiles: function hideSmiles() {
	      main_core_events.EventEmitter.emit(im_const.EventType.conference.hideSmiles);
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"bx-im-component-smiles-box\">\n\t\t\t<div class=\"bx-im-component-smiles-box-close\" @click=\"hideSmiles\"></div>\n\t\t\t<div class=\"bx-im-component-smiles-box-list\">\n\t\t\t\t<bx-smiles\n\t\t\t\t\t@selectSmile=\"onSelectSmile\"\n\t\t\t\t\t@selectSet=\"onSelectSet\"\n\t\t\t\t/>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

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
	      return ui_vue.BitrixVue.getFilteredPhrases('BX_IM_COMPONENT_CALL_CHECK_DEVICES_');
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
	      gettingVideo: false,
	      isFlippedVideo: BX.Call.Hardware.enableMirroring
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
	    this.getApplication().initHardware().then(function () {
	      _this.getDefaultDevices();
	    })["catch"](function () {
	      ui_dialogs_messagebox.MessageBox.show({
	        message: _this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_HARDWARE_ERROR'),
	        modal: true,
	        buttons: ui_dialogs_messagebox.MessageBoxButtons.OK
	      });
	    });
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
	      return ui_vue.BitrixVue.getFilteredPhrases('BX_IM_COMPONENT_CALL_CHECK_DEVICES_');
	    },
	    cameraVideoClasses: function cameraVideoClasses() {
	      return {
	        'bx-im-component-call-check-devices-camera-video': true,
	        'bx-im-component-call-check-devices-camera-video-flipped': this.isFlippedVideo
	      };
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
	          ideal: /*BX.Call.Hardware.preferHdQuality*/1280
	        };
	        constraints.video.height = {
	          ideal: /*BX.Call.Hardware.preferHdQuality*/720
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
	      })["catch"](function (e) {
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
	            ideal: /*BX.Call.Hardware.preferHdQuality*/1280
	          };
	          constraints.video.height = {
	            ideal: /*BX.Call.Hardware.preferHdQuality*/720
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
	      })["catch"](function (error) {
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
	        this.getApplication().setCameraState(false);
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
	      return this.$Bitrix.Application.get();
	    }
	  },
	  components: {
	    MicLevel: MicLevel
	  },
	  template: "\n\t<div class=\"bx-im-component-call-device-check-container\">\n\t\t<div class=\"bx-im-component-call-check-devices\">\n\t\t\t<div v-show=\"noVideo\">\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-camera-no-video\">\n\t\t\t\t\t<div class=\"bx-im-component-call-check-devices-camera-no-video-icon\"></div>\n\t\t\t\t\t<div class=\"bx-im-component-call-check-devices-camera-no-video-text\">{{ noVideoText }}</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<div v-show=\"!noVideo\">\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-camera-video-container\">\n\t\t\t\t\t<video :class=\"cameraVideoClasses\" ref=\"video\" muted autoplay playsinline></video>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<template v-if=\"!isMobile()\">\n\t\t\t\t<mic-level v-show=\"showMic\" :localStream=\"mediaStream\"/>\n\t\t\t</template>\n\t\t</div>\n\t</div>\n\t"
	};

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var Error = {
	  data: function data() {
	    return {
	      downloadAppArticleCode: 11387752,
	      callFeedbackSent: false
	    };
	  },
	  computed: _objectSpread({
	    errorCode: function errorCode() {
	      return this.conference.common.error;
	    },
	    bitrix24only: function bitrix24only() {
	      return this.errorCode === im_const.ConferenceErrorCode.bitrix24only;
	    },
	    detectIntranetUser: function detectIntranetUser() {
	      return this.errorCode === im_const.ConferenceErrorCode.detectIntranetUser;
	    },
	    userLimitReached: function userLimitReached() {
	      return this.errorCode === im_const.ConferenceErrorCode.userLimitReached;
	    },
	    kickedFromCall: function kickedFromCall() {
	      return this.errorCode === im_const.ConferenceErrorCode.kickedFromCall;
	    },
	    wrongAlias: function wrongAlias() {
	      return this.errorCode === im_const.ConferenceErrorCode.wrongAlias;
	    },
	    conferenceFinished: function conferenceFinished() {
	      return this.errorCode === im_const.ConferenceErrorCode.finished;
	    },
	    unsupportedBrowser: function unsupportedBrowser() {
	      return this.errorCode === im_const.ConferenceErrorCode.unsupportedBrowser;
	    },
	    missingMicrophone: function missingMicrophone() {
	      return this.errorCode === im_const.ConferenceErrorCode.missingMicrophone;
	    },
	    unsafeConnection: function unsafeConnection() {
	      return this.errorCode === im_const.ConferenceErrorCode.unsafeConnection;
	    },
	    noSignalFromCamera: function noSignalFromCamera() {
	      return this.errorCode === im_const.ConferenceErrorCode.noSignalFromCamera;
	    },
	    userLeftCall: function userLeftCall() {
	      return this.errorCode === im_const.ConferenceErrorCode.userLeftCall;
	    },
	    showFeedback: function showFeedback() {
	      console.warn('this.$Bitrix.Application.get()', this.$Bitrix.Application.get());
	      console.warn('this.$Bitrix.Application.get().showFeedback', this.$Bitrix.Application.get().showFeedback);
	      return this.$Bitrix.Application.get().showFeedback;
	    },
	    callDetails: function callDetails() {
	      console.warn('this.$Bitrix.Application.get().callDetails', this.$Bitrix.Application.get().callDetails);
	      return this.$Bitrix.Application.get().callDetails;
	    },
	    localize: function localize() {
	      return ui_vue.BitrixVue.getFilteredPhrases('BX_IM_COMPONENT_CALL_');
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    conference: function conference(state) {
	      return state.conference;
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
	      im_lib_cookie.Cookie.set(null, "VIDEOCONF_GUEST_".concat(this.conference.common.alias), '', {
	        path: '/'
	      });
	      location.reload(true);
	    },
	    getBxLink: function getBxLink() {
	      return "bx://videoconf/code/".concat(this.$Bitrix.Application.get().getAlias());
	    },
	    openHelpArticle: function openHelpArticle() {
	      if (BX.Helper) {
	        BX.Helper.show("redirect=detail&code=" + this.downloadAppArticleCode);
	      }
	    },
	    isMobile: function isMobile() {
	      return im_lib_utils.Utils.device.isMobile();
	    },
	    onFeedbackSent: function onFeedbackSent() {
	      var _this = this;
	      setTimeout(function () {
	        _this.callFeedbackSent = true;
	      }, 1500);
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-component-call-error-wrap\">\n\t\t\t<template v-if=\"bitrix24only\">\n\t\t\t\t<div class=\"bx-im-component-call-error-container\">\n\t\t\t\t\t<div class=\"bx-im-component-call-error-icon bx-im-component-call-error-icon-b24only\"></div>\n\t\t\t\t\t<div class=\"bx-im-component-call-error-content\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-text\">{{ localize['BX_IM_COMPONENT_CALL_ERROR_MESSAGE_B24_ONLY'] }}</div>\n\t\t\t\t\t\t<template v-if=\"!isMobile()\">\n\t\t\t\t\t\t\t<a @click.prevent=\"openHelpArticle\" class=\"bx-im-component-call-error-more-link\">{{ localize['BX_IM_COMPONENT_CALL_BUTTON_CREATE_OWN'] }}</a>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-if=\"detectIntranetUser\">\n\t\t\t\t<div class=\"bx-im-component-call-error-container\">\n\t\t\t\t\t<div class=\"bx-im-component-call-error-icon bx-im-component-call-error-icon-intranet\"></div>\n\t\t\t\t\t<div class=\"bx-im-component-call-error-content\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-text\">{{ localize['BX_IM_COMPONENT_CALL_ERROR_MESSAGE_PLEASE_LOG_IN'] }}</div>\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-buttons\">\n\t\t\t\t\t\t\t<button @click=\"redirectToAuthorize\" class=\"ui-btn ui-btn-sm ui-btn-primary bx-im-component-call-error-button-authorize\">{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_AUTHORIZE'] }}</button>\n\t\t\t\t\t\t\t<button @click=\"continueAsGuest\" class=\"ui-btn ui-btn-sm bx-im-component-call-error-button-as-guest\">{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_AS_GUEST'] }}</button>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-if=\"userLimitReached\">\n\t\t\t\t<div class=\"bx-im-component-call-error-container\">\n\t\t\t\t\t<div class=\"bx-im-component-call-error-icon bx-im-component-call-error-icon-full\"></div>\n\t\t\t\t\t<div class=\"bx-im-component-call-error-content\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-text\">{{ localize['BX_IM_COMPONENT_CALL_ERROR_MESSAGE_USER_LIMIT'] }}</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-if=\"kickedFromCall\">\n\t\t\t\t<div class=\"bx-im-component-call-error-container\">\n\t\t\t\t\t<div class=\"bx-im-component-call-error-icon bx-im-component-call-error-icon-kicked\"></div>\n\t\t\t\t\t<div class=\"bx-im-component-call-error-content\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-text\">{{ localize['BX_IM_COMPONENT_CALL_ERROR_MESSAGE_KICKED'] }}</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-if=\"wrongAlias || conferenceFinished\">\n\t\t\t\t<div class=\"bx-im-component-call-error-container\">\n\t\t\t\t\t<div class=\"bx-im-component-call-error-icon bx-im-component-call-error-icon-finished\"></div>\n\t\t\t\t\t<div class=\"bx-im-component-call-error-content\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-text\">{{ localize['BX_IM_COMPONENT_CALL_ERROR_FINISHED'] }}</div>\n\t\t\t\t\t\t<template v-if=\"!isMobile()\">\n\t\t\t\t\t\t\t<a @click.prevent=\"openHelpArticle\" class=\"bx-im-component-call-error-more-link\">{{ localize['BX_IM_COMPONENT_CALL_BUTTON_CREATE_OWN'] }}</a>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-if=\"unsupportedBrowser\">\n\t\t\t\t<div class=\"bx-im-component-call-error-container\">\n\t\t\t\t\t<div class=\"bx-im-component-call-error-icon bx-im-component-call-error-icon-browser\"></div>\n\t\t\t\t\t<div class=\"bx-im-component-call-error-content\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-text\">{{ localize['BX_IM_COMPONENT_CALL_ERROR_UNSUPPORTED_BROWSER'] }}</div>\n\t\t\t\t\t\t<template v-if=\"!isMobile()\">\n\t\t\t\t\t\t\t<a @click.prevent=\"openHelpArticle\" class=\"bx-im-component-call-error-more-link\">{{ localize['BX_IM_COMPONENT_CALL_BUTTON_DETAILS'] }}</a>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-if=\"missingMicrophone\">\n\t\t\t\t<div class=\"bx-im-component-call-error-container\">\n\t\t\t\t\t<div class=\"bx-im-component-call-error-content\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-text\">{{ localize['BX_IM_COMPONENT_CALL_ERROR_NO_MIC'] }}</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-if=\"unsafeConnection\">\n\t\t\t\t<div class=\"bx-im-component-call-error-container\">\n\t\t\t\t\t<div class=\"bx-im-component-call-error-icon bx-im-component-call-error-icon-https\"></div>\n\t\t\t\t\t<div class=\"bx-im-component-call-error-content\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-text\">{{ localize['BX_IM_COMPONENT_CALL_ERROR_NO_HTTPS'] }}</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-if=\"noSignalFromCamera\">\n\t\t\t\t<div class=\"bx-im-component-call-error-container\">\n\t\t\t\t\t<div class=\"bx-im-component-call-error-content\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-text\">{{ localize['BX_IM_COMPONENT_CALL_ERROR_NO_SIGNAL_FROM_CAMERA'] }}</div>\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-buttons\">\n\t\t\t\t\t\t\t<button @click=\"reloadPage\" class=\"ui-btn ui-btn-sm ui-btn-no-caps\">{{ localize['BX_IM_COMPONENT_CALL_BUTTON_RELOAD'] }}</button>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-if=\"userLeftCall\">\n\t\t\t\t<template v-if=\"!callFeedbackSent && showFeedback\">\n\t\t\t\t\t<bx-im-component-call-feedback @feedbackSent=\"onFeedbackSent\" :callDetails=\"callDetails\" :darkMode=\"true\"/>\n\t\t\t\t</template>\n\t\t\t\t<template v-else>\n\t\t\t\t\t<div class=\"bx-im-component-call-error-container\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-error-content\">\n\t\t\t\t\t\t\t<div class=\"bx-im-component-call-error-text\">{{ localize['BX_IM_COMPONENT_CALL_ERROR_USER_LEFT_THE_CALL'] }}</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t</template>\n\t\t</div>\n\t"
	};

	var OrientationDisabled = {
	  template: "\n\t\t<div class=\"bx-im-component-call-orientation-disabled-wrap\">\n\t\t\t<div class=\"bx-im-component-call-orientation-disabled-icon\"></div>\n\t\t\t<div class=\"bx-im-component-call-orientation-disabled-text\">\n\t\t\t\t{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_ROTATE_DEVICE') }}\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var PasswordCheck = {
	  data: function data() {
	    return {
	      password: '',
	      checkingPassword: '',
	      wrongPassword: ''
	    };
	  },
	  created: function created() {
	    main_core_events.EventEmitter.subscribe(im_const.EventType.conference.setPasswordFocus, this.onSetPasswordFocus);
	  },
	  beforeDestroy: function beforeDestroy() {
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.conference.setPasswordFocus, this.onSetPasswordFocus);
	  },
	  computed: _objectSpread$1({
	    conferenceTitle: function conferenceTitle() {
	      return this.conference.common.conferenceTitle;
	    },
	    localize: function localize() {
	      return ui_vue.BitrixVue.getFilteredPhrases('BX_IM_COMPONENT_CALL_');
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    conference: function conference(state) {
	      return state.conference;
	    }
	  })),
	  methods: {
	    onSetPasswordFocus: function onSetPasswordFocus() {
	      this.$refs['passwordInput'].focus();
	    },
	    checkPassword: function checkPassword() {
	      var _this = this;
	      if (!this.password || this.checkingPassword) {
	        this.wrongPassword = true;
	        return false;
	      }
	      this.checkingPassword = true;
	      this.wrongPassword = false;
	      this.getApplication().checkPassword(this.password)["catch"](function () {
	        _this.wrongPassword = true;
	      })["finally"](function () {
	        _this.checkingPassword = false;
	      });
	    },
	    getApplication: function getApplication() {
	      return this.$Bitrix.Application.get();
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div>\n\t\t\t<div class=\"bx-im-component-call-info-container\">\n\t\t\t\t<div class=\"bx-im-component-call-info-logo\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-info-title\">{{ conferenceTitle }}</div>\n\t\t\t</div>\n\t\t\t<div class=\"bx-im-component-call-password-container\">\n\t\t\t\t<template v-if=\"wrongPassword\">\n\t\t\t\t\t<div class=\"bx-im-component-call-password-error\">\n\t\t\t\t\t\t{{ localize['BX_IM_COMPONENT_CALL_PASSWORD_WRONG'] }}\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t\t<template v-else>\n\t\t\t\t\t<div class=\"bx-im-component-call-password-title\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-password-title-logo\"></div>\n\t\t\t\t\t\t<div class=\"bx-im-component-call-password-title-text\">\n\t\t\t\t\t\t\t{{ localize['BX_IM_COMPONENT_CALL_PASSWORD_TITLE'] }}\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t\t<input\n\t\t\t\t\t@keyup.enter=\"checkPassword\"\n\t\t\t\t\ttype=\"text\"\n\t\t\t\t\tv-model=\"password\"\n\t\t\t\t\tclass=\"bx-im-component-call-password-input\"\n\t\t\t\t\t:placeholder=\"localize['BX_IM_COMPONENT_CALL_PASSWORD_PLACEHOLDER']\"\n\t\t\t\t\tref=\"passwordInput\"\n\t\t\t\t/>\n\t\t\t\t<button @click=\"checkPassword\" class=\"ui-btn ui-btn-sm ui-btn-primary bx-im-component-call-password-button\">\n\t\t\t  \t\t{{ localize['BX_IM_COMPONENT_CALL_PASSWORD_JOIN'] }}\n\t\t\t\t</button>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	var LoadingStatus = {
	  computed: {
	    localize: function localize() {
	      return ui_vue.BitrixVue.getFilteredPhrases('BX_IM_COMPONENT_CALL_');
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"bx-im-component-call-loading\">\n\t\t\t<div class=\"bx-im-component-call-loading-text\">{{ localize['BX_IM_COMPONENT_CALL_LOADING'] }}</div>\n\t\t</div>\n\t"
	};

	var NOT_ALLOWED_ERROR_CODE = 'NotAllowedError';
	var NOT_FOUND_ERROR_CODE = 'NotFoundError';
	var RequestPermissions = {
	  props: {
	    skipRequest: {
	      type: Boolean,
	      required: false,
	      "default": false
	    }
	  },
	  created: function created() {
	    main_core_events.EventEmitter.subscribe(im_const.EventType.conference.requestPermissions, this.onRequestPermissions);
	  },
	  beforeDestroy: function beforeDestroy() {
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.conference.requestPermissions, this.onRequestPermissions);
	  },
	  computed: {
	    localize: function localize() {
	      return ui_vue.BitrixVue.getFilteredPhrases('BX_IM_COMPONENT_CALL_');
	    }
	  },
	  methods: {
	    onRequestPermissions: function onRequestPermissions() {
	      this.requestPermissions();
	    },
	    requestPermissions: function requestPermissions() {
	      var _this = this;
	      this.getApplication().initHardware().then(function () {
	        return navigator.mediaDevices.getUserMedia({
	          audio: true,
	          video: true
	        });
	      }).then(function () {
	        _this.setPermissionsRequestedFlag();
	      })["catch"](function (error) {
	        if (error.name === NOT_ALLOWED_ERROR_CODE) {
	          _this.showMessageBox(_this.localize['BX_IM_COMPONENT_CALL_NOT_ALLOWED_ERROR']);
	          return false;
	        } else if (error.name === NOT_FOUND_ERROR_CODE) {
	          // means there is no camera, request only microphone
	          return navigator.mediaDevices.getUserMedia({
	            audio: true,
	            video: false
	          }).then(function () {
	            _this.setPermissionsRequestedFlag();
	          })["catch"](function (error) {
	            if (error.name === NOT_ALLOWED_ERROR_CODE) {
	              _this.showMessageBox(_this.localize['BX_IM_COMPONENT_CALL_NOT_ALLOWED_ERROR']);
	              return false;
	            }
	          });
	        }
	        _this.showMessageBox(_this.localize['BX_IM_COMPONENT_CALL_HARDWARE_ERROR']);
	      });
	    },
	    setPermissionsRequestedFlag: function setPermissionsRequestedFlag() {
	      var _this2 = this;
	      this.$nextTick(function () {
	        return _this2.$store.dispatch('conference/setPermissionsRequested', {
	          status: true
	        });
	      });
	    },
	    showMessageBox: function showMessageBox(text) {
	      ui_dialogs_messagebox.MessageBox.show({
	        message: text,
	        modal: true,
	        buttons: ui_dialogs_messagebox.MessageBoxButtons.OK
	      });
	    },
	    getApplication: function getApplication() {
	      return this.$Bitrix.Application.get();
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"bx-im-component-call-permissions-container\">\n\t\t\t<template v-if=\"!skipRequest\">\n\t\t\t\t<div class=\"bx-im-component-call-permissions-text\">{{ localize['BX_IM_COMPONENT_CALL_PERMISSIONS_TEXT'] }}</div>\n\t\t\t\t<button @click=\"requestPermissions\" class=\"ui-btn ui-btn-sm ui-btn-primary bx-im-component-call-permissions-button\">\n\t\t\t\t\t{{ localize['BX_IM_COMPONENT_CALL_ENABLE_DEVICES_BUTTON'] }}\n\t\t\t\t</button>\n\t\t\t\t<slot></slot>\n\t\t\t</template>\n\t\t\t<template v-else>\n\t\t\t\t<div class=\"bx-im-component-call-permissions-text\">{{ localize['BX_IM_COMPONENT_CALL_PERMISSIONS_LOADING'] }}</div>\n\t\t\t\t<button class=\"ui-btn ui-btn-sm ui-btn-wait bx-im-component-call-permissions-button\">\n\t\t\t\t\t{{ localize['BX_IM_COMPONENT_CALL_PERMISSIONS_BUTTON'] }}\n\t\t\t\t</button>\n\t\t\t</template>\n\t\t</div>\n\t"
	};

	function ownKeys$2(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$2(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$2(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$2(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var MobileChatButton = {
	  computed: _objectSpread$2({
	    dialogCounter: function dialogCounter() {
	      if (this.dialog) {
	        return this.dialog.counter;
	      }
	    },
	    userCounter: function userCounter() {
	      return this.conference.common.userCount;
	    },
	    localize: function localize() {
	      return ui_vue.BitrixVue.getFilteredPhrases('BX_IM_COMPONENT_CALL_');
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    dialog: function dialog(state) {
	      return state.dialogues.collection[state.application.dialog.dialogId];
	    },
	    conference: function conference(state) {
	      return state.conference;
	    }
	  })),
	  methods: {
	    openChat: function openChat() {
	      this.getApplication().toggleChat();
	    },
	    openUserList: function openUserList() {
	      this.getApplication().toggleUserList();
	    },
	    getApplication: function getApplication() {
	      return this.$Bitrix.Application.get();
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-component-call-open-chat-button-container\">\n\t\t\t<div @click=\"openChat\" class=\"ui-btn ui-btn-sm ui-btn-icon-chat bx-im-component-call-open-chat-button\">\n\t\t\t\t{{ localize['BX_IM_COMPONENT_CALL_OPEN_CHAT'] }}\n\t\t\t\t<div v-if=\"dialogCounter > 0\" class=\"bx-im-component-call-open-chat-button-counter\">{{ dialogCounter }}</div>\n\t\t\t</div>\n\t\t\t\n\t\t\t<div @click=\"openUserList\" class=\"ui-btn ui-btn-sm ui-btn-icon-chat bx-im-component-call-open-user-list-button\">\n\t\t\t\t{{ localize['BX_IM_COMPONENT_CALL_OPEN_USER_LIST'] }}\n\t\t\t\t<div class=\"bx-im-component-call-open-chat-button-counter\">{{ userCounter }}</div>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	function ownKeys$3(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$3(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$3(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$3(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var ConferenceInfo = {
	  props: {
	    compactMode: {
	      type: Boolean,
	      required: false,
	      "default": false
	    }
	  },
	  data: function data() {
	    return {
	      conferenceDuration: '',
	      durationInterval: null
	    };
	  },
	  created: function created() {
	    var _this = this;
	    if (this.conferenceStarted) {
	      this.updateConferenceDuration();
	      this.durationInterval = setInterval(function () {
	        _this.updateConferenceDuration();
	      }, 1000);
	    }
	  },
	  beforeDestroy: function beforeDestroy() {
	    clearInterval(this.durationInterval);
	  },
	  computed: _objectSpread$3({
	    conferenceStarted: function conferenceStarted() {
	      return this.conference.common.conferenceStarted;
	    },
	    conferenceStartDate: function conferenceStartDate() {
	      return this.conference.common.conferenceStartDate;
	    },
	    conferenceTitle: function conferenceTitle() {
	      return this.conference.common.conferenceTitle;
	    },
	    userId: function userId() {
	      return this.application.common.userId;
	    },
	    isBroadcast: function isBroadcast() {
	      return this.conference.common.isBroadcast;
	    },
	    presentersList: function presentersList() {
	      return this.conference.common.presenters;
	    },
	    presentersInfo: function presentersInfo() {
	      return this.$store.getters['users/getList'](this.presentersList);
	    },
	    formattedPresentersList: function formattedPresentersList() {
	      var presentersCount = this.presentersList.length;
	      var prefix = presentersCount > 1 ? this.localize['BX_IM_COMPONENT_CALL_SPEAKERS_MULTIPLE'] : this.localize['BX_IM_COMPONENT_CALL_SPEAKER'];
	      var presenters = this.presentersInfo.map(function (user) {
	        return user.name;
	      }).join(', ');
	      return "".concat(prefix, ": ").concat(presenters);
	    },
	    isCurrentUserPresenter: function isCurrentUserPresenter() {
	      return this.presentersList.includes(this.userId);
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
	    conferenceStatusClasses: function conferenceStatusClasses() {
	      return ['bx-im-component-call-info-status', this.conferenceStarted ? 'bx-im-component-call-info-status-active' : 'bx-im-component-call-info-status-not-active'];
	    },
	    containerClasses: function containerClasses() {
	      return [this.compactMode ? 'bx-im-component-call-info-container-compact' : 'bx-im-component-call-info-container'];
	    },
	    localize: function localize() {
	      return ui_vue.BitrixVue.getFilteredPhrases('BX_IM_COMPONENT_CALL_');
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    conference: function conference(state) {
	      return state.conference;
	    }
	  })),
	  watch: {
	    conferenceStarted: function conferenceStarted(newValue) {
	      var _this2 = this;
	      if (newValue === true) {
	        this.durationInterval = setInterval(function () {
	          _this2.updateConferenceDuration();
	        }, 1000);
	      }
	      this.updateConferenceDuration();
	    }
	  },
	  methods: {
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
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div :class=\"containerClasses\">\n\t\t\t<template v-if=\"compactMode\">\n\t\t\t\t<div class=\"bx-im-component-call-info-title-container\">\n\t\t\t\t\t<div class=\"bx-im-component-call-info-logo\"></div>\n\t\t\t\t\t<div class=\"bx-im-component-call-info-title\">{{ conferenceTitle }}</div>\n\t\t\t\t</div>\n\t\t\t\t<div v-if=\"isBroadcast\" class=\"bx-im-component-call-info-speakers\">{{ formattedPresentersList }}</div>\n\t\t\t</template>\n\t\t\t<template v-else>\n\t\t\t\t<div class=\"bx-im-component-call-info-logo\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-info-title\">{{ conferenceTitle }}</div>\n\t\t\t  \t<div v-if=\"isBroadcast\" class=\"bx-im-component-call-info-speakers\">{{ formattedPresentersList }}</div>\t\n\t\t\t</template>\n\t\t\t<div :class=\"conferenceStatusClasses\">{{ conferenceStatusText }}</div>\n\t\t</div>\n\t"
	};

	function ownKeys$4(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$4(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$4(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$4(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var UserForm = {
	  data: function data() {
	    return {
	      userNewName: ''
	    };
	  },
	  computed: _objectSpread$4({
	    conferenceStarted: function conferenceStarted() {
	      return this.conference.common.conferenceStarted;
	    },
	    userHasRealName: function userHasRealName() {
	      if (this.user) {
	        return this.user.name !== this.localize['BX_IM_COMPONENT_CALL_DEFAULT_USER_NAME'];
	      }
	      return false;
	    },
	    intranetAvatarStyle: function intranetAvatarStyle() {
	      if (this.user && !this.user.extranet && this.user.avatar) {
	        return {
	          backgroundImage: "url('".concat(this.user.avatar, "')")
	        };
	      }
	      return '';
	    },
	    logoutLink: function logoutLink() {
	      return "".concat(this.publicLink, "?logout=yes&sessid=").concat(BX.bitrix_sessid());
	    },
	    publicLink: function publicLink() {
	      if (this.dialog) {
	        return this.dialog["public"].link;
	      }
	    },
	    userId: function userId() {
	      return this.application.common.userId;
	    },
	    isBroadcast: function isBroadcast() {
	      return this.conference.common.isBroadcast;
	    },
	    presentersList: function presentersList() {
	      return this.conference.common.presenters;
	    },
	    isCurrentUserPresenter: function isCurrentUserPresenter() {
	      return this.presentersList.includes(this.userId);
	    },
	    localize: function localize() {
	      return ui_vue.BitrixVue.getFilteredPhrases('BX_IM_COMPONENT_CALL_');
	    },
	    videoModeButtonClasses: function videoModeButtonClasses() {
	      var classes = ['ui-btn', 'ui-btn-sm', 'ui-btn-primary', 'bx-im-component-call-join-video'];
	      if (!this.getApplication().hardwareInited) {
	        classes.push('ui-btn-disabled');
	      }
	      return classes;
	    },
	    audioModeButtonClasses: function audioModeButtonClasses() {
	      var classes = ['ui-btn', 'ui-btn-sm', 'bx-im-component-call-join-audio'];
	      if (!this.getApplication().hardwareInited) {
	        classes.push('ui-btn-disabled');
	      }
	      return classes;
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    user: function user(state) {
	      return state.users.collection[state.application.common.userId];
	    },
	    application: function application(state) {
	      return state.application;
	    },
	    conference: function conference(state) {
	      return state.conference;
	    }
	  })),
	  methods: {
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
	        main_core_events.EventEmitter.emit(im_const.EventType.conference.waitForStart);
	        this.getApplication().setUserReadyToJoin();
	        this.getApplication().setJoinType(video);
	      } else {
	        var viewerMode = this.isBroadcast && !this.isCurrentUserPresenter;
	        im_lib_logger.Logger.warn('ready to join call', video, viewerMode);
	        if (viewerMode) {
	          this.getApplication().joinCall(this.getApplication().preCall.id, {
	            joinAsViewer: true
	          });
	        } else {
	          this.getApplication().startCall(video);
	        }
	      }
	    },
	    setNewName: function setNewName() {
	      if (this.userNewName.length > 0) {
	        this.getApplication().renameGuest(this.userNewName.trim());
	      }
	    },
	    getApplication: function getApplication() {
	      return this.$Bitrix.Application.get();
	    },
	    isDesktop: function isDesktop() {
	      return im_lib_utils.Utils.platform.isBitrixDesktop();
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-component-call-form\">\n\t\t\t<template v-if=\"user && userHasRealName\">\n\t\t\t\t<template v-if=\"!user.extranet\">\n\t\t\t\t\t<div class=\"bx-im-component-call-intranet-name-container\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-intranet-name-title\">\n\t\t\t\t\t\t\t{{ localize['BX_IM_COMPONENT_CALL_INTRANET_NAME_TITLE'] }}\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"bx-im-component-call-intranet-name-content\">\n\t\t\t\t\t\t\t<div class=\"bx-im-component-call-intranet-name-content-left\">\n\t\t\t\t\t\t\t\t<div :style=\"intranetAvatarStyle\" class=\"bx-im-component-call-intranet-name-avatar\"></div>\n\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-intranet-name-text\">{{ user.name }}</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<template v-if=\"!isDesktop()\">\n\t\t\t\t\t\t\t\t<a :href=\"logoutLink\" class=\"bx-im-component-call-intranet-name-logout\">\n\t\t\t\t\t\t\t\t\t{{ localize['BX_IM_COMPONENT_CALL_INTRANET_LOGOUT'] }}\n\t\t\t\t\t\t\t\t</a>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t\t<template v-else-if=\"user.extranet\">\n\t\t\t\t\t<div class=\"bx-im-component-call-guest-name-container\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-guest-name-text\">{{ user.name }}</div>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t</template>\n\t\t\t<!-- New guest, need to specify name -->\n\t\t\t<template v-else-if=\"user && !userHasRealName\">\n\t\t\t\t<input\n\t\t\t\t\tv-model=\"userNewName\"\n\t\t\t\t\ttype=\"text\"\n\t\t\t\t\t:placeholder=\"localize['BX_IM_COMPONENT_CALL_NAME_PLACEHOLDER']\"\n\t\t\t\t\tclass=\"bx-im-component-call-name-input\"\n\t\t\t\t\tref=\"nameInput\"\n\t\t\t\t/>\n\t\t\t</template>\n\t\t\t<!-- Buttons -->\n\t\t\t<template v-if=\"user\">\n\t\t\t\t<!-- Broadcast mode -->\n\t\t\t\t<template v-if=\"isBroadcast\">\n\t\t\t\t\t<!-- Speaker can start conference -->\n\t\t\t\t\t<template v-if=\"isCurrentUserPresenter && !conferenceStarted\">\n\t\t\t\t\t\t<button\n\t\t\t\t\t\t\t@click=\"startConference({video: true})\"\n\t\t\t\t\t\t\t:class=\"videoModeButtonClasses\"\n\t\t\t\t\t\t>\n\t\t\t\t\t\t\t{{ localize['BX_IM_COMPONENT_CALL_START_WITH_VIDEO'] }}\n\t\t\t\t\t\t</button>\n\t\t\t\t\t\t<button\n\t\t\t\t\t\t\t@click=\"startConference({video: false})\"\n\t\t\t\t\t\t\t:class=\"audioModeButtonClasses\"\n\t\t\t\t\t\t>\n\t\t\t\t\t\t\t{{ localize['BX_IM_COMPONENT_CALL_START_WITH_AUDIO'] }}\n\t\t\t\t\t\t</button>\n\t\t\t\t\t</template>\n\t\t\t\t\t<!-- Speakers can join with audio/video -->\n\t\t\t\t\t<template v-else-if=\"conferenceStarted && isCurrentUserPresenter\">\n\t\t\t\t\t\t<button\n\t\t\t\t\t\t\t@click=\"joinConference({video: true})\"\n\t\t\t\t\t\t\t:class=\"videoModeButtonClasses\"\n\t\t\t\t\t\t>\n\t\t\t\t\t\t\t{{ localize['BX_IM_COMPONENT_CALL_JOIN_WITH_VIDEO'] }}\n\t\t\t\t\t\t</button>\n\t\t\t\t\t\t<button\n\t\t\t\t\t\t\t@click=\"joinConference({video: false})\"\n\t\t\t\t\t\t\t:class=\"audioModeButtonClasses\"\n\t\t\t\t\t\t>\n\t\t\t\t\t\t\t{{ localize['BX_IM_COMPONENT_CALL_JOIN_WITH_AUDIO'] }}\n\t\t\t\t\t\t</button>\n\t\t\t\t\t</template>\n\t\t\t\t\t<!-- Others can join as viewers -->\n\t\t\t\t\t<template v-else-if=\"!isCurrentUserPresenter\">\n\t\t\t\t\t\t<button\n\t\t\t\t\t\t\t@click=\"joinConference({video: false})\"\n\t\t\t\t\t\t\tclass=\"ui-btn ui-btn-sm ui-btn-primary bx-im-component-call-join-video\"\n\t\t\t\t\t\t>\n\t\t\t\t\t\t\t{{ localize['BX_IM_COMPONENT_CALL_JOIN'] }}\n\t\t\t\t\t\t</button>\n\t\t\t\t\t</template>\n\t\t\t\t</template>\n\t\t\t\t<!-- End broadcast mode -->\n\t\t\t\t<template v-else-if=\"!isBroadcast\">\n\t\t\t\t\t<!-- Intranet user can start conference -->\n\t\t\t\t\t<template v-if=\"!user.extranet && !conferenceStarted\">\n\t\t\t\t\t\t<button\n\t\t\t\t\t\t\t@click=\"startConference({video: true})\"\n\t\t\t\t\t\t\t:class=\"videoModeButtonClasses\"\n\t\t\t\t\t\t>\n\t\t\t\t\t\t\t{{ localize['BX_IM_COMPONENT_CALL_START_WITH_VIDEO'] }}\n\t\t\t\t\t\t</button>\n\t\t\t\t\t\t<button\n\t\t\t\t\t\t\t@click=\"startConference({video: false})\"\n\t\t\t\t\t\t\t:class=\"audioModeButtonClasses\"\n\t\t\t\t\t\t>\n\t\t\t\t\t\t\t{{ localize['BX_IM_COMPONENT_CALL_START_WITH_AUDIO'] }}\n\t\t\t\t\t\t</button>\n\t\t\t\t\t</template>\n\t\t\t\t\t<!-- Others can join -->\n\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t<button\n\t\t\t\t\t\t\t@click=\"joinConference({video: true})\"\n\t\t\t\t\t\t\t:class=\"videoModeButtonClasses\"\n\t\t\t\t\t\t>\n\t\t\t\t\t\t\t{{ localize['BX_IM_COMPONENT_CALL_JOIN_WITH_VIDEO'] }}\n\t\t\t\t\t\t</button>\n\t\t\t\t\t\t<button\n\t\t\t\t\t\t\t@click=\"joinConference({video: false})\"\n\t\t\t\t\t\t\t:class=\"audioModeButtonClasses\"\n\t\t\t\t\t\t>\n\t\t\t\t\t\t\t{{ localize['BX_IM_COMPONENT_CALL_JOIN_WITH_AUDIO'] }}\n\t\t\t\t\t\t</button>\n\t\t\t\t\t</template>\n\t\t\t\t</template>\n\t\t\t</template>\n\t\t\t<!--End normal (not broadcast) mode-->\n\t\t</div>\n\t"
	};

	function ownKeys$5(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$5(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$5(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$5(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var ChatHeader = {
	  created: function created() {
	    this.desktop = new im_lib_desktop.Desktop();
	  },
	  computed: _objectSpread$5({
	    showTotalCounter: function showTotalCounter() {
	      return im_lib_utils.Utils.platform.isBitrixDesktop() && (this.desktop.getApiVersion() >= 60 || !im_lib_utils.Utils.platform.isWindows()) && !this.getApplication().isExternalUser() && this.messageCount > 0;
	    },
	    messageCount: function messageCount() {
	      return this.conference.common.messageCount;
	    },
	    formattedCounter: function formattedCounter() {
	      return this.messageCount > 99 ? '99+' : this.messageCount;
	    },
	    localize: function localize() {
	      return ui_vue.BitrixVue.getFilteredPhrases('BX_IM_COMPONENT_CALL_');
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    conference: function conference(state) {
	      return state.conference;
	    }
	  })),
	  methods: {
	    onCloseChat: function onCloseChat() {
	      this.getApplication().toggleChat();
	    },
	    onTotalCounterClick: function onTotalCounterClick() {
	      if (opener && opener.BXDesktopWindow) {
	        opener.BXDesktopWindow.ExecuteCommand('show.active');
	      }
	    },
	    getApplication: function getApplication() {
	      return this.$Bitrix.Application.get();
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-component-call-right-header\">\n\t\t\t<div class=\"bx-im-component-call-right-header-left\">\n\t\t\t\t<div @click=\"onCloseChat\" class=\"bx-im-component-call-right-header-close\" :title=\"localize['BX_IM_COMPONENT_CALL_CHAT_CLOSE_TITLE']\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-right-header-title\">{{ localize['BX_IM_COMPONENT_CALL_CHAT_TITLE'] }}</div>\n\t\t\t</div>\n\t\t\t<template v-if=\"showTotalCounter\">\n\t\t\t\t<div @click=\"onTotalCounterClick\" class=\"bx-im-component-call-right-header-right bx-im-component-call-right-header-all-chats\">\n\t\t\t\t\t<div class=\"bx-im-component-call-right-header-all-chats-title\">{{ localize['BX_IM_COMPONENT_CALL_ALL_CHATS'] }}</div>\n\t\t\t\t\t<div class=\"bx-im-component-call-right-header-all-chats-counter\">{{ messageCount }}</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t</div>\n\t"
	};

	function ownKeys$6(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$6(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$6(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$6(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var WaitingForStart = {
	  computed: _objectSpread$6({
	    userCounter: function userCounter() {
	      return this.dialog.userCounter;
	    },
	    localize: function localize() {
	      return ui_vue.BitrixVue.getFilteredPhrases('BX_IM_COMPONENT_CALL_');
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    conference: function conference(state) {
	      return state.conference;
	    },
	    dialog: function dialog(state) {
	      return state.dialogues.collection[state.application.dialog.dialogId];
	    }
	  })),
	  // language=Vue
	  template: "\n\t\t<div class=\"bx-im-component-call-wait-container\">\n\t\t\t<div class=\"bx-im-component-call-wait-logo\"></div>\n\t\t\t<div class=\"bx-im-component-call-wait-title\">{{ localize['BX_IM_COMPONENT_CALL_WAIT_START_TITLE'] }}</div>\n\t\t\t<div class=\"bx-im-component-call-wait-user-counter\">\n\t\t\t\t{{ localize['BX_IM_COMPONENT_CALL_WAIT_START_USER_COUNT'] }} {{ userCounter }}\n\t\t\t</div>\n\t\t\t<slot></slot>\n\t\t</div>\n\t"
	};

	function ownKeys$7(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$7(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$7(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$7(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var UserListItem = {
	  props: {
	    userId: {
	      type: Number,
	      required: true
	    }
	  },
	  data: function data() {
	    return {
	      renameMode: false,
	      newName: '',
	      renameRequested: false,
	      menuId: 'bx-messenger-context-popup-external-data',
	      onlineStates: [im_const.ConferenceUserState.Ready, im_const.ConferenceUserState.Connected]
	    };
	  },
	  computed: _objectSpread$7({
	    user: function user() {
	      return this.$store.getters['users/get'](this.userId, true);
	    },
	    // statuses
	    currentUser: function currentUser() {
	      return this.application.common.userId;
	    },
	    chatOwner: function chatOwner() {
	      if (!this.dialog) {
	        return 0;
	      }
	      return this.dialog.ownerId;
	    },
	    isCurrentUserOwner: function isCurrentUserOwner() {
	      return this.chatOwner === this.currentUser;
	    },
	    isCurrentUserExternal: function isCurrentUserExternal() {
	      return !!this.conference.user.hash;
	    },
	    isMobile: function isMobile() {
	      return im_lib_utils.Utils.device.isMobile();
	    },
	    isDesktop: function isDesktop() {
	      return im_lib_utils.Utils.platform.isBitrixDesktop();
	    },
	    isGuestWithDefaultName: function isGuestWithDefaultName() {
	      var guestDefaultName = this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_DEFAULT_USER_NAME');
	      return this.user.id === this.currentUser && this.user.extranet && this.user.name === guestDefaultName;
	    },
	    userCallStatus: function userCallStatus() {
	      return this.$store.getters['call/getUser'](this.user.id);
	    },
	    isUserInCall: function isUserInCall() {
	      return this.onlineStates.includes(this.userCallStatus.state);
	    },
	    userInCallCount: function userInCallCount() {
	      var _this = this;
	      var usersInCall = Object.values(this.call.users).filter(function (user) {
	        return _this.onlineStates.includes(user.state);
	      });
	      return usersInCall.length;
	    },
	    isBroadcast: function isBroadcast() {
	      return this.conference.common.isBroadcast;
	    },
	    presentersList: function presentersList() {
	      return this.conference.common.presenters;
	    },
	    isUserPresenter: function isUserPresenter() {
	      return this.presentersList.includes(this.user.id);
	    },
	    // end statuses
	    formattedSubtitle: function formattedSubtitle() {
	      var subtitles = [];
	      if (this.user.id === this.chatOwner) {
	        subtitles.push(this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_STATUS_OWNER'));
	      }
	      if (this.user.id === this.currentUser) {
	        subtitles.push(this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_STATUS_CURRENT_USER'));
	      }

	      // if (!this.user.extranet && !this.user.isOnline)
	      // {
	      // 	subtitles.push(this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_STATUS_OFFLINE'));
	      // }

	      return subtitles.join(', ');
	    },
	    isMenuNeeded: function isMenuNeeded() {
	      return this.getMenuItems.length > 0;
	    },
	    menuItems: function menuItems() {
	      var _this2 = this;
	      var items = [];
	      // for self
	      if (this.user.id === this.currentUser) {
	        // self-rename
	        if (this.isCurrentUserExternal) {
	          items.push({
	            text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_RENAME_SELF'),
	            onclick: function onclick() {
	              _this2.closeMenu();
	              _this2.onRenameStart();
	            }
	          });
	        }
	        // change background
	        if (this.isDesktop) {
	          items.push({
	            text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_CHANGE_BACKGROUND'),
	            onclick: function onclick() {
	              _this2.closeMenu();
	              _this2.$emit('userChangeBackground');
	            }
	          });
	        }
	      }
	      // for other users
	      else {
	        // force-rename
	        if (this.isCurrentUserOwner && this.user.externalAuthId === 'call') {
	          items.push({
	            text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_RENAME'),
	            onclick: function onclick() {
	              _this2.closeMenu();
	              _this2.onRenameStart();
	            }
	          });
	        }
	        // kick
	        if (this.isCurrentUserOwner && !this.isUserPresenter) {
	          items.push({
	            text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_KICK'),
	            onclick: function onclick() {
	              _this2.closeMenu();
	              _this2.$emit('userKick', {
	                user: _this2.user
	              });
	            }
	          });
	        }
	        if (this.isUserInCall && this.userCallStatus.cameraState && this.userInCallCount > 2) {
	          // pin
	          if (!this.userCallStatus.pinned) {
	            items.push({
	              text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_PIN'),
	              onclick: function onclick() {
	                _this2.closeMenu();
	                _this2.$emit('userPin', {
	                  user: _this2.user
	                });
	              }
	            });
	          }
	          // unpin
	          else {
	            items.push({
	              text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_UNPIN'),
	              onclick: function onclick() {
	                _this2.closeMenu();
	                _this2.$emit('userUnpin');
	              }
	            });
	          }
	        }
	        // open 1-1 chat and profile
	        if (this.isDesktop && !this.user.extranet) {
	          items.push({
	            text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_OPEN_CHAT'),
	            onclick: function onclick() {
	              _this2.closeMenu();
	              _this2.$emit('userOpenChat', {
	                user: _this2.user
	              });
	            }
	          });
	          items.push({
	            text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_OPEN_PROFILE'),
	            onclick: function onclick() {
	              _this2.closeMenu();
	              _this2.$emit('userOpenProfile', {
	                user: _this2.user
	              });
	            }
	          });
	        }
	        // insert name
	        items.push({
	          text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_MENU_INSERT_NAME'),
	          onclick: function onclick() {
	            _this2.closeMenu();
	            _this2.$emit('userInsertName', {
	              user: _this2.user
	            });
	          }
	        });
	      }
	      return items;
	    },
	    avatarWrapClasses: function avatarWrapClasses() {
	      var classes = ['bx-im-component-call-user-list-item-avatar-wrap'];
	      if (this.userCallStatus.talking) {
	        classes.push('bx-im-component-call-user-list-item-avatar-wrap-talking');
	      }
	      return classes;
	    },
	    avatarClasses: function avatarClasses() {
	      var classes = ['bx-im-component-call-user-list-item-avatar'];
	      if (!this.user.avatar && this.user.extranet) {
	        classes.push('bx-im-component-call-user-list-item-avatar-extranet');
	      } else if (!this.user.avatar && !this.user.extranet) {
	        classes.push('bx-im-component-call-user-list-item-avatar-default');
	      }
	      return classes;
	    },
	    avatarStyle: function avatarStyle() {
	      var style = {};
	      if (this.user.avatar) {
	        style.backgroundImage = "url('".concat(this.user.avatar, "')");
	      } else if (!this.user.avatar && !this.user.extranet) {
	        style.backgroundColor = this.user.color;
	      }
	      return style;
	    },
	    isCallStatusPanelNeeded: function isCallStatusPanelNeeded() {
	      if (this.isBroadcast) {
	        return this.conference.common.state === im_const.ConferenceStateType.call && this.isUserInCall && this.isUserPresenter;
	      } else {
	        return this.conference.common.state === im_const.ConferenceStateType.call && this.isUserInCall;
	      }
	    },
	    callLeftIconClasses: function callLeftIconClasses() {
	      var classes = ['bx-im-component-call-user-list-item-icons-icon bx-im-component-call-user-list-item-icons-left'];
	      if (this.userCallStatus.floorRequestState) {
	        classes.push('bx-im-component-call-user-list-item-icons-floor-request');
	      } else if (this.userCallStatus.screenState) {
	        classes.push('bx-im-component-call-user-list-item-icons-screen');
	      }
	      return classes;
	    },
	    callCenterIconClasses: function callCenterIconClasses() {
	      var classes = ['bx-im-component-call-user-list-item-icons-icon bx-im-component-call-user-list-item-icons-center'];
	      if (this.userCallStatus.microphoneState) {
	        classes.push('bx-im-component-call-user-list-item-icons-mic-on');
	      } else {
	        classes.push('bx-im-component-call-user-list-item-icons-mic-off');
	      }
	      return classes;
	    },
	    callRightIconClasses: function callRightIconClasses() {
	      var classes = ['bx-im-component-call-user-list-item-icons-icon bx-im-component-call-user-list-item-icons-right'];
	      if (this.userCallStatus.cameraState) {
	        classes.push('bx-im-component-call-user-list-item-icons-camera-on');
	      } else {
	        classes.push('bx-im-component-call-user-list-item-icons-camera-off');
	      }
	      return classes;
	    },
	    bodyClasses: function bodyClasses() {
	      var classes = ['bx-im-component-call-user-list-item-body'];
	      if (!this.isUserInCall) {
	        classes.push('bx-im-component-call-user-list-item-body-offline');
	      }
	      return classes;
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    application: function application(state) {
	      return state.application;
	    },
	    conference: function conference(state) {
	      return state.conference;
	    },
	    call: function call(state) {
	      return state.call;
	    },
	    dialog: function dialog(state) {
	      return state.dialogues.collection[state.application.dialog.dialogId];
	    }
	  })),
	  methods: {
	    openMenu: function openMenu() {
	      var _this3 = this;
	      if (this.menuPopup) {
	        this.closeMenu();
	        return false;
	      }

	      //menu for other items
	      var existingMenu = main_popup.MenuManager.getMenuById(this.menuId);
	      if (existingMenu) {
	        existingMenu.destroy();
	      }
	      this.menuPopup = main_popup.MenuManager.create({
	        id: this.menuId,
	        bindElement: this.$refs['user-menu'],
	        items: this.menuItems,
	        events: {
	          onPopupClose: function onPopupClose() {
	            return _this3.menuPopup.destroy();
	          },
	          onPopupDestroy: function onPopupDestroy() {
	            return _this3.menuPopup = null;
	          }
	        }
	      });
	      this.menuPopup.show();
	    },
	    closeMenu: function closeMenu() {
	      this.menuPopup.destroy();
	      this.menuPopup = null;
	    },
	    onRenameStart: function onRenameStart() {
	      var _this4 = this;
	      this.newName = this.user.name;
	      this.renameMode = true;
	      this.$nextTick(function () {
	        _this4.$refs['rename-input'].focus();
	        _this4.$refs['rename-input'].select();
	      });
	    },
	    onRenameKeyDown: function onRenameKeyDown(event) {
	      //enter
	      if (event.keyCode === 13) {
	        this.changeName();
	      }
	      //escape
	      else if (event.keyCode === 27) {
	        this.renameMode = false;
	      }
	    },
	    changeName: function changeName() {
	      var _this5 = this;
	      if (this.user.name === this.newName.trim() || this.newName === '') {
	        this.renameMode = false;
	        return false;
	      }
	      this.$emit('userChangeName', {
	        user: this.user,
	        newName: this.newName
	      });
	      this.$nextTick(function () {
	        _this5.renameMode = false;
	      });
	    },
	    onFocus: function onFocus(event) {
	      main_core_events.EventEmitter.emit(im_const.EventType.conference.userRenameFocus, event);
	    },
	    onBlur: function onBlur(event) {
	      main_core_events.EventEmitter.emit(im_const.EventType.conference.userRenameBlur, event);
	    }
	  },
	  //language=Vue
	  template: "\n\t\t<div class=\"bx-im-component-call-user-list-item\">\n\t\t\t<!-- Avatar -->\n\t\t\t<div :class=\"avatarWrapClasses\">\n\t\t\t\t<div :class=\"avatarClasses\" :style=\"avatarStyle\"></div>\n\t\t\t</div>\n\t\t\t<!-- Body -->\n\t\t\t<div :class=\"bodyClasses\">\n\t\t\t\t<!-- Introduce yourself blinking mode -->\n\t\t\t\t<template v-if=\"!renameMode && isGuestWithDefaultName\">\n\t\t\t\t\t<div class=\"bx-im-component-call-user-list-item-body-left\">\n\t\t\t\t\t\t<div @click=\"onRenameStart\" class=\"bx-im-component-call-user-list-introduce-yourself\">\n\t\t\t\t\t\t\t<div class=\"bx-im-component-call-user-list-introduce-yourself-text\">{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_INTRODUCE_YOURSELF') }}</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t\t<!-- Rename mode -->\n\t\t\t\t<template v-else-if=\"renameMode\">\n\t\t\t\t\t<div class=\"bx-im-component-call-user-list-item-body-left\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-user-list-change-name-container\">\n\t\t\t\t\t\t\t<div @click=\"renameMode = false\" class=\"bx-im-component-call-user-list-change-name-cancel\"></div>\n\t\t\t\t\t\t\t<input @keydown=\"onRenameKeyDown\" @focus=\"onFocus\" @blur=\"onBlur\" v-model=\"newName\" :ref=\"'rename-input'\" type=\"text\" class=\"bx-im-component-call-user-list-change-name-input\">\n\t\t\t\t\t\t\t<div v-if=\"!renameRequested\" @click=\"changeName\" class=\"bx-im-component-call-user-list-change-name-confirm\"></div>\n\t\t\t\t\t\t\t<div v-else class=\"bx-im-component-call-user-list-change-name-loader\">\n\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-user-list-change-name-loader-icon\"></div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t\t<template v-if=\"!renameMode && !isGuestWithDefaultName\">\n\t\t\t\t\t<div class=\"bx-im-component-call-user-list-item-body-left\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-user-list-item-name-wrap\">\n\t\t\t\t\t\t\t<!-- Name -->\n\t\t\t\t\t\t\t<div class=\"bx-im-component-call-user-list-item-name\">{{ user.name }}</div>\n\t\t\t\t\t\t\t<!-- Status subtitle -->\n\t\t\t\t\t\t\t<div v-if=\"formattedSubtitle !== ''\" class=\"bx-im-component-call-user-list-item-name-subtitle\">{{ formattedSubtitle }}</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<!-- Context menu icon -->\n\t\t\t\t\t\t<div v-if=\"menuItems.length > 0 && !isMobile\" @click=\"openMenu\" ref=\"user-menu\" class=\"bx-im-component-call-user-list-item-menu\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t\t<template v-if=\"isCallStatusPanelNeeded\">\n\t\t\t\t\t<div class=\"bx-im-component-call-user-list-item-icons\">\n\t\t\t\t\t\t<div :class=\"callLeftIconClasses\"></div>\n\t\t\t\t\t\t<div :class=\"callCenterIconClasses\"></div>\n\t\t\t\t\t\t<div :class=\"callRightIconClasses\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	function ownKeys$8(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$8(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$8(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$8(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var UserList = {
	  components: {
	    UserListItem: UserListItem
	  },
	  data: function data() {
	    return {
	      usersPerPage: 50,
	      firstPageLoaded: false,
	      pagesLoaded: 0,
	      hasMoreToLoad: true,
	      rename: {
	        user: 0,
	        newName: '',
	        renameRequested: false
	      }
	    };
	  },
	  created: function created() {
	    im_lib_logger.Logger.warn('Conference: user list created');
	    this.requestUsers({
	      firstPage: true
	    });
	  },
	  beforeDestroy: function beforeDestroy() {
	    this.loaderObserver = null;
	  },
	  computed: _objectSpread$8({
	    userId: function userId() {
	      return this.application.common.userId;
	    },
	    isBroadcast: function isBroadcast() {
	      return this.conference.common.isBroadcast;
	    },
	    usersList: function usersList() {
	      var _this = this;
	      var users = this.conference.common.users.filter(function (user) {
	        return !_this.presentersList.includes(user);
	      });
	      return babelHelpers.toConsumableArray(users).sort(this.userSortFunction);
	    },
	    presentersList: function presentersList() {
	      return babelHelpers.toConsumableArray(this.conference.common.presenters).sort(this.userSortFunction);
	    },
	    rightPanelMode: function rightPanelMode() {
	      return this.conference.common.rightPanelMode;
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    user: function user(state) {
	      return state.users.collection[state.application.common.userId];
	    },
	    application: function application(state) {
	      return state.application;
	    },
	    conference: function conference(state) {
	      return state.conference;
	    },
	    call: function call(state) {
	      return state.call;
	    },
	    dialog: function dialog(state) {
	      return state.dialogues.collection[state.application.dialog.dialogId];
	    }
	  })),
	  methods: {
	    requestUsers: function requestUsers() {
	      var _this2 = this;
	      var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
	        _ref$firstPage = _ref.firstPage,
	        firstPage = _ref$firstPage === void 0 ? false : _ref$firstPage;
	      this.$Bitrix.RestClient.get().callMethod('im.dialog.users.list', {
	        'DIALOG_ID': this.application.dialog.dialogId,
	        'LIMIT': this.usersPerPage,
	        'OFFSET': firstPage ? 0 : this.pagesLoaded * this.usersPerPage
	      }).then(function (result) {
	        im_lib_logger.Logger.warn('Conference: getting next user list result', result.data());
	        var users = result.data();
	        _this2.pagesLoaded++;
	        if (users.length < _this2.usersPerPage) {
	          _this2.hasMoreToLoad = false;
	        }
	        _this2.$store.dispatch('users/set', users);
	        var usersIds = users.map(function (user) {
	          return user.id;
	        });
	        return _this2.$store.dispatch('conference/setUsers', {
	          users: usersIds
	        });
	      }).then(function () {
	        if (firstPage) {
	          _this2.firstPageLoaded = true;
	        }
	      })["catch"](function (result) {
	        im_lib_logger.Logger.warn('Conference: error getting users list', result.error().ex);
	      });
	    },
	    onUserMenuKick: function onUserMenuKick(_ref2) {
	      var user = _ref2.user;
	      this.showUserKickConfirm(user);
	    },
	    showUserKickConfirm: function showUserKickConfirm(user) {
	      var _this3 = this;
	      if (this.userKickConfirm) {
	        this.userKickConfirm.close();
	      }
	      var confirmMessage = this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_KICK_INTRANET_USER_CONFIRM_TEXT');
	      if (user.extranet) {
	        confirmMessage = this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_KICK_GUEST_USER_CONFIRM_TEXT');
	      }
	      this.userKickConfirm = ui_dialogs_messagebox.MessageBox.create({
	        message: confirmMessage,
	        modal: true,
	        buttons: ui_dialogs_messagebox.MessageBoxButtons.OK_CANCEL,
	        onOk: function onOk() {
	          _this3.kickUser(user);
	          _this3.userKickConfirm.close();
	        },
	        onCancel: function onCancel() {
	          _this3.userKickConfirm.close();
	        }
	      });
	      this.userKickConfirm.show();
	    },
	    kickUser: function kickUser(user) {
	      var _this4 = this;
	      this.$store.dispatch('conference/removeUsers', {
	        users: [user.id]
	      });
	      this.$Bitrix.RestClient.get().callMethod('im.chat.user.delete', {
	        user_id: user.id,
	        chat_id: this.application.dialog.chatId
	      })["catch"](function (error) {
	        im_lib_logger.Logger.error('Conference: removing user from chat error', error);
	        _this4.$store.dispatch('conference/setUsers', {
	          users: [user.id]
	        });
	      });
	    },
	    onUserMenuInsertName: function onUserMenuInsertName(_ref3) {
	      var user = _ref3.user;
	      if (this.rightPanelMode === im_const.ConferenceRightPanelMode.hidden || this.rightPanelMode === im_const.ConferenceRightPanelMode.users) {
	        this.getApplication().toggleChat();
	      }
	      this.$nextTick(function () {
	        main_core_events.EventEmitter.emit(im_const.EventType.textarea.insertText, {
	          text: "".concat(user.name, ", "),
	          focus: true
	        });
	      });
	    },
	    onUserChangeName: function onUserChangeName(_ref4) {
	      var _this5 = this;
	      var user = _ref4.user,
	        newName = _ref4.newName;
	      var method = user.id === this.userId ? 'im.call.user.update' : 'im.call.user.force.rename';
	      var oldName = user.name;
	      this.$store.dispatch('users/update', {
	        id: user.id,
	        fields: {
	          name: newName,
	          lastActivityDate: new Date()
	        }
	      });
	      this.$Bitrix.RestClient.get().callMethod(method, {
	        name: newName,
	        chat_id: this.application.dialog.chatId,
	        user_id: user.id
	      }).then(function () {
	        im_lib_logger.Logger.warn('Conference: rename completed', user.id, newName);
	        if (oldName === _this5.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_DEFAULT_USER_NAME')) {
	          _this5.getApplication().setUserWasRenamed();
	        }
	      })["catch"](function (error) {
	        im_lib_logger.Logger.error('Conference: renaming error', error);
	        _this5.$store.dispatch('users/update', {
	          id: user.id,
	          fields: {
	            name: oldName,
	            lastActivityDate: new Date()
	          }
	        });
	      });
	    },
	    onUserMenuPin: function onUserMenuPin(_ref5) {
	      var user = _ref5.user;
	      this.getApplication().pinUser(user);
	    },
	    onUserMenuUnpin: function onUserMenuUnpin() {
	      this.getApplication().unpinUser();
	    },
	    onUserMenuChangeBackground: function onUserMenuChangeBackground() {
	      this.getApplication().changeBackground();
	    },
	    onUserMenuOpenChat: function onUserMenuOpenChat(_ref6) {
	      var user = _ref6.user;
	      this.getApplication().openChat(user);
	    },
	    onUserMenuOpenProfile: function onUserMenuOpenProfile(_ref7) {
	      var user = _ref7.user;
	      this.getApplication().openProfile(user);
	    },
	    // Helpers
	    getLoaderObserver: function getLoaderObserver() {
	      var _this6 = this;
	      var options = {
	        root: document.querySelector('.bx-im-component-call-right-users'),
	        threshold: 0.01
	      };
	      var callback = function callback(entries, observer) {
	        entries.forEach(function (entry) {
	          if (entry.isIntersecting && entry.intersectionRatio > 0.01) {
	            im_lib_logger.Logger.warn('Conference: UserList: I see loader! Load next page!');
	            _this6.requestUsers();
	          }
	        });
	      };
	      return new IntersectionObserver(callback, options);
	    },
	    userSortFunction: function userSortFunction(userA, userB) {
	      if (userA === this.userId) {
	        return -1;
	      }
	      if (userB === this.userId) {
	        return 1;
	      }
	      if (this.call.users[userA] && (this.call.users[userA].floorRequestState || this.call.users[userA].screenState)) {
	        return -1;
	      }
	      if (this.call.users[userB] && (this.call.users[userB].floorRequestState || this.call.users[userB].screenState)) {
	        return 1;
	      }
	      if (this.call.users[userA] && [im_const.ConferenceUserState.Ready, im_const.ConferenceUserState.Connected].includes(this.call.users[userA].state)) {
	        return -1;
	      }
	      if (this.call.users[userB] && [im_const.ConferenceUserState.Ready, im_const.ConferenceUserState.Connected].includes(this.call.users[userB].state)) {
	        return 1;
	      }
	      return 0;
	    },
	    getApplication: function getApplication() {
	      return this.$Bitrix.Application.get();
	    }
	  },
	  directives: {
	    'bx-im-directive-user-list-observer': {
	      inserted: function inserted(element, bindings, vnode) {
	        vnode.context.loaderObserver = vnode.context.getLoaderObserver();
	        vnode.context.loaderObserver.observe(element);
	        return true;
	      },
	      unbind: function unbind(element, bindings, vnode) {
	        if (vnode.context.loaderObserver) {
	          vnode.context.loaderObserver.unobserve(element);
	        }
	        return true;
	      }
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-component-call-user-list\">\n\t\t\t<!-- Loading first page -->\n\t\t\t<div v-if=\"!firstPageLoaded\" class=\"bx-im-component-call-user-list-loader\">\n\t\t\t\t<div class=\"bx-im-component-call-user-list-loader-icon\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-user-list-loader-text\">\n\t\t\t\t\t{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_LOADING_USERS') }}\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<!-- Loading completed -->\n\t\t\t<template v-else>\n\t\t\t\t<!-- Speakers list section (if broadcast) -->\n\t\t\t\t<template v-if=\"isBroadcast\">\n\t\t\t\t\t<!-- Speakers category title -->\n\t\t\t\t\t<div class=\"bx-im-component-call-user-list-category\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-user-list-category-text\">\n\t\t\t\t\t\t\t{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_CATEGORY_PRESENTERS') }}\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"bx-im-component-call-user-list-category-counter\">\n\t\t\t\t\t\t\t{{ presentersList.length }}\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<!-- Speakers list -->\n\t\t\t\t\t<div class=\"bx-im-component-call-user-list-items\">\n\t\t\t\t\t\t<template v-for=\"presenter in presentersList\">\n\t\t\t\t\t\t\t<UserListItem\n\t\t\t\t\t\t\t\t@userChangeName=\"onUserChangeName\"\n\t\t\t\t\t\t\t\t@userKick=\"onUserMenuKick\"\n\t\t\t\t\t\t\t\t@userInsertName=\"onUserMenuInsertName\"\n\t\t\t\t\t\t\t\t@userPin=\"onUserMenuPin\"\n\t\t\t\t\t\t\t\t@userUnpin=\"onUserMenuUnpin\"\n\t\t\t\t\t\t\t\t@userChangeBackground=\"onUserMenuChangeBackground\"\n\t\t\t\t\t\t\t\t@userOpenChat=\"onUserMenuOpenChat\"\n\t\t\t\t\t\t\t\t@userOpenProfile=\"onUserMenuOpenProfile\"\n\t\t\t\t\t\t\t\t:userId=\"presenter\"\n\t\t\t\t\t\t\t\t:key=\"presenter\"\n\t\t\t\t\t\t\t/>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t\t<!-- Participants list section (if there are any users) -->\n\t\t\t\t<template v-if=\"usersList.length > 0\">\n\t\t\t\t\t<!-- Show participants category title if broadcast -->\n\t\t\t\t\t<div v-if=\"isBroadcast\" class=\"bx-im-component-call-user-list-category bx-im-component-call-user-list-category-participants\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-user-list-category-text\">\n\t\t\t\t\t\t\t{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_CATEGORY_PARTICIPANTS') }}\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"bx-im-component-call-user-list-category-counter\">\n\t\t\t\t\t\t\t{{ usersList.length }}\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<!-- Participants list -->\n\t\t\t\t\t<div class=\"bx-im-component-call-user-list-items\">\n\t\t\t\t\t\t<template v-for=\"user in usersList\">\n\t\t\t\t\t\t\t<UserListItem\n\t\t\t\t\t\t\t\t@userChangeName=\"onUserChangeName\"\n\t\t\t\t\t\t\t\t@userKick=\"onUserMenuKick\"\n\t\t\t\t\t\t\t\t@userInsertName=\"onUserMenuInsertName\" \n\t\t\t\t\t\t\t\t@userPin=\"onUserMenuPin\"\n\t\t\t\t\t\t\t\t@userUnpin=\"onUserMenuUnpin\"\n\t\t\t\t\t\t\t\t@userChangeBackground=\"onUserMenuChangeBackground\"\n\t\t\t\t\t\t\t\t@userOpenChat=\"onUserMenuOpenChat\"\n\t\t\t\t\t\t\t\t@userOpenProfile=\"onUserMenuOpenProfile\"\n\t\t\t\t\t\t\t\t:userId=\"user\"\n\t\t\t\t\t\t\t\t:key=\"user\" />\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t\t<!-- Next page loader -->\n\t\t\t\t<div v-if=\"hasMoreToLoad\" v-bx-im-directive-user-list-observer class=\"bx-im-component-call-user-list-loader\">\n\t\t\t\t\t<div class=\"bx-im-component-call-user-list-loader-icon\"></div>\n\t\t\t\t\t<div class=\"bx-im-component-call-user-list-loader-text\">\n\t\t\t\t\t\t{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_LOADING_USERS') }}\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\t\n\t\t</div>\n\t"
	};

	function ownKeys$9(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$9(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$9(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$9(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var UserListHeader = {
	  computed: _objectSpread$9({
	    userId: function userId() {
	      return this.application.common.userId;
	    },
	    isCurrentUserOwner: function isCurrentUserOwner() {
	      if (!this.dialog) {
	        return false;
	      }
	      return this.dialog.ownerId === this.userId;
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    user: function user(state) {
	      return state.users.collection[state.application.common.userId];
	    },
	    application: function application(state) {
	      return state.application;
	    },
	    conference: function conference(state) {
	      return state.conference;
	    },
	    dialog: function dialog(state) {
	      return state.dialogues.collection[state.application.dialog.dialogId];
	    }
	  })),
	  methods: {
	    onCloseUsers: function onCloseUsers() {
	      this.getApplication().toggleUserList();
	    },
	    openMenu: function openMenu() {
	      var _this = this;
	      if (this.menuPopup) {
	        this.closeMenu();
	        return false;
	      }
	      this.menuPopup = main_popup.MenuManager.create({
	        id: 'bx-im-component-call-user-list-header-popup',
	        bindElement: this.$refs['user-list-header-menu'],
	        items: this.getMenuItems(),
	        events: {
	          onPopupClose: function onPopupClose() {
	            return _this.menuPopup.destroy();
	          },
	          onPopupDestroy: function onPopupDestroy() {
	            return _this.menuPopup = null;
	          }
	        }
	      });
	      this.menuPopup.show();
	    },
	    closeMenu: function closeMenu() {
	      this.menuPopup.destroy();
	      this.menuPopup = null;
	    },
	    getMenuItems: function getMenuItems() {
	      var _this2 = this;
	      var items = [{
	        text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_HEADER_MENU_COPY_LINK'),
	        onclick: function onclick() {
	          _this2.closeMenu();
	          _this2.onMenuCopyLink();
	        }
	      }];
	      if (this.isCurrentUserOwner) {
	        items.push({
	          text: this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USER_LIST_HEADER_MENU_CHANGE_LINK'),
	          onclick: function onclick() {
	            _this2.closeMenu();
	            _this2.onMenuChangeLink();
	          }
	        });
	      }
	      return items;
	    },
	    onMenuCopyLink: function onMenuCopyLink() {
	      var publicLink = this.dialog["public"].link;
	      im_lib_clipboard.Clipboard.copy(publicLink);
	      var notificationText = this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_LINK_COPIED');
	      BX.UI.Notification.Center.notify({
	        content: notificationText,
	        autoHideDelay: 4000
	      });
	    },
	    onMenuChangeLink: function onMenuChangeLink() {
	      var _this3 = this;
	      var confirmMessage = this.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_CHANGE_LINK_CONFIRM_TEXT');
	      this.changeLinkConfirm = ui_dialogs_messagebox.MessageBox.create({
	        message: confirmMessage,
	        modal: true,
	        buttons: ui_dialogs_messagebox.MessageBoxButtons.OK_CANCEL,
	        onOk: function onOk() {
	          _this3.changeLink();
	          _this3.changeLinkConfirm.getPopupWindow().destroy();
	        },
	        onCancel: function onCancel() {
	          _this3.changeLinkConfirm.getPopupWindow().destroy();
	        }
	      });
	      this.changeLinkConfirm.show();
	    },
	    changeLink: function changeLink() {
	      var _this4 = this;
	      this.getApplication().changeLink().then(function () {
	        var notificationText = _this4.$Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_LINK_CHANGED');
	        BX.UI.Notification.Center.notify({
	          content: notificationText,
	          autoHideDelay: 4000
	        });
	      })["catch"](function (error) {
	        console.error('Conference: change link error', error);
	      });
	    },
	    getApplication: function getApplication() {
	      return this.$Bitrix.Application.get();
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-component-call-right-header\">\n\t\t\t<div class=\"bx-im-component-call-right-header-left\">\n\t\t\t\t<div @click=\"onCloseUsers\" class=\"bx-im-component-call-right-header-close\" :title=\"$Bitrix.Loc.getMessage['BX_IM_COMPONENT_CALL_CHAT_CLOSE_TITLE']\"></div>\n\t\t\t<div class=\"bx-im-component-call-right-header-title\">{{ $Bitrix.Loc.getMessage('BX_IM_COMPONENT_CALL_USERS_LIST_TITLE') }}</div>\n\t\t\t</div>\n\t\t\t<div class=\"bx-im-component-call-right-header-right\">\n\t\t\t\t<div @click=\"openMenu\" class=\"bx-im-component-call-user-list-header-more\" ref=\"user-list-header-menu\"></div>\t\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	function ownKeys$a(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$a(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$a(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$a(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	//const
	var popupModes = Object.freeze({
	  preparation: 'preparation'
	});
	ui_vue.BitrixVue.component('bx-im-component-conference-public', {
	  components: {
	    Error: Error,
	    CheckDevices: CheckDevices,
	    OrientationDisabled: OrientationDisabled,
	    PasswordCheck: PasswordCheck,
	    LoadingStatus: LoadingStatus,
	    RequestPermissions: RequestPermissions,
	    MobileChatButton: MobileChatButton,
	    ConferenceInfo: ConferenceInfo,
	    UserForm: UserForm,
	    ChatHeader: ChatHeader,
	    WaitingForStart: WaitingForStart,
	    UserList: UserList,
	    UserListHeader: UserListHeader,
	    ConferenceSmiles: ConferenceSmiles
	  },
	  props: {
	    dialogId: {
	      type: String,
	      "default": "0"
	    }
	  },
	  data: function data() {
	    return {
	      waitingForStart: false,
	      popupMode: popupModes.preparation,
	      viewPortMetaNode: null,
	      chatDrag: false,
	      // in %
	      rightPanelSplitMode: {
	        usersHeight: 50,
	        chatHeight: 50,
	        chatMinHeight: 30,
	        chatMaxHeight: 80
	      }
	    };
	  },
	  created: function created() {
	    this.initEventHandlers();
	    main_core_events.EventEmitter.subscribe(im_const.EventType.conference.waitForStart, this.onWaitForStart);
	    main_core_events.EventEmitter.subscribe(im_const.EventType.conference.hideSmiles, this.onHideSmiles);
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
	      this.getApplication().setError(im_const.ConferenceErrorCode.unsafeConnection);
	    }
	    if (!this.passwordChecked) {
	      main_core_events.EventEmitter.emit(im_const.EventType.conference.setPasswordFocus);
	    }
	  },
	  beforeDestroy: function beforeDestroy() {
	    this.destroyHandlers();
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.conference.waitForStart, this.onWaitForStart);
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.conference.hideSmiles, this.onHideSmiles);
	    clearInterval(this.durationInterval);
	  },
	  computed: _objectSpread$a({
	    EventType: function EventType() {
	      return im_const.EventType;
	    },
	    RightPanelMode: function RightPanelMode() {
	      return im_const.ConferenceRightPanelMode;
	    },
	    userId: function userId() {
	      return this.application.common.userId;
	    },
	    dialogInited: function dialogInited() {
	      if (this.dialog) {
	        return this.dialog.init;
	      }
	    },
	    conferenceStarted: function conferenceStarted() {
	      return this.conference.common.conferenceStarted;
	    },
	    userInited: function userInited() {
	      return this.conference.common.inited;
	    },
	    userHasRealName: function userHasRealName() {
	      if (this.user) {
	        return this.user.name !== this.localize['BX_IM_COMPONENT_CALL_DEFAULT_USER_NAME'];
	      }
	      return false;
	    },
	    rightPanelMode: function rightPanelMode() {
	      return this.conference.common.rightPanelMode;
	    },
	    userListClasses: function userListClasses() {
	      var result = [];
	      if (this.rightPanelMode === 'split') {
	        result.push('bx-im-component-call-right-top');
	      } else if (this.rightPanelMode === 'users') {
	        result.push('bx-im-component-call-right-full');
	      }
	      return result;
	    },
	    userListStyles: function userListStyles() {
	      if (this.rightPanelMode !== im_const.ConferenceRightPanelMode.split) {
	        return {};
	      }
	      return {
	        height: "".concat(this.rightPanelSplitMode.usersHeight, "%")
	      };
	    },
	    chatClasses: function chatClasses() {
	      var result = [];
	      if (this.rightPanelMode === 'split') {
	        result.push('bx-im-component-call-right-bottom');
	      } else if (this.rightPanelMode === 'chat') {
	        result.push('bx-im-component-call-right-full');
	      }
	      return result;
	    },
	    chatStyles: function chatStyles() {
	      if (this.rightPanelMode !== im_const.ConferenceRightPanelMode.split) {
	        return {};
	      }
	      return {
	        height: "".concat(this.rightPanelSplitMode.chatHeight, "%")
	      };
	    },
	    isChatShowed: function isChatShowed() {
	      return this.conference.common.showChat;
	    },
	    isPreparationStep: function isPreparationStep() {
	      return this.conference.common.state === im_const.ConferenceStateType.preparation;
	    },
	    isBroadcast: function isBroadcast() {
	      return this.conference.common.isBroadcast;
	    },
	    presentersList: function presentersList() {
	      return this.conference.common.presenters;
	    },
	    isCurrentUserPresenter: function isCurrentUserPresenter() {
	      return this.presentersList.includes(this.userId);
	    },
	    errorCode: function errorCode() {
	      return this.conference.common.error;
	    },
	    passwordChecked: function passwordChecked() {
	      return this.conference.common.passChecked;
	    },
	    permissionsRequested: function permissionsRequested() {
	      return this.conference.common.permissionsRequested;
	    },
	    callContainerClasses: function callContainerClasses() {
	      return [this.conference.common.callEnded ? 'with-clouds' : ''];
	    },
	    wrapClasses: function wrapClasses() {
	      var classes = ['bx-im-component-call-wrap'];
	      if (this.isMobile() && this.isBroadcast && !this.isCurrentUserPresenter && this.isPreparationStep) {
	        classes.push('bx-im-component-call-mobile-viewer-mode');
	      }
	      return classes;
	    },
	    chatId: function chatId() {
	      if (this.application) {
	        return this.application.dialog.chatId;
	      }
	      return 0;
	    },
	    localize: function localize() {
	      return ui_vue.BitrixVue.getFilteredPhrases(['BX_IM_COMPONENT_CALL_', 'IM_DIALOG_CLIPBOARD_']);
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    conference: function conference(state) {
	      return state.conference;
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
	  watch: {
	    isChatShowed: function isChatShowed(newValue) {
	      var _this = this;
	      if (this.isMobile()) {
	        return false;
	      }
	      if (newValue === true) {
	        this.$nextTick(function () {
	          main_core_events.EventEmitter.emit(im_const.EventType.dialog.scrollOnStart, {
	            chatId: _this.chatId
	          });
	          main_core_events.EventEmitter.emit(im_const.EventType.textarea.setFocus);
	        });
	      }
	    },
	    rightPanelMode: function rightPanelMode(newValue) {
	      var _this2 = this;
	      if (newValue === im_const.ConferenceRightPanelMode.chat || newValue === im_const.ConferenceRightPanelMode.split) {
	        this.$nextTick(function () {
	          main_core_events.EventEmitter.emit(im_const.EventType.dialog.scrollOnStart, {
	            chatId: _this2.chatId
	          });
	          main_core_events.EventEmitter.emit(im_const.EventType.textarea.setFocus);
	        });
	      }
	    },
	    dialogInited: function dialogInited(newValue) {
	      if (newValue === true) {
	        this.getApplication().setDialogInited();
	      }
	    },
	    //to skip request permissions step in desktop
	    userInited: function userInited(newValue) {
	      if (newValue === true && this.isDesktop() && this.passwordChecked) {
	        this.$nextTick(function () {
	          main_core_events.EventEmitter.emit(im_const.EventType.conference.requestPermissions);
	        });
	      }
	    },
	    user: function user() {
	      if (this.user && this.userHasRealName) {
	        this.getApplication().setUserWasRenamed();
	      }
	    }
	  },
	  methods: {
	    initEventHandlers: function initEventHandlers() {
	      this.sendMessageHandler = new im_eventHandler.SendMessageHandler(this.$Bitrix);
	      this.textareaHandler = new ConferenceTextareaHandler(this.$Bitrix);
	      this.readingHandler = new im_eventHandler.ReadingHandler(this.$Bitrix);
	      this.reactionHandler = new im_eventHandler.ReactionHandler(this.$Bitrix);
	      this.textareaUploadHandler = new ConferenceTextareaUploadHandler(this.$Bitrix);
	    },
	    destroyHandlers: function destroyHandlers() {
	      this.sendMessageHandler.destroy();
	      this.textareaHandler.destroy();
	      this.readingHandler.destroy();
	      this.reactionHandler.destroy();
	      this.textareaUploadHandler.destroy();
	    },
	    onHideSmiles: function onHideSmiles() {
	      this.getApplication().toggleSmiles();
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
	      main_core_events.EventEmitter.emit(im_const.EventType.textarea.insertText, {
	        text: event.text
	      });
	    },
	    onSmilesSelectSet: function onSmilesSelectSet() {
	      main_core_events.EventEmitter.emit(im_const.EventType.textarea.setFocus);
	    },
	    onWaitForStart: function onWaitForStart() {
	      this.waitingForStart = true;
	    },
	    onChatStartDrag: function onChatStartDrag(event) {
	      if (this.chatDrag) {
	        return;
	      }
	      this.chatDrag = true;
	      this.chatDragStartPoint = event.clientY;
	      this.chatDragStartHeight = this.rightPanelSplitMode.chatHeight;
	      this.addChatDragEvents();
	    },
	    onChatContinueDrag: function onChatContinueDrag(event) {
	      if (!this.chatDrag) {
	        return;
	      }
	      this.chatDragControlPoint = event.clientY;
	      var availableHeight = document.body.clientHeight;
	      var maxHeightInPx = availableHeight * (this.rightPanelSplitMode.chatMaxHeight / 100);
	      var minHeightInPx = availableHeight * (this.rightPanelSplitMode.chatMinHeight / 100);
	      var startHeightInPx = availableHeight * (this.chatDragStartHeight / 100);
	      var chatHeightInPx = Math.max(Math.min(startHeightInPx + this.chatDragStartPoint - this.chatDragControlPoint, maxHeightInPx), minHeightInPx);
	      var chatHeight = chatHeightInPx / availableHeight * 100;
	      if (this.rightPanelSplitMode.chatHeight !== chatHeight) {
	        this.rightPanelSplitMode.chatHeight = chatHeight;
	        this.rightPanelSplitMode.usersHeight = 100 - chatHeight;
	      }
	    },
	    onChatStopDrag: function onChatStopDrag(event) {
	      if (!this.chatDrag) {
	        return;
	      }
	      this.chatDrag = false;
	      this.removeChatDragEvents();
	      main_core_events.EventEmitter.emit(im_const.EventType.dialog.scrollToBottom, {
	        chatId: this.chatId,
	        force: true
	      });
	    },
	    addChatDragEvents: function addChatDragEvents() {
	      document.addEventListener('mousemove', this.onChatContinueDrag);
	      document.addEventListener('mouseup', this.onChatStopDrag);
	      document.addEventListener('mouseleave', this.onChatStopDrag);
	    },
	    removeChatDragEvents: function removeChatDragEvents() {
	      document.removeEventListener('mousemove', this.onChatContinueDrag);
	      document.removeEventListener('mouseup', this.onChatStopDrag);
	      document.removeEventListener('mouseleave', this.onChatStopDrag);
	    },
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
	    isHttps: function isHttps() {
	      return location.protocol === 'https:';
	    },
	    getUserHash: function getUserHash() {
	      return this.conference.user.hash;
	    },
	    getApplication: function getApplication() {
	      return this.$Bitrix.Application.get();
	    } /* endregion 03. Helpers */
	  },
	  template: "\n\t<div :class=\"wrapClasses\">\n\t\t<div class=\"bx-im-component-call\">\n\t\t\t<div class=\"bx-im-component-call-left\">\n\t\t\t\t<div id=\"bx-im-component-call-container\" :class=\"callContainerClasses\"></div>\n\t\t\t\t<div v-if=\"isPreparationStep\" class=\"bx-im-component-call-left-preparation\">\n\t\t\t\t\t<!-- Step 1: Errors page -->\n\t\t\t\t\t<Error v-if=\"errorCode\"/>\n\t\t\t\t\t<!-- Step 2: Password page -->\n\t\t\t\t\t<PasswordCheck v-else-if=\"!passwordChecked\"/>\n\t\t\t\t\t<template v-else-if=\"!errorCode && passwordChecked\">\n\t\t\t\t\t\t<!-- Step 3: Loading page -->\n\t\t\t\t\t\t<LoadingStatus v-if=\"!userInited\"/>\n\t\t\t\t\t\t<template v-else-if=\"userInited\">\n\t\t\t\t\t\t\t<!-- BROADCAST MODE -->\n\t\t\t\t\t\t  \t<template v-if=\"isBroadcast\">\n\t\t\t\t\t\t  \t\t<template v-if=\"!isDesktop() && !permissionsRequested && isCurrentUserPresenter\">\n\t\t\t\t\t\t\t\t\t<ConferenceInfo/>\n\t\t\t\t\t\t\t\t\t<RequestPermissions>\n\t\t\t\t\t\t\t\t\t\t<template v-if=\"isMobile()\">\n\t\t\t\t\t\t\t\t\t\t\t<MobileChatButton/>\n\t\t\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t\t</RequestPermissions>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t<!-- Skip permissions request for desktop and show button with loader  -->\n\t\t\t\t\t\t\t\t<template v-if=\"isDesktop() && (!permissionsRequested || !user) && isCurrentUserPresenter\">\n\t\t\t\t\t\t\t\t\t<ConferenceInfo/>\n\t\t\t\t\t\t\t\t\t<RequestPermissions :skipRequest=\"true\"/>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t<!-- Step 5: Page with video and mic check -->\n\t\t\t\t\t\t\t\t<div v-if=\"permissionsRequested || !isCurrentUserPresenter\" class=\"bx-im-component-call-video-step-container\">\n\t\t\t\t\t\t\t\t\t<!-- Compact conference info -->\n\t\t\t\t\t\t\t\t\t<ConferenceInfo :compactMode=\"true\"/>\n\t\t\t\t\t\t\t\t\t<CheckDevices v-if=\"isCurrentUserPresenter\" />\n\t\t\t\t\t\t\t\t\t<!-- Bottom part of interface -->\n\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-bottom-container\">\n\t\t\t\t\t\t\t\t\t\t<UserForm v-if=\"!waitingForStart\"/>\n\t\t\t\t\t\t\t\t\t\t<WaitingForStart v-else>\n\t\t\t\t\t\t\t\t\t\t\t<template v-if=\"isMobile()\">\n\t\t\t\t\t\t\t\t\t\t\t\t<MobileChatButton/>\n\t\t\t\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t\t\t</WaitingForStart>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t<!-- END BROADCAST MODE -->\n\t\t\t\t\t\t\t<!-- NORMAL MODE (NOT BROADCAST) -->\n\t\t\t\t\t\t  \t<template v-else-if=\"!isBroadcast\">\n\t\t\t\t\t\t\t\t<!-- Step 4: Permissions page -->\n\t\t\t\t\t\t\t\t<template v-if=\"!isDesktop() && !permissionsRequested\">\n\t\t\t\t\t\t\t\t\t<ConferenceInfo/>\n\t\t\t\t\t\t\t\t\t<RequestPermissions>\n\t\t\t\t\t\t\t\t\t\t<template v-if=\"isMobile()\">\n\t\t\t\t\t\t\t\t\t\t\t<MobileChatButton/>\n\t\t\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t\t</RequestPermissions>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t<!-- Skip permissions request for desktop and show button with loader  -->\n\t\t\t\t\t\t\t\t<template v-if=\"isDesktop() && (!permissionsRequested || !user)\">\n\t\t\t\t\t\t\t\t\t<ConferenceInfo/>\n\t\t\t\t\t\t\t\t\t<RequestPermissions :skipRequest=\"true\"/>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t<!-- Step 5: Page with video and mic check -->\n\t\t\t\t\t\t\t\t<div v-else-if=\"permissionsRequested\" class=\"bx-im-component-call-video-step-container\">\n\t\t\t\t\t\t\t\t\t<!-- Compact conference info -->\n\t\t\t\t\t\t\t\t\t<ConferenceInfo :compactMode=\"true\"/>\n\t\t\t\t\t\t\t\t\t<CheckDevices/>\n\t\t\t\t\t\t\t\t\t<!-- Bottom part of interface -->\n\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-bottom-container\">\n\t\t\t\t\t\t\t\t\t\t<UserForm v-if=\"!waitingForStart\"/>\n\t\t\t\t\t\t\t\t\t\t<WaitingForStart v-else>\n\t\t\t\t\t\t\t\t\t\t\t<template v-if=\"isMobile()\">\n\t\t\t\t\t\t\t\t\t\t\t\t<MobileChatButton/>\n\t\t\t\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t\t\t</WaitingForStart>\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t<!-- END NORMAL MODE (NOT BROADCAST) -->\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</template>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<template v-if=\"userInited && !errorCode\">\n\t\t\t\t<transition :name=\"!isMobile()? 'videoconf-chat-slide': ''\">\n\t\t\t\t\t<div v-show=\"rightPanelMode !== RightPanelMode.hidden\" class=\"bx-im-component-call-right\">\n\t\t\t\t\t\t<!-- Start users list -->\n\t\t\t\t\t\t<div v-show=\"rightPanelMode === RightPanelMode.split || rightPanelMode === RightPanelMode.users\" :class=\"userListClasses\" :style=\"userListStyles\">\n\t\t\t\t\t\t\t<UserListHeader />\n\t\t\t\t\t\t\t<div class=\"bx-im-component-call-right-users\">\n\t\t\t\t\t\t\t\t<UserList />\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<!-- End users list -->\n\t\t\t\t\t\t<!-- Start chat -->\n\t\t\t\t\t\t<div v-show=\"rightPanelMode === RightPanelMode.split || rightPanelMode === RightPanelMode.chat\" :class=\"chatClasses\" :style=\"chatStyles\">\n\t\t\t\t\t\t\t<!-- Resize handler -->\n\t\t\t\t\t\t\t<div\n\t\t\t\t\t\t\t\tv-if=\"rightPanelMode === RightPanelMode.split\"\n\t\t\t\t\t\t\t\t@mousedown=\"onChatStartDrag\"\n\t\t\t\t\t\t\t\tclass=\"bx-im-component-call-right-bottom-resize-handle\"\n\t\t\t\t\t\t\t></div>\n\t\t\t\t\t\t\t<ChatHeader />\n\t\t\t\t\t\t\t<div class=\"bx-im-component-call-right-chat\">\n\t\t\t\t\t\t\t\t<bx-im-component-dialog\n\t\t\t\t\t\t\t\t\t:userId=\"userId\"\n\t\t\t\t\t\t\t\t\t:dialogId=\"dialogId\"\n\t\t\t\t\t\t\t\t/>\n\t\t\t\t\t\t\t\t<keep-alive include=\"bx-im-component-call-smiles\">\n\t\t\t\t\t\t\t\t\t<ConferenceSmiles\n\t\t\t\t\t\t\t\t\t\tv-if=\"conference.common.showSmiles\"\n\t\t\t\t\t\t\t\t\t\t@selectSmile=\"onSmilesSelectSmile\"\n\t\t\t\t\t\t\t\t\t\t@selectSet=\"onSmilesSelectSet\"\n\t\t\t\t\t\t\t\t\t/>\n\t\t\t\t\t\t\t\t</keep-alive>\n\t\t\t\t\t\t\t\t<div v-if=\"user\" class=\"bx-im-component-call-textarea\">\n\t\t\t\t\t\t\t\t\t<bx-im-component-textarea\n\t\t\t\t\t\t\t\t\t\t:userId=\"userId\"\n\t\t\t\t\t\t\t\t\t\t:dialogId=\"dialogId\"\n\t\t\t\t\t\t\t\t\t\t:writesEventLetter=\"3\"\n\t\t\t\t\t\t\t\t\t\t:enableFile=\"true\"\n\t\t\t\t\t\t\t\t\t\t:enableEdit=\"true\"\n\t\t\t\t\t\t\t\t\t\t:enableCommand=\"false\"\n\t\t\t\t\t\t\t\t\t\t:enableMention=\"false\"\n\t\t\t\t\t\t\t\t\t\t:autoFocus=\"true\"\n\t\t\t\t\t\t\t\t\t/>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<!-- End chat -->\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</transition>\n\t\t\t</template>\n\t\t</div>\n\t</div>\n\t"
	});

}((this.BX.Messenger = this.BX.Messenger || {}),BX,BX,BX.Messenger.EventHandler,BX.Messenger,window,BX.UI,window,BX,BX,BX.Messenger.Lib,BX.Messenger,BX.Messenger.Lib,BX,BX.Messenger.Lib,BX.Messenger.Lib,BX.Messenger.Const,BX.Event,BX,BX.Main,BX.Messenger.Lib,BX.UI.Dialogs));
//# sourceMappingURL=conference-public.bundle.js.map
