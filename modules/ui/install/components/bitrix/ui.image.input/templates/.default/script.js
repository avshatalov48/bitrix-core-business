(function (exports,main_core,main_core_events,main_loader) {
	'use strict';

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-image-item-shadow\"></div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var ImageInput = /*#__PURE__*/function () {
	  function ImageInput() {
	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, ImageInput);
	    babelHelpers.defineProperty(this, "container", null);
	    babelHelpers.defineProperty(this, "loaderContainer", null);
	    babelHelpers.defineProperty(this, "addButton", null);
	    babelHelpers.defineProperty(this, "loader", null);
	    babelHelpers.defineProperty(this, "timeout", null);
	    babelHelpers.defineProperty(this, "uploading", false);
	    this.instanceId = params.instanceId;
	    this.containerId = params.containerId;
	    this.loaderContainerId = params.loaderContainerId;
	    this.settings = params.settings || {};
	    this.addImageHandler = this.addImage.bind(this);
	    this.editImageHandler = this.editImage.bind(this);
	    main_core_events.EventEmitter.subscribe('onUploaderIsInited', this.onUploaderIsInitedHandler.bind(this));
	  }

	  babelHelpers.createClass(ImageInput, [{
	    key: "onUploaderIsInitedHandler",
	    value: function onUploaderIsInitedHandler(event) {
	      var _this = this;

	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 2),
	          id = _event$getCompatData2[0],
	          uploader = _event$getCompatData2[1];

	      if (this.instanceId === id) {
	        if (this.getPreviews().length > 0) {
	          main_core.Dom.addClass(this.getFileWrapper(), 'ui-image-input-wrapper');
	        }

	        requestAnimationFrame(function () {
	          _this.getLoaderContainer() && (_this.getLoaderContainer().style.display = 'none');
	          _this.getContainer().style.display = '';
	        });
	        main_core_events.EventEmitter.subscribe(uploader, 'onFileIsDeleted', this.onFileIsDeletedHandler.bind(this));
	        main_core_events.EventEmitter.subscribe(uploader, 'onStart', this.onUploadStartHandler.bind(this));
	        main_core_events.EventEmitter.subscribe(uploader, 'onDone', this.onUploadDoneHandler.bind(this));
	        main_core_events.EventEmitter.subscribe(uploader, 'onFileCanvasIsLoaded', this.onFileCanvasIsLoadedHandler.bind(this));
	      }
	    }
	  }, {
	    key: "getInputInstance",
	    value: function getInputInstance() {
	      return BX.UI.FileInput.getInstance(this.instanceId);
	    }
	  }, {
	    key: "getFileInput",
	    value: function getFileInput() {
	      return this.getInputInstance().agent.fileInput;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.container) {
	        this.container = document.getElementById(this.containerId);

	        if (!main_core.Type.isDomNode(this.container)) {
	          throw Error("Can't find container with id ".concat(this.containerId));
	        }
	      }

	      return this.container;
	    }
	  }, {
	    key: "getFileWrapper",
	    value: function getFileWrapper() {
	      if (!this.fileWrapper) {
	        this.fileWrapper = this.getContainer().querySelector('.adm-fileinput-wrapper');
	      }

	      return this.fileWrapper;
	    }
	  }, {
	    key: "getLoaderContainer",
	    value: function getLoaderContainer() {
	      if (!this.loaderContainer) {
	        this.loaderContainer = document.getElementById(this.loaderContainerId);
	      }

	      return this.loaderContainer;
	    }
	  }, {
	    key: "getAddButton",
	    value: function getAddButton() {
	      if (!this.addButton) {
	        this.addButton = this.getContainer().querySelector('[data-role="image-add-button"]');
	      }

	      return this.addButton;
	    }
	  }, {
	    key: "editImage",
	    value: function editImage(event) {
	      if (event.target === this.getFileInput()) {
	        // api call .click() to fire file upload dialog
	        if (event.detail === 0) {
	          return;
	        } // disable default file dialog open
	        else {
	            event.preventDefault();
	          }
	      }

	      var inputInstance = this.getInputInstance();
	      var items = inputInstance.agent.getItems().items;

	      for (var id in items) {
	        if (items.hasOwnProperty(id)) {
	          // hack to open editor (for unknown reasons the flag disappears)
	          inputInstance.frameFlags.active = true;
	          inputInstance.frameFiles(id);
	          break;
	        }
	      }
	    }
	  }, {
	    key: "addImage",
	    value: function addImage(event) {
	      event.preventDefault();
	      event.stopPropagation();
	      this.getFileInput().click();
	    }
	    /**
	     * @returns {Loader}
	     */

	  }, {
	    key: "getLoader",
	    value: function getLoader() {
	      if (!this.loader) {
	        this.loader = new main_loader.Loader({
	          target: this.getFileWrapper().querySelector('.adm-fileinput-drag-area')
	        });
	      }

	      return this.loader;
	    }
	  }, {
	    key: "showLoader",
	    value: function showLoader() {
	      this.getLoader().setOptions({
	        size: Math.min(this.getContainer().offsetHeight, this.getContainer().offsetWidth)
	      });
	      this.getLoader().show();
	    }
	  }, {
	    key: "hideLoader",
	    value: function hideLoader() {
	      this.getLoader().hide();
	    }
	  }, {
	    key: "onFileIsDeletedHandler",
	    value: function onFileIsDeletedHandler() {
	      var _this2 = this;

	      this.timeout = clearTimeout(this.timeout);
	      this.timeout = setTimeout(function () {
	        _this2.hideLoader();

	        _this2.recalculateWrapper();
	      }, 100);
	    }
	  }, {
	    key: "onUploadStartHandler",
	    value: function onUploadStartHandler(event) {
	      var _this3 = this;

	      var _event$getCompatData3 = event.getCompatData(),
	          _event$getCompatData4 = babelHelpers.slicedToArray(_event$getCompatData3, 1),
	          stream = _event$getCompatData4[0];

	      if (stream) {
	        this.uploading = true;
	      }

	      clearTimeout(this.timeout);
	      this.timeout = setTimeout(function () {
	        _this3.showLoader();

	        _this3.recalculateWrapper();
	      }, 100);
	    }
	  }, {
	    key: "onUploadDoneHandler",
	    value: function onUploadDoneHandler(event) {
	      var _this4 = this;

	      var _event$getCompatData5 = event.getCompatData(),
	          _event$getCompatData6 = babelHelpers.slicedToArray(_event$getCompatData5, 1),
	          stream = _event$getCompatData6[0];

	      if (stream) {
	        this.uploading = false;
	        this.timeout = clearTimeout(this.timeout);
	        requestAnimationFrame(function () {
	          _this4.hideLoader();

	          _this4.recalculateWrapper();
	        });
	      }
	    }
	  }, {
	    key: "onFileCanvasIsLoadedHandler",
	    value: function onFileCanvasIsLoadedHandler() {
	      var _this5 = this;

	      if (this.timeout && !this.uploading) {
	        this.uploading = false;
	        this.timeout = clearTimeout(this.timeout);
	        requestAnimationFrame(function () {
	          _this5.hideLoader();

	          _this5.recalculateWrapper();
	        });
	      }
	    }
	  }, {
	    key: "isMultipleInput",
	    value: function isMultipleInput() {
	      return this.getInputInstance().uploadParams.maxCount !== 1;
	    }
	  }, {
	    key: "buildShadowElement",
	    value: function buildShadowElement(wrapper) {
	      var shadowElement = wrapper.querySelector('div.ui-image-item-shadow');

	      if (!shadowElement) {
	        shadowElement = main_core.Tag.render(_templateObject());
	        main_core.Dom.prepend(shadowElement, wrapper);
	      }

	      var canvas = wrapper.querySelector('canvas');

	      if (canvas) {
	        var bottomMargin = 4;
	        shadowElement.style.height = canvas.offsetHeight + 'px';
	        shadowElement.style.width = canvas.offsetWidth - bottomMargin + 'px';
	        wrapper.querySelector('.adm-fileinput-item-preview').style.height = canvas.offsetHeight + 'px';
	        wrapper.closest('.adm-fileinput-item-wrapper').style.height = canvas.offsetHeight + 'px';
	      }
	    }
	  }, {
	    key: "getPreviews",
	    value: function getPreviews() {
	      return this.getFileWrapper().querySelectorAll('.adm-fileinput-item');
	    }
	  }, {
	    key: "recalculateWrapper",
	    value: function recalculateWrapper() {
	      var wrapper = this.getFileWrapper();
	      var previews = this.getPreviews();
	      var length = Math.min(previews.length, 3);

	      if (length) {
	        this.buildShadowElement(previews[0]);
	        main_core.Dom.addClass(wrapper, 'ui-image-input-wrapper');
	        this.getFileInput().style.display = 'none';
	        main_core.Event.unbind(wrapper, 'click', this.editImageHandler);
	        main_core.Event.bind(wrapper, 'click', this.editImageHandler);

	        if (this.isMultipleInput()) {
	          this.getAddButton().style.display = '';
	          main_core.Event.unbind(this.getAddButton(), 'click', this.addImageHandler);
	          main_core.Event.bind(this.getAddButton(), 'click', this.addImageHandler);
	        }
	      } else {
	        main_core.Dom.removeClass(wrapper, 'ui-image-input-wrapper');
	        this.getFileInput().style.display = '';
	        main_core.Event.unbind(wrapper, 'click', this.editImageHandler);

	        if (this.isMultipleInput()) {
	          this.getAddButton().style.display = 'none';
	          main_core.Event.unbind(this.getAddButton(), 'click', this.addImageHandler);
	        }
	      }

	      switch (length) {
	        case 3:
	          main_core.Dom.addClass(wrapper, 'ui-image-input-wrapper-multiple');
	          main_core.Dom.removeClass(wrapper, 'ui-image-input-wrapper-double');
	          break;

	        case 2:
	          main_core.Dom.addClass(wrapper, 'ui-image-input-wrapper-double');
	          main_core.Dom.removeClass(wrapper, 'ui-image-input-wrapper-multiple');
	          break;

	        default:
	          main_core.Dom.removeClass(wrapper, 'ui-image-input-wrapper-double');
	          main_core.Dom.removeClass(wrapper, 'ui-image-input-wrapper-multiple');
	          break;
	      }
	    }
	  }]);
	  return ImageInput;
	}();

	main_core.Reflection.namespace('BX.UI').ImageInput = ImageInput;

}((this.window = this.window || {}),BX,BX.Event,BX));
//# sourceMappingURL=script.js.map
