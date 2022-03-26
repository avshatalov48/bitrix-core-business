(function (exports,main_core,main_core_events) {
	'use strict';

	var ImageInput = /*#__PURE__*/function () {
	  babelHelpers.createClass(ImageInput, null, [{
	    key: "getById",
	    value: function getById(id) {
	      return ImageInput.imageInputInstances.get(id) || null;
	    }
	  }]);

	  function ImageInput(id) {
	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, ImageInput);
	    babelHelpers.defineProperty(this, "onUploaderIsInitedHandler", this.handleOnUploaderIsInited.bind(this));
	    babelHelpers.defineProperty(this, "values", new Map());
	    babelHelpers.defineProperty(this, "newValues", new Map());
	    this.id = id;
	    this.wrapper = BX(id);
	    this.productId = options.productId;
	    this.skuId = options.skuId;
	    this.iblockId = options.iblockId;
	    this.saveable = options.saveable;
	    this.inputId = options.inputId;
	    this.ajaxStatus = ImageInput.WAIT_STATUS;

	    if (options.hideAddButton === true) {
	      var addButton = this.wrapper.querySelector('[data-role="image-add-button"]');

	      if (main_core.Type.isDomNode(addButton)) {
	        addButton.style.display = 'none';
	      }
	    }

	    if (main_core.Type.isObject(options.values)) {
	      for (var key in options.values) {
	        if (!options.values.hasOwnProperty(key)) {
	          continue;
	        }

	        this.values.set(key, options.values[key]);
	      }
	    }

	    if (this.isSaveable()) {
	      main_core_events.EventEmitter.subscribe('onUploaderIsInited', this.onUploaderIsInitedHandler);
	    }

	    ImageInput.imageInputInstances.set(this.id, this);
	  }

	  babelHelpers.createClass(ImageInput, [{
	    key: "isSaveable",
	    value: function isSaveable() {
	      return this.saveable === true;
	    }
	  }, {
	    key: "handleOnUploaderIsInited",
	    value: function handleOnUploaderIsInited(event) {
	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 2),
	          id = _event$getCompatData2[0],
	          uploader = _event$getCompatData2[1];

	      if (main_core.Type.isStringFilled(this.inputId) && this.inputId === id) {
	        this.uploaderFieldMap = new Map();
	        main_core_events.EventEmitter.subscribe(uploader, 'onFileIsDeleted', this.onFileDelete.bind(this));
	        main_core_events.EventEmitter.subscribe(uploader, 'onFileIsUploaded', this.onFileUpload.bind(this));
	        main_core_events.EventEmitter.subscribe(uploader, 'onDone', this.onDone.bind(this));
	        main_core_events.EventEmitter.subscribe(uploader, 'onQueueIsChanged', this.onQueueIsChanged.bind(this));
	      }
	    }
	  }, {
	    key: "unsubscribeEvents",
	    value: function unsubscribeEvents() {
	      if (this.isSaveable()) {
	        main_core_events.EventEmitter.unsubscribe('onUploaderIsInited', this.onUploaderIsInitedHandler);
	      }
	    }
	  }, {
	    key: "unsubscribeImageInputEvents",
	    value: function unsubscribeImageInputEvents() {
	      if (main_core.Reflection.getClass('BX.UI.ImageInput')) {
	        var imageInput = BX.UI.ImageInput.getById(this.inputId);

	        if (imageInput) {
	          imageInput.unsubscribeEvents();
	        }
	      }
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "setId",
	    value: function setId(id) {
	      this.id = id;
	    }
	  }, {
	    key: "onFileDelete",
	    value: function onFileDelete(event) {
	      var _event$getCompatData3 = event.getCompatData(),
	          _event$getCompatData4 = babelHelpers.slicedToArray(_event$getCompatData3, 4),
	          file = _event$getCompatData4[3];

	      var inputName = file.input_name;

	      if (main_core.Type.isNil(inputName)) {
	        return null;
	      }

	      this.values.delete(inputName);

	      if (this.isSaveable()) {
	        this.save();
	      }
	    }
	  }, {
	    key: "onQueueIsChanged",
	    value: function onQueueIsChanged(event) {
	      var _event$getCompatData5 = event.getCompatData(),
	          _event$getCompatData6 = babelHelpers.slicedToArray(_event$getCompatData5, 4),
	          type = _event$getCompatData6[1],
	          itemId = _event$getCompatData6[2],
	          uploaderItem = _event$getCompatData6[3];

	      var image = uploaderItem.file;

	      if (type === 'add' && 'input_name' in image && main_core.Type.isNil(this.uploaderFieldMap.get(itemId))) {
	        this.uploaderFieldMap.set(itemId, image['input_name']);
	      }
	    }
	  }, {
	    key: "onDone",
	    value: function onDone() {
	      if (this.newValues.size > 0 && this.isSaveable()) {
	        this.save();
	      }

	      this.newValues.clear();
	    }
	  }, {
	    key: "onFileUpload",
	    value: function onFileUpload(event) {
	      var _event$getCompatData7 = event.getCompatData(),
	          _event$getCompatData8 = babelHelpers.slicedToArray(_event$getCompatData7, 3),
	          itemId = _event$getCompatData8[0],
	          params = _event$getCompatData8[2];

	      if (!this.isSaveable() || !main_core.Type.isObject(params) || !('file' in params) || !('files' in params.file) || !('default' in params.file.files)) {
	        return;
	      }

	      var currentUploadedFile = params['file']['files']['default'];
	      var photoItem = {
	        fileId: itemId,
	        data: {
	          name: currentUploadedFile.name,
	          type: currentUploadedFile.type,
	          tmp_name: currentUploadedFile.path,
	          size: currentUploadedFile.size,
	          error: null
	        }
	      };
	      var fileFieldName = this.uploaderFieldMap.get(itemId) || itemId;
	      this.values.set(fileFieldName, photoItem);
	      this.newValues.set(fileFieldName, photoItem);
	    }
	  }, {
	    key: "save",
	    value: function save() {
	      var _this = this;

	      if (this.submitFileTimeOut) {
	        clearTimeout(this.submitFileTimeOut);
	      }

	      var requestId = main_core.Text.getRandom(20);
	      this.refreshImageSelectorId = requestId;
	      this.submitFileTimeOut = setTimeout(function () {
	        var values = {};

	        _this.values.forEach(function (file, id) {
	          values[id] = file;
	        });

	        main_core.ajax.runAction('catalog.productSelector.saveMorePhoto', {
	          json: {
	            productId: _this.productId,
	            variationId: _this.skuId,
	            iblockId: _this.iblockId,
	            imageValues: values
	          }
	        }).then(function (response) {
	          var _response$data;

	          if (!_this.refreshImageSelectorId === requestId) {
	            return;
	          }

	          _this.values.clear();

	          if (main_core.Type.isObject((_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.values)) {
	            for (var key in response.data.values) {
	              if (!response.data.values.hasOwnProperty(key)) {
	                continue;
	              }

	              _this.values.set(key, response.data.values[key]);
	            }
	          }

	          main_core.Runtime.html(_this.wrapper, response.data.input);
	          main_core_events.EventEmitter.emit('Catalog.ImageInput::save', [_this.id, _this.inputId, response]);
	        });
	      }, 500);
	    }
	  }]);
	  return ImageInput;
	}();

	babelHelpers.defineProperty(ImageInput, "imageInputInstances", new Map());
	babelHelpers.defineProperty(ImageInput, "PROCESS_STATUS", 'PROCESS');
	babelHelpers.defineProperty(ImageInput, "WAIT_STATUS", 'WAIT');
	main_core.Reflection.namespace('BX.Catalog').ImageInput = ImageInput;

}((this.window = this.window || {}),BX,BX.Event));
//# sourceMappingURL=script.js.map
