this.BX = this.BX || {};
(function (exports,ui_vue_vuex,im_lib_logger,im_const,im_view_textarea,ui_vue_components_smiles,ui_vue) {
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
	  props: ['micId'],
	  data: function data() {
	    return {
	      bars: [],
	      barDisabledColor: '#999',
	      barEnabledColor: '#86a732'
	    };
	  },
	  watch: {
	    micId: function micId(newId) {
	      this.startAudioCheck();
	    }
	  },
	  mounted: function mounted() {
	    this.startAudioCheck();
	    this.bars = babelHelpers.toConsumableArray(document.querySelectorAll('.bx-im-component-call-check-devices-micro-level-item'));
	  },
	  methods: {
	    startAudioCheck: function startAudioCheck() {
	      var _this = this;

	      if (this.micId === 0) {
	        return false;
	      }

	      navigator.mediaDevices.getUserMedia({
	        audio: {
	          deviceId: {
	            exact: this.micId
	          }
	        }
	      }).then(function (stream) {
	        _this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
	        _this.analyser = _this.audioContext.createAnalyser();
	        _this.microphone = _this.audioContext.createMediaStreamSource(stream);
	        _this.scriptNode = _this.audioContext.createScriptProcessor(2048, 1, 1);
	        _this.analyser.smoothingTimeConstant = 0.8;
	        _this.analyser.fftSize = 1024;

	        _this.microphone.connect(_this.analyser);

	        _this.analyser.connect(_this.scriptNode);

	        _this.scriptNode.connect(_this.audioContext.destination);

	        _this.scriptNode.onaudioprocess = _this.processVolume;
	      });
	    },
	    processVolume: function processVolume() {
	      var _this2 = this;

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
	        elem.style.backgroundColor = _this2.barDisabledColor;
	      });
	      elementsToColor.forEach(function (elem) {
	        elem.style.backgroundColor = _this2.barEnabledColor;
	      });
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-component-call-check-devices-row\">\n\t\t\t<div class=\"bx-im-component-call-check-devices-micro-icon\"></div>\n\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level\">\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-level-item\"></div>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	/**
	 * Bitrix Videoconf
	 * Check devices component (Vue component)
	 *
	 * @package bitrix
	 * @subpackage imopenlines
	 * @copyright 2001-2019 Bitrix
	 */
	ui_vue.Vue.component('bx-im-component-call-check-devices', {
	  data: function data() {
	    return {
	      cameraList: [],
	      microphoneList: [],
	      audioOutputList: [],
	      defaultDevices: {
	        camera: 0,
	        microphone: 0,
	        audioOutput: 0
	      },
	      currentlySelected: {
	        camera: 0,
	        microphone: 0,
	        audioOutput: 0
	      },
	      defaultOptions: {
	        preferHDQuality: true,
	        enableMicAutoParameters: true
	      },
	      selectedOptions: {
	        preferHDQuality: true,
	        enableMicAutoParameters: true
	      },
	      noVideo: true
	    };
	  },
	  created: function created() {
	    if (BX.Call.Hardware) {
	      this.cameraList = BX.Call.Hardware.cameraList;
	      this.microphoneList = BX.Call.Hardware.microphoneList;
	      this.audioOutputList = BX.Call.Hardware.audioOutputList;
	      this.defaultOptions.preferHDQuality = BX.Call.Hardware.preferHdQuality;
	      this.selectedOptions.preferHDQuality = this.defaultOptions.preferHDQuality;
	      this.defaultOptions.enableMicAutoParameters = BX.Call.Hardware.enableMicAutoParameters;
	      this.selectedOptions.enableMicAutoParameters = this.defaultOptions.enableMicAutoParameters;
	    }

	    this.getDefaultDevices();
	    this.getVideoFromCamera(this.currentCamera);
	  },
	  watch: {
	    currentCamera: function currentCamera() {
	      this.getVideoFromCamera(this.currentCamera);
	    }
	  },
	  computed: {
	    currentCamera: function currentCamera() {
	      return this.currentlySelected.camera;
	    },
	    localize: function localize() {
	      return ui_vue.Vue.getFilteredPhrases('BX_IM_COMPONENT_CALL_CHECK_DEVICES_', this.$root.$bitrixMessages);
	    }
	  },
	  methods: {
	    getDefaultDevices: function getDefaultDevices() {
	      var _this = this;

	      if (BX.Call.Hardware.defaultCamera) {
	        this.defaultDevices.camera = BX.Call.Hardware.defaultCamera;
	        this.currentlySelected.camera = this.defaultDevices.camera;
	      } else {
	        navigator.mediaDevices.getUserMedia({
	          video: true
	        }).then(function (stream) {
	          if (stream && stream.getVideoTracks()[0]) {
	            _this.noVideo = false;
	            _this.defaultDevices.camera = stream.getVideoTracks()[0].getSettings().deviceId;
	            _this.currentlySelected.camera = _this.defaultDevices.camera;
	          }
	        }).catch(function (e) {
	          console.warn('error getting default video', e);
	        });
	      }

	      if (BX.Call.Hardware.defaultMicrophone) {
	        this.defaultDevices.microphone = BX.Call.Hardware.defaultMicrophone;
	        this.currentlySelected.microphone = this.defaultDevices.microphone;
	      } else {
	        navigator.mediaDevices.getUserMedia({
	          audio: true
	        }).then(function (stream) {
	          if (stream && stream.getAudioTracks()[0]) {
	            _this.defaultDevices.microphone = stream.getAudioTracks()[0].getSettings().deviceId;
	            _this.currentlySelected.microphone = _this.defaultDevices.microphone;
	          }
	        }).catch(function (e) {
	          console.warn('error getting default audio', e);
	        });
	      }

	      if (BX.Call.Hardware.defaultSpeaker) {
	        this.defaultDevices.audioOutput = BX.Call.Hardware.defaultSpeaker;
	        this.currentlySelected.audioOutput = this.defaultDevices.audioOutput;
	      }
	    },
	    getVideoFromCamera: function getVideoFromCamera(id) {
	      var _this2 = this;

	      if (id === 0) {
	        return false;
	      }

	      navigator.mediaDevices.getUserMedia({
	        video: {
	          deviceId: {
	            exact: id
	          },
	          width: 450,
	          height: 338
	        }
	      }).then(function (stream) {
	        _this2.$refs['video'].volume = 0;
	        _this2.$refs['video'].srcObject = stream;

	        _this2.$refs['video'].play();
	      }).catch(function (e) {
	        console.warn('getting video from camera error', e);
	      });
	    },
	    save: function save() {
	      var changedValues = {};

	      if (this.currentlySelected.camera !== this.defaultDevices.camera) {
	        changedValues['camera'] = this.currentlySelected.camera;
	      }

	      if (this.currentlySelected.microphone !== this.defaultDevices.microphone) {
	        changedValues['microphone'] = this.currentlySelected.microphone;
	      }

	      if (this.currentlySelected.audioOutput !== this.defaultDevices.audioOutput) {
	        changedValues['audioOutput'] = this.currentlySelected.audioOutput;
	      }

	      if (this.selectedOptions.preferHDQuality !== this.defaultOptions.preferHDQuality) {
	        changedValues['preferHDQuality'] = this.selectedOptions.preferHDQuality;
	      }

	      if (this.selectedOptions.enableMicAutoParameters !== this.defaultOptions.enableMicAutoParameters) {
	        changedValues['enableMicAutoParameters'] = this.selectedOptions.enableMicAutoParameters;
	      }

	      if (!this.isEmptyObject(changedValues)) {
	        this.$emit('save', changedValues);
	      }
	    },
	    exit: function exit() {
	      this.$emit('exit');
	    },
	    isEmptyObject: function isEmptyObject(obj) {
	      return Object.keys(obj).length === 0;
	    }
	  },
	  components: {
	    MicLevel: MicLevel
	  },
	  template: "\n\t\t<div class=\"bx-im-component-call-check-devices\">\n\t\t\t<h3 class=\"bx-im-component-call-check-devices-title\">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_VIDEO_SETTINGS'] }}</h3>\n\t\t\t<!-- Camera select -->\n\t\t\t<div class=\"bx-im-component-call-check-devices-row bx-im-component-call-check-devices-camera-wrap\">\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-select-label\">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_CAMERA'] }}</div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-camera-select ui-ctl ui-ctl-after-icon ui-ctl-dropdown\">\n\t\t\t\t\t<div class=\"ui-ctl-after ui-ctl-icon-angle\"></div>\n\t\t\t\t\t<select v-model=\"currentlySelected.camera\" class=\"ui-ctl-element\">\n\t\t\t\t\t\t<template v-if=\"isEmptyObject(cameraList)\">\n\t\t\t\t\t\t\t<option disabled value=\"0\">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_NO_CAMERA'] }}</option>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t\t<option disabled value=\"0\">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_CHOOSE_CAMERA'] }}</option>\n\t\t\t\t\t\t\t<option v-for=\"(camera, id) in cameraList\" :value=\"id\" :key=\"id\">{{ camera }}</option>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</select>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<!-- Video box -->\n\t\t\t<template v-if=\"noVideo\">\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-camera-no-video\">\n\t\t\t\t\t<div>{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_NO_VIDEO'] }}</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-else>\n\t\t\t\t<video ref=\"video\" class=\"bx-im-component-call-check-devices-camera-video\"></video>\n\t\t\t</template>\n\t\t\t<!-- Receive HD -->\n\t\t\t<div class=\"bx-im-component-call-check-devices-row bx-im-component-call-check-devices-option-hd\">\n\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox\">\n\t\t\t\t\t<input type=\"checkbox\" v-model=\"selectedOptions.preferHDQuality\" id=\"video_hd\" class=\"ui-ctl-element\"/>\n\t\t\t\t\t<div class=\"ui-ctl-label-text\">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_RECEIVE_HD'] }}</div>\n\t\t\t\t</label>\n\t\t\t</div>\n\t\t\t\n\t\t\t<h3 class=\"bx-im-component-call-check-devices-title\">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_AUDIO_SETTINGS'] }}</h3>\n\t\t\t<!-- Mic select -->\n\t\t\t<div class=\"bx-im-component-call-check-devices-row bx-im-component-call-check-devices-micro-wrap\">\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-select-label\">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_MICRO'] }}</div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-micro-select ui-ctl ui-ctl-after-icon ui-ctl-dropdown\">\n\t\t\t\t\t<div class=\"ui-ctl-after ui-ctl-icon-angle\"></div>\n\t\t\t\t\t<select v-model=\"currentlySelected.microphone\" class=\"ui-ctl-element\">\n\t\t\t\t\t\t<template v-if=\"isEmptyObject(microphoneList)\">\n\t\t\t\t\t\t\t<option disabled value=\"0\">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_NO_MICRO'] }}</option>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t\t<option disabled value=\"0\">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_CHOOSE_MICRO'] }}</option>\n\t\t\t\t\t\t\t<option v-for=\"(microphone, id) in microphoneList\" :value=\"id\" :key=\"id\">{{ microphone }}</option>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</select>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<!-- Mic Level -->\n\t\t\t<mic-level :micId=\"currentlySelected.microphone\"/>\n\t\t\t<!-- Auto mic options -->\n\t\t\t<div class=\"bx-im-component-call-check-devices-row bx-im-component-call-check-devices-option-auto-mic\">\t\t\t\t\n\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox\">\n\t\t\t\t\t<input type=\"checkbox\" v-model=\"selectedOptions.enableMicAutoParameters\" id=\"micro_auto_settings\" class=\"ui-ctl-element\"/>\n\t\t\t\t\t<div class=\"ui-ctl-label-text\">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_AUTO_MIC_OPTIONS'] }}</div>\n\t\t\t\t</label>\n\t\t\t</div>\n\t\t\t<!-- Output select -->\n\t\t\t<div class=\"bx-im-component-call-check-devices-row bx-im-component-call-check-devices-output-wrap\">\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-select-label\">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_SPEAKER'] }}</div>\n\t\t\t\t<div class=\"bx-im-component-call-check-devices-output-select ui-ctl ui-ctl-after-icon ui-ctl-dropdown\">\n\t\t\t\t\t<div class=\"ui-ctl-after ui-ctl-icon-angle\"></div>\n\t\t\t\t\t<select v-model=\"currentlySelected.audioOutput\" class=\"ui-ctl-element\">\n\t\t\t\t\t\t<template v-if=\"isEmptyObject(audioOutputList)\">\n\t\t\t\t\t\t\t<option disabled value=\"0\">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_NO_SPEAKER'] }}</option>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t\t<option disabled value=\"0\">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_CHOOSE_SPEAKER'] }}</option>\n\t\t\t\t\t\t\t<option v-for=\"(output, id) in audioOutputList\" :value=\"id\" :key=\"id\">{{ output }}</option>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</select>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<!-- Buttons -->\n\t\t\t<div class=\"bx-im-component-call-check-devices-row bx-im-component-call-check-devices-buttons\">\n\t\t\t\t<button @click=\"save\" class=\"ui-btn ui-btn-sm ui-btn-success-dark ui-btn-no-caps bx-im-component-call-check-devices-button-back\">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_BUTTON_SAVE'] }}</button>\n\t\t\t\t<button @click=\"exit\" class=\"ui-btn ui-btn-sm ui-btn-no-caps bx-im-component-call-check-devices-button-back\">{{ localize['BX_IM_COMPONENT_CALL_CHECK_DEVICES_BUTTON_BACK'] }}</button>\n\t\t\t</div>\n\t\t</div>\n\t"
	});

	/**
	 * Bitrix im
	 * Pubic call vue component
	 *
	 * @package bitrix
	 * @subpackage mobile
	 * @copyright 2001-2019 Bitrix
	 */
	var popupModes = Object.freeze({
	  preparation: 'preparation',
	  checkDevices: 'checkDevices'
	});
	/**
	 * @notice Do not mutate or clone this component! It is under development.
	 */

	ui_vue.Vue.component('bx-im-component-call', {
	  props: {
	    chatId: {
	      default: 0
	    }
	  },
	  data: function data() {
	    return {
	      userNewName: '',
	      isSettingNewName: false,
	      popupMode: popupModes.preparation
	    };
	  },
	  created: function created() {
	    window.addEventListener('beforeunload', this.onBeforeUnload.bind(this));
	  },
	  watch: {
	    showChat: function showChat(newValue) {
	      var _this = this;

	      if (newValue === true) {
	        this.$nextTick(function () {
	          _this.$root.$emit('focusTextarea');

	          _this.$root.$emit('scrollDialog');
	        });
	      }

	      if (this.user && !this.userHasRealName) {
	        this.$nextTick(function () {
	          _this.$refs.nameInput.focus();
	        });
	      }
	    },
	    dialogInited: function dialogInited(newValue) {
	      if (newValue === true) {
	        this.$root.$bitrixApplication.setDialogInited();
	      }
	    }
	  },
	  computed: babelHelpers.objectSpread({
	    userId: function userId() {
	      return this.application.common.userId;
	    },
	    dialogId: function dialogId() {
	      return this.application.dialog.dialogId;
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
	      return this.callApplication.common.userCount;
	    },
	    userInCallCounter: function userInCallCounter() {
	      return this.callApplication.common.userInCallCount;
	    },
	    isPreparationStep: function isPreparationStep() {
	      return this.callApplication.common.state === im_const.CallStateType.preparation;
	    },
	    isPopupPreparation: function isPopupPreparation() {
	      return this.popupMode === popupModes.preparation;
	    },
	    isPopupCheckDevices: function isPopupCheckDevices() {
	      return this.popupMode === popupModes.checkDevices;
	    },
	    callError: function callError() {
	      return this.callApplication.common.callError;
	    },
	    noSignalFromCamera: function noSignalFromCamera() {
	      return this.callError === im_const.CallErrorCode.noSignalFromCamera;
	    },
	    newNameButtonClasses: function newNameButtonClasses() {
	      return ['ui-btn', 'ui-btn-sm', 'ui-btn-success-dark', 'ui-btn-no-caps', {
	        'ui-btn-wait': this.isSettingNewName
	      }, {
	        'ui-btn-disabled': this.isSettingNewName
	      }];
	    },
	    startCallButtonClasses: function startCallButtonClasses() {
	      return ['ui-btn', 'ui-btn-sm', 'ui-btn-success-dark', 'ui-btn-no-caps', 'bx-im-component-call-left-preparation-buttons-start'];
	    },
	    reloadButtonClasses: function reloadButtonClasses() {
	      return ['ui-btn', 'ui-btn-sm', 'ui-btn-no-caps', 'bx-im-component-call-left-preparation-buttons-reload'];
	    },
	    checkDevicesButtonClasses: function checkDevicesButtonClasses() {
	      return ['ui-btn', 'ui-btn-sm', 'ui-btn-no-caps', 'bx-im-component-call-left-preparation-buttons-check-devices'];
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
	    onNewNameButtonClick: function onNewNameButtonClick(name) {
	      this.isSettingNewName = true;
	      name = name.trim();

	      if (name.length > 0) {
	        this.$root.$bitrixApplication.setUserName(name);
	      }
	    },
	    onCloseChat: function onCloseChat() {
	      this.$root.$bitrixApplication.toggleChat();
	    },
	    reloadPage: function reloadPage() {
	      location.reload();
	    },
	    onTextareaSend: function onTextareaSend(event) {
	      if (!event.text) {
	        return false;
	      }

	      if (this.callApplication.common.showSmiles) {
	        this.$store.commit('callApplication/toggleSmiles');
	      }

	      this.$root.$bitrixApplication.addMessage(event.text);
	    },
	    onTextareaFileSelected: function onTextareaFileSelected(event) {
	      var fileInput = event && event.fileInput ? event.fileInput : '';

	      if (!fileInput) {
	        return false;
	      }

	      if (fileInput.files[0].size > this.application.disk.maxFileSize) {
	        // TODO alert
	        //alert(this.localize.BX_LIVECHAT_FILE_SIZE_EXCEEDED.replace('#LIMIT#', Math.round(this.application.disk.maxFileSize / 1024 / 1024)));
	        return false;
	      }

	      this.$root.$bitrixApplication.uploadFile(fileInput);
	    },
	    onTextareaWrites: function onTextareaWrites(event) {
	      this.$root.$bitrixController.application.startWriting();
	    },
	    startCall: function startCall() {
	      this.$root.$bitrixApplication.startCall();
	    },
	    onTextareaAppButtonClick: function onTextareaAppButtonClick(event) {
	      if (event.appId === 'smile') {
	        this.$store.commit('callApplication/toggleSmiles');
	      }
	    },
	    onBeforeUnload: function onBeforeUnload(event) {
	      if (!this.isPreparationStep) {
	        var message = this.localize['BX_IM_COMPONENT_CALL_CLOSE_CONFIRM'];
	        event.returnValue = message;
	        return message;
	      }
	    },
	    hideSmiles: function hideSmiles() {
	      this.$store.commit('callApplication/toggleSmiles');
	    },
	    onSmilesSelectSmile: function onSmilesSelectSmile(event) {
	      this.$root.$emit('insertText', {
	        text: event.text
	      });
	    },
	    onSmilesSelectSet: function onSmilesSelectSet(event) {
	      console.warn('select set');
	    },
	    checkDevices: function checkDevices() {
	      this.popupMode = popupModes.checkDevices;
	    },
	    onCheckDevicesSave: function onCheckDevicesSave(changedValues) {
	      this.$root.$bitrixApplication.onCheckDevicesSave(changedValues);
	      this.popupMode = popupModes.preparation;
	    },
	    onCheckDevicesExit: function onCheckDevicesExit() {
	      this.popupMode = popupModes.preparation;
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-component-call\">\n\t\t\t<template v-if=\"!userInited\">\n\t\t\t\t<div class=\"bx-im-component-call-loading\">\n\t\t\t\t\t<div class=\"bx-im-component-call-loading-text\">{{ localize['BX_IM_COMPONENT_CALL_LOADING'] }}</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-else>\n\t\t\t\t<div class=\"bx-im-component-call-left\">\n\t\t\t\t\t<div id=\"bx-im-component-call-container\"></div>\n\t\t\t\t\t<div v-if=\"isPreparationStep\" class=\"bx-im-component-call-left-preparation\">\n\t\t\t\t\t\t<template v-if=\"isPopupPreparation\">\n\t\t\t\t\t\t\t<template v-if=\"callError\">\n\t\t\t\t\t\t\t\t<template v-if=\"noSignalFromCamera\">\n\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-left-preparation-title\">{{ localize['BX_IM_COMPONENT_CALL_ERROR_NO_SIGNAL_FROM_CAMERA'] }}</div>\n\t\t\t\t\t\t\t\t\t<button @click=\"reloadPage\" :class=\"reloadButtonClasses\">{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_RELOAD'] }}</button>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t<template v-else-if=\"!callError\">\n\t\t\t\t\t\t\t\t<template v-if=\"userInCallCounter > 0\">\n\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-left-preparation-title\">{{ localize['BX_IM_COMPONENT_CALL_PREPARE_TITLE_JOIN_CALL'] }}</div>\n\t\t\t\t\t\t\t\t\t<button @click=\"startCall\" :class=\"startCallButtonClasses\">{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_JOIN'] }}</button>\n\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-left-preparation-user-count\">{{ localize['BX_IM_COMPONENT_CALL_PREPARE_USER_COUNT'] }} {{ userInCallCounter }}</div>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t<template v-else-if=\"userInCallCounter === 0 && userCounter > 1\">\n\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-left-preparation-title\">{{ localize['BX_IM_COMPONENT_CALL_PREPARE_TITLE_START_CALL'] }}</div>\n\t\t\t\t\t\t\t\t\t<button @click=\"startCall\" :class=\"startCallButtonClasses\">{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_START'] }}</button>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-left-preparation-user-count\">{{ localize['BX_IM_COMPONENT_CALL_PREPARE_NO_USERS'] }}</div>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t<button @click=\"checkDevices\" :class=\"checkDevicesButtonClasses\">{{ this.localize['BX_IM_COMPONENT_CALL_BUTTON_CHECK_DEVICES'] }}</button>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t\t<template v-else-if=\"isPopupCheckDevices\">\n\t\t\t\t\t\t\t<bx-im-component-call-check-devices @save=\"onCheckDevicesSave\" @exit=\"onCheckDevicesExit\"/>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<transition name=\"videoconf-chat-slide\">\n\t\t\t\t\t<div v-show=\"showChat\" class=\"bx-im-component-call-right\">\n\t\t\t\t\t\t<div class=\"bx-im-component-call-right-header\">\n\t\t\t\t\t\t\t<div @click=\"onCloseChat\" class=\"bx-im-component-call-right-header-close\" :title=\"localize['BX_IM_COMPONENT_CALL_CHAT_CLOSE_TITLE']\"></div>\n\t\t\t\t\t\t\t<div class=\"bx-im-component-call-right-header-title\">{{ localize['BX_IM_COMPONENT_CALL_CHAT_TITLE'] }}</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"bx-im-component-call-right-chat\">\n\t\t\t\t\t\t\t<bx-im-component-dialog\n\t\t\t\t\t\t\t\t:userId=\"userId\"\n\t\t\t\t\t\t\t\t:dialogId=\"dialogId\"\n\t\t\t\t\t\t\t\tlistenEventScrollToBottom=\"scrollDialog\"\n\t\t\t\t\t\t\t/>\n\t\t\t\t\t\t\t<keep-alive include=\"bx-im-component-call-smiles\">\n\t\t\t\t\t\t\t\t<template v-if=\"callApplication.common.showSmiles\">\n\t\t\t\t\t\t\t\t\t<bx-im-component-call-smiles @selectSmile=\"onSmilesSelectSmile\" @selectSet=\"onSmilesSelectSet\"/>\t\n\t\t\t\t\t\t\t\t</template>\t\n\t\t\t\t\t\t\t</keep-alive>\n\t\t\t\t\t\t\t<div v-if=\"user && userHasRealName\" class=\"bx-im-component-call-textarea\">\n\t\t\t\t\t\t\t\t<bx-im-view-textarea\n\t\t\t\t\t\t\t\t\t:userId=\"userId\"\n\t\t\t\t\t\t\t\t\t:dialogId=\"dialogId\" \n\t\t\t\t\t\t\t\t\t:enableFile=\"true\"\n\t\t\t\t\t\t\t\t\t:enableEdit=\"true\"\n\t\t\t\t\t\t\t\t\t:enableCommand=\"false\"\n\t\t\t\t\t\t\t\t\t:enableMention=\"false\"\n\t\t\t\t\t\t\t\t\t:autoFocus=\"true\"\n\t\t\t\t\t\t\t\t\tlistenEventFocus=\"focusTextarea\"\n\t\t\t\t\t\t\t\t\tlistenEventInsertText=\"insertText\"\n\t\t\t\t\t\t\t\t\t@send=\"onTextareaSend\"\n\t\t\t\t\t\t\t\t\t@fileSelected=\"onTextareaFileSelected\"\n\t\t\t\t\t\t\t\t\t@writes=\"onTextareaWrites\"\n\t\t\t\t\t\t\t\t\t@appButtonClick=\"onTextareaAppButtonClick\"\n\t\t\t\t\t\t\t\t/>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<div v-else-if=\"user && !userHasRealName\" class=\"bx-im-component-call-textarea-guest\">\n\t\t\t\t\t\t\t\t<div class=\"bx-im-component-call-textarea-guest-title\">{{ localize['BX_IM_COMPONENT_CALL_INTRODUCE_YOURSELF'] }}</div>\n\t\t\t\t\t\t\t\t<div>\n\t\t\t\t\t\t\t\t\t<input ref=\"nameInput\" v-model=\"userNewName\" @keyup.enter=\"onNewNameButtonClick(userNewName)\" type=\"text\" :placeholder=\"localize['BX_IM_COMPONENT_CALL_INTRODUCE_YOURSELF_PLACEHOLDER']\" class=\"bx-im-component-call-textarea-guest-input\"/>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t<div>\n\t\t\t\t\t\t\t\t\t<button @click=\"onNewNameButtonClick(userNewName)\" :class=\"newNameButtonClasses\">{{ localize['BX_IM_COMPONENT_CALL_INTRODUCE_YOURSELF_BUTTON'] }}</button>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</div>\t\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</transition>\n\t\t\t</template>\n\t\t</div>\n\t"
	});

}((this.BX.Messenger = this.BX.Messenger || {}),BX,BX.Messenger.Lib,BX.Messenger.Const,window,window,BX));
//# sourceMappingURL=call.bundle.js.map
