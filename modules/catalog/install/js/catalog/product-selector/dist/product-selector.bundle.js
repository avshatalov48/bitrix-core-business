this.BX = this.BX || {};
(function (exports,catalog_skuTree,ui_entitySelector,main_core_events,catalog_productSelector,main_core) {
	'use strict';

	var Base = /*#__PURE__*/function () {
	  function Base(id) {
	    var config = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, Base);
	    babelHelpers.defineProperty(this, "TYPE", '');
	    this.id = id || main_core.Text.getRandom();
	    this.config = config || {};
	    this.setMorePhotoValues(config.morePhoto);
	    this.setFields(config.fields);
	  }

	  babelHelpers.createClass(Base, [{
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getProductId",
	    value: function getProductId() {
	      return this.id;
	    }
	  }, {
	    key: "isSaveable",
	    value: function isSaveable() {
	      return false;
	    }
	  }, {
	    key: "setSaveable",
	    value: function setSaveable(value) {
	      this.config.saveProductFields = value;
	    }
	  }, {
	    key: "isNew",
	    value: function isNew() {
	      return this.getConfig('isNew', false);
	    }
	  }, {
	    key: "getConfig",
	    value: function getConfig(name, defaultValue) {
	      return BX.prop.get(this.config, name, defaultValue);
	    }
	  }, {
	    key: "getType",
	    value: function getType() {
	      return this.TYPE;
	    }
	  }, {
	    key: "getFields",
	    value: function getFields() {
	      return this.fields;
	    }
	  }, {
	    key: "getField",
	    value: function getField(fieldName) {
	      return BX.prop.get(this.fields, fieldName, '');
	    }
	  }, {
	    key: "setFields",
	    value: function setFields(fields) {
	      this.fields = main_core.Type.isObject(fields) ? fields : {};
	    }
	  }, {
	    key: "isEnableFileSaving",
	    value: function isEnableFileSaving() {
	      return false;
	    }
	  }, {
	    key: "getMorePhotoValues",
	    value: function getMorePhotoValues() {
	      return this.morePhoto;
	    }
	  }, {
	    key: "setMorePhotoValues",
	    value: function setMorePhotoValues(values) {
	      this.morePhoto = main_core.Type.isPlainObject(values) ? values : {};
	    }
	  }, {
	    key: "removeMorePhotoItem",
	    value: function removeMorePhotoItem(fileId) {
	      return false;
	    }
	  }, {
	    key: "addMorePhotoItem",
	    value: function addMorePhotoItem(fileId, value) {
	      this.morePhoto[fileId] = value;
	    }
	  }, {
	    key: "getFileType",
	    value: function getFileType() {
	      return this.getType();
	    }
	  }, {
	    key: "setFileType",
	    value: function setFileType(value) {
	      this.config.fileType = value || '';
	    }
	  }, {
	    key: "getDetailPath",
	    value: function getDetailPath() {
	      return '';
	    }
	  }, {
	    key: "setDetailPath",
	    value: function setDetailPath(value) {
	      this.config.DETAIL_PATH = value || '';
	    }
	  }]);
	  return Base;
	}();

	function _templateObject10() {
	  var data = babelHelpers.taggedTemplateLiteral(["", ""]);

	  _templateObject10 = function _templateObject10() {
	    return data;
	  };

	  return data;
	}

	function _templateObject9() {
	  var data = babelHelpers.taggedTemplateLiteral(["", ""]);

	  _templateObject9 = function _templateObject9() {
	    return data;
	  };

	  return data;
	}

	function _templateObject8() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-ctl ui-ctl-w100 ui-ctl-after-icon\"></div>"]);

	  _templateObject8 = function _templateObject8() {
	    return data;
	  };

	  return data;
	}

	function _templateObject7() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<button\n\t\t\t\t\tclass=\"ui-ctl-after ui-ctl-icon-search\"\n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t></button>\n\t\t\t"]);

	  _templateObject7 = function _templateObject7() {
	    return data;
	  };

	  return data;
	}

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<a\n\t\t\t\t\thref=\"", "\"\n\t\t\t\t\ttarget=\"_blank\"\n\t\t\t\t\tclass=\"ui-ctl-after ui-ctl-icon-forward\"\n\t\t\t\t></button>\n\t\t\t"]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<button\n\t\t\t\t\tclass=\"ui-ctl-after ui-ctl-icon-clear\" \n\t\t\t\t\tonclick=\"", "\"\n\t\t\t\t></button>\n\t\t\t"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input\n\t\t\t\t \ttype=\"hidden\" \n\t\t\t\t\tname=\"", "\" \n\t\t\t\t\tvalue=\"", "\"\n\t\t\t\t>\n\t\t\t"]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input type=\"text\" \n\t\t\t\t\tclass=\"ui-ctl-element ui-ctl-textbox\" \n\t\t\t\t\tautocomplete=\"off\"\n\t\t\t\t\tvalue=\"", "\"\n\t\t\t\t\tplaceholder=\"", "\"\n\t\t\t\t\tonchange=\"", "\"\n\t\t\t\t>\n\t\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-ctl-tag\">", "</div>\n\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-w100\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var ProductSearchInput = /*#__PURE__*/function () {
	  function ProductSearchInput(id) {
	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, ProductSearchInput);
	    babelHelpers.defineProperty(this, "cache", new main_core.Cache.MemoryCache());
	    this.id = id || main_core.Text.getRandom();
	    this.selector = options.selector;

	    if (!(this.selector instanceof catalog_productSelector.ProductSelector)) {
	      throw new Error('Product selector instance not found.');
	    }

	    this.model = options.model || {};
	    this.isEnabledSearch = options.isSearchEnabled || false;
	    this.isEnabledDetailLink = options.isEnabledDetailLink;
	    this.inputName = options.inputName || '';
	  }

	  babelHelpers.createClass(ProductSearchInput, [{
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getField",
	    value: function getField(fieldName) {
	      return this.model.getField(fieldName);
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.getField(this.inputName);
	    }
	  }, {
	    key: "isSearchEnabled",
	    value: function isSearchEnabled() {
	      return this.isEnabledSearch;
	    }
	  }, {
	    key: "isEmptyModel",
	    value: function isEmptyModel() {
	      return this.model.getType() === 'empty';
	    }
	  }, {
	    key: "toggleIcon",
	    value: function toggleIcon(icon, value) {
	      if (main_core.Type.isDomNode(icon)) {
	        main_core.Dom.style(icon, 'display', value);
	      }
	    }
	  }, {
	    key: "getNameBlock",
	    value: function getNameBlock() {
	      return main_core.Tag.render(_templateObject(), this.getNameTag(), this.getNameInput(), this.getHiddenNameInput());
	    }
	  }, {
	    key: "getNameTag",
	    value: function getNameTag() {
	      if (!this.model.isNew()) {
	        return '';
	      }

	      return main_core.Tag.render(_templateObject2(), main_core.Loc.getMessage('CATALOG_SELECTOR_NEW_TAG_TITLE'));
	    }
	  }, {
	    key: "getNameInput",
	    value: function getNameInput() {
	      var _this = this;

	      return this.cache.remember('nameInput', function () {
	        return main_core.Tag.render(_templateObject3(), main_core.Text.encode(_this.getValue()), main_core.Text.encode(_this.getPlaceholder()), _this.handleNameInputHiddenChange.bind(_this));
	      });
	    }
	  }, {
	    key: "getHiddenNameInput",
	    value: function getHiddenNameInput() {
	      var _this2 = this;

	      return this.cache.remember('hiddenNameInput', function () {
	        return main_core.Tag.render(_templateObject4(), main_core.Text.encode(_this2.inputName), main_core.Text.encode(_this2.getValue()));
	      });
	    }
	  }, {
	    key: "handleNameInputHiddenChange",
	    value: function handleNameInputHiddenChange(event) {
	      this.getHiddenNameInput().value = event.target.value;
	    }
	  }, {
	    key: "getClearIcon",
	    value: function getClearIcon() {
	      var _this3 = this;

	      return this.cache.remember('closeIcon', function () {
	        return main_core.Tag.render(_templateObject5(), _this3.handleClearIconClick.bind(_this3));
	      });
	    }
	  }, {
	    key: "getArrowIcon",
	    value: function getArrowIcon() {
	      var _this4 = this;

	      return this.cache.remember('arrowIcon', function () {
	        return main_core.Tag.render(_templateObject6(), _this4.model.getDetailPath());
	      });
	    }
	  }, {
	    key: "getSearchIcon",
	    value: function getSearchIcon() {
	      var _this5 = this;

	      return this.cache.remember('searchIcon', function () {
	        return main_core.Tag.render(_templateObject7(), _this5.handleSearchIconClick.bind(_this5));
	      });
	    }
	  }, {
	    key: "layout",
	    value: function layout() {
	      var block = main_core.Tag.render(_templateObject8());

	      if (!main_core.Type.isStringFilled(this.getValue())) {
	        this.toggleIcon(this.getClearIcon(), 'none');
	      }

	      block.appendChild(this.getClearIcon());

	      if (this.showDetailLink() && main_core.Type.isStringFilled(this.getValue())) {
	        this.toggleIcon(this.getClearIcon(), 'none');
	        this.toggleIcon(this.getArrowIcon(), 'block');
	        block.appendChild(this.getArrowIcon());
	      }

	      if (this.isSearchEnabled()) {
	        var iconValue = main_core.Type.isStringFilled(this.getValue()) ? 'none' : 'block';
	        this.toggleIcon(this.getSearchIcon(), iconValue);
	        block.appendChild(this.getSearchIcon());
	        main_core.Event.bind(this.getNameInput(), 'click', this.handleShowSearchDialog.bind(this));
	        main_core.Event.bind(this.getNameInput(), 'input', this.handleShowSearchDialog.bind(this));
	        main_core.Event.bind(this.getNameInput(), 'blur', this.handleNameInputBlur.bind(this));
	        main_core.Event.bind(this.getNameInput(), 'keydown', this.handleNameInputKeyDown.bind(this));
	      }

	      main_core.Event.bind(this.getNameInput(), 'click', this.handleIconsSwitchingOnNameInput.bind(this));
	      main_core.Event.bind(this.getNameInput(), 'input', this.handleIconsSwitchingOnNameInput.bind(this));

	      if (this.selector && this.selector.isSaveable()) {
	        main_core.Event.bind(this.getNameInput(), 'change', this.handleNameInputChange.bind(this));
	      }

	      block.appendChild(this.getNameBlock());
	      return block;
	    }
	  }, {
	    key: "showDetailLink",
	    value: function showDetailLink() {
	      return this.isEnabledDetailLink;
	    }
	  }, {
	    key: "getDialog",
	    value: function getDialog() {
	      var _this6 = this;

	      return this.cache.remember('dialog', function () {
	        return new ui_entitySelector.Dialog({
	          id: _this6.id,
	          height: 300,
	          context: 'catalog-products',
	          targetNode: _this6.getNameInput(),
	          enableSearch: false,
	          multiple: false,
	          dropdownMode: true,
	          searchTabOptions: {
	            stub: true,
	            stubOptions: {
	              title: main_core.Tag.message(_templateObject9(), 'CATALOG_SELECTOR_IS_EMPTY_TITLE'),
	              subtitle: main_core.Tag.message(_templateObject10(), 'CATALOG_SELECTOR_IS_EMPTY_SUBTITLE'),
	              arrow: true
	            }
	          },
	          searchOptions: {
	            allowCreateItem: true
	          },
	          events: {
	            'Item:onSelect': _this6.onProductSelect.bind(_this6),
	            'Search:onItemCreateAsync': _this6.createProduct.bind(_this6)
	          },
	          entities: [{
	            id: 'product',
	            options: {
	              iblockId: _this6.selector.getIblockId(),
	              basePriceId: _this6.selector.getBasePriceId()
	            }
	          }]
	        });
	      });
	    }
	  }, {
	    key: "handleNameInputKeyDown",
	    value: function handleNameInputKeyDown(event) {
	      var dialog = this.getDialog();

	      if (event.key === 'Enter' && dialog.getActiveTab() === dialog.getSearchTab()) {
	        // prevent a form submit
	        event.preventDefault();

	        if (main_core.Browser.isMac() && event.metaKey || event.ctrlKey) {
	          dialog.getSearchTab().getFooter().createItem();
	        }
	      }
	    }
	  }, {
	    key: "handleIconsSwitchingOnNameInput",
	    value: function handleIconsSwitchingOnNameInput(event) {
	      this.toggleIcon(this.getArrowIcon(), 'none');

	      if (main_core.Type.isStringFilled(event.target.value)) {
	        this.toggleIcon(this.getClearIcon(), 'block');
	        this.toggleIcon(this.getSearchIcon(), 'none');
	      } else {
	        this.toggleIcon(this.getClearIcon(), 'none');
	        this.toggleIcon(this.getSearchIcon(), 'block');
	      }
	    }
	  }, {
	    key: "handleClearIconClick",
	    value: function handleClearIconClick(event) {
	      if (this.selector.isProductSearchEnabled() && !this.isEmptyModel()) {
	        this.selector.clearState();
	        this.selector.clearLayout();
	        this.selector.layout();
	        this.selector.searchInDialog();
	      } else {
	        this.getNameInput().value = '';
	        this.toggleIcon(this.getClearIcon(), 'none');
	      }

	      this.selector.focusName();
	      this.selector.emit('onClear', {
	        selectorId: this.selector.getId(),
	        rowId: this.selector.getRowId()
	      });
	      event.stopPropagation();
	      event.preventDefault();
	    }
	  }, {
	    key: "handleNameInputChange",
	    value: function handleNameInputChange(event) {
	      var value = event.target.value;
	      main_core_events.EventEmitter.emit('ProductList::onChangeFields', {
	        rowId: this.selector.getRowId(),
	        fields: {
	          'NAME': value
	        }
	      });
	    }
	  }, {
	    key: "focusName",
	    value: function focusName() {
	      var _this7 = this;

	      requestAnimationFrame(function () {
	        return _this7.getNameInput().focus();
	      });
	    }
	  }, {
	    key: "searchInDialog",
	    value: function searchInDialog() {
	      var searchQuery = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';

	      if (!this.selector.isProductSearchEnabled()) {
	        return;
	      }

	      var dialog = this.getDialog();

	      if (dialog) {
	        dialog.show();
	        dialog.search(searchQuery);
	      }
	    }
	  }, {
	    key: "handleShowSearchDialog",
	    value: function handleShowSearchDialog(event) {
	      if (this.isEmptyModel()) {
	        this.selector.searchInDialog(event.target.value);
	      }
	    }
	  }, {
	    key: "handleNameInputBlur",
	    value: function handleNameInputBlur(event) {
	      var _this8 = this;

	      // timeout to toggle clear icon handler while cursor is inside of name input
	      setTimeout(function () {
	        _this8.toggleIcon(_this8.getClearIcon(), 'none');

	        if (_this8.showDetailLink() && main_core.Type.isStringFilled(_this8.getValue())) {
	          _this8.toggleIcon(_this8.getSearchIcon(), 'none');

	          _this8.toggleIcon(_this8.getArrowIcon(), 'block');
	        } else {
	          _this8.toggleIcon(_this8.getArrowIcon(), 'none');

	          _this8.toggleIcon(_this8.getSearchIcon(), 'block');
	        }
	      }, 200);
	    }
	  }, {
	    key: "handleSearchIconClick",
	    value: function handleSearchIconClick(event) {
	      this.selector.searchInDialog();
	      this.selector.focusName();
	      event.stopPropagation();
	      event.preventDefault();
	    }
	  }, {
	    key: "onProductSelect",
	    value: function onProductSelect(event) {
	      var item = event.getData().item;
	      item.getDialog().getTargetNode().value = item.getTitle();
	      this.toggleIcon(this.getSearchIcon(), 'none');

	      if (this.selector) {
	        this.selector.onProductSelect(item.getId(), {
	          saveProductFields: item.getCustomData().get('saveProductFields'),
	          isNew: item.getCustomData().get('isNew')
	        });
	      }

	      item.getDialog().hide();
	    }
	  }, {
	    key: "createProduct",
	    value: function createProduct(event) {
	      var _this9 = this;

	      return new Promise(function (resolve, reject) {
	        var _event$getData = event.getData(),
	            searchQuery = _event$getData.searchQuery;

	        var dialog = event.getTarget();
	        var fields = {
	          NAME: searchQuery.getQuery(),
	          IBLOCK_ID: _this9.selector.getIblockId()
	        };
	        dialog.showLoader();
	        main_core.ajax.runAction('catalog.productSelector.createProduct', {
	          json: {
	            fields: fields
	          }
	        }).then(function (response) {
	          dialog.hideLoader();
	          var item = dialog.addItem({
	            id: response.data.id,
	            entityId: 'product',
	            title: searchQuery.getQuery(),
	            tabs: dialog.getRecentTab().getId(),
	            customData: {
	              saveProductFields: true,
	              isNew: true
	            }
	          });

	          if (item) {
	            item.select();
	          }

	          dialog.hide();
	          resolve();
	        }).catch(function () {
	          return reject();
	        });
	      });
	    }
	  }, {
	    key: "getPlaceholder",
	    value: function getPlaceholder() {
	      return this.isSearchEnabled() && this.isEmptyModel() ? main_core.Loc.getMessage('CATALOG_SELECTOR_BEFORE_SEARCH_TITLE') : main_core.Loc.getMessage('CATALOG_SELECTOR_VIEW_NAME_TITLE');
	    }
	  }]);
	  return ProductSearchInput;
	}();

	function _templateObject$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div></div>"]);

	  _templateObject$1 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var ProductImageInput = /*#__PURE__*/function () {
	  function ProductImageInput(id) {
	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, ProductImageInput);
	    this.id = id || main_core.Text.getRandom();
	    this.selector = options.selector || null;

	    if (!(this.selector instanceof catalog_productSelector.ProductSelector)) {
	      throw new Error('Product selector instance not found.');
	    }

	    this.config = options.config || {};
	    this.setView(options.view);

	    if (main_core.Type.isStringFilled(options.inputHtml)) {
	      this.setInputHtml(options.inputHtml);
	    } else {
	      this.restoreDefaultInputHtml();
	    }

	    this.enableSaving = options.enableSaving;
	    this.uploaderFieldMap = {};

	    if (this.isEnabledLiveSaving()) {
	      main_core_events.EventEmitter.subscribe('onUploaderIsInited', this.onUploaderIsInitedHandler.bind(this));
	    }
	  }

	  babelHelpers.createClass(ProductImageInput, [{
	    key: "onUploaderIsInitedHandler",
	    value: function onUploaderIsInitedHandler(event) {
	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 2),
	          id = _event$getCompatData2[0],
	          uploader = _event$getCompatData2[1];

	      if (!this.isViewMode() && main_core.Type.isStringFilled(this.id) && this.id === id) {
	        this.uploaderFieldMap = {};
	        main_core_events.EventEmitter.subscribe(uploader, 'onFileIsDeleted', this.onFileDelete.bind(this));
	        main_core_events.EventEmitter.subscribe(uploader, 'onFileIsUploaded', this.onFileUpload.bind(this));
	        main_core_events.EventEmitter.subscribe(uploader, 'onQueueIsChanged', this.onQueueIsChanged.bind(this));
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
	    key: "setView",
	    value: function setView(html) {
	      this.view = main_core.Type.isStringFilled(html) ? html : '';
	    }
	  }, {
	    key: "setInputHtml",
	    value: function setInputHtml(html) {
	      this.inputHtml = main_core.Type.isStringFilled(html) ? html : '';
	    }
	  }, {
	    key: "restoreDefaultInputHtml",
	    value: function restoreDefaultInputHtml() {
	      this.inputHtml = "\n\t\t\t<div class='ui-image-input-container ui-image-input-img--disabled'>\n\t\t\t\t<div class='adm-fileinput-wrapper '>\n\t\t\t\t\t<div class='adm-fileinput-area mode-pict adm-fileinput-drag-area'></div>\n\t\t\t\t</div>\n\t\t\t</div>\n";
	    }
	  }, {
	    key: "isViewMode",
	    value: function isViewMode() {
	      return this.selector && this.selector.isViewMode();
	    }
	  }, {
	    key: "isEnabledLiveSaving",
	    value: function isEnabledLiveSaving() {
	      return this.enableSaving;
	    }
	  }, {
	    key: "layout",
	    value: function layout() {
	      var imageContainer = main_core.Tag.render(_templateObject$1());
	      main_core.Runtime.html(imageContainer, this.isViewMode() ? this.view : this.inputHtml);
	      return imageContainer;
	    }
	  }, {
	    key: "onFileDelete",
	    value: function onFileDelete(event) {
	      var _event$getCompatData3 = event.getCompatData(),
	          _event$getCompatData4 = babelHelpers.slicedToArray(_event$getCompatData3, 4),
	          file = _event$getCompatData4[3];

	      var fileId = file.fileId;

	      if (this.isViewMode() || !this.selector) {
	        return;
	      }

	      var deleteResult = this.selector.getModel().removeMorePhotoItem(fileId);

	      if (deleteResult) {
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

	      if (type === 'add' && 'input_name' in image && main_core.Type.isNil(this.uploaderFieldMap[itemId])) {
	        this.uploaderFieldMap[itemId] = image['input_name'];
	      }
	    }
	  }, {
	    key: "onFileUpload",
	    value: function onFileUpload(event) {
	      var _event$getCompatData7 = event.getCompatData(),
	          _event$getCompatData8 = babelHelpers.slicedToArray(_event$getCompatData7, 3),
	          itemId = _event$getCompatData8[0],
	          params = _event$getCompatData8[2];

	      if (!main_core.Type.isObject(params) || !('file' in params) || !('files' in params.file) || !('default' in params.file.files) || this.isViewMode() || !this.selector) {
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
	      var fileFieldName = this.uploaderFieldMap[itemId] || itemId;
	      this.selector.getModel().addMorePhotoItem(fileFieldName, photoItem);
	      this.save(true);
	    }
	  }, {
	    key: "save",
	    value: function save(rebuild) {
	      if (this.selector) {
	        this.selector.saveFiles(rebuild);
	      }
	    }
	  }]);
	  return ProductImageInput;
	}();

	var Empty = /*#__PURE__*/function (_Base) {
	  babelHelpers.inherits(Empty, _Base);

	  function Empty() {
	    var _babelHelpers$getProt;

	    var _this;

	    babelHelpers.classCallCheck(this, Empty);

	    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	      args[_key] = arguments[_key];
	    }

	    _this = babelHelpers.possibleConstructorReturn(this, (_babelHelpers$getProt = babelHelpers.getPrototypeOf(Empty)).call.apply(_babelHelpers$getProt, [this].concat(args)));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "TYPE", 'empty');
	    return _this;
	  }

	  return Empty;
	}(Base);

	var Product = /*#__PURE__*/function (_Base) {
	  babelHelpers.inherits(Product, _Base);

	  function Product() {
	    var _babelHelpers$getProt;

	    var _this;

	    babelHelpers.classCallCheck(this, Product);

	    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	      args[_key] = arguments[_key];
	    }

	    _this = babelHelpers.possibleConstructorReturn(this, (_babelHelpers$getProt = babelHelpers.getPrototypeOf(Product)).call.apply(_babelHelpers$getProt, [this].concat(args)));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "TYPE", 'product');
	    return _this;
	  }

	  babelHelpers.createClass(Product, [{
	    key: "isSaveable",
	    value: function isSaveable() {
	      return this.getConfig('saveProductFields', false);
	    }
	  }, {
	    key: "getType",
	    value: function getType() {
	      return Product.TYPE;
	    }
	  }, {
	    key: "isEnableFileSaving",
	    value: function isEnableFileSaving() {
	      return true;
	    }
	  }, {
	    key: "getDetailPath",
	    value: function getDetailPath() {
	      return this.getConfig('DETAIL_PATH', '');
	    }
	  }, {
	    key: "removeMorePhotoItem",
	    value: function removeMorePhotoItem(fileId) {
	      for (var index in this.morePhoto) {
	        var value = this.morePhoto[index];

	        if (!main_core.Type.isObject(value)) {
	          value = main_core.Text.toInteger(value);
	        }

	        if (main_core.Type.isNumber(value) && value === main_core.Text.toInteger(fileId) || main_core.Type.isObject(value) && value.fileId === fileId) {
	          delete this.morePhoto[index];
	          return true;
	        }
	      }

	      return false;
	    }
	  }]);
	  return Product;
	}(Base);

	var Sku = /*#__PURE__*/function (_Product) {
	  babelHelpers.inherits(Sku, _Product);

	  function Sku() {
	    var _babelHelpers$getProt;

	    var _this;

	    babelHelpers.classCallCheck(this, Sku);

	    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	      args[_key] = arguments[_key];
	    }

	    _this = babelHelpers.possibleConstructorReturn(this, (_babelHelpers$getProt = babelHelpers.getPrototypeOf(Sku)).call.apply(_babelHelpers$getProt, [this].concat(args)));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "TYPE", 'sku');
	    return _this;
	  }

	  babelHelpers.createClass(Sku, [{
	    key: "getFileType",
	    value: function getFileType() {
	      return this.config.fileType === Product.TYPE ? Product.TYPE : Sku.TYPE;
	    }
	  }, {
	    key: "getProductId",
	    value: function getProductId() {
	      return this.getConfig('productId');
	    }
	  }]);
	  return Sku;
	}(Product);

	function _templateObject5$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"catalog-product-field-input\"></div>"]);

	  _templateObject5$1 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span title=\"", "\">", "</span>"]);

	  _templateObject4$1 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<a href=\"", "\" title=\"", "\">", "</a>\n\t\t\t"]);

	  _templateObject3$1 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2$1() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"catalog-product-img\"></div>"]);

	  _templateObject2$1 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject$2() {
	  var data = babelHelpers.taggedTemplateLiteral(["<div class=\"catalog-product-field-inner\"></div>"]);

	  _templateObject$2 = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var instances = new Map();
	var ProductSelector = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(ProductSelector, _EventEmitter);
	  babelHelpers.createClass(ProductSelector, null, [{
	    key: "getById",
	    value: function getById(id) {
	      return instances.get(id) || null;
	    }
	  }]);

	  function ProductSelector(id) {
	    var _this;

	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, ProductSelector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ProductSelector).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "mode", ProductSelector.MODE_EDIT);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "cache", new main_core.Cache.MemoryCache());
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "variationChangeHandler", _this.handleVariationChange.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.setEventNamespace('BX.Catalog.ProductSelector');

	    _this.id = id || main_core.Text.getRandom();
	    _this.options = options || {};
	    _this.iblockId = main_core.Text.toNumber(options.iblockId);
	    _this.basePriceId = main_core.Text.toNumber(options.basePriceId);

	    _this.setMode(options.mode);

	    _this.model = _this.createModel(options);

	    _this.model.setFields(options.fields);

	    _this.model.setMorePhotoValues(options.morePhotoValues);

	    _this.model.setDetailPath(_this.getConfig('DETAIL_PATH'));

	    _this.skuTree = options.skuTree || null;

	    _this.setFileType(options.fileType);

	    _this.layout();

	    main_core_events.EventEmitter.subscribe('ProductList::onChangeFields', main_core.Runtime.debounce(_this.onChangeFields, 500, babelHelpers.assertThisInitialized(_this)));
	    instances.set(_this.id, babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }

	  babelHelpers.createClass(ProductSelector, [{
	    key: "createModel",
	    value: function createModel() {
	      var _options$config;

	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var productId = main_core.Text.toInteger(options.productId) || 0;

	      if (productId <= 0) {
	        return new Empty();
	      }

	      var modelConfig = (options === null || options === void 0 ? void 0 : (_options$config = options.config) === null || _options$config === void 0 ? void 0 : _options$config.MODEL_CONFIG) || {};
	      var skuId = main_core.Text.toInteger(options.skuId) || 0;

	      if (skuId > 0 && skuId !== productId) {
	        return new Sku(skuId, babelHelpers.objectSpread({}, modelConfig, {
	          productId: productId
	        }));
	      }

	      return new Product(productId, modelConfig);
	    }
	  }, {
	    key: "getModel",
	    value: function getModel() {
	      return this.model;
	    }
	  }, {
	    key: "setMode",
	    value: function setMode(mode) {
	      if (!main_core.Type.isNil(mode)) {
	        this.mode = mode === ProductSelector.MODE_VIEW ? ProductSelector.MODE_VIEW : ProductSelector.MODE_EDIT;
	      }
	    }
	  }, {
	    key: "setFileType",
	    value: function setFileType(fileType) {
	      this.fileType = fileType === ProductSelector.SKU_TYPE ? ProductSelector.SKU_TYPE : ProductSelector.PRODUCT_TYPE;
	    }
	  }, {
	    key: "isViewMode",
	    value: function isViewMode() {
	      return this.mode === ProductSelector.MODE_VIEW;
	    }
	  }, {
	    key: "isSaveable",
	    value: function isSaveable() {
	      return !this.isViewMode() && this.model.isSaveable();
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getIblockId",
	    value: function getIblockId() {
	      return this.iblockId;
	    }
	  }, {
	    key: "getBasePriceId",
	    value: function getBasePriceId() {
	      return this.basePriceId;
	    }
	  }, {
	    key: "getConfig",
	    value: function getConfig(name, defaultValue) {
	      return BX.prop.get(this.options.config, name, defaultValue);
	    }
	  }, {
	    key: "getRowId",
	    value: function getRowId() {
	      return this.getConfig('ROW_ID');
	    }
	  }, {
	    key: "getFileInput",
	    value: function getFileInput() {
	      if (!this.fileInput) {
	        this.fileInput = new ProductImageInput(this.options.fileInputId, {
	          selector: this,
	          view: this.options.fileView,
	          inputHtml: this.options.fileInput,
	          enableSaving: this.getConfig('ENABLE_IMAGE_CHANGE_SAVING', false)
	        });
	      }

	      return this.fileInput;
	    }
	  }, {
	    key: "isProductFileType",
	    value: function isProductFileType() {
	      return this.fileType === ProductSelector.PRODUCT_TYPE;
	    }
	  }, {
	    key: "isProductSearchEnabled",
	    value: function isProductSearchEnabled() {
	      return this.getConfig('ENABLE_SEARCH', false) && this.getIblockId() > 0;
	    }
	  }, {
	    key: "isInputDetailLinkEnabled",
	    value: function isInputDetailLinkEnabled() {
	      return this.getConfig('ENABLE_INPUT_DETAIL_LINK', false) && main_core.Type.isStringFilled(this.model.getDetailPath());
	    }
	  }, {
	    key: "getWrapper",
	    value: function getWrapper() {
	      this.wrapper = document.getElementById(this.id);
	      return this.wrapper;
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(node) {
	      this.clearLayout();
	      this.wrapper = node;
	      this.layout();
	    }
	  }, {
	    key: "layout",
	    value: function layout() {
	      var wrapper = this.getWrapper();

	      if (!wrapper) {
	        return;
	      }

	      this.defineWrapperClass(wrapper);
	      this.layoutImage();
	      var block = main_core.Tag.render(_templateObject$2());
	      wrapper.appendChild(block);
	      block.appendChild(this.layoutNameBlock());
	      block.appendChild(this.getImageContainer());
	      this.layoutSkuTree();
	      this.subscribeToVariationChange();
	    }
	  }, {
	    key: "focusName",
	    value: function focusName() {
	      if (this.searchInput) {
	        this.searchInput.focusName();
	      }

	      return this;
	    }
	  }, {
	    key: "searchInDialog",
	    value: function searchInDialog() {
	      var searchQuery = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';

	      if (this.searchInput) {
	        this.searchInput.searchInDialog(searchQuery);
	      }

	      return this;
	    }
	  }, {
	    key: "getImageContainer",
	    value: function getImageContainer() {
	      return this.cache.remember('imageContainer', function () {
	        return main_core.Tag.render(_templateObject2$1());
	      });
	    }
	  }, {
	    key: "layoutImage",
	    value: function layoutImage() {
	      this.getImageContainer().innerHTML = '';
	      this.getImageContainer().appendChild(this.getFileInput().layout());
	      this.refreshImageSelectorId = null;
	    }
	  }, {
	    key: "clearState",
	    value: function clearState() {
	      this.model = this.createModel();
	      this.fileInput.setInputHtml(this.options.fileInput || '');
	      this.skuTree = null;
	      this.skuTreeInstance = null;
	      this.refreshImageSelectorId = null;
	    }
	  }, {
	    key: "clearLayout",
	    value: function clearLayout() {
	      var wrapper = this.getWrapper();

	      if (wrapper) {
	        main_core.Event.unbindAll(wrapper);
	        wrapper.innerHTML = '';
	      }

	      this.unsubscribeToVariationChange();
	    }
	  }, {
	    key: "defineWrapperClass",
	    value: function defineWrapperClass(wrapper) {
	      if (this.isViewMode()) {
	        main_core.Dom.addClass(wrapper, 'catalog-product-view');
	        main_core.Dom.removeClass(wrapper, 'catalog-product-edit');
	      } else {
	        main_core.Dom.addClass(wrapper, 'catalog-product-edit');
	        main_core.Dom.removeClass(wrapper, 'catalog-product-view');
	      }
	    }
	  }, {
	    key: "getNameBlockView",
	    value: function getNameBlockView() {
	      var productName = main_core.Text.encode(this.model.getField('NAME'));
	      var namePlaceholder = main_core.Loc.getMessage('CATALOG_SELECTOR_VIEW_NAME_TITLE');

	      if (this.getModel().getDetailPath()) {
	        return main_core.Tag.render(_templateObject3$1(), this.getModel().getDetailPath(), namePlaceholder, productName);
	      }

	      return main_core.Tag.render(_templateObject4$1(), namePlaceholder, productName);
	    }
	  }, {
	    key: "layoutNameBlock",
	    value: function layoutNameBlock() {
	      var block = main_core.Tag.render(_templateObject5$1());

	      if (this.isViewMode()) {
	        block.appendChild(this.getNameBlockView());
	      } else {
	        this.searchInput = new ProductSearchInput(this.id, {
	          selector: this,
	          model: this.getModel(),
	          inputName: 'NAME',
	          isSearchEnabled: this.isProductSearchEnabled(),
	          iblockId: this.getIblockId(),
	          basePriceId: this.getBasePriceId(),
	          isEnabledDetailLink: this.isInputDetailLinkEnabled()
	        });
	        block.appendChild(this.searchInput.layout());
	      }

	      return block;
	    }
	  }, {
	    key: "updateSkuTree",
	    value: function updateSkuTree(tree) {
	      this.skuTree = tree;
	      this.skuTreeInstance = null;
	    }
	  }, {
	    key: "getSkuTreeInstance",
	    value: function getSkuTreeInstance() {
	      if (this.skuTree && !this.skuTreeInstance) {
	        this.skuTreeInstance = new catalog_skuTree.SkuTree({
	          skuTree: this.skuTree,
	          selectable: this.getConfig('ENABLE_SKU_SELECTION', true)
	        });
	      }

	      return this.skuTreeInstance;
	    }
	  }, {
	    key: "layoutSkuTree",
	    value: function layoutSkuTree() {
	      var skuTree = this.getSkuTreeInstance();
	      var wrapper = this.getWrapper();

	      if (skuTree && wrapper) {
	        wrapper.appendChild(skuTree.layout());
	      }
	    }
	  }, {
	    key: "subscribeToVariationChange",
	    value: function subscribeToVariationChange() {
	      var skuTree = this.getSkuTreeInstance();

	      if (skuTree) {
	        skuTree.subscribe('SkuProperty::onChange', this.variationChangeHandler);
	      }
	    }
	  }, {
	    key: "unsubscribeToVariationChange",
	    value: function unsubscribeToVariationChange() {
	      var skuTree = this.getSkuTreeInstance();

	      if (skuTree) {
	        skuTree.unsubscribe('SkuProperty::onChange', this.variationChangeHandler);
	      }
	    }
	  }, {
	    key: "handleVariationChange",
	    value: function handleVariationChange(event) {
	      var _this2 = this;

	      var _event$getData = event.getData(),
	          _event$getData2 = babelHelpers.slicedToArray(_event$getData, 1),
	          skuFields = _event$getData2[0];

	      var productId = main_core.Text.toNumber(skuFields.PARENT_PRODUCT_ID);
	      var variationId = main_core.Text.toNumber(skuFields.ID);

	      if (productId <= 0 || variationId <= 0) {
	        return;
	      }

	      this.model.setSaveable(false);
	      this.emit('onBeforeChange', {
	        selectorId: this.getId(),
	        rowId: this.getRowId()
	      });
	      main_core.ajax.runAction('catalog.productSelector.getSelectedSku', {
	        json: {
	          variationId: variationId,
	          options: {
	            priceId: this.basePriceId,
	            urlBuilder: this.getConfig('URL_BUILDER_CONTEXT')
	          }
	        }
	      }).then(function (response) {
	        return _this2.processResponse(response, babelHelpers.objectSpread({}, _this2.options.config));
	      });
	    }
	  }, {
	    key: "onChangeFields",
	    value: function onChangeFields(event) {
	      var eventData = event.getData();

	      if (!this.isSaveable() || eventData.rowId !== this.getRowId()) {
	        return;
	      }

	      if (!main_core.Type.isNil(eventData.productId) && eventData.productId !== this.getModel().getProductId()) {
	        return;
	      }

	      var fields = eventData.fields;
	      var priceValue = main_core.Text.toNumber(fields.PRICE);

	      if (priceValue > 0 && main_core.Type.isStringFilled(fields.CURRENCY)) {
	        fields.PRICES = {};
	        fields.PRICES[this.getBasePriceId()] = {
	          PRICE: priceValue,
	          CURRENCY: fields.CURRENCY
	        };
	      }

	      this.updateProduct(fields);
	    }
	  }, {
	    key: "updateProduct",
	    value: function updateProduct(fields) {
	      if (!main_core.Type.isPlainObject(fields)) {
	        return;
	      }

	      if (this.getModel().getId() <= 0 || this.getIblockId() <= 0) {
	        return;
	      }

	      main_core.ajax.runAction('catalog.productSelector.updateProduct', {
	        json: {
	          id: this.getModel().getId(),
	          iblockId: this.getIblockId(),
	          updateFields: fields
	        }
	      });
	    }
	  }, {
	    key: "saveFiles",
	    value: function saveFiles(rebuild) {
	      var _this3 = this;

	      var imageValues = this.getModel().getMorePhotoValues();

	      if (this.submitFileTimeOut) {
	        clearTimeout(this.submitFileTimeOut);
	      }

	      var requestId = main_core.Text.getRandom(20);
	      this.refreshImageSelectorId = requestId;
	      this.submitFileTimeOut = setTimeout(function () {
	        main_core.ajax.runAction('catalog.productSelector.saveMorePhoto', {
	          json: {
	            productId: _this3.model.getProductId(),
	            variationId: _this3.model.getId(),
	            iblockId: _this3.getIblockId(),
	            imageValues: imageValues
	          }
	        }).then(function (response) {
	          if (!rebuild && _this3.refreshImageSelectorId === requestId) {
	            return;
	          }

	          _this3.getFileInput().setId(response.data.id);

	          _this3.getFileInput().setInputHtml(response.data.input);

	          _this3.getFileInput().setView(response.data.preview);

	          _this3.getModel().setMorePhotoValues(response.data.values);

	          _this3.layoutImage();
	        });
	      }, 500);
	    }
	  }, {
	    key: "onProductSelect",
	    value: function onProductSelect(productId, itemConfig) {
	      this.emit('onBeforeChange', {
	        selectorId: this.getId(),
	        rowId: this.getRowId()
	      });
	      this.productSelectAjaxAction(productId, itemConfig);
	    }
	  }, {
	    key: "productSelectAjaxAction",
	    value: function productSelectAjaxAction(productId) {
	      var _this4 = this;

	      var itemConfig = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {
	        saveProductFields: false,
	        isNew: false
	      };
	      main_core.ajax.runAction('catalog.productSelector.getProduct', {
	        json: {
	          productId: productId,
	          options: {
	            priceId: this.basePriceId,
	            urlBuilder: this.getConfig('URL_BUILDER_CONTEXT')
	          }
	        }
	      }).then(function (response) {
	        return _this4.processResponse(response, babelHelpers.objectSpread({}, _this4.options.config, itemConfig), true);
	      });
	    }
	  }, {
	    key: "processResponse",
	    value: function processResponse(response) {
	      var config = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var isProductAction = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
	      var data = (response === null || response === void 0 ? void 0 : response.data) || null;

	      if (data) {
	        this.changeSelectedElement(data, config);
	      } else if (isProductAction) {
	        this.clearState();
	      } else {
	        this.productSelectAjaxAction(this.getModel().getProductId());
	      }

	      this.unsubscribeToVariationChange();
	      this.clearLayout();
	      this.layout();
	      var fields = (data === null || data === void 0 ? void 0 : data.fields) || null;
	      this.emit('onChange', {
	        selectorId: this.id,
	        rowId: this.getRowId(),
	        isNew: config.isNew || false,
	        fields: fields
	      });
	    }
	  }, {
	    key: "changeSelectedElement",
	    value: function changeSelectedElement(data, config) {
	      var productId = main_core.Text.toInteger(data.productId);
	      var productChanged = this.getModel().getId() !== productId;

	      if (productChanged) {
	        var skuId = main_core.Text.toInteger(data.skuId);

	        if (skuId > 0 && skuId !== productId) {
	          config.productId = productId;
	          this.model = new Sku(skuId, config);
	        } else {
	          this.model = new Product(productId, config);
	        }
	      }

	      this.getModel().setFields(data.fields);
	      var imageField = {
	        id: '',
	        input: '',
	        preview: '',
	        values: []
	      };

	      if (main_core.Type.isObject(data.image)) {
	        imageField.id = data.image.id;
	        imageField.input = data.image.input;
	        imageField.preview = data.image.preview;
	        imageField.values = data.image.values;
	        this.getModel().setFileType(data.fileType);
	      }

	      this.getFileInput().setId(imageField.id);
	      this.getFileInput().setInputHtml(imageField.input);
	      this.getFileInput().setView(imageField.preview);
	      this.getModel().setMorePhotoValues(imageField.values);

	      if (data.detailUrl) {
	        this.getModel().setDetailPath(data.detailUrl);
	      }

	      if (main_core.Type.isObject(data.skuTree)) {
	        this.updateSkuTree(data.skuTree);
	      }
	    }
	  }]);
	  return ProductSelector;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(ProductSelector, "MODE_VIEW", 'view');
	babelHelpers.defineProperty(ProductSelector, "MODE_EDIT", 'edit');
	babelHelpers.defineProperty(ProductSelector, "PRODUCT_TYPE", 'product');
	babelHelpers.defineProperty(ProductSelector, "SKU_TYPE", 'sku');

	exports.ProductSelector = ProductSelector;

}((this.BX.Catalog = this.BX.Catalog || {}),BX.Catalog.SkuTree,BX.UI.EntitySelector,BX.Event,BX.Catalog,BX));
//# sourceMappingURL=product-selector.bundle.js.map
