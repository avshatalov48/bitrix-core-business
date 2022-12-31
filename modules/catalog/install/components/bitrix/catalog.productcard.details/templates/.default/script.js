(function (exports,main_core,catalog_entityCard,main_core_events,main_popup,ui_buttons) {
	'use strict';

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _isQuantityTraceNoticeShown = /*#__PURE__*/new WeakMap();

	var ProductCard = /*#__PURE__*/function (_EntityCard) {
	  babelHelpers.inherits(ProductCard, _EntityCard);

	  function ProductCard(id) {
	    var _this;

	    var settings = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, ProductCard);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ProductCard).call(this, id, settings));

	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _isQuantityTraceNoticeShown, {
	      writable: true,
	      value: false
	    });

	    _this.initDocumentTypeSelector();

	    return _this;
	  }

	  babelHelpers.createClass(ProductCard, [{
	    key: "getEntityType",
	    value: function getEntityType() {
	      return 'Product';
	    }
	  }, {
	    key: "onSectionLayout",
	    value: function onSectionLayout(event) {
	      var _this2 = this;

	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 2),
	          section = _event$getCompatData2[0],
	          eventData = _event$getCompatData2[1];

	      if (eventData.id === 'catalog_parameters') {
	        eventData.visible = this.isSimpleProduct && this.isCardSettingEnabled('CATALOG_PARAMETERS');
	      }

	      main_core_events.EventEmitter.subscribe('BX.UI.EntityEditorList:onItemSelect', function (event) {
	        var _event$getData$;

	        var isQuantityTraceRestricted = !(_this2.isWithOrdersMode && !_this2.isInventoryManagementUsed);

	        if (babelHelpers.classPrivateFieldGet(_this2, _isQuantityTraceNoticeShown) || !isQuantityTraceRestricted) {
	          return;
	        }

	        var field = (_event$getData$ = event.getData()[1]) === null || _event$getData$ === void 0 ? void 0 : _event$getData$.field;

	        if (!field) {
	          return;
	        }

	        if (field.getId() !== 'QUANTITY_TRACE' || field._selectedValue !== 'N') {
	          return;
	        }

	        var popup = new main_popup.Popup({
	          content: main_core.Loc.getMessage('CPD_QUANTITY_TRACE_NOTICE'),
	          overlay: true,
	          titleBar: main_core.Loc.getMessage('CPD_QUANTITY_TRACE_NOTICE_TITLE'),
	          closeByEsc: true,
	          closeIcon: true,
	          buttons: [new ui_buttons.Button({
	            text: main_core.Loc.getMessage('CPD_QUANTITY_TRACE_ACCEPT'),
	            className: 'ui-btn ui-btn-md ui-btn-primary',
	            events: {
	              click: function () {
	                babelHelpers.classPrivateFieldSet(this, _isQuantityTraceNoticeShown, false);
	                popup.destroy();
	              }.bind(_this2)
	            }
	          })],
	          events: {
	            onAfterClose: function () {
	              babelHelpers.classPrivateFieldSet(this, _isQuantityTraceNoticeShown, false);
	            }.bind(_this2)
	          }
	        });
	        popup.show();
	        babelHelpers.classPrivateFieldSet(_this2, _isQuantityTraceNoticeShown, true);
	      });
	      section === null || section === void 0 ? void 0 : section.getChildren().forEach(function (field) {
	        if (_this2.hiddenFields.includes(field === null || field === void 0 ? void 0 : field.getId())) {
	          field.setVisible(false);
	        }
	      });
	      main_core_events.EventEmitter.subscribe('onEntityUpdate', function (event) {
	        var _event$getData$2;

	        var editor = (_event$getData$2 = event.getData()[0]) === null || _event$getData$2 === void 0 ? void 0 : _event$getData$2.sender;

	        if (!editor) {
	          return;
	        }

	        var quantityTraceValue = editor._model.getField('QUANTITY_TRACE', 'D');

	        var isQuantityTraceRestricted = !(_this2.isWithOrdersMode && !_this2.isInventoryManagementUsed);

	        if (quantityTraceValue !== 'N' && isQuantityTraceRestricted) {
	          var _editor$getControlByI;

	          (_editor$getControlByI = editor.getControlById('QUANTITY_TRACE')) === null || _editor$getControlByI === void 0 ? void 0 : _editor$getControlByI.setVisible(false);
	        }
	      });
	    }
	  }, {
	    key: "onGridUpdatedHandler",
	    value: function onGridUpdatedHandler(event) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(ProductCard.prototype), "onGridUpdatedHandler", this).call(this, event);

	      var _event$getCompatData3 = event.getCompatData(),
	          _event$getCompatData4 = babelHelpers.slicedToArray(_event$getCompatData3, 1),
	          grid = _event$getCompatData4[0];

	      if (grid && grid.getId() === this.getVariationGridId() && grid.getRows().getCountDisplayed() <= 0) {
	        document.location.reload();
	      }
	    }
	  }, {
	    key: "onEditorAjaxSubmit",
	    value: function onEditorAjaxSubmit(event) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(ProductCard.prototype), "onEditorAjaxSubmit", this).call(this, event);

	      var _event$getCompatData5 = event.getCompatData(),
	          _event$getCompatData6 = babelHelpers.slicedToArray(_event$getCompatData5, 2),
	          response = _event$getCompatData6[1];

	      if (response.data) {
	        if (response.data.NOTIFY_ABOUT_NEW_VARIATION) {
	          this.showNotification(main_core.Loc.getMessage('CPD_NEW_VARIATION_ADDED'));
	        }
	      }
	    }
	  }, {
	    key: "initDocumentTypeSelector",
	    value: function initDocumentTypeSelector() {
	      var productTypeSelector = document.getElementById(this.settings.productTypeSelector);
	      var productTypeSelectorTypes = this.settings.productTypeSelectorTypes;

	      if (!productTypeSelector || !productTypeSelectorTypes) {
	        return;
	      }

	      var menuItems = [];
	      Object.keys(productTypeSelectorTypes).forEach(function (type) {
	        menuItems.push({
	          text: productTypeSelectorTypes[type],
	          onclick: function onclick(e) {
	            var slider = BX.SidePanel.Instance.getTopSlider();

	            if (slider) {
	              slider.url = BX.Uri.addParam(slider.getUrl(), {
	                productTypeId: type
	              });
	              slider.requestMethod = 'post';
	              slider.setFrameSrc();
	            }
	          }
	        });
	      });
	      var popupMenu = main_popup.MenuManager.create({
	        id: 'productcard-product-type-selector',
	        bindElement: productTypeSelector,
	        items: menuItems,
	        minWidth: productTypeSelector.offsetWidth
	      });
	      productTypeSelector.addEventListener('click', function (e) {
	        e.preventDefault();
	        popupMenu.show();
	      });
	    }
	  }]);
	  return ProductCard;
	}(catalog_entityCard.EntityCard);

	main_core.Reflection.namespace('BX.Catalog').ProductCard = ProductCard;

}((this.window = this.window || {}),BX,BX.Catalog.EntityCard,BX.Event,BX.Main,BX.UI));
//# sourceMappingURL=script.js.map
