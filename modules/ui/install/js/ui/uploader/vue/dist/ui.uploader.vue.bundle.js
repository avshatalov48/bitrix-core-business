this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,ui_vue3,ui_uploader_core,main_core,main_core_events) {
	'use strict';

	/**
	 * @memberof BX.UI.Uploader
	 */
	var _uploader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploader");
	var _items = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("items");
	var _uploaderError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploaderError");
	var _removeFilesFromServer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("removeFilesFromServer");
	var _handleFileAdd = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleFileAdd");
	var _handleFileRemove = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleFileRemove");
	var _handleFileStateChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleFileStateChange");
	var _handleFileComplete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleFileComplete");
	var _handleFileError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleFileError");
	var _handleError = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleError");
	var _handleUploadStart = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleUploadStart");
	var _handleUploadComplete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleUploadComplete");
	class VueUploaderAdapter extends main_core_events.EventEmitter {
	  constructor(uploaderOptions) {
	    super();
	    Object.defineProperty(this, _handleUploadComplete, {
	      value: _handleUploadComplete2
	    });
	    Object.defineProperty(this, _handleUploadStart, {
	      value: _handleUploadStart2
	    });
	    Object.defineProperty(this, _handleError, {
	      value: _handleError2
	    });
	    Object.defineProperty(this, _handleFileError, {
	      value: _handleFileError2
	    });
	    Object.defineProperty(this, _handleFileComplete, {
	      value: _handleFileComplete2
	    });
	    Object.defineProperty(this, _handleFileStateChange, {
	      value: _handleFileStateChange2
	    });
	    Object.defineProperty(this, _handleFileRemove, {
	      value: _handleFileRemove2
	    });
	    Object.defineProperty(this, _handleFileAdd, {
	      value: _handleFileAdd2
	    });
	    Object.defineProperty(this, _uploader, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _items, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _uploaderError, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _removeFilesFromServer, {
	      writable: true,
	      value: true
	    });
	    this.setEventNamespace('BX.UI.Uploader.Vue.Adapter');
	    babelHelpers.classPrivateFieldLooseBase(this, _items)[_items] = ui_vue3.ref([]);
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderError)[_uploaderError] = ui_vue3.shallowRef(null);
	    const events = {
	      [ui_uploader_core.UploaderEvent.FILE_ADD_START]: babelHelpers.classPrivateFieldLooseBase(this, _handleFileAdd)[_handleFileAdd].bind(this),
	      [ui_uploader_core.UploaderEvent.FILE_REMOVE]: babelHelpers.classPrivateFieldLooseBase(this, _handleFileRemove)[_handleFileRemove].bind(this),
	      [ui_uploader_core.UploaderEvent.FILE_STATE_CHANGE]: babelHelpers.classPrivateFieldLooseBase(this, _handleFileStateChange)[_handleFileStateChange].bind(this),
	      [ui_uploader_core.UploaderEvent.FILE_COMPLETE]: babelHelpers.classPrivateFieldLooseBase(this, _handleFileComplete)[_handleFileComplete].bind(this),
	      [ui_uploader_core.UploaderEvent.FILE_ERROR]: babelHelpers.classPrivateFieldLooseBase(this, _handleFileError)[_handleFileError].bind(this),
	      [ui_uploader_core.UploaderEvent.ERROR]: babelHelpers.classPrivateFieldLooseBase(this, _handleError)[_handleError].bind(this),
	      [ui_uploader_core.UploaderEvent.UPLOAD_START]: babelHelpers.classPrivateFieldLooseBase(this, _handleUploadStart)[_handleUploadStart].bind(this),
	      [ui_uploader_core.UploaderEvent.UPLOAD_COMPLETE]: babelHelpers.classPrivateFieldLooseBase(this, _handleUploadComplete)[_handleUploadComplete].bind(this)
	    };
	    if (uploaderOptions instanceof ui_uploader_core.Uploader) {
	      babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader] = uploaderOptions;
	      if (babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].getFileCount() > 0) {
	        throw new Error('VueUploaderAdapter: an uploader have some files. We cannot create an adapter.');
	      }

	      // Resubscribe events because adapter events must be first
	      Object.keys(events).forEach(eventName => {
	        const currentListeners = [...babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].getListeners(eventName).keys()];
	        babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].unsubscribeAll(eventName);
	        babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].subscribe(eventName, events[eventName]);
	        currentListeners.forEach(listener => {
	          babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].subscribe(eventName, listener);
	        });
	      });
	    } else {
	      const options = main_core.Type.isPlainObject(uploaderOptions) ? {
	        ...uploaderOptions
	      } : {};
	      const userEvents = options.events;
	      options.events = events;
	      babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader] = new ui_uploader_core.Uploader(options);
	      babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].subscribeFromOptions(userEvents);
	    }
	  }
	  getUploader() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader];
	  }
	  getReactiveItems() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _items)[_items];
	  }
	  getUploaderError() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _uploaderError)[_uploaderError];
	  }
	  getItems() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _items)[_items].value;
	  }
	  getItem(id) {
	    return this.getItems().find(item => item.id === id) || null;
	  }
	  setRemoveFilesFromServerWhenDestroy(value = true) {
	    babelHelpers.classPrivateFieldLooseBase(this, _removeFilesFromServer)[_removeFilesFromServer] = value;
	  }
	  destroy() {
	    babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader].destroy({
	      removeFilesFromServer: babelHelpers.classPrivateFieldLooseBase(this, _removeFilesFromServer)[_removeFilesFromServer]
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _uploader)[_uploader] = null;
	  }
	}
	function _handleFileAdd2(event) {
	  const file = event.getData().file;
	  const item = file.getState();
	  this.emit('Item:onBeforeAdd', {
	    item
	  });
	  this.getItems().push(item);
	  this.emit('Item:onAdd', {
	    item
	  });
	}
	function _handleFileRemove2(event) {
	  const file = event.getData().file;
	  const position = this.getItems().findIndex(fileInfo => {
	    return fileInfo.id === file.getId();
	  });
	  if (position >= 0) {
	    const result = this.getItems().splice(position, 1);
	    this.emit('Item:onRemove', {
	      item: result[0]
	    });
	  }
	}
	function _handleFileStateChange2(event) {
	  const file = event.getData().file;
	  const item = this.getItem(file.getId());
	  if (item) {
	    Object.assign(item, file.getState());
	  }
	}
	function _handleFileComplete2(event) {
	  const file = event.getData().file;
	  const item = file.getState();
	  this.emit('Item:onComplete', {
	    item
	  });
	}
	function _handleFileError2(event) {
	  const file = event.getData().file;
	  const error = event.getData().error;
	  const item = file.getState();
	  this.emit('Item:onError', {
	    item,
	    error
	  });
	}
	function _handleError2(event) {
	  const {
	    error
	  } = event.getData();
	  babelHelpers.classPrivateFieldLooseBase(this, _uploaderError)[_uploaderError].value = error.toJSON();
	  this.emit('Uploader:onError', new main_core_events.BaseEvent({
	    data: event.getData()
	  }));
	}
	function _handleUploadStart2(event) {
	  this.emit('Uploader:onUploadStart', new main_core_events.BaseEvent({
	    data: event.getData()
	  }));
	}
	function _handleUploadComplete2(event) {
	  this.emit('Uploader:onUploadComplete', new main_core_events.BaseEvent({
	    data: event.getData()
	  }));
	}

	/**
	 * @memberof BX.UI.Uploader
	 */
	var _vueAdapter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("vueAdapter");
	var _uploaderOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("uploaderOptions");
	var _widgetOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("widgetOptions");
	var _vueApp = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("vueApp");
	var _rootComponent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rootComponent");
	class VueUploaderWidget extends main_core_events.EventEmitter {
	  constructor(uploaderOptions, widgetOptions = {}) {
	    super();
	    Object.defineProperty(this, _vueAdapter, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _uploaderOptions, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _widgetOptions, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _vueApp, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _rootComponent, {
	      writable: true,
	      value: null
	    });
	    this.setEventNamespace('BX.UI.Uploader.Vue.Widget');
	    babelHelpers.classPrivateFieldLooseBase(this, _uploaderOptions)[_uploaderOptions] = uploaderOptions;
	    babelHelpers.classPrivateFieldLooseBase(this, _widgetOptions)[_widgetOptions] = widgetOptions;
	  }
	  defineComponent() {
	    return null;
	  }
	  getAdapter() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _vueAdapter)[_vueAdapter] === null) {
	      babelHelpers.classPrivateFieldLooseBase(this, _vueAdapter)[_vueAdapter] = new VueUploaderAdapter(babelHelpers.classPrivateFieldLooseBase(this, _uploaderOptions)[_uploaderOptions]);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _vueAdapter)[_vueAdapter];
	  }
	  getUploader() {
	    return this.getAdapter().getUploader();
	  }
	  getVueApp() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _vueApp)[_vueApp] !== null) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _vueApp)[_vueApp];
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _vueApp)[_vueApp] = ui_vue3.BitrixVue.createApp(this.defineComponent(), {
	      uploaderOptions: babelHelpers.classPrivateFieldLooseBase(this, _uploaderOptions)[_uploaderOptions],
	      widgetOptions: babelHelpers.classPrivateFieldLooseBase(this, _widgetOptions)[_widgetOptions],
	      uploaderAdapter: this.getAdapter()
	    });
	    return babelHelpers.classPrivateFieldLooseBase(this, _vueApp)[_vueApp];
	  }
	  getRootComponent() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _rootComponent)[_rootComponent];
	  }
	  renderTo(node) {
	    if (main_core.Type.isDomNode(node) && babelHelpers.classPrivateFieldLooseBase(this, _rootComponent)[_rootComponent] === null) {
	      babelHelpers.classPrivateFieldLooseBase(this, _rootComponent)[_rootComponent] = this.getVueApp().mount(node);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _rootComponent)[_rootComponent];
	  }
	}

	/**
	 * @memberof BX.UI.Uploader
	 */
	const VueUploaderComponent = {
	  name: 'VueUploaderComponent',
	  props: {
	    uploaderOptions: {
	      type: Object
	    },
	    widgetOptions: {
	      type: Object,
	      default: {}
	    },
	    uploaderAdapter: {
	      type: Object,
	      default: null
	    }
	  },
	  data: () => ({
	    items: [],
	    uploaderError: null
	  }),
	  provide() {
	    return {
	      uploader: this.uploader,
	      adapter: this.adapter,
	      widgetOptions: this.widgetOptions,
	      emitter: this.emitter
	    };
	  },
	  beforeCreate() {
	    if (this.uploaderAdapter === null) {
	      this.hasOwnAdapter = true;
	      const uploaderOptions = {
	        ...(main_core.Type.isPlainObject(this.customUploaderOptions) ? this.customUploaderOptions : {}),
	        ...this.uploaderOptions
	      };
	      this.adapter = new VueUploaderAdapter(uploaderOptions);
	    } else {
	      this.hasOwnAdapter = false;
	      this.adapter = this.uploaderAdapter;
	    }
	    this.uploader = this.adapter.getUploader();
	    this.emitter = new main_core_events.EventEmitter(this, `BX.UI.Uploader.${this.$options.name}`);
	    this.emitter.subscribeFromOptions(this.widgetOptions.events);
	  },
	  created() {
	    this.items = this.adapter.getReactiveItems();
	    this.uploaderError = this.adapter.getUploaderError();
	  },
	  unmounted() {
	    if (this.hasOwnAdapter) {
	      this.adapter.destroy();
	      this.adapter = null;
	      this.uploader = null;
	    }
	  }
	};

	exports.VueUploaderAdapter = VueUploaderAdapter;
	exports.VueUploaderWidget = VueUploaderWidget;
	exports.VueUploaderComponent = VueUploaderComponent;

}((this.BX.UI.Uploader = this.BX.UI.Uploader || {}),BX.Vue3,BX.UI.Uploader,BX,BX.Event));
//# sourceMappingURL=ui.uploader.vue.bundle.js.map
