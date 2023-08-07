/* eslint-disable */
(function (exports,ui_progressbarjs_uploader,ui_vue_vuex,im_model,main_core_events,im_const,ui_vue_components_audioplayer,ui_designTokens,ui_vue_directives_lazyload,ui_icons,ui_vue_components_socialvideo,im_lib_utils,ui_vue) {
	'use strict';

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	ui_vue.BitrixVue.component('bx-im-view-element-file', {
	  /*
	   * @emits EventType.dialog.clickOnUploadCancel {file: object, event: MouseEvent}
	   */
	  mounted: function mounted() {
	    this.createProgressbar();
	  },
	  beforeDestroy: function beforeDestroy() {
	    this.removeProgressbar();
	  },
	  props: {
	    userId: {
	      "default": 0
	    },
	    messageType: {
	      "default": im_const.MessageType.self
	    },
	    file: {
	      type: Object,
	      required: true
	    }
	  },
	  methods: {
	    download: function download(file) {
	      if (file.progress !== 100) {
	        return false;
	      }
	      if (BX.UI && BX.UI.Viewer && Object.keys(file.viewerAttrs).length > 0) {
	        return false;
	      }
	      if (file.type === im_const.FileType.image && file.urlShow) {
	        if (im_lib_utils.Utils.platform.isBitrixMobile()) {
	          BXMobileApp.UI.Photo.show({
	            photos: this.files.collection[this.application.dialog.chatId].filter(function (file) {
	              return file.type === 'image';
	            }).map(function (file) {
	              return {
	                url: file.urlShow.replace('bxhttp', 'http')
	              };
	            }).reverse(),
	            default_photo: file.urlShow.replace('bxhttp', 'http')
	          });
	        } else {
	          window.open(file.urlShow, '_blank');
	        }
	      } else if (file.type === im_const.FileType.video && file.urlShow) {
	        if (im_lib_utils.Utils.platform.isBitrixMobile()) {
	          app.openDocument({
	            url: file.urlShow,
	            name: file.name
	          });
	        } else {
	          window.open(file.urlShow, '_blank');
	        }
	      } else if (file.urlDownload) {
	        if (im_lib_utils.Utils.platform.isBitrixMobile()) {
	          app.openDocument({
	            url: file.urlDownload,
	            name: file.name
	          });
	        } else {
	          window.open(file.urlDownload, '_blank');
	        }
	      } else {
	        if (im_lib_utils.Utils.platform.isBitrixMobile()) {
	          app.openDocument({
	            url: file.urlShow,
	            name: file.name
	          });
	        } else {
	          window.open(file.urlShow, '_blank');
	        }
	      }
	    },
	    createProgressbar: function createProgressbar() {
	      var _this = this;
	      if (this.uploader) {
	        return true;
	      }
	      if (this.file.progress === 100) {
	        return false;
	      }
	      var blurElement = undefined;
	      if (this.file.progress < 0 || this.file.type !== im_const.FileType.image && this.file.type !== im_const.FileType.video) {
	        blurElement = false;
	      }
	      this.uploader = new ui_progressbarjs_uploader.Uploader({
	        container: this.$refs.container,
	        blurElement: blurElement,
	        direction: this.$refs.container.offsetHeight > 54 ? ui_progressbarjs_uploader.Uploader.direction.vertical : ui_progressbarjs_uploader.Uploader.direction.horizontal,
	        icon: this.file.progress < 0 ? ui_progressbarjs_uploader.Uploader.icon.cloud : ui_progressbarjs_uploader.Uploader.icon.cancel,
	        sizes: {
	          circle: this.$refs.container.offsetHeight > 54 ? 54 : 38,
	          progress: this.$refs.container.offsetHeight > 54 ? 4 : 8
	        },
	        labels: {
	          loading: this.localize['IM_MESSENGER_ELEMENT_FILE_UPLOAD_LOADING'],
	          completed: this.localize['IM_MESSENGER_ELEMENT_FILE_UPLOAD_COMPLETED'],
	          canceled: this.localize['IM_MESSENGER_ELEMENT_FILE_UPLOAD_CANCELED'],
	          cancelTitle: this.localize['IM_MESSENGER_ELEMENT_FILE_UPLOAD_CANCEL_TITLE'],
	          megabyte: this.localize['IM_MESSENGER_ELEMENT_FILE_SIZE_MB']
	        },
	        cancelCallback: this.file.progress < 0 ? null : function (event) {
	          main_core_events.EventEmitter.emit(im_const.EventType.dialog.clickOnUploadCancel, {
	            file: _this.file,
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
	      if (this.file.size && this.file.size / 1024 / 1024 <= 2 || this.$refs.container.offsetHeight <= 54 && this.$refs.container.offsetWidth < 240) {
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
	      if (this.file.status === im_const.FileStatus.error) {
	        this.uploader.setProgress(0);
	        this.uploader.setCancelDisable(false);
	        this.uploader.setIcon(ui_progressbarjs_uploader.Uploader.icon.error);
	        this.uploader.setProgressTitle(this.localize['IM_MESSENGER_ELEMENT_FILE_UPLOAD_ERROR']);
	      } else if (this.file.status === im_const.FileStatus.wait) {
	        this.uploader.setProgress(this.file.progress > 5 ? this.file.progress : 5);
	        this.uploader.setCancelDisable(true);
	        this.uploader.setIcon(ui_progressbarjs_uploader.Uploader.icon.cloud);
	        this.uploader.setProgressTitle(this.localize['IM_MESSENGER_ELEMENT_FILE_UPLOAD_SAVING']);
	      } else if (this.file.progress === 100) {
	        this.uploader.setProgress(100);
	      } else if (this.file.progress === -1) {
	        this.uploader.setProgress(10);
	        this.uploader.setProgressTitle(this.localize['IM_MESSENGER_ELEMENT_FILE_UPLOAD_WAITING']);
	      } else {
	        if (this.file.progress === 0) {
	          this.uploader.setIcon(ui_progressbarjs_uploader.Uploader.icon.cancel);
	        }
	        var progress = this.file.progress > 5 ? this.file.progress : 5;
	        this.uploader.setProgress(progress);
	        if (this.file.size / 1024 / 1024 <= 2) {
	          this.uploader.setProgressTitle(this.localize['IM_MESSENGER_ELEMENT_FILE_UPLOAD_LOADING']);
	        } else {
	          this.uploader.setByteSent(this.file.size / 100 * this.file.progress, this.file.size);
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
	  computed: _objectSpread({
	    FileStatus: function FileStatus() {
	      return im_const.FileStatus;
	    },
	    localize: function localize() {
	      return ui_vue.BitrixVue.getFilteredPhrases('IM_MESSENGER_ELEMENT_FILE_', this);
	    },
	    fileName: function fileName() {
	      var maxLength = 70;
	      if (this.file.name.length < maxLength) {
	        return this.file.name;
	      }
	      var endWordLength = 10;
	      var secondPart = this.file.name.substring(this.file.name.length - 1 - (this.file.extension.length + 1 + endWordLength));
	      var firstPart = this.file.name.substring(0, maxLength - secondPart.length - 3);
	      return firstPart.trim() + '...' + secondPart.trim();
	    },
	    fileSize: function fileSize() {
	      var size = this.file.size;
	      if (size <= 0) {
	        return '&nbsp;';
	      }
	      var sizes = ["BYTE", "KB", "MB", "GB", "TB"];
	      var position = 0;
	      while (size >= 1024 && position < 4) {
	        size /= 1024;
	        position++;
	      }
	      return Math.round(size) + " " + this.localize['IM_MESSENGER_ELEMENT_FILE_SIZE_' + sizes[position]];
	    },
	    uploadProgress: function uploadProgress() {
	      return this.file.status + ' ' + this.file.progress;
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    application: function application(state) {
	      return state.application;
	    },
	    files: function files(state) {
	      return state.files;
	    }
	  })),
	  watch: {
	    uploadProgress: function uploadProgress() {
	      this.updateProgressbar();
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-element-file\" @click=\"download(file, $event)\" ref=\"container\">\n\t\t\t<div class=\"bx-im-element-file-icon\">\n\t\t\t\t<div :class=\"['ui-icon', 'ui-icon-file-'+file.icon]\"><i></i></div>\n\t\t\t</div>\n\t\t\t<div class=\"bx-im-element-file-block\">\n\t\t\t\t<div class=\"bx-im-element-file-name\" :title=\"file.name\">\n\t\t\t\t\t{{fileName}}\n\t\t\t\t</div>\n\t\t\t\t<div class=\"bx-im-element-file-size\" v-html=\"fileSize\"></div>\n\t\t\t</div>\n\t\t</div>\n\t"
	});

	/**
	 * Bitrix Messenger
	 * File element Vue component
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	ui_vue.Vue.cloneComponent('bx-im-view-element-file-audio', 'bx-im-view-element-file', {
	  computed: {
	    background: function background() {
	      return this.messageType === im_const.MessageType.self ? 'dark' : 'light';
	    }
	  },
	  template: "\n\t\t<div :class=\"['bx-im-element-file-audio', 'bx-im-element-file-audio-'+messageType]\" ref=\"container\">\n\t\t\t<bx-audioplayer :id=\"file.id\" :src=\"file.urlShow\" :background=\"background\"/>\n\t\t</div>\t\n\t"
	});

	/**
	 * Bitrix Messenger
	 * File element Vue component
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	ui_vue.Vue.cloneComponent('bx-im-view-element-file-image', 'bx-im-view-element-file', {
	  methods: {
	    getImageSize: function getImageSize(width, height, maxWidth) {
	      var aspectRatio;
	      if (width > maxWidth) {
	        aspectRatio = maxWidth / width;
	      } else {
	        aspectRatio = 1;
	      }
	      return {
	        width: width * aspectRatio,
	        height: height * aspectRatio
	      };
	    }
	  },
	  computed: {
	    styleFileSizes: function styleFileSizes() {
	      var sizes = this.getImageSize(this.file.image.width, this.file.image.height, 280);
	      return {
	        width: sizes.width + 'px',
	        height: sizes.height + 'px',
	        backgroundSize: sizes.width < 100 || sizes.height < 100 ? 'contain' : 'initial'
	      };
	    },
	    styleBoxSizes: function styleBoxSizes() {
	      if (parseInt(this.styleFileSizes.height) <= 280) {
	        return {};
	      }
	      return {
	        height: '280px'
	      };
	    },
	    fileSource: function fileSource() {
	      return this.file.urlPreview;
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-element-file-image\" @click=\"download(file, $event)\" :style=\"styleBoxSizes\" ref=\"container\">\n\t\t\t<img v-bx-lazyload\n\t\t\t\tclass=\"bx-im-element-file-image-source\"\n\t\t\t\t:data-lazyload-src=\"fileSource\"\n\t\t\t\t:title=\"$Bitrix.Loc.getMessage('IM_MESSENGER_ELEMENT_FILE_SHOW_TITLE').replace('#NAME#', file.name).replace('#SIZE#', fileSize)\"\n\t\t\t\t:style=\"styleFileSizes\"\n\t\t\t\t:data-viewer=\"file.viewerAttrs.viewer === null\"\n\t\t\t\t:data-viewer-type=\"file.viewerAttrs.viewerType? file.viewerAttrs.viewerType: false\"\n\t\t\t\t:data-src=\"file.viewerAttrs.src? file.viewerAttrs.src: false\"\n\t\t\t\t:data-viewer-group-by=\"file.viewerAttrs.viewerGroupBy? file.viewerAttrs.viewerGroupBy: false\"\n\t\t\t\t:data-title=\"file.viewerAttrs.title? file.viewerAttrs.title: false\"\n\t\t\t\t:data-actions=\"file.viewerAttrs.actions? file.viewerAttrs.actions: false\"\n\t\t\t/>\n\t\t</div>\n\t"
	});

	/**
	 * Bitrix Messenger
	 * File element Vue component
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	ui_vue.Vue.cloneComponent('bx-im-view-element-file-video', 'bx-im-view-element-file', {
	  methods: {
	    getImageSize: function getImageSize(width, height, maxWidth) {
	      var aspectRatio;
	      if (width > maxWidth) {
	        aspectRatio = maxWidth / width;
	      } else {
	        aspectRatio = 1;
	      }
	      return {
	        width: width * aspectRatio,
	        height: height * aspectRatio
	      };
	    }
	  },
	  computed: {
	    isSafari: function isSafari() {
	      return im_lib_utils.Utils.browser.isSafari() || im_lib_utils.Utils.platform.isBitrixMobile();
	    },
	    styleBoxSizes: function styleBoxSizes() {
	      if (parseInt(this.styleVideoSizes.height) <= 280) {
	        return {};
	      }
	      return {
	        height: '280px'
	      };
	    },
	    styleVideoSizes: function styleVideoSizes() {
	      if (!this.file.image) {
	        return {};
	      }
	      var sizes = this.getImageSize(this.file.image.width, this.file.image.height, 280);
	      return {
	        width: sizes.width + 'px',
	        height: sizes.height + 'px',
	        backgroundSize: sizes.width < 100 || sizes.height < 100 ? 'contain' : 'initial'
	      };
	    },
	    autoplay: function autoplay() {
	      return this.file.size < 5000000 && this.application.options.autoplayVideo;
	    }
	  },
	  template: "\n\t\t<div :class=\"['bx-im-element-file-video', {'bx-im-element-file-video-safari': isSafari}]\" :style=\"styleBoxSizes\" ref=\"container\">\n\t\t\t<bx-socialvideo \n\t\t\t\t:id=\"file.id\" \n\t\t\t\t:src=\"file.urlShow\" \n\t\t\t\t:preview=\"file.urlPreview\" \n\t\t\t\t:containerStyle=\"styleBoxSizes\"\n\t\t\t\t:elementStyle=\"styleVideoSizes\"\n\t\t\t\t:autoplay=\"autoplay\"\n\t\t\t\t:showControls=\"!file.viewerAttrs.viewerType\"\n\t\t\t\t:data-viewer=\"file.viewerAttrs.viewer === null\"\n\t\t\t\t:data-viewer-type=\"file.viewerAttrs.viewerType? file.viewerAttrs.viewerType: false\"\n\t\t\t\t:data-src=\"file.viewerAttrs.src? file.viewerAttrs.src: false\"\n\t\t\t\t:data-viewer-group-by=\"file.viewerAttrs.viewerGroupBy? file.viewerAttrs.viewerGroupBy: false\"\n\t\t\t\t:data-title=\"file.viewerAttrs.title? file.viewerAttrs.title: false\"\n\t\t\t\t:data-actions=\"file.viewerAttrs.action? file.viewerAttrs.actions: false\"\n\t\t\t\t@click=\"download(file, $event)\"\n\t\t\t/>\n\t\t</div>\n\t"
	});

}((this.window = this.window || {}),BX.ProgressBarJs,BX,BX.Messenger.Model,BX.Event,BX.Messenger.Const,window,BX,window,BX,window,BX.Messenger.Lib,BX));
//# sourceMappingURL=media.bundle.js.map
