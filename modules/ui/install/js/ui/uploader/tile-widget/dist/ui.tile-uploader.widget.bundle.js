this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
this.BX.UI.FileUploader = this.BX.UI.FileUploader || {};
(function (exports,main_core_events,ui_fileUploader_api,ui_progressround,ui_fileUploader_widgets_tileUploader,ui_vue,main_core,main_popup,ui_vue_portal,ui_buttons) {
	'use strict';

	var DropArea = ui_vue.BitrixVue.localComponent('drop-area', {
	  mounted: function mounted() {
	    var tileUploader = this.$Bitrix.Application.get();
	    tileUploader.getUploader().assignDropzone(this.$refs.dropArea);
	    tileUploader.getUploader().assignBrowse(this.$refs.dropArea);
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"disk-file-control-panel\" ref=\"dropArea\">\n\t\t\t<div class=\"disk-file-control-panel-btn-upload-box\">\n\t\t\t\t<label class=\"disk-file-control-panel-btn-upload\">Drag and Drop</label>\n\t\t\t\t<div class=\"disk-file-control-panel-btn-settings\"></div>\n\t\t\t</div>\n\t\t</div>\n\t"
	});

	var UploadLoader = ui_vue.BitrixVue.component('tile-uploader.uploader-loader', {
	  mounted: function mounted() {
	    this.createProgressbar();
	  },
	  beforeDestroy: function beforeDestroy() {
	    this.removeProgressbar();
	  },
	  props: {
	    progress: {
	      type: Number,
	      default: 0
	    },
	    item: {
	      type: Object,
	      default: {}
	    }
	  },
	  methods: {
	    createProgressbar: function createProgressbar() {
	      this.loader = new ui_progressround.ProgressRound({
	        width: 20,
	        // colorTrack: '#d8d8d8',
	        // colorBar: '#2fc6f6',
	        colorTrack: '#73d8f8',
	        colorBar: '#fff',
	        lineSize: 3,
	        rotation: true,
	        color: ui_progressround.ProgressRound.Color.SUCCESS
	      });
	      this.loader.renderTo(this.$refs.container);
	    },
	    updateProgressbar: function updateProgressbar() {
	      if (this.item.status !== 'uploading') {
	        return;
	      }

	      if (!this.loader) {
	        this.createProgressbar();
	      }

	      this.loader.update(this.item.progress ? this.item.progress : 0);
	    },
	    removeProgressbar: function removeProgressbar() {
	      if (this.loader) {
	        main_core.Dom.remove(this.loader.getContainer());
	        this.loader = null;
	      }
	    }
	  },
	  computed: {
	    uploadProgress: function uploadProgress() {
	      return String(this.item.progress);
	    }
	  },
	  watch: {
	    uploadProgress: function uploadProgress() {
	      this.updateProgressbar();
	    }
	  },
	  template: "<div class=\"ui-tile-uploader-item-loader\" ref=\"container\"></div>"
	});

	var TileItem = ui_vue.BitrixVue.localComponent('tile', {
	  props: {
	    item: {
	      type: Object,
	      default: {}
	    }
	  },
	  components: {
	    UploadLoader: UploadLoader
	  },
	  data: function data() {
	    return {
	      tileId: 'tile-uploader-' + main_core.Text.getRandom().toLowerCase(),
	      menu: null,
	      errorPopup: null
	    };
	  },
	  methods: {
	    remove: function remove(id) {
	      var tileUploader = this.$Bitrix.Application.get();
	      tileUploader.remove(id);
	    },
	    handleMouseEnter: function handleMouseEnter(item) {
	      if (item.error) {
	        if (!this.errorPopup) {
	          this.errorPopup = new main_popup.Popup({
	            bindElement: this.$refs.container,
	            darkMode: true,
	            animation: 'fading-slide',
	            width: 250,
	            angle: {
	              offset: 110
	            },
	            offsetTop: 6,
	            offsetLeft: -25,
	            content: item.error.getMessage() + '<br>' + item.error.getDescription()
	          });
	        }

	        this.errorPopup.show();
	      }
	    },
	    handleMouseLeave: function handleMouseLeave() {
	      if (this.errorPopup) {
	        this.errorPopup.destroy();
	        this.errorPopup = null;
	      }
	    },
	    showMenu: function showMenu(item) {
	      if (this.menu) {
	        this.menu.destroy();
	      }

	      this.menu = main_popup.MenuManager.create({
	        id: this.tileId,
	        bindElement: this.$refs.menu,
	        cacheable: false,
	        items: [{
	          text: 'Download',
	          href: item.downloadUrl
	        }]
	      });
	      this.menu.show();
	    }
	  },
	  computed: {
	    FileStatus: function FileStatus() {
	      return ui_fileUploader_api.FileStatus;
	    },
	    status: function status() {
	      if (this.item.status === ui_fileUploader_api.FileStatus.UPLOADING) {
	        return this.item.progress + '%';
	      } else if (this.item.status === ui_fileUploader_api.FileStatus.LOAD_FAILED || this.item.status === ui_fileUploader_api.FileStatus.UPLOAD_FAILED) {
	        return main_core.Loc.getMessage('TILE_UPLOADER_ERROR_STATUS');
	      } else {
	        return main_core.Loc.getMessage('TILE_UPLOADER_WAITING_STATUS');
	      }
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div\n\t\t\tclass=\"ui-tile-uploader-item\"\n\t\t\t:class=\"['ui-tile-uploader-item--' + item.status]\"\n\t\t\t@mouseenter=\"handleMouseEnter(item)\"\n\t\t\t@mouseleave=\"handleMouseLeave(item)\"\n\t\t\tref=\"container\"\n\t\t>\n\t\t\t<div class=\"ui-tile-uploader-item-content\">\n\t\t\t\t<div v-if=\"item.status !== FileStatus.COMPLETE\" key=\"loader\" class=\"ui-tile-uploader-item-state\">\n\t\t\t\t\t<UploadLoader v-if=\"item.status === FileStatus.UPLOADING\" :item=\"item\"></UploadLoader>\n\t\t\t\t\t<div v-else class=\"ui-tile-uploader-item-state-icon\"></div>\n\n\t\t\t\t\t<div class=\"ui-tile-uploader-item-status\">{{status}}</div>\n\t\t\t\t\t<div class=\"ui-tile-uploader-item-state-desc\">{{item.sizeFormatted}}</div>\n\t\t\t\t\t<div class=\"ui-tile-uploader-item-state-remove\" @click=\"remove(item.id)\"></div>\n\t\t\t\t</div>\n\t\t\t\t<div v-else key=\"complete\" class=\"ui-tile-uploader-item-actions\">\n\t\t\t\t\t<div class=\"ui-tile-uploader-item-remove\" @click=\"remove(item.id)\"></div>\n\t\t\t\t\t<div class=\"ui-tile-uploader-item-menu\" @click=\"showMenu(item)\" ref=\"menu\"></div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"ui-tile-uploader-item-preview\">\n\t\t\t\t\t<template v-if=\"item.isImage\">\n\t\t\t\t\t\t<div\n\t\t\t\t\t\t\tclass=\"ui-tile-uploader-item-image\"\n\t\t\t\t\t\t\t:class=\"{ 'ui-tile-uploader-item-image-default': item.previewUrl === null }\"\n\t\t\t\t\t\t\t:style=\"{ backgroundImage: item.previewUrl !== null ? 'url(' + item.previewUrl + ')' : '' }\">\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"ui-tile-uploader-item-image-name\" :title=\"item.originalName\">{{item.originalName}}</div>\n\t\t\t\t\t</template>\n\t\t\t\t\t<div v-else class=\"ui-tile-uploader-item-file\"></div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t"
	  /*template: `
	  	<div class="disk-file-thumb" :class="[
	  		item.isImage ? 'disk-file-thumb-preview' : 'disk-file-thumb-file',
	  		item.status === 'uploading' ? 'disk-file-thumb--active' : '',
	  		'disk-file-thumb--' + item.status
	  	]">
	  		<template v-if="item.isImage && item.previewUrl !== null">
	  			<div
	  				class="disk-file-thumb-image" style="background-size: cover"
	  				:style="{ backgroundImage: 'url(' + item.previewUrl + ')' }">
	  			</div>
	  		</template>
	  		<div class="disk-file-thumb-loader" ref="loader">
	  			<div class="disk-file-thumb-loader-size">{{item.sizeFormatted}}</div>
	  		</div>
	  			<div class="ui-icon disk-file-thumb-icon" :class="'ui-icon-file-'+ item.extension"><i></i></div>
	  			<div class="disk-file-thumb-text">{{item.originalName}}</div>
	  			<div class="disk-file-thumb-btn-box">
	  			<div class="disk-file-thumb-btn-close" @click="remove(item.id)"></div>
	  			<div class="disk-file-thumb-btn-more" @click="showMenu(item)" ref="menu"></div>
	  		</div>
	  	</div>
	  `*/

	});

	var TileList = ui_vue.BitrixVue.localComponent('tile-list', {
	  props: {
	    items: {
	      type: Array,
	      default: []
	    }
	  },
	  components: {
	    TileItem: TileItem
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"ui-tile-uploader-items\">\n\t\t\t<TileItem v-for=\"item in items\" :key=\"item.id\" :item=\"item\" />\n\t\t</div>\n\t"
	});

	var Stack = ui_vue.BitrixVue.component('tile-uploader.stack', {
	  mounted: function mounted() {
	    var tileUploader = this.$Bitrix.Application.get();
	    tileUploader.getUploader().assignDropzone(this.$refs.container);
	  },
	  template: "<div class=\"ui-tile-uploader-stack\" v-on:click=\"$emit('click')\" ref=\"container\">Stack</div>"
	});

	var TileUploaderComponent = ui_vue.BitrixVue.localComponent('tile-uploader', {
	  props: {
	    error: {
	      type: Object
	    },
	    stackMode: {
	      type: Boolean,
	      default: false
	    },
	    items: {
	      type: Array,
	      default: []
	    }
	  },
	  data: function data() {
	    return {
	      popup: null,
	      popupContentId: ''
	    };
	  },
	  components: {
	    DropArea: DropArea,
	    TileList: TileList,
	    Stack: Stack,
	    MountingPortal: ui_vue_portal.MountingPortal
	  },
	  methods: {
	    showPopup: function showPopup() {
	      var _this = this;

	      if (!this.popup) {
	        var id = 'stack-uploader-' + main_core.Text.getRandom().toLowerCase();
	        var popup = new main_popup.Popup({
	          width: 750,
	          height: 400,
	          draggable: true,
	          titleBar: 'Uploaded Files',
	          content: "<div id=\"".concat(id, "\"></div>"),
	          cacheable: false,
	          closeIcon: true,
	          closeByEsc: true,
	          events: {
	            onDestroy: function onDestroy() {
	              return _this.popup = null;
	            }
	          },
	          buttons: [new ui_buttons.CloseButton({
	            onclick: function onclick() {
	              return _this.popup.close();
	            }
	          })]
	        });
	        this.popupContentId = "#".concat(id);
	        this.popup = popup;
	      }

	      this.popup.show();
	    }
	  },
	  watch: {
	    error: function error() {
	      alert(this.error.message);
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"ui-tile-uploader\">\n\t\t\t{{ error && error.message ? error.message : 'none' }}\n\t\t\t<template v-if=\"stackMode\">\n\t\t\t\t<Stack v-on:click=\"showPopup\"/>\n\t\t\t\t<mounting-portal v-if=\"popup\" :mount-to=\"popupContentId\" append>\n\t\t\t\t\t<TileList :items=\"items\"></TileList>\n\t\t\t\t</mounting-portal>\n\t\t\t</template>\n\t\t\t<template v-else>\n\t\t\t\t<template v-if=\"items.length === 0\">\n\t\t\t\t\t<DropArea />\n\t\t\t\t</template>\n\t\t\t\t<template v-else>\n\t\t\t\t\t<TileList :items=\"items\"></TileList>\n\t\t\t\t\t<DropArea />\n\t\t\t\t</template>\n\t\t\t</template>\n\t\t</div>\n\t"
	});

	/**
	 * @memberof BX.UI.FileUploader.Widgets
	 */

	var _uploader = new WeakMap();

	var _vueApp = new WeakMap();

	var _items = new WeakMap();

	var _error = new WeakMap();

	var _uploaderStatus = new WeakMap();

	var _stackMode = new WeakMap();

	var TileUploader = /*#__PURE__*/function () {
	  function TileUploader(uploaderOptions, tileUploaderOptions) {
	    var _this = this;

	    babelHelpers.classCallCheck(this, TileUploader);

	    _uploader.set(this, {
	      writable: true,
	      value: null
	    });

	    _vueApp.set(this, {
	      writable: true,
	      value: null
	    });

	    _items.set(this, {
	      writable: true,
	      value: []
	    });

	    _error.set(this, {
	      writable: true,
	      value: {}
	    });

	    _uploaderStatus.set(this, {
	      writable: true,
	      value: ui_fileUploader_api.UploaderStatus.STOPPED
	    });

	    _stackMode.set(this, {
	      writable: true,
	      value: false
	    });

	    var widgetOptions = main_core.Type.isPlainObject(tileUploaderOptions) ? Object.assign({}, tileUploaderOptions) : {};
	    babelHelpers.classPrivateFieldSet(this, _stackMode, widgetOptions.stackMode === true);
	    var context = this;
	    babelHelpers.classPrivateFieldSet(this, _vueApp, ui_vue.BitrixVue.createApp({
	      data: function data() {
	        return {
	          items: babelHelpers.classPrivateFieldGet(_this, _items),
	          stackMode: babelHelpers.classPrivateFieldGet(_this, _stackMode),
	          error: babelHelpers.classPrivateFieldGet(_this, _error)
	        };
	      },
	      components: {
	        TileUploaderComponent: TileUploaderComponent
	      },
	      beforeCreate: function beforeCreate() {
	        this.$bitrix.Application.set(context);
	      },
	      // language=Vue
	      template: "<TileUploaderComponent :items=\"items\" :stackMode=\"stackMode\" :error=\"error\" />"
	    }));
	    var options = main_core.Type.isPlainObject(uploaderOptions) ? Object.assign({}, uploaderOptions) : {};
	    var userEvents = options.events;
	    options.events = {
	      'File:onAddStart': this.handleFileAdd.bind(this),
	      'File:onRemove': this.handleFileRemove.bind(this),
	      'File:onUploadProgress': this.handleFileUploadProgress.bind(this),
	      'File:onStateChange': this.handleFileStateChange.bind(this),
	      'File:onError': this.handleFileError.bind(this),
	      'onError': this.handleError.bind(this)
	    };
	    babelHelpers.classPrivateFieldSet(this, _uploader, new ui_fileUploader_api.Uploader(options));
	    babelHelpers.classPrivateFieldGet(this, _uploader).subscribeFromOptions(userEvents);
	  }

	  babelHelpers.createClass(TileUploader, [{
	    key: "getUploader",
	    value: function getUploader() {
	      return babelHelpers.classPrivateFieldGet(this, _uploader);
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(node) {
	      if (main_core.Type.isDomNode(node)) {
	        var container = main_core.Dom.create('div');
	        node.appendChild(container);

	        if (!this.getUploader().getHiddenFieldsContainer()) {
	          this.getUploader().setHiddenFieldsContainer(node);
	        }

	        babelHelpers.classPrivateFieldGet(this, _vueApp).mount(container);
	      }
	    }
	  }, {
	    key: "remove",
	    value: function remove(id) {
	      this.getUploader().removeFile(id);
	    }
	  }, {
	    key: "getItem",
	    value: function getItem(id) {
	      return babelHelpers.classPrivateFieldGet(this, _items).find(function (item) {
	        return item.id === id;
	      });
	    }
	  }, {
	    key: "createItemFromFile",
	    value: function createItemFromFile(file) {
	      var item = file.getState();
	      item.progress = 0;
	      return item;
	    }
	  }, {
	    key: "handleFileAdd",
	    value: function handleFileAdd(event) {
	      var _event$getData = event.getData(),
	          file = _event$getData.file,
	          error = _event$getData.error;

	      babelHelpers.classPrivateFieldGet(this, _items).push(this.createItemFromFile(file));
	    }
	  }, {
	    key: "handleFileError",
	    value: function handleFileError(event) {
	      var _event$getData2 = event.getData(),
	          file = _event$getData2.file,
	          error = _event$getData2.error;

	      var item = this.getItem(file.getId());
	      item.error = error;
	    }
	  }, {
	    key: "handleFileRemove",
	    value: function handleFileRemove(event) {
	      var _event$getData3 = event.getData(),
	          file = _event$getData3.file;

	      var position = babelHelpers.classPrivateFieldGet(this, _items).findIndex(function (fileInfo) {
	        return fileInfo.id === file.getId();
	      });

	      if (position >= 0) {
	        babelHelpers.classPrivateFieldGet(this, _items).splice(position, 1);
	      }
	    }
	  }, {
	    key: "handleFileUploadProgress",
	    value: function handleFileUploadProgress(event) {
	      var _event$getData4 = event.getData(),
	          file = _event$getData4.file,
	          progress = _event$getData4.progress;

	      var item = this.getItem(file.getId());

	      if (item) {
	        item.progress = progress;
	      }
	    }
	  }, {
	    key: "handleFileStateChange",
	    value: function handleFileStateChange(event) {
	      var _event$getData5 = event.getData(),
	          file = _event$getData5.file;

	      var item = this.getItem(file.getId());

	      if (item) {
	        Object.assign(item, file.getState());
	      }
	    }
	  }, {
	    key: "handleError",
	    value: function handleError(event) {
	      Object.assign(babelHelpers.classPrivateFieldGet(this, _error), event.getData().error.toJSON());
	    }
	  }]);
	  return TileUploader;
	}();

	var StackUploader = /*#__PURE__*/function (_TileUploader) {
	  babelHelpers.inherits(StackUploader, _TileUploader);

	  function StackUploader(uploaderOptions, tileUploaderOptions) {
	    var _this2;

	    babelHelpers.classCallCheck(this, StackUploader);
	    _this2 = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(StackUploader).call(this, uploaderOptions, tileUploaderOptions));

	    _this2.getVue().setTemplate('');

	    return _this2;
	  }

	  return StackUploader;
	}(TileUploader);

	exports.TileUploader = TileUploader;

}((this.BX.UI.FileUploader.Widgets = this.BX.UI.FileUploader.Widgets || {}),BX.Event,BX.UI.FileUploader,BX.UI,BX.UI.FileUploader.Widgets,BX,BX,BX.Main,BX.Vue,BX.UI));
//# sourceMappingURL=ui.tile-uploader.widget.bundle.js.map
