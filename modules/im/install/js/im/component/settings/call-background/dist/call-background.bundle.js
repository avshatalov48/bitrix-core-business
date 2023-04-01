this.BX = this.BX || {};
(function (exports,main_core,im_lib_uploader,im_lib_utils,rest_client,ui_infoHelper,ui_notification,ui_fonts_opensans,ui_vue,ui_progressbarjs_uploader,im_const) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * File element Vue component
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2021 Bitrix
	 */
	var CallBackgroundItem = {
	  props: {
	    selected: {
	      type: Boolean,
	      "default": false
	    },
	    item: {
	      type: Object,
	      "default": {}
	    }
	  },
	  mounted: function mounted() {
	    this.createProgressbar();
	  },
	  beforeDestroy: function beforeDestroy() {
	    this.removeProgressbar();
	  },
	  methods: {
	    createProgressbar: function createProgressbar() {
	      var _this = this;
	      if (this.uploader) {
	        return true;
	      }
	      if (!this.item.state) {
	        return true;
	      }
	      if (this.item.state.progress === 100) {
	        return false;
	      }
	      this.uploader = new ui_progressbarjs_uploader.Uploader({
	        container: this.$refs.container,
	        labels: {
	          loading: this.localize['BX_IM_COMPONENT_SETTINGS_CALL_BG_FILE_UPLOAD_LOADING'],
	          completed: this.localize['BX_IM_COMPONENT_SETTINGS_CALL_BG_FILE_UPLOAD_COMPLETED'],
	          canceled: this.localize['BX_IM_COMPONENT_SETTINGS_CALL_BG_FILE_UPLOAD_CANCELED'],
	          cancelTitle: this.localize['BX_IM_COMPONENT_SETTINGS_CALL_BG_FILE_UPLOAD_CANCEL_TITLE'],
	          megabyte: this.localize['BX_IM_COMPONENT_SETTINGS_CALL_BG_FILE_SIZE_MB']
	        },
	        cancelCallback: this.item.state.progress < 0 ? null : function (event) {
	          _this.$emit('cancel', {
	            item: _this.item,
	            event: event
	          });
	        },
	        destroyCallback: function destroyCallback() {
	          if (_this.uploader) {
	            _this.uploader = null;
	          }
	        }
	      });
	      this.uploader.start();
	      if (this.item.state.size && this.item.state.size / 1024 / 1024 <= 2 || this.$refs.container.offsetHeight <= 54 && this.$refs.container.offsetWidth < 240) {
	        this.uploader.setProgressTitleVisibility(false);
	      }
	      this.updateProgressbar();
	      return true;
	    },
	    updateProgressbar: function updateProgressbar() {
	      if (!this.uploader) {
	        var result = this.createProgressbar();
	        if (!result) {
	          return false;
	        }
	      }
	      if (this.item.state.status === im_const.FileStatus.error) {
	        this.uploader.setProgress(0);
	        this.uploader.setCancelDisable(false);
	        this.uploader.setIcon(ui_progressbarjs_uploader.Uploader.icon.error);
	        this.uploader.setProgressTitle(this.localize['BX_IM_COMPONENT_SETTINGS_CALL_BG_FILE_UPLOAD_ERROR']);
	      } else if (this.item.state.status === im_const.FileStatus.wait) {
	        this.uploader.setProgress(this.item.state.progress > 5 ? this.item.state.progress : 5);
	        this.uploader.setCancelDisable(true);
	        this.uploader.setIcon(ui_progressbarjs_uploader.Uploader.icon.cloud);
	        this.uploader.setProgressTitle(this.localize['BX_IM_COMPONENT_SETTINGS_CALL_BG_FILE_UPLOAD_SAVING']);
	      } else if (this.item.state.progress === 100) {
	        this.uploader.setProgress(100);
	      } else if (this.item.state.progress === -1) {
	        this.uploader.setProgress(10);
	        this.uploader.setProgressTitle(this.localize['BX_IM_COMPONENT_SETTINGS_CALL_BG_FILE_UPLOAD_WAITING']);
	      } else {
	        if (this.item.state.progress === 0) {
	          this.uploader.setIcon(ui_progressbarjs_uploader.Uploader.icon.cancel);
	        }
	        var progress = this.item.state.progress > 5 ? this.item.state.progress : 5;
	        this.uploader.setProgress(progress);
	        if (this.item.state.size / 1024 / 1024 <= 2) {
	          this.uploader.setProgressTitle(this.localize['BX_IM_COMPONENT_SETTINGS_CALL_BG_FILE_UPLOAD_LOADING']);
	        } else {
	          this.uploader.setByteSent(this.item.state.size / 100 * this.item.state.progress, this.item.state.size);
	        }
	      }
	    },
	    removeProgressbar: function removeProgressbar() {
	      if (!this.uploader) {
	        return true;
	      }
	      this.uploader.destroy(false);
	      return true;
	    }
	  },
	  computed: {
	    uploadProgress: function uploadProgress() {
	      if (!this.item.state) {
	        return '';
	      }
	      return this.item.state.status + ' ' + this.item.state.progress;
	    },
	    localize: function localize() {
	      return ui_vue.BitrixVue.getFilteredPhrases('BX_IM_COMPONENT_SETTINGS_CALL_BG_', this);
	    }
	  },
	  watch: {
	    uploadProgress: function uploadProgress() {
	      this.updateProgressbar();
	    }
	  },
	  template: "\n\t\t<div :key=\"item.id\" @click=\"$emit('select')\" :class=\"['bx-im-settings-video-background-dialog-item', {'bx-im-settings-video-background-dialog-item-selected': selected, 'bx-im-settings-video-background-dialog-item-unsupported': !item.isSupported , 'bx-im-settings-video-background-dialog-item-loading': item.isLoading }]\" ref=\"container\">\n\t\t\t<div class=\"bx-im-settings-video-background-dialog-item-image\" :style=\"{backgroundImage: item.preview? 'url('+item.preview+')': ''}\"></div>\n\t\t\t<div v-if=\"item.isSupported && item.isVideo\" class=\"bx-im-settings-video-background-dialog-item-video\"></div>\n\t\t\t<div v-if=\"!item.isLoading\" class=\"bx-im-settings-video-background-dialog-item-title\">\n\t\t\t\t<span class=\"bx-im-settings-video-background-dialog-item-title-text\">{{item.title}}</span>\n\t\t\t\t<div v-if=\"item.canRemove\" class=\"bx-im-settings-video-background-dialog-item-remove\" :title=\"localize.BX_IM_COMPONENT_SETTINGS_CALL_BG_REMOVE\" @click=\"$emit('remove')\"></div>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	var ActionType = Object.freeze({
	  none: 'none',
	  upload: 'upload',
	  blur: 'blur',
	  gaussianBlur: 'gaussianBlur'
	});
	var LimitCode = Object.freeze({
	  blur: 'call_blur_background',
	  image: 'call_background'
	});
	ui_vue.BitrixVue.component('bx-im-component-settings-call-background', {
	  props: {
	    isDesktop: {
	      type: Boolean,
	      "default": false
	    },
	    width: {
	      "default": 0
	    },
	    height: {
	      "default": 450
	    }
	  },
	  data: function data() {
	    return {
	      actions: [],
	      standard: [],
	      custom: [],
	      selected: '',
	      ActionType: ActionType,
	      loading: true,
	      diskFolderId: 0
	    };
	  },
	  components: {
	    'bx-im-component-settings-call-background-item': CallBackgroundItem
	  },
	  created: function created() {
	    var _this = this;
	    this.defaultValue = this.isDesktop ? window.BX.desktop.getBackgroundImage() : {
	      id: ActionType.none,
	      background: ''
	    };
	    this.selected = this.defaultValue.id;
	    this.limit = {};
	    rest_client.rest.callMethod("im.v2.call.background.get").then(function (response) {
	      _this.loading = false;
	      _this.diskFolderId = response.data().upload.folderId;
	      response.data().backgrounds["default"].forEach(function (element) {
	        element.isVideo = element.id.includes(':video');
	        element.isCustom = false;
	        element.canRemove = false;
	        element.isSupported = true;
	        _this.standard.push(element);
	      });
	      response.data().backgrounds.custom.forEach(function (element) {
	        element.isCustom = true;
	        element.canRemove = true;
	        if (element.isSupported) {
	          element.title = main_core.Loc.getMessage('BX_IM_COMPONENT_SETTINGS_CALL_BG_CUSTOM');
	        } else {
	          element.title = main_core.Loc.getMessage('BX_IM_COMPONENT_SETTINGS_CALL_BG_UNSUPPORTED');
	        }
	        _this.custom.push(element);
	      });
	      response.data().limits.forEach(function (element) {
	        _this.limit[element.id] = element;
	      });
	      if (_this.diskFolderId) {
	        _this.actions = _this.actions.map(function (element) {
	          element.isSupported = true;
	          return element;
	        });
	      } else {
	        _this.actions = _this.actions.filter(function (element) {
	          return element.id !== ActionType.upload;
	        });
	      }
	      if (!window.BX.UI.InfoHelper.isInited()) {
	        window.BX.UI.InfoHelper.init({
	          frameUrlTemplate: response.data().infoHelperParams.frameUrlTemplate
	        });
	      }
	      if (_this.isDesktop) {
	        window.BX.desktop.hideLoader();
	      }
	    })["catch"](function () {
	      _this.loading = false;
	    });
	    this.actions.push({
	      id: ActionType.none,
	      title: main_core.Loc.getMessage('BX_IM_COMPONENT_SETTINGS_CALL_BG_ACTION_NONE'),
	      background: ActionType.none
	    });
	    this.actions.push({
	      id: ActionType.upload,
	      title: main_core.Loc.getMessage('BX_IM_COMPONENT_SETTINGS_CALL_BG_ACTION_UPLOAD')
	    });
	    this.actions.push({
	      id: ActionType.gaussianBlur,
	      title: main_core.Loc.getMessage('BX_IM_COMPONENT_SETTINGS_CALL_BG_ACTION_BLUR'),
	      background: ActionType.gaussianBlur
	    });
	    this.actions.push({
	      id: ActionType.blur,
	      title: main_core.Loc.getMessage('BX_IM_COMPONENT_SETTINGS_CALL_BG_ACTION_BLUR_MAX'),
	      background: ActionType.blur
	    });
	  },
	  mounted: function mounted() {
	    var _this2 = this;
	    this.uploader = new im_lib_uploader.Uploader({
	      inputNode: this.$refs.uploadInput,
	      generatePreview: true,
	      fileMaxSize: 100 * 1024 * 1024
	    });
	    this.uploader.subscribe('onFileMaxSizeExceeded', function (event) {
	      var eventData = event.getData();
	      var file = eventData.file;
	      BX.UI.Notification.Center.notify({
	        content: main_core.Loc.getMessage('BX_IM_COMPONENT_SETTINGS_CALL_BG_FILE_SIZE_EXCEEDED').replace('#LIMIT#', 100).replace('#FILE_NAME#', file.name),
	        autoHideDelay: 5000
	      });
	    });
	    this.uploader.subscribe('onSelectFile', function (event) {
	      var eventData = event.getData();
	      var file = eventData.file;
	      if (!_this2.isAllowedType(file.type) || !eventData.previewData) {
	        BX.UI.Notification.Center.notify({
	          content: main_core.Loc.getMessage('BX_IM_COMPONENT_SETTINGS_CALL_BG_UNSUPPORTED_FILE').replace('#FILE_NAME#', file.name),
	          autoHideDelay: 5000
	        });
	        return false;
	      }
	      _this2.uploader.addTask({
	        taskId: "custom:".concat(Date.now()),
	        chunkSize: 1024 * 1024,
	        fileData: file,
	        fileName: file.name,
	        diskFolderId: _this2.diskFolderId,
	        generateUniqueName: true,
	        previewBlob: eventData.previewData
	      });
	    });
	    this.uploader.subscribe('onStartUpload', function (event) {
	      var eventData = event.getData();
	      var filePreview = URL.createObjectURL(eventData.previewData);
	      _this2.custom.unshift({
	        id: eventData.id,
	        background: filePreview,
	        preview: filePreview,
	        title: main_core.Loc.getMessage('BX_IM_COMPONENT_SETTINGS_CALL_BG_CUSTOM'),
	        isVideo: eventData.file.type.startsWith('video'),
	        isCustom: true,
	        canRemove: false,
	        isSupported: true,
	        isLoading: true,
	        state: {
	          progress: 0,
	          status: im_const.FileStatus.upload,
	          size: eventData.file.size
	        }
	      });
	    });
	    this.uploader.subscribe('onProgress', function (event) {
	      var eventData = event.getData();
	      var element = _this2.custom.find(function (element) {
	        return element.id === eventData.id;
	      });
	      if (!element) {
	        return;
	      }
	      element.state.progress = eventData.progress;
	    });
	    this.uploader.subscribe('onComplete', function (event) {
	      var eventData = event.getData();
	      var element = _this2.custom.find(function (element) {
	        return element.id === eventData.id;
	      });
	      if (!element) {
	        return;
	      }
	      element.id = eventData.result.data.file.id;
	      if (element.isVideo) {
	        element.background = eventData.result.data.file.links.download;
	      }
	      element.isLoading = false;
	      element.canRemove = true;
	      _this2.select(element);
	      rest_client.rest.callMethod('im.v2.call.background.commit', {
	        fileId: element.id
	      });
	    });
	    this.uploader.subscribe('onUploadFileError', function (event) {
	      var eventData = event.getData();
	      var element = _this2.custom.find(function (element) {
	        return element.id === eventData.id;
	      });
	      if (!element) {
	        return;
	      }
	      element.state.status = im_const.FileStatus.error;
	      element.state.progress = 0;
	    });
	    this.uploader.subscribe('onCreateFileError', function (event) {
	      var eventData = event.getData();
	      var element = _this2.custom.find(function (element) {
	        return element.id === eventData.id;
	      });
	      if (!element) {
	        return;
	      }
	      element.state.status = im_const.FileStatus.error;
	      element.state.progress = 0;
	    });
	  },
	  computed: {
	    isMaskAvailable: function isMaskAvailable() {
	      if (window.BX.getClass('BX.desktop')) {
	        return window.BX.desktop.getApiVersion() >= 72;
	      } else if (window.BX.getClass("BX.Messenger.Lib.Utils.platform")) {
	        return window.BX.Messenger.Lib.Utils.platform.getDesktopVersion() >= 72;
	      }
	    },
	    containerSize: function containerSize() {
	      var result = {};
	      if (this.isDesktop) {
	        result.height = 'calc(100vh - 79px)'; // 79 button panel
	      } else {
	        result.height = this.height + 'px';
	      }
	      if (this.width > 0) {
	        result.width = this.width + 'px';
	      }
	      return result;
	    },
	    backgrounds: function backgrounds() {
	      return [].concat(this.custom).concat(this.standard);
	    },
	    uploadTypes: function uploadTypes() {
	      if (im_lib_utils.Utils.platform.isBitrixDesktop()) {
	        return '';
	      }
	      return '.png, .jpg, .jpeg, .avi, .mp4';
	    }
	  },
	  methods: {
	    hasLimit: function hasLimit(elementId) {
	      if (elementId === ActionType.none) {
	        return true;
	      }
	      if ([ActionType.blur, ActionType.gaussianBlur].includes(elementId)) {
	        if (this.limit[LimitCode.blur] && this.limit[LimitCode.blur].active && this.limit[LimitCode.blur].articleCode && window.BX.UI.InfoHelper) {
	          window.BX.UI.InfoHelper.show(this.limit[LimitCode.blur].articleCode);
	          return false;
	        }
	        return true;
	      }
	      if (this.limit[LimitCode.image] && this.limit[LimitCode.image].active && this.limit[LimitCode.image].articleCode && window.BX.UI.InfoHelper) {
	        window.BX.UI.InfoHelper.show(this.limit[LimitCode.image].articleCode);
	        return false;
	      }
	      return true;
	    },
	    select: function select(element) {
	      if (!this.hasLimit(element.id)) {
	        return false;
	      }
	      if (!element.isSupported || element.isLoading) {
	        return false;
	      }
	      if (element.id === ActionType.upload) {
	        this.$refs.uploadInput.click();
	        return false;
	      }
	      this.selected = element.id;
	      if (this.isDesktop) {
	        window.BX.desktop.setCallBackground(element.id, element.background);
	      }
	      return true;
	    },
	    remove: function remove(element) {
	      if (element.id === this.selected) {
	        this.selected = ActionType.none;
	        if (this.isDesktop) {
	          window.BX.desktop.setCallBackground(ActionType.none, ActionType.none);
	        }
	      }
	      if (element.isLoading) {
	        this.uploader.deleteTask(element.id);
	      } else {
	        main_core.ajax.runAction('disk.api.file.delete', {
	          data: {
	            fileId: element.id
	          }
	        });
	      }
	      this.custom = this.custom.filter(function (el) {
	        return el.id !== element.id;
	      });
	      return true;
	    },
	    save: function save() {
	      window.close();
	    },
	    cancel: function cancel() {
	      if (this.defaultValue.id === this.selected) {
	        window.close();
	        return true;
	      }
	      if (this.isDesktop) {
	        window.BX.desktop.setCallBackground(this.defaultValue.id, this.defaultValue.background).then(function () {
	          window.close();
	        });
	      } else {
	        window.close();
	      }
	      return true;
	    },
	    isAllowedType: function isAllowedType(type) {
	      return ['image/png', 'image/jpeg', 'video/avi', 'video/mp4', 'video/quicktime'].includes(type);
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-settings-video-background-dialog\">\n\t\t\t<div class=\"bx-im-settings-video-background-dialog-inner\" :style=\"containerSize\">\n\t\t\t\t<div class=\"bx-im-settings-video-background-dialog-container\">\n\t\t\t\t\t<div class=\"bx-im-settings-video-background-upload-input\"><input type=\"file\" :accept=\"uploadTypes\" ref=\"uploadInput\"/></div>\n\t\t\t\t\t<template v-if=\"loading\">\n\t\t\t\t\t\t<div class=\"bx-im-settings-video-background-dialog-loader\">\n\t\t\t\t\t\t\t<svg class=\"bx-desktop-loader-circular\" viewBox=\"25 25 50 50\">\n\t\t\t\t\t\t\t\t<circle class=\"bx-desktop-loader-path\" cx=\"50\" cy=\"50\" r=\"20\" fill=\"none\" stroke-miterlimit=\"10\"/>\n\t\t\t\t\t\t\t</svg>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</template>\n\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t<div class=\"bx-im-settings-video-background-dialog-content\">\n\t\t\t\t\t\t<template v-for=\"(element in actions\">\n\t\t\t\t\t\t\t<div :key=\"element.id\" @click=\"select(element)\" :class=\"['bx-im-settings-video-background-dialog-item', 'bx-im-settings-video-background-dialog-action', 'bx-im-settings-video-background-dialog-action-'+element.id, {'bx-im-settings-video-background-dialog-item-selected': selected === element.id }]\">\n\t\t\t\t\t\t\t\t<div class=\"bx-im-settings-video-background-dialog-action-title\">{{element.title}}</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\n\t\t\t\t\t\t<template v-for=\"(item in backgrounds\">\n\t\t\t\t\t\t\t<bx-im-component-settings-call-background-item \n\t\t\t\t\t\t\t\t:key=\"item.id\" \n\t\t\t\t\t\t\t\t:item=\"item\" \n\t\t\t\t\t\t\t\t:selected=\"selected === item.id\" \n\t\t\t\t\t\t\t\t@select=\"select(item)\" \n\t\t\t\t\t\t\t\t@cancel=\"remove(item)\"\n\t\t\t\t\t\t\t\t@remove=\"remove(item)\"\n\t\t\t\t\t\t\t/>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</template>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<div class=\"ui-btn-container ui-btn-container-center\">\n\t\t\t\t<button :class=\"['ui-btn', 'ui-btn-success', {'ui-btn-wait ui-btn-disabled': loading}]\" @click=\"save\">{{$Bitrix.Loc.getMessage('BX_IM_COMPONENT_SETTINGS_CALL_BG_SAVE')}}</button>\n\t\t\t\t<button class=\"ui-btn ui-btn-link\" @click=\"cancel\">{{$Bitrix.Loc.getMessage('BX_IM_COMPONENT_SETTINGS_CALL_BG_CANCEL')}}</button>\n\t\t\t</div>\n\t\t</div>\n\t"
	});

}((this.BX.Messenger = this.BX.Messenger || {}),BX,BX.Messenger.Lib,BX.Messenger.Lib,BX,BX,BX,BX,BX,BX.ProgressBarJs,BX.Messenger.Const));
//# sourceMappingURL=call-background.bundle.js.map
