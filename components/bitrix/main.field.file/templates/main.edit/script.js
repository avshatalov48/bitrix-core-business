/* eslint-disable */
this.BX = this.BX || {};
this.BX.Main = this.BX.Main || {};
this.BX.Main.Field = this.BX.Main.Field || {};
(function (exports,ui_vue3,main_core,ui_uploader_tileWidget) {
	'use strict';

	var InputManager = {
	  props: {
	    controlId: {
	      type: String,
	      required: true
	    },
	    controlName: {
	      type: String,
	      required: true
	    },
	    multiple: {
	      type: Boolean,
	      required: true
	    },
	    filledValues: {
	      type: Object,
	      required: true
	    }
	  },
	  data: function data() {
	    return {
	      values: this.filledValues,
	      deletedValues: []
	    };
	  },
	  methods: {
	    fireChange: function fireChange() {
	      BX.fireEvent(this.$refs.valueChanger, "change");
	    },
	    setValues: function setValues(values) {
	      var prevValues = this.values;
	      this.values = [].concat(babelHelpers.toConsumableArray(this.filledValues), babelHelpers.toConsumableArray(values)).filter(function (value, index, array) {
	        return array.indexOf(value) === index;
	      });
	      if (!this.arraysAreEqual(prevValues, this.values)) {
	        this.fireChange();
	      }
	    },
	    addDeleted: function addDeleted(fileId) {
	      this.deletedValues = [].concat(babelHelpers.toConsumableArray(this.deletedValues), [fileId]);
	      this.fireChange();
	    },
	    removeValue: function removeValue(fileId) {
	      var index = this.values.indexOf(fileId);
	      if (index >= 0) {
	        this.values.splice(index, 1);
	        this.fireChange();
	      }
	    },
	    arraysAreEqual: function arraysAreEqual(a, b) {
	      if (a.length !== b.length) {
	        return false;
	      }
	      for (var i = 0; i > a.length; i++) {
	        if (a[i] !== b[i]) {
	          return false;
	        }
	      }
	      return true;
	    }
	  },
	  template: "\n\t\t<input ref=\"valueChanger\" type=\"hidden\" />\n\t\t<div class=\"uf-hidden-inputs\" style=\"display: none;\">\n\t\t\t<div v-if=\"Object.hasOwn(this.values, '0')\">\n\t\t\t\t<input v-if=\"this.multiple\" v-for=\"(el, index) in values\" :key=\"index\" type=\"hidden\"\n\t\t\t\t\t   :name=\"controlName + '[]'\"\n\t\t\t\t\t   :value=\"values[index]\"/>\n\t\t\t\t<input v-else type=\"hidden\" :name=\"controlName\" :value=\"values[values.length - 1]\" />\n\t\t\t</div>\n\t\t\t<div v-else>\n\t\t\t\t<input type=\"hidden\" :name=\"this.multiple ? controlName + '[]' : controlName\" />\n\t\t\t</div>\n            <div v-if=\"Object.hasOwn(this.deletedValues, '0')\">\n\t\t\t\t<input v-if=\"this.multiple\" v-for=\"(el, index) in deletedValues\" :key=\"index\" type=\"hidden\"\n\t\t\t\t\t   :name=\"controlName + '_del' + '[]'\"\n\t\t\t\t\t   :value=\"deletedValues[index]\"/>\n\t\t\t\t<input v-else type=\"hidden\" :name=\"controlName + '_del'\" :value=\"deletedValues[0]\" />\n\t\t\t\t<input v-for=\"(el, index) in deletedValues\" :key=\"index\" type=\"hidden\"\n\t\t\t\t\t   :name=\"controlId + '_deleted' + '[]'\"\n\t\t\t\t\t   :value=\"deletedValues[index]\" />\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	var Main = {
	  data: function data() {
	    var data = {
	      fileTokens: []
	    };
	    return this.getPreparedData(data);
	  },
	  props: {
	    controlId: {
	      type: String,
	      required: true
	    },
	    container: {
	      type: HTMLElement,
	      required: true
	    },
	    context: {
	      type: Object,
	      required: true
	    },
	    filledValues: {
	      type: Object
	    }
	  },
	  components: {
	    InputManager: InputManager,
	    TileWidgetComponent: ui_uploader_tileWidget.TileWidgetComponent
	  },
	  computed: {
	    uploaderOptions: function uploaderOptions() {
	      var _this = this;
	      return {
	        controller: 'main.fileUploader.fieldFileUploaderController',
	        controllerOptions: this.context,
	        files: this.fileTokens,
	        events: {
	          onUploadComplete: function onUploadComplete() {
	            void _this.$nextTick(function () {
	              _this.fileTokens = _this.getFileIdList();
	            });
	          },
	          "File:onRemove": function FileOnRemove(event) {
	            var eventData = event.getData();
	            if (main_core.Type.isObject(eventData) && main_core.Type.isObject(eventData["file"])) {
	              var file = eventData["file"];
	              if (main_core.Type.isObject(file)) {
	                var fileId = file.getServerFileId();
	                if (main_core.Type.isNumber(fileId)) {
	                  _this.$refs.inputManager.addDeleted(fileId);
	                } else {
	                  _this.$refs.inputManager.addDeleted(_this.getRealFileId(file));
	                }
	              }
	            }
	          }
	        },
	        multiple: this.context.multiple,
	        autoUpload: true,
	        treatOversizeImageAsFile: true
	      };
	    },
	    widgetOptions: function widgetOptions() {
	      return {};
	    }
	  },
	  methods: {
	    getPreparedData: function getPreparedData(data) {
	      var filledValues = this.filledValues;
	      if (main_core.Type.isArrayFilled(filledValues)) {
	        data.fileTokens = filledValues;
	      }
	      return data;
	    },
	    getFileIdList: function getFileIdList() {
	      var _this2 = this;
	      var ids = [];
	      this.$refs.uploader.uploader.getFiles().forEach(function (file) {
	        if (file.isComplete()) {
	          var realFileId = _this2.getRealFileId(file);
	          if (main_core.Type.isNumber(realFileId)) {
	            ids.push(realFileId);
	          } else {
	            ids.push(file.getServerFileId());
	          }
	        }
	      });
	      return ids;
	    },
	    getRealFileId: function getRealFileId(file) {
	      var _file$getCustomData = file.getCustomData(),
	        realFileId = _file$getCustomData.realFileId;
	      return main_core.Type.isNumber(realFileId) ? realFileId : null;
	    },
	    updateInputManagerValues: function updateInputManagerValues() {
	      this.$refs.inputManager.setValues(this.fileTokens);
	    }
	  },
	  template: "\n\t<div class=\"main-field-file-wrapper\">\n\t\t<InputManager\n\t\t\tref=\"inputManager\"\n\t\t\t:controlId=\"controlId\"\n\t\t\t:controlName=\"context.fieldName\"\n\t\t\t:multiple=\"context.multiple\"\n\t\t\t:filledValues=\"filledValues\"\n\t\t/>\n\t\t<TileWidgetComponent\n\t\t\tref=\"uploader\"\n\t\t\t:uploaderOptions=\"uploaderOptions\"\n\t\t\t:widgetOptions=\"widgetOptions\"\n\t\t/>\n\t</div>",
	  created: function created() {
	    this.$watch('fileTokens', this.updateInputManagerValues, {
	      deep: true
	    });
	  }
	};

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _controlId = /*#__PURE__*/new WeakMap();
	var _container = /*#__PURE__*/new WeakMap();
	var _context = /*#__PURE__*/new WeakMap();
	var _value = /*#__PURE__*/new WeakMap();
	var _app = /*#__PURE__*/new WeakMap();
	var App = /*#__PURE__*/function () {
	  function App(params) {
	    babelHelpers.classCallCheck(this, App);
	    _classPrivateFieldInitSpec(this, _controlId, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _container, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _context, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _value, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _app, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldSet(this, _controlId, params.controlId);
	    babelHelpers.classPrivateFieldSet(this, _container, document.getElementById(params.containerId));
	    if (!main_core.Type.isDomNode(babelHelpers.classPrivateFieldGet(this, _container))) {
	      throw new Error('container not found');
	    }
	    babelHelpers.classPrivateFieldSet(this, _context, params.context);
	    babelHelpers.classPrivateFieldSet(this, _value, params.value.map(function (value) {
	      return parseInt(value);
	    }));
	  }
	  babelHelpers.createClass(App, [{
	    key: "start",
	    value: function start() {
	      babelHelpers.classPrivateFieldSet(this, _app, ui_vue3.BitrixVue.createApp(_objectSpread({}, Main), {
	        controlId: babelHelpers.classPrivateFieldGet(this, _controlId),
	        container: babelHelpers.classPrivateFieldGet(this, _container),
	        context: babelHelpers.classPrivateFieldGet(this, _context),
	        filledValues: babelHelpers.classPrivateFieldGet(this, _value)
	      }));
	      babelHelpers.classPrivateFieldGet(this, _app).mount(babelHelpers.classPrivateFieldGet(this, _container));
	    }
	  }, {
	    key: "stop",
	    value: function stop() {
	      babelHelpers.classPrivateFieldGet(this, _app).unmount();
	      babelHelpers.classPrivateFieldSet(this, _app, null);
	    }
	  }]);
	  return App;
	}();

	exports.App = App;

}((this.BX.Main.Field.File = this.BX.Main.Field.File || {}),BX.Vue3,BX,BX.UI.Uploader));
//# sourceMappingURL=script.js.map
