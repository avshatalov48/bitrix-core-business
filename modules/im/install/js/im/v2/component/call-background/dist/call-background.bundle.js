/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,ui_vue3,ui_buttons,ui_fonts_opensans,im_v2_lib_progressbar,ui_infoHelper,im_v2_lib_utils,im_v2_lib_desktopApi,rest_client,im_v2_const,main_core,main_core_events,im_v2_lib_logger,im_lib_uploader) {
	'use strict';

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var Background = /*#__PURE__*/function () {
	  function Background(params) {
	    babelHelpers.classCallCheck(this, Background);
	    babelHelpers.defineProperty(this, "id", '');
	    babelHelpers.defineProperty(this, "title", '');
	    babelHelpers.defineProperty(this, "background", '');
	    babelHelpers.defineProperty(this, "preview", '');
	    babelHelpers.defineProperty(this, "isVideo", false);
	    babelHelpers.defineProperty(this, "isSupported", true);
	    babelHelpers.defineProperty(this, "isCustom", false);
	    babelHelpers.defineProperty(this, "canRemove", false);
	    babelHelpers.defineProperty(this, "isLoading", false);
	    babelHelpers.defineProperty(this, "uploadState", null);
	    Object.assign(this, params);
	  }
	  babelHelpers.createClass(Background, [{
	    key: "setUploadProgress",
	    value: function setUploadProgress(progress) {
	      this.uploadState.progress = progress;
	    }
	  }, {
	    key: "setUploadError",
	    value: function setUploadError() {
	      this.uploadState.status = im_v2_const.FileStatus.error;
	      this.uploadState.progress = 0;
	    }
	  }, {
	    key: "onUploadComplete",
	    value: function onUploadComplete(fileResult) {
	      this.id = fileResult.id;
	      if (this.isVideo) {
	        this.background = fileResult.links.download;
	      }
	      this.isLoading = false;
	      this.canRemove = true;
	    }
	  }], [{
	    key: "createDefaultFromRest",
	    value: function createDefaultFromRest(restItem) {
	      return new Background(_objectSpread(_objectSpread({}, restItem), {}, {
	        isVideo: restItem.id.includes(':video'),
	        isCustom: false,
	        canRemove: false,
	        isSupported: true
	      }));
	    }
	  }, {
	    key: "createCustomFromRest",
	    value: function createCustomFromRest(restItem) {
	      var title = main_core.Loc.getMessage('BX_IM_CALL_BG_CUSTOM');
	      if (!restItem.isSupported) {
	        title = main_core.Loc.getMessage('BX_IM_CALL_BG_UNSUPPORTED');
	      }
	      return new Background(_objectSpread(_objectSpread({}, restItem), {}, {
	        title: title,
	        isCustom: true,
	        canRemove: true
	      }));
	    }
	  }, {
	    key: "createCustomFromUploaderEvent",
	    value: function createCustomFromUploaderEvent(uploaderData) {
	      var id = uploaderData.id,
	        filePreview = uploaderData.filePreview,
	        file = uploaderData.file;
	      return new Background({
	        id: id,
	        background: filePreview,
	        preview: filePreview,
	        title: main_core.Loc.getMessage('BX_IM_CALL_BG_CUSTOM'),
	        isVideo: file.type.startsWith('video'),
	        isCustom: true,
	        canRemove: false,
	        isSupported: true,
	        isLoading: true,
	        uploadState: {
	          progress: 0,
	          status: im_v2_const.FileStatus.upload,
	          size: file.size
	        }
	      });
	    }
	  }]);
	  return Background;
	}();

	// @vue/component
	var BackgroundComponent = {
	  props: {
	    element: {
	      type: Object,
	      required: true
	    },
	    isSelected: {
	      type: Boolean,
	      required: true
	    }
	  },
	  emits: ['click', 'remove', 'cancel'],
	  data: function data() {
	    return {};
	  },
	  computed: {
	    background: function background() {
	      return this.element;
	    },
	    containerClasses: function containerClasses() {
	      var classes = [];
	      if (this.isSelected) {
	        classes.push('--selected');
	      }
	      if (!this.background.isSupported) {
	        classes.push('--unsupported');
	      }
	      if (this.background.isLoading) {
	        classes.push('--loading');
	      }
	      return classes;
	    },
	    imageStyle: function imageStyle() {
	      var backgroundImage = '';
	      if (this.background.preview) {
	        backgroundImage = "url('".concat(this.background.preview, "')");
	      }
	      return {
	        backgroundImage: backgroundImage
	      };
	    }
	  },
	  watch: {
	    'background.uploadState.status': function backgroundUploadStateStatus() {
	      this.getProgressBarManager().update();
	    },
	    'background.uploadState.progress': function backgroundUploadStateProgress() {
	      this.getProgressBarManager().update();
	    }
	  },
	  mounted: function mounted() {
	    this.initProgressBar();
	  },
	  beforeUnmount: function beforeUnmount() {
	    this.removeProgressBar();
	  },
	  methods: {
	    initProgressBar: function initProgressBar() {
	      var _this = this;
	      if (!this.background.uploadState || this.background.uploadState.progress === 100) {
	        return;
	      }
	      this.progressBarManager = new im_v2_lib_progressbar.ProgressBarManager({
	        container: this.$refs['container'],
	        uploadState: this.background.uploadState
	      });
	      this.progressBarManager.subscribe(im_v2_lib_progressbar.ProgressBarManager.event.cancel, function () {
	        _this.$emit('cancel', _this.background);
	      });
	      this.progressBarManager.subscribe(im_v2_lib_progressbar.ProgressBarManager.event.destroy, function () {
	        if (_this.progressBar) {
	          _this.progressBar = null;
	        }
	      });
	      this.progressBarManager.start();
	    },
	    removeProgressBar: function removeProgressBar() {
	      if (!this.progressBarManager) {
	        return;
	      }
	      this.progressBarManager.destroy();
	    },
	    getProgressBarManager: function getProgressBarManager() {
	      return this.progressBarManager;
	    },
	    loc: function loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: "\n\t\t<div @click=\"$emit('click')\" :class=\"containerClasses\" class=\"bx-im-call-background__item\" ref=\"container\">\n\t\t\t<div :style=\"imageStyle\" class=\"bx-im-call-background__item_image\"></div>\n\t\t\t<div v-if=\"background.isSupported && background.isVideo\" class=\"bx-im-call-background__item_video\"></div>\n\t\t\t<div v-if=\"!background.isLoading\" class=\"bx-im-call-background__item_title_container\">\n\t\t\t\t<span class=\"bx-im-call-background__item_title\">{{background.title}}</span>\n\t\t\t\t<div\n\t\t\t\t\tv-if=\"background.canRemove\"\n\t\t\t\t\t:title=\"loc('BX_IM_CALL_BG_REMOVE')\"\n\t\t\t\t\t@click.stop=\"$emit('remove')\"\n\t\t\t\t\tclass=\"bx-im-call-background__item_remove\"\n\t\t\t\t></div>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	var Action = /*#__PURE__*/function () {
	  function Action(type) {
	    babelHelpers.classCallCheck(this, Action);
	    var id = Action.type.none;
	    var background = Action.type.none;
	    var title = main_core.Loc.getMessage('BX_IM_CALL_BG_ACTION_NONE');
	    if (type === Action.type.upload) {
	      id = type;
	      background = type;
	      title = main_core.Loc.getMessage('BX_IM_CALL_BG_ACTION_UPLOAD');
	    } else if (type === Action.type.gaussianBlur) {
	      id = type;
	      background = type;
	      title = main_core.Loc.getMessage('BX_IM_CALL_BG_ACTION_BLUR');
	    } else if (type === Action.type.blur) {
	      id = type;
	      background = type;
	      title = main_core.Loc.getMessage('BX_IM_CALL_BG_ACTION_BLUR_MAX');
	    }
	    this.id = id;
	    this.background = background;
	    this.title = title;
	  }
	  babelHelpers.createClass(Action, [{
	    key: "isEmpty",
	    value: function isEmpty() {
	      return this.id === Action.type.none;
	    }
	  }, {
	    key: "isBlur",
	    value: function isBlur() {
	      return this.id === Action.type.gaussianBlur || this.id === Action.type.blur;
	    }
	  }, {
	    key: "isUpload",
	    value: function isUpload() {
	      return this.id === Action.type.upload;
	    }
	  }]);
	  return Action;
	}();
	babelHelpers.defineProperty(Action, "type", {
	  none: 'none',
	  upload: 'upload',
	  blur: 'blur',
	  gaussianBlur: 'gaussianBlur'
	});

	// @vue/component
	var ActionComponent = {
	  props: {
	    element: {
	      type: Object,
	      required: true
	    },
	    isSelected: {
	      type: Boolean,
	      required: true
	    }
	  },
	  data: function data() {
	    return {};
	  },
	  computed: {
	    action: function action() {
	      return this.element;
	    },
	    containerClasses: function containerClasses() {
	      var classes = ["--".concat(this.action.id)];
	      if (this.isSelected) {
	        classes.push('--selected');
	      }
	      return classes;
	    }
	  },
	  template: "\n\t\t<div :class=\"containerClasses\" class=\"bx-im-call-background__item --action\">\n\t\t\t<div class=\"bx-im-call-background__action_icon\"></div>\n\t\t\t<div class=\"bx-im-call-background__action_title\">\n\t\t\t\t{{ action.title }}\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	var Mask = /*#__PURE__*/function () {
	  function Mask(params) {
	    babelHelpers.classCallCheck(this, Mask);
	    babelHelpers.defineProperty(this, "id", '');
	    babelHelpers.defineProperty(this, "active", true);
	    babelHelpers.defineProperty(this, "mask", '');
	    babelHelpers.defineProperty(this, "background", '');
	    babelHelpers.defineProperty(this, "preview", '');
	    babelHelpers.defineProperty(this, "title", '');
	    babelHelpers.defineProperty(this, "isLoading", false);
	    Object.assign(this, params);
	  }
	  babelHelpers.createClass(Mask, [{
	    key: "isEmpty",
	    value: function isEmpty() {
	      return this.id === '';
	    }
	  }], [{
	    key: "createEmpty",
	    value: function createEmpty() {
	      return new Mask({
	        active: true,
	        id: '',
	        mask: '',
	        preview: '',
	        background: '',
	        title: main_core.Loc.getMessage('BX_IM_CALL_BG_NO_MASK_TITLE')
	      });
	    }
	  }, {
	    key: "createFromRest",
	    value: function createFromRest(rawMask) {
	      var active = rawMask.active,
	        id = rawMask.id,
	        mask = rawMask.mask,
	        background = rawMask.background,
	        preview = rawMask.preview,
	        title = rawMask.title;
	      return new Mask({
	        active: active,
	        id: id,
	        mask: mask,
	        preview: preview,
	        background: background,
	        title: title
	      });
	    }
	  }]);
	  return Mask;
	}();

	// @vue/component
	var MaskComponent = {
	  props: {
	    element: {
	      type: Object,
	      required: true
	    },
	    isSelected: {
	      type: Boolean,
	      required: true
	    }
	  },
	  data: function data() {
	    return {};
	  },
	  computed: {
	    mask: function mask() {
	      return this.element;
	    },
	    containerClasses: function containerClasses() {
	      var classes = ["--".concat(this.mask.id)];
	      if (this.isSelected) {
	        classes.push('--selected');
	      }
	      if (!this.mask.active) {
	        classes.push('--inactive');
	      }
	      return classes;
	    },
	    imageStyle: function imageStyle() {
	      var backgroundImage = '';
	      if (this.mask.preview) {
	        backgroundImage = "url('".concat(this.mask.preview, "')");
	      }
	      return {
	        backgroundImage: backgroundImage
	      };
	    }
	  },
	  methods: {
	    loc: function loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: "\n\t\t<div :class=\"containerClasses\" class=\"bx-im-call-background__item --mask\">\n\t\t\t<div v-if=\"!mask.active\" class=\"bx-im-call-background__mask_fade\"></div>\n\t\t\t<div class=\"bx-im-call-background__mask_background\"></div>\n\t\t\t<div :style=\"imageStyle\" class=\"bx-im-call-background__item_image\"></div>\n\t\t\t<div v-if=\"mask.isLoading\" class=\"bx-im-call-background__mask_loading-container\">\n\t\t\t\t<div class=\"bx-im-call-background__mask_loading-icon\"></div>\n\t\t\t\t<div class=\"bx-im-call-background__mask_loading-text\">{{ loc('BX_IM_CALL_BG_MASK_LOADING') }}</div>\n\t\t\t</div>\n\t\t\t<div v-else-if=\"!mask.active\" class=\"bx-im-call-background__mask_soon-container\">\n\t\t\t\t<div class=\"bx-im-call-background__mask_soon-text\">{{ loc('BX_IM_CALL_BG_MASK_COMING_SOON') }}</div>\n\t\t\t</div>\n\t\t\t<div v-else class=\"bx-im-call-background__mask_title\">{{ mask.title }}</div>\n\t\t</div>\n\t"
	};

	// @vue/component
	var Loader = {
	  name: 'CallBackgroundLoader',
	  data: function data() {
	    return {};
	  },
	  template: "\n\t\t<div class=\"bx-im-call-background__loader\">\n\t\t\t<svg class=\"bx-desktop-loader-circular\" viewBox=\"25 25 50 50\">\n\t\t\t\t<circle class=\"bx-desktop-loader-path\" cx=\"50\" cy=\"50\" r=\"20\" fill=\"none\" stroke-miterlimit=\"10\"/>\n\t\t\t</svg>\n\t\t</div>\n\t"
	};

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _initLimits = /*#__PURE__*/new WeakSet();
	var _initInfoHelper = /*#__PURE__*/new WeakSet();
	var _limitIsActive = /*#__PURE__*/new WeakSet();
	var LimitManager = /*#__PURE__*/function () {
	  function LimitManager(params) {
	    babelHelpers.classCallCheck(this, LimitManager);
	    _classPrivateMethodInitSpec(this, _limitIsActive);
	    _classPrivateMethodInitSpec(this, _initInfoHelper);
	    _classPrivateMethodInitSpec(this, _initLimits);
	    babelHelpers.defineProperty(this, "limits", {});
	    var _limits = params.limits,
	      _infoHelperUrlTemplate = params.infoHelperUrlTemplate;
	    _classPrivateMethodGet(this, _initLimits, _initLimits2).call(this, _limits);
	    _classPrivateMethodGet(this, _initInfoHelper, _initInfoHelper2).call(this, _infoHelperUrlTemplate);
	  }
	  babelHelpers.createClass(LimitManager, [{
	    key: "isLimitedAction",
	    value: function isLimitedAction(action) {
	      if (action.isEmpty() || action.isUpload()) {
	        return false;
	      }
	      return action.isBlur() && _classPrivateMethodGet(this, _limitIsActive, _limitIsActive2).call(this, LimitManager.limitCode.blur);
	    }
	  }, {
	    key: "isLimitedBackground",
	    value: function isLimitedBackground() {
	      return _classPrivateMethodGet(this, _limitIsActive, _limitIsActive2).call(this, LimitManager.limitCode.image);
	    }
	  }, {
	    key: "showLimitSlider",
	    value: function showLimitSlider(limitCode) {
	      window.BX.UI.InfoHelper.show(this.limits[limitCode].articleCode);
	    } // region Mask feature
	  }], [{
	    key: "isMaskFeatureAvailable",
	    value: function isMaskFeatureAvailable() {
	      if (!im_v2_lib_utils.Utils.platform.isBitrixDesktop()) {
	        return true;
	      }
	      return im_v2_lib_desktopApi.DesktopApi.isFeatureEnabled(im_v2_lib_desktopApi.DesktopFeature.mask.id);
	    }
	  }, {
	    key: "isMaskFeatureSupportedByDesktopVersion",
	    value: function isMaskFeatureSupportedByDesktopVersion() {
	      if (!im_v2_lib_utils.Utils.platform.isBitrixDesktop()) {
	        return true;
	      }
	      return im_v2_lib_desktopApi.DesktopApi.isFeatureSupported(im_v2_lib_desktopApi.DesktopFeature.mask.id);
	    } // endregion Mask feature
	  }, {
	    key: "showHelpArticle",
	    value: function showHelpArticle(articleCode) {
	      var _window$BX$Helper;
	      (_window$BX$Helper = window.BX.Helper) === null || _window$BX$Helper === void 0 ? void 0 : _window$BX$Helper.show("redirect=detail&code=".concat(articleCode));
	    }
	  }]);
	  return LimitManager;
	}();
	function _initLimits2(limits) {
	  var _this = this;
	  limits.forEach(function (limit) {
	    _this.limits[limit.id] = limit;
	  });
	}
	function _initInfoHelper2(infoHelperUrlTemplate) {
	  if (window.BX.UI.InfoHelper.isInited()) {
	    return;
	  }
	  window.BX.UI.InfoHelper.init({
	    frameUrlTemplate: infoHelperUrlTemplate
	  });
	}
	function _limitIsActive2(limitCode) {
	  var _this$limits$limitCod, _this$limits$limitCod2;
	  var limitIsActive = !!((_this$limits$limitCod = this.limits[limitCode]) !== null && _this$limits$limitCod !== void 0 && _this$limits$limitCod.active);
	  var articleIsActive = !!((_this$limits$limitCod2 = this.limits[limitCode]) !== null && _this$limits$limitCod2 !== void 0 && _this$limits$limitCod2.articleCode);
	  return limitIsActive && articleIsActive;
	}
	babelHelpers.defineProperty(LimitManager, "limitCode", {
	  blur: 'call_blur_background',
	  image: 'call_background'
	});

	var TabId = {
	  mask: 'mask',
	  background: 'background'
	};
	var MASK_HELP_ARTICLE_CODE = 12398124;

	// @vue/component
	var TabPanel = {
	  props: {
	    selectedTab: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['tabChange'],
	  data: function data() {
	    return {};
	  },
	  computed: {
	    tabs: function tabs() {
	      var tabs = [];
	      if (LimitManager.isMaskFeatureAvailable()) {
	        tabs.push({
	          id: TabId.mask,
	          loc: 'BX_IM_CALL_BG_TAB_MASK',
	          isNew: true
	        });
	      }
	      tabs.push({
	        id: TabId.background,
	        loc: 'BX_IM_CALL_BG_TAB_BG',
	        isNew: false
	      });
	      return tabs;
	    }
	  },
	  methods: {
	    loc: function loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-call-background__tab-panel\">\n\t\t\t<div\n\t\t\t\tv-for=\"tab in tabs\"\n\t\t\t\t:key=\"tab.id\"\n\t\t\t\t@click=\"$emit('tabChange', tab.id)\"\n\t\t\t\t:class=\"{'--active': selectedTab === tab.id, '--new': tab.isNew}\"\n\t\t\t\tclass=\"bx-im-call-background__tab\"\n\t\t\t>\n\t\t\t\t<div v-if=\"tab.isNew\" class=\"bx-im-call-background__tab_new\">{{ loc('BX_IM_CALL_BG_TAB_NEW') }}</div>\n\t\t\t\t<div class=\"bx-im-call-background__tab_text\">{{ loc(tab.loc) }}</div>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var VIDEO_CONSTRAINT_WIDTH = 1280;
	var VIDEO_CONSTRAINT_HEIGHT = 720;

	// @vue/component
	var VideoPreview = {
	  data: function data() {
	    return {
	      noVideo: false
	    };
	  },
	  computed: {
	    videoClasses: function videoClasses() {
	      return {
	        '--flipped': BX.Call.Hardware.enableMirroring
	      };
	    }
	  },
	  created: function created() {
	    var _this = this;
	    this.initHardware().then(function () {
	      _this.getDefaultDevices();
	    })["catch"](function (error) {
	      console.error('VideoPreview: error initing hardware', error);
	    });
	  },
	  beforeUnmount: function beforeUnmount() {
	    this.videoStream.getTracks().forEach(function (tr) {
	      return tr.stop();
	    });
	    this.videoStream = null;
	  },
	  methods: {
	    getDefaultDevices: function getDefaultDevices() {
	      var _this2 = this;
	      var constraints = {
	        audio: false,
	        video: true
	      };
	      constraints.video = {};
	      constraints.video.width = {
	        ideal: VIDEO_CONSTRAINT_WIDTH
	      };
	      constraints.video.height = {
	        ideal: VIDEO_CONSTRAINT_HEIGHT
	      };
	      if (BX.Call.Hardware.defaultCamera) {
	        this.selectedCamera = BX.Call.Hardware.defaultCamera;
	        constraints.video = _objectSpread$1(_objectSpread$1({}, constraints.video), {
	          deviceId: {
	            exact: this.selectedCamera
	          }
	        });
	      } else if (Object.keys(BX.Call.Hardware.cameraList).length === 0) {
	        console.error('VideoPreview: no camera');
	        return;
	      }
	      navigator.mediaDevices.getUserMedia(constraints).then(function (stream) {
	        _this2.videoStream = stream;
	        if (stream.getVideoTracks().length === 0) {
	          _this2.noVideo = true;
	          console.error('VideoPreview: no video tracks');
	          return;
	        }
	        if (!_this2.selectedCamera) {
	          _this2.selectedCamera = stream.getVideoTracks()[0].getSettings().deviceId;
	        }
	        _this2.playLocalVideo();
	      });
	    },
	    playLocalVideo: function playLocalVideo() {
	      im_v2_lib_logger.Logger.warn('VideoPreview: playing local video');
	      this.$refs['video'].volume = 0;
	      this.$refs['video'].srcObject = this.videoStream;
	      this.$refs['video'].play();
	    },
	    initHardware: function initHardware() {
	      return BX.Call.Hardware.init();
	    },
	    loc: function loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-call-background__video\">\n\t\t\t<div v-if=\"noVideo\" class=\"bx-im-call-background__no-cam_container\">\n\t\t\t\t<div class=\"bx-im-call-background__no-cam_icon\"></div>\n\t\t\t\t<div class=\"bx-im-call-background__no-cam_title\">{{ loc('BX_IM_CALL_BG_NO_CAM') }}</div>\n\t\t\t</div>\n\t\t\t<video v-else :class=\"videoClasses\" ref=\"video\" muted autoplay playsinline></video>\n\t\t</div>\n\t"
	};

	var BackgroundService = /*#__PURE__*/function () {
	  function BackgroundService() {
	    babelHelpers.classCallCheck(this, BackgroundService);
	  }
	  babelHelpers.createClass(BackgroundService, [{
	    key: "getElementsList",
	    value: function getElementsList() {
	      var _query;
	      var query = (_query = {}, babelHelpers.defineProperty(_query, im_v2_const.RestMethod.imCallBackgroundGet, [im_v2_const.RestMethod.imCallBackgroundGet]), babelHelpers.defineProperty(_query, im_v2_const.RestMethod.imCallMaskGet, [im_v2_const.RestMethod.imCallMaskGet]), _query);
	      return new Promise(function (resolve, reject) {
	        rest_client.rest.callBatch(query, function (response) {
	          im_v2_lib_logger.Logger.warn('BackgroundService: getElementsList result', response);
	          var backgroundResult = response[im_v2_const.RestMethod.imCallBackgroundGet];
	          var maskResult = response[im_v2_const.RestMethod.imCallMaskGet];
	          if (backgroundResult.error()) {
	            console.error('BackgroundService: error getting background list', backgroundResult.error());
	            return reject('Error getting background list');
	          }
	          if (maskResult.error()) {
	            console.error('BackgroundService: error getting mask list', maskResult.error());
	            return reject('Error getting mask list');
	          }
	          return resolve({
	            backgroundResult: backgroundResult.data(),
	            maskResult: maskResult.data()
	          });
	        });
	      });
	    }
	  }, {
	    key: "commitBackground",
	    value: function commitBackground(fileId) {
	      return rest_client.rest.callMethod(im_v2_const.RestMethod.imCallBackgroundCommit, {
	        fileId: fileId
	      });
	    }
	  }, {
	    key: "deleteFile",
	    value: function deleteFile(fileId) {
	      return rest_client.rest.callMethod(im_v2_const.RestMethod.imCallBackgroundDelete, {
	        fileId: fileId
	      });
	    }
	  }]);
	  return BackgroundService;
	}();

	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var FILE_MAX_SIZE = 100 * 1024 * 1024;
	var FILE_MAX_SIZE_PHRASE_NUMBER = 100;
	var UPLOAD_CHUNK_SIZE = 1024 * 1024;
	var NOTIFICATION_HIDE_DELAY = 5000;
	var CUSTOM_BG_TASK_PREFIX = 'custom';
	var EVENT_NAMESPACE = 'BX.Messenger.v2.CallBackground.UploadManager';
	var _bindEvents = /*#__PURE__*/new WeakSet();
	var _onFileMaxSizeExceeded = /*#__PURE__*/new WeakSet();
	var _onSelectFile = /*#__PURE__*/new WeakSet();
	var _onStartUpload = /*#__PURE__*/new WeakSet();
	var _onProgress = /*#__PURE__*/new WeakSet();
	var _onComplete = /*#__PURE__*/new WeakSet();
	var _onUploadError = /*#__PURE__*/new WeakSet();
	var _addUploadTask = /*#__PURE__*/new WeakSet();
	var _isAllowedType = /*#__PURE__*/new WeakSet();
	var _showNotification = /*#__PURE__*/new WeakSet();
	var UploadManager = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(UploadManager, _EventEmitter);
	  function UploadManager(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, UploadManager);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(UploadManager).call(this));
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _showNotification);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _isAllowedType);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _addUploadTask);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _onUploadError);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _onComplete);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _onProgress);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _onStartUpload);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _onSelectFile);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _onFileMaxSizeExceeded);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _bindEvents);
	    _this.setEventNamespace(EVENT_NAMESPACE);
	    var inputNode = params.inputNode;
	    _this.uploader = new im_lib_uploader.Uploader({
	      inputNode: inputNode,
	      generatePreview: true,
	      fileMaxSize: FILE_MAX_SIZE
	    });
	    _classPrivateMethodGet$1(babelHelpers.assertThisInitialized(_this), _bindEvents, _bindEvents2).call(babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }
	  babelHelpers.createClass(UploadManager, [{
	    key: "setDiskFolderId",
	    value: function setDiskFolderId(diskFolderId) {
	      this.diskFolderId = diskFolderId;
	    }
	  }, {
	    key: "cancelUpload",
	    value: function cancelUpload(fileId) {
	      this.uploader.deleteTask(fileId);
	    } // region events
	  }]);
	  return UploadManager;
	}(main_core_events.EventEmitter);
	function _bindEvents2() {
	  this.uploader.subscribe('onFileMaxSizeExceeded', _classPrivateMethodGet$1(this, _onFileMaxSizeExceeded, _onFileMaxSizeExceeded2).bind(this));
	  this.uploader.subscribe('onSelectFile', _classPrivateMethodGet$1(this, _onSelectFile, _onSelectFile2).bind(this));
	  this.uploader.subscribe('onStartUpload', _classPrivateMethodGet$1(this, _onStartUpload, _onStartUpload2).bind(this));
	  this.uploader.subscribe('onProgress', _classPrivateMethodGet$1(this, _onProgress, _onProgress2).bind(this));
	  this.uploader.subscribe('onComplete', _classPrivateMethodGet$1(this, _onComplete, _onComplete2).bind(this));
	  this.uploader.subscribe('onUploadFileError', _classPrivateMethodGet$1(this, _onUploadError, _onUploadError2).bind(this));
	  this.uploader.subscribe('onCreateFileError', _classPrivateMethodGet$1(this, _onUploadError, _onUploadError2).bind(this));
	}
	function _onFileMaxSizeExceeded2(event) {
	  im_v2_lib_logger.Logger.warn('UploadManager: onFileMaxSizeExceeded', event);
	  var eventData = event.getData();
	  var file = eventData.file;
	  var phrase = main_core.Loc.getMessage('BX_IM_CALL_BG_FILE_SIZE_EXCEEDED').replace('#LIMIT#', FILE_MAX_SIZE_PHRASE_NUMBER).replace('#FILE_NAME#', file.name);
	  _classPrivateMethodGet$1(this, _showNotification, _showNotification2).call(this, phrase);
	}
	function _onSelectFile2(event) {
	  im_v2_lib_logger.Logger.warn('UploadManager: onSelectFile', event);
	  var _event$getData = event.getData(),
	    file = _event$getData.file,
	    previewData = _event$getData.previewData;
	  if (!_classPrivateMethodGet$1(this, _isAllowedType, _isAllowedType2).call(this, file.type) || !previewData) {
	    var phrase = main_core.Loc.getMessage('BX_IM_CALL_BG_UNSUPPORTED_FILE').replace('#FILE_NAME#', file.name);
	    _classPrivateMethodGet$1(this, _showNotification, _showNotification2).call(this, phrase);
	    return false;
	  }
	  _classPrivateMethodGet$1(this, _addUploadTask, _addUploadTask2).call(this, file, previewData);
	}
	function _onStartUpload2(event) {
	  im_v2_lib_logger.Logger.warn('UploadManager: onStartUpload', event);
	  var _event$getData2 = event.getData(),
	    previewData = _event$getData2.previewData,
	    id = _event$getData2.id,
	    file = _event$getData2.file;
	  var filePreview = URL.createObjectURL(previewData);
	  this.emit(UploadManager.event.uploadStart, {
	    id: id,
	    filePreview: filePreview,
	    file: file
	  });
	}
	function _onProgress2(event) {
	  im_v2_lib_logger.Logger.warn('UploadManager: onProgress', event);
	  var _event$getData3 = event.getData(),
	    id = _event$getData3.id,
	    progress = _event$getData3.progress;
	  this.emit(UploadManager.event.uploadProgress, {
	    id: id,
	    progress: progress
	  });
	}
	function _onComplete2(event) {
	  im_v2_lib_logger.Logger.warn('UploadManager: onComplete', event);
	  var _event$getData4 = event.getData(),
	    id = _event$getData4.id,
	    result = _event$getData4.result;
	  this.emit(UploadManager.event.uploadComplete, {
	    id: id,
	    fileResult: result.data.file
	  });
	}
	function _onUploadError2(event) {
	  im_v2_lib_logger.Logger.warn('UploadManager: onUploadError', event);
	  var eventData = event.getData();
	  this.emit(UploadManager.event.uploadError, {
	    id: eventData.id
	  });
	}
	function _addUploadTask2(file, previewData) {
	  this.uploader.addTask({
	    taskId: "".concat(CUSTOM_BG_TASK_PREFIX, ":").concat(Date.now()),
	    chunkSize: UPLOAD_CHUNK_SIZE,
	    fileData: file,
	    fileName: file.name,
	    diskFolderId: this.diskFolderId,
	    generateUniqueName: true,
	    previewBlob: previewData
	  });
	}
	function _isAllowedType2(fileType) {
	  return UploadManager.allowedFileTypes.includes(fileType);
	}
	function _showNotification2(text) {
	  BX.UI.Notification.Center.notify({
	    content: text,
	    autoHideDelay: NOTIFICATION_HIDE_DELAY
	  });
	}
	babelHelpers.defineProperty(UploadManager, "allowedFileTypes", ['image/png', 'image/jpg', 'image/jpeg', 'video/avi', 'video/mp4', 'video/quicktime']);
	babelHelpers.defineProperty(UploadManager, "event", {
	  uploadStart: 'uploadStart',
	  uploadProgress: 'uploadProgress',
	  uploadComplete: 'uploadComplete',
	  uploadError: 'uploadError'
	});

	// @vue/component
	var CallBackground = {
	  name: 'CallBackground',
	  components: {
	    BackgroundComponent: BackgroundComponent,
	    ActionComponent: ActionComponent,
	    MaskComponent: MaskComponent,
	    Loader: Loader,
	    TabPanel: TabPanel,
	    VideoPreview: VideoPreview
	  },
	  props: {
	    tab: {
	      type: String,
	      "default": TabId.background
	    }
	  },
	  data: function data() {
	    return {
	      selectedTab: '',
	      selectedBackgroundId: '',
	      selectedMaskId: '',
	      loadingItems: true,
	      actions: [],
	      defaultBackgrounds: [],
	      customBackgrounds: [],
	      masks: [],
	      listIsScrolled: false
	    };
	  },
	  computed: {
	    TabId: function TabId$$1() {
	      return TabId;
	    },
	    backgrounds: function backgrounds() {
	      return [].concat(babelHelpers.toConsumableArray(this.customBackgrounds), babelHelpers.toConsumableArray(this.defaultBackgrounds));
	    },
	    containerClasses: function containerClasses() {
	      var classes = [];
	      if (this.isDesktop) {
	        classes.push('--desktop');
	      }
	      return classes;
	    },
	    uploadTypes: function uploadTypes() {
	      return UploadManager.allowedFileTypes.join(', ');
	    },
	    descriptionText: function descriptionText() {
	      var replaces = {
	        '#HIGHLIGHT_START#': '<span class="bx-im-call-background__description_highlight">',
	        '#HIGHLIGHT_END#': '</span>',
	        '#BR#': '</br></br>'
	      };
	      if (this.selectedTab === TabId.mask) {
	        return this.loc('BX_IM_CALL_BG_DESCRIPTION_MASK_2', replaces);
	      }
	      return this.loc('BX_IM_CALL_BG_DESCRIPTION_BG', replaces);
	    },
	    isDesktop: function isDesktop() {
	      return im_v2_lib_utils.Utils.platform.isBitrixDesktop();
	    }
	  },
	  created: function created() {
	    var _this = this;
	    this.initSelectedTab();
	    this.getBackgroundService().getElementsList().then(function (result) {
	      var backgroundResult = result.backgroundResult,
	        maskResult = result.maskResult;
	      _this.initLimitManager(backgroundResult);
	      _this.initBackgroundList(backgroundResult);
	      _this.uploadManager.setDiskFolderId(backgroundResult.upload.folderId);
	      var uploadActionIsAvailable = !!backgroundResult.upload.folderId;
	      _this.initActions(uploadActionIsAvailable);
	      _this.initMasks(maskResult);
	      _this.initMaskLoadEventHandler();
	      _this.initPreviouslySelectedItem();
	      _this.loadingItems = false;
	      _this.hideLoader();
	    })["catch"](function () {
	      _this.loadingItems = false;
	    });
	  },
	  mounted: function mounted() {
	    this.initUploader();
	  },
	  methods: {
	    // region init
	    initSelectedTab: function initSelectedTab() {
	      if (this.tab === TabId.mask && !LimitManager.isMaskFeatureAvailable()) {
	        this.selectedTab = TabId.background;
	        return;
	      }
	      if (this.tab === TabId.mask && !LimitManager.isMaskFeatureSupportedByDesktopVersion()) {
	        this.selectedTab = TabId.background;
	        LimitManager.showHelpArticle(MASK_HELP_ARTICLE_CODE);
	        return;
	      }
	      this.selectedTab = this.tab;
	    },
	    initPreviouslySelectedItem: function initPreviouslySelectedItem() {
	      this.initPreviouslySelectedMask();
	      this.initPreviouslySelectedBackground();
	    },
	    initPreviouslySelectedMask: function initPreviouslySelectedMask() {
	      if (this.isDesktop) {
	        var _DesktopApi$getCallMa = im_v2_lib_desktopApi.DesktopApi.getCallMask(),
	          maskId = _DesktopApi$getCallMa.id;
	        var foundMask = this.masks.find(function (mask) {
	          return mask.id === maskId;
	        });
	        if (!foundMask) {
	          foundMask = Mask.createEmpty();
	        }
	        this.previouslySelectedMask = foundMask;
	        im_v2_lib_logger.Logger.warn('CallBackground: previously selected mask', this.previouslySelectedMask);
	      } else {
	        this.previouslySelectedMask = Mask.createEmpty();
	      }
	      this.selectedMaskId = this.previouslySelectedMask.id;
	    },
	    initPreviouslySelectedBackground: function initPreviouslySelectedBackground() {
	      if (this.isDesktop) {
	        var _DesktopApi$getBackgr = im_v2_lib_desktopApi.DesktopApi.getBackgroundImage(),
	          backgroundId = _DesktopApi$getBackgr.id;
	        var itemsToSearch = [].concat(babelHelpers.toConsumableArray(this.actions), babelHelpers.toConsumableArray(this.backgrounds));
	        var foundBackground = itemsToSearch.find(function (item) {
	          return item.id === backgroundId;
	        });
	        if (!foundBackground) {
	          foundBackground = new Action(Action.type.none);
	        }
	        this.previouslySelectedBackground = foundBackground;
	        im_v2_lib_logger.Logger.warn('CallBackground: previously selected background', this.previouslySelectedBackground);
	      } else {
	        this.previouslySelectedBackground = new Action(Action.type.none);
	      }
	      this.selectedBackgroundId = this.previouslySelectedBackground.id;
	    },
	    initActions: function initActions(uploadActionIsAvailable) {
	      this.actions = [new Action(Action.type.none)].concat(babelHelpers.toConsumableArray(uploadActionIsAvailable ? [new Action(Action.type.upload)] : []), [new Action(Action.type.gaussianBlur), new Action(Action.type.blur)]);
	    },
	    initBackgroundList: function initBackgroundList(restResult) {
	      var _this2 = this;
	      this.defaultBackgrounds = [];
	      restResult.backgrounds["default"].forEach(function (background) {
	        _this2.defaultBackgrounds.push(Background.createDefaultFromRest(background));
	      });
	      this.customBackgrounds = [];
	      restResult.backgrounds.custom.forEach(function (background) {
	        _this2.customBackgrounds.push(Background.createCustomFromRest(background));
	      });
	    },
	    initLimitManager: function initLimitManager(result) {
	      var limits = result.limits,
	        infoHelperParams = result.infoHelperParams;
	      this.limitManager = new LimitManager({
	        limits: limits,
	        infoHelperUrlTemplate: infoHelperParams.frameUrlTemplate
	      });
	    },
	    initUploader: function initUploader() {
	      var _this3 = this;
	      this.uploadManager = new UploadManager({
	        inputNode: this.$refs['uploadInput']
	      });
	      this.uploadManager.subscribe(UploadManager.event.uploadStart, function (event) {
	        var backgroundsInstance = Background.createCustomFromUploaderEvent(event.getData());
	        _this3.customBackgrounds.unshift(backgroundsInstance);
	      });
	      this.uploadManager.subscribe(UploadManager.event.uploadProgress, function (event) {
	        var _event$getData = event.getData(),
	          id = _event$getData.id,
	          progress = _event$getData.progress;
	        var background = _this3.findCustomBackgroundById(id);
	        if (!background) {
	          return;
	        }
	        background.setUploadProgress(progress);
	      });
	      this.uploadManager.subscribe(UploadManager.event.uploadComplete, function (event) {
	        var _event$getData2 = event.getData(),
	          id = _event$getData2.id,
	          fileResult = _event$getData2.fileResult;
	        var background = _this3.findCustomBackgroundById(id);
	        if (!background) {
	          return;
	        }
	        background.onUploadComplete(fileResult);
	        _this3.onBackgroundClick(background);
	        _this3.getBackgroundService().commitBackground(background.id);
	      });
	      this.uploadManager.subscribe(UploadManager.event.uploadError, function (event) {
	        var _event$getData3 = event.getData(),
	          id = _event$getData3.id;
	        var background = _this3.findCustomBackgroundById(id);
	        if (!background) {
	          return;
	        }
	        background.setUploadError();
	      });
	    },
	    initMasks: function initMasks(result) {
	      var _this4 = this;
	      var masks = result.masks;
	      this.masks.push(Mask.createEmpty());
	      masks.forEach(function (mask) {
	        _this4.masks.push(Mask.createFromRest(mask));
	      });
	    },
	    initMaskLoadEventHandler: function initMaskLoadEventHandler() {
	      if (!this.isDesktop) {
	        return;
	      }
	      this.maskLoadTimeouts = {};
	      im_v2_lib_desktopApi.DesktopApi.setCallMaskLoadHandlers(this.onMaskLoad.bind(this));
	    },
	    // endregion init
	    // region component events
	    onActionClick: function onActionClick(action) {
	      if (this.getLimitManager().isLimitedAction(action)) {
	        this.getLimitManager().showLimitSlider(LimitManager.limitCode.blur);
	        return;
	      }
	      if (action.isUpload()) {
	        this.$refs['uploadInput'].click();
	        return;
	      }
	      this.selectedBackgroundId = action.id;
	      if (action.isEmpty()) {
	        this.removeCallBackground();
	        return;
	      }
	      this.selectedMaskId = '';
	      this.setCallBlur(action);
	    },
	    onBackgroundClick: function onBackgroundClick(background) {
	      if (this.getLimitManager().isLimitedBackground()) {
	        this.getLimitManager().showLimitSlider(LimitManager.limitCode.image);
	        return;
	      }
	      if (!background.isSupported || background.isLoading) {
	        return;
	      }
	      this.selectedBackgroundId = background.id;
	      this.selectedMaskId = '';
	      this.setCallBackground(background);
	    },
	    onBackgroundRemove: function onBackgroundRemove(background) {
	      if (background.id === this.selectedBackgroundId) {
	        this.selectedBackgroundId = Action.type.none;
	        this.removeCallBackground();
	      }
	      if (background.isLoading) {
	        this.uploadManager.cancelUpload(background.id);
	      } else {
	        this.getBackgroundService().deleteFile(background.id);
	      }
	      this.customBackgrounds = this.customBackgrounds.filter(function (element) {
	        return element.id !== background.id;
	      });
	    },
	    onMaskClick: function onMaskClick(mask) {
	      if (!mask.active) {
	        return;
	      }
	      if (mask.isEmpty()) {
	        this.selectedMaskId = mask.id;
	        this.removeCallMask();
	      }
	      this.setCallMask(mask);
	    },
	    onSaveButtonClick: function onSaveButtonClick() {
	      window.close();
	    },
	    onCancelButtonClick: function onCancelButtonClick() {
	      var _this5 = this;
	      var backgroundWasChanged = this.previouslySelectedBackground.id !== this.selectedBackgroundId;
	      var maskWasChanged = this.previouslySelectedMask.id !== this.selectedMaskId;
	      if (!backgroundWasChanged && !maskWasChanged) {
	        window.close();
	        return;
	      }
	      var backgroundPromise = Promise.resolve();
	      if (backgroundWasChanged) {
	        backgroundPromise = this.setCallBackground(this.previouslySelectedBackground);
	      }
	      backgroundPromise.then(function () {
	        if (maskWasChanged && !_this5.previouslySelectedMask.isEmpty()) {
	          _this5.setCallMask(_this5.previouslySelectedMask);
	          _this5.isWaitingForMaskToCancel = true;
	        } else if (_this5.previouslySelectedMask.isEmpty()) {
	          _this5.removeCallMask();
	          window.close();
	        } else {
	          window.close();
	        }
	      });
	    },
	    onListScroll: function onListScroll(event) {
	      if (event.target.scrollTop === 0) {
	        this.listIsScrolled = false;
	        return;
	      }
	      this.listIsScrolled = true;
	    },
	    onTabChange: function onTabChange(newTabId) {
	      if (newTabId === TabId.mask && !LimitManager.isMaskFeatureSupportedByDesktopVersion()) {
	        LimitManager.showHelpArticle(MASK_HELP_ARTICLE_CODE);
	        return;
	      }
	      this.selectedTab = newTabId;
	    },
	    onMaskLoad: function onMaskLoad(url) {
	      im_v2_lib_logger.Logger.warn('CallBackground: onMaskLoad', url);
	      if (this.isWaitingForMaskToCancel) {
	        window.close();
	        return;
	      }
	      var masksWithoutEmpty = this.masks.filter(function (mask) {
	        return !mask.isEmpty();
	      });
	      var loadedMask = masksWithoutEmpty.find(function (mask) {
	        return url.includes(mask.mask);
	      });
	      im_v2_lib_logger.Logger.warn('CallBackground: loaded mask', loadedMask);
	      if (!loadedMask) {
	        return;
	      }
	      clearTimeout(this.maskLoadTimeouts[loadedMask.id]);
	      loadedMask.isLoading = false;
	      if (this.lastRequestedMaskId === loadedMask.id) {
	        this.selectedMaskId = loadedMask.id;
	      }
	    },
	    // endregion component events
	    // region desktop interactions
	    setCallBackground: function setCallBackground(backgroundInstance) {
	      im_v2_lib_logger.Logger.warn('CallBackground: set background', backgroundInstance);
	      if (!this.isDesktop) {
	        return;
	      }
	      return im_v2_lib_desktopApi.DesktopApi.setCallBackground(backgroundInstance.id, backgroundInstance.background);
	    },
	    setCallBlur: function setCallBlur(action) {
	      im_v2_lib_logger.Logger.warn('CallBackground: set blur', action);
	      if (!this.isDesktop) {
	        return;
	      }
	      return im_v2_lib_desktopApi.DesktopApi.setCallBackground(action.id, action.background);
	    },
	    removeCallBackground: function removeCallBackground() {
	      if (!this.isDesktop) {
	        return;
	      }
	      return im_v2_lib_desktopApi.DesktopApi.setCallBackground(Action.type.none, Action.type.none);
	    },
	    setCallMask: function setCallMask(mask) {
	      im_v2_lib_logger.Logger.warn('CallBackground: set mask', mask);
	      if (!this.isDesktop) {
	        return;
	      }
	      if (mask.isEmpty()) {
	        im_v2_lib_logger.Logger.warn('CallBackground: empty mask - removing it');
	        im_v2_lib_desktopApi.DesktopApi.setCallMask();
	        return;
	      }
	      this.lastRequestedMaskId = mask.id;
	      var MASK_LOAD_STATUS_DELAY = 500;
	      this.maskLoadTimeouts[mask.id] = setTimeout(function () {
	        mask.isLoading = true;
	      }, MASK_LOAD_STATUS_DELAY);
	      im_v2_lib_desktopApi.DesktopApi.setCallMask(mask.id, mask.mask, mask.background);
	    },
	    removeCallMask: function removeCallMask() {
	      if (!this.isDesktop) {
	        return;
	      }
	      im_v2_lib_desktopApi.DesktopApi.setCallMask();
	    },
	    hideLoader: function hideLoader() {
	      if (!this.isDesktop) {
	        return;
	      }
	      im_v2_lib_desktopApi.DesktopApi.hideLoader();
	    },
	    // endregion desktop interactions
	    findCustomBackgroundById: function findCustomBackgroundById(id) {
	      return this.customBackgrounds.find(function (element) {
	        return element.id === id;
	      });
	    },
	    getBackgroundService: function getBackgroundService() {
	      if (!this.backgroundService) {
	        this.backgroundService = new BackgroundService();
	      }
	      return this.backgroundService;
	    },
	    getLimitManager: function getLimitManager() {
	      return this.limitManager;
	    },
	    loc: function loc(phraseCode) {
	      var replacements = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: "\n\t\t<div :class=\"{'--desktop': isDesktop}\" class=\"bx-im-call-background__scope bx-im-call-background__container\">\n\t\t\t<div v-if=\"loadingItems\" class=\"bx-im-call-background__loader_container\">\n\t\t\t\t<Loader />\n\t\t\t</div>\n\t\t\t<div v-else class=\"bx-im-call-background__content\">\n\t\t\t\t<div class=\"bx-im-call-background__left\">\n\t\t\t\t\t<VideoPreview />\n\t\t\t\t\t<div v-html=\"descriptionText\" class=\"bx-im-call-background__description\"></div>\n\t\t\t\t</div>\n\t\t\t\t<div :class=\"{'--scrolled': listIsScrolled}\" class=\"bx-im-call-background__right\">\n\t\t\t\t\t<TabPanel :selectedTab=\"selectedTab\" @tabChange=\"onTabChange\" />\n\t\t\t\t\t<div v-if=\"selectedTab === TabId.background\" @scroll=\"onListScroll\" class=\"bx-im-call-background__list\">\n\t\t\t\t\t\t<ActionComponent\n\t\t\t\t\t\t\tv-for=\"action in actions\"\n\t\t\t\t\t\t\t:element=\"action\"\n\t\t\t\t\t\t\t:key=\"action.id\"\n\t\t\t\t\t\t\t:isSelected=\"selectedBackgroundId === action.id\"\n\t\t\t\t\t\t\t@click=\"onActionClick(action)\"\n\t\t\t\t\t\t/>\n\t\t\t\t\t\t<BackgroundComponent\n\t\t\t\t\t\t\tv-for=\"background in backgrounds\"\n\t\t\t\t\t\t\t:element=\"background\"\n\t\t\t\t\t\t\t:key=\"background.id\"\n\t\t\t\t\t\t\t:isSelected=\"selectedBackgroundId === background.id\"\n\t\t\t\t\t\t\t@click=\"onBackgroundClick(background)\"\n\t\t\t\t\t\t\t@cancel=\"onBackgroundRemove(background)\"\n\t\t\t\t\t\t\t@remove=\"onBackgroundRemove(background)\"\n\t\t\t\t\t\t/>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div v-else-if=\"selectedTab === TabId.mask\" @scroll=\"onListScroll\" class=\"bx-im-call-background__list\">\n\t\t\t\t\t\t<MaskComponent\n\t\t\t\t\t\t\tv-for=\"mask in masks\"\n\t\t\t\t\t\t\t:element=\"mask\"\n\t\t\t\t\t\t\t:key=\"mask.id\"\n\t\t\t\t\t\t\t:isSelected=\"selectedMaskId === mask.id\"\n\t\t\t\t\t\t\t@click=\"onMaskClick(mask)\"\n\t\t\t\t\t\t/>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\t\n\t\t\t</div>\n\t\t\t<div class=\"bx-im-call-background__button-panel\">\n\t\t\t\t<button @click=\"onSaveButtonClick\" :class=\"{'ui-btn-wait ui-btn-disabled': loadingItems}\" class=\"ui-btn ui-btn-success\">\n\t\t\t\t\t{{ loc('BX_IM_CALL_BG_SAVE') }}\n\t\t\t\t</button>\n\t\t\t\t<button @click=\"onCancelButtonClick\" class=\"ui-btn ui-btn-link\">\n\t\t\t\t\t{{ loc('BX_IM_CALL_BG_CANCEL') }}\n\t\t\t\t</button>\n\t\t\t</div>\n\t\t</div>\n\t\t<div class=\"bx-im-call-background__upload-input\">\n\t\t\t<input type=\"file\" :accept=\"uploadTypes\" ref=\"uploadInput\"/>\n\t\t</div>\n\t"
	};

	exports.CallBackground = CallBackground;

}((this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {}),BX.Vue3,BX.UI,BX,BX.Messenger.v2.Lib,BX.UI,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Const,BX,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.Lib));
//# sourceMappingURL=call-background.bundle.js.map
