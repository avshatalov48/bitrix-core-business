(function (exports,main_core,main_core_events) {
	'use strict';

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-image-item-shadow\"></div>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var ImageInput =
	/*#__PURE__*/
	function () {
	  function ImageInput() {
	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, ImageInput);
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
	        if (uploader && main_core.Type.isDomNode(uploader.fileInput)) {
	          var wrapper = uploader.fileInput.closest('.adm-fileinput-wrapper');

	          if (main_core.Type.isDomNode(wrapper)) {
	            var previews = wrapper.querySelectorAll('.adm-fileinput-item');

	            if (previews.length) {
	              main_core.Dom.addClass(wrapper, 'ui-image-input-wrapper');
	            }
	          }
	        }

	        requestAnimationFrame(function () {
	          _this.getLoaderContainer() && (_this.getLoaderContainer().style.display = 'none');
	          _this.getContainer().style.display = '';
	        });
	        main_core_events.EventEmitter.subscribe(uploader, 'onFileIsCreated', this.onFileIsCreatedHandler.bind(this));
	        main_core_events.EventEmitter.subscribe(uploader, 'onFileIsDeleted', this.onFileIsCreatedHandler.bind(this));
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
	  }, {
	    key: "onFileIsCreatedHandler",
	    value: function onFileIsCreatedHandler(event) {
	      var _this2 = this;

	      var _event$getCompatData3 = event.getCompatData(),
	          _event$getCompatData4 = babelHelpers.slicedToArray(_event$getCompatData3, 3),
	          uploader = _event$getCompatData4[2];

	      if (uploader && main_core.Type.isDomNode(uploader.fileInput)) {
	        var wrapper = uploader.fileInput.closest('.adm-fileinput-wrapper');

	        if (main_core.Type.isDomNode(wrapper)) {
	          setTimeout(function () {
	            _this2.recalculateWrapper(wrapper);
	          }, 100);
	        }
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
	      if (!wrapper.querySelector('div.ui-image-item-shadow')) {
	        var shadowElement = main_core.Tag.render(_templateObject());
	        var bottomMargin = 4;
	        var preview = wrapper.querySelector('.adm-fileinput-item-preview');
	        var previewWrapper = wrapper.closest('.adm-fileinput-item-wrapper');
	        var canvas = wrapper.querySelector('canvas');

	        if (canvas) {
	          shadowElement.style.height = canvas.offsetHeight + 'px';
	          shadowElement.style.width = canvas.offsetWidth - bottomMargin + 'px';
	          preview.style.height = canvas.offsetHeight + 'px';
	          previewWrapper.style.height = canvas.offsetHeight + 'px';
	        }

	        main_core.Dom.prepend(shadowElement, wrapper);
	      }
	    }
	  }, {
	    key: "recalculateWrapper",
	    value: function recalculateWrapper(wrapper) {
	      var previews = wrapper.querySelectorAll('.adm-fileinput-item');
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

}((this.window = this.window || {}),BX,BX.Event));
//# sourceMappingURL=script.js.map
