(function (exports,main_core,main_popup,catalog_entityCard,main_core_events) {
	'use strict';

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<label class=\"ui-ctl-block ui-entity-editor-popup-create-field-item ui-ctl-w100\">\n\t\t\t\t\t<div class=\"ui-ctl-w10\" style=\"text-align: center\">", "</div>\n\t\t\t\t\t<div class=\"ui-ctl-w75\">\n\t\t\t\t\t\t<span class=\"ui-entity-editor-popup-create-field-item-title\">", "</span>\n\t\t\t\t\t\t<span class=\"ui-entity-editor-popup-create-field-item-desc\">", "</span>\t\n\t\t\t\t\t</div>\t\t\t\t\t\n\t\t\t\t</label>\n\t\t\t"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<input type=\"checkbox\">\n\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class='ui-entity-editor-popup-create-field-list'></div>\n\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	var ProductCard = /*#__PURE__*/function (_EntityCard) {
	  babelHelpers.inherits(ProductCard, _EntityCard);

	  function ProductCard(id) {
	    var _this;

	    var settings = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, ProductCard);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ProductCard).call(this, id, settings));
	    _this.variationGridId = settings.variationGridId;
	    _this.settingsButtonId = settings.settingsButtonId;

	    _this.bindCardSettingsButton();

	    main_core_events.EventEmitter.subscribe('SidePanel.Slider:onMessage', _this.onSliderMessage.bind(babelHelpers.assertThisInitialized(_this)));
	    main_core_events.EventEmitter.subscribe('Grid::updated', _this.onGridUpdatedHandler.bind(babelHelpers.assertThisInitialized(_this)));
	    main_core_events.EventEmitter.subscribe('BX.UI.EntityEditorSection:onLayout', _this.onSectionLayout.bind(babelHelpers.assertThisInitialized(_this)));
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
	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 2),
	          eventData = _event$getCompatData2[1];

	      if (eventData.id === 'catalog_parameters') {
	        eventData.visible = this.isSimpleProduct && this.isCardSettingEnabled('CATALOG_PARAMETERS');
	      }
	    }
	  }, {
	    key: "onEditorAjaxSubmit",
	    value: function onEditorAjaxSubmit(event) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(ProductCard.prototype), "onEditorAjaxSubmit", this).call(this, event);

	      var _event$getCompatData3 = event.getCompatData(),
	          _event$getCompatData4 = babelHelpers.slicedToArray(_event$getCompatData3, 2),
	          response = _event$getCompatData4[1];

	      if (response.data) {
	        if (response.data.NOTIFY_ABOUT_NEW_VARIATION) {
	          this.showNotification(main_core.Loc.getMessage('CPD_NEW_VARIATION_ADDED'));
	        }
	      }
	    }
	  }, {
	    key: "onGridUpdatedHandler",
	    value: function onGridUpdatedHandler(event) {
	      var _event$getCompatData5 = event.getCompatData(),
	          _event$getCompatData6 = babelHelpers.slicedToArray(_event$getCompatData5, 1),
	          grid = _event$getCompatData6[0];

	      if (grid && grid.getId() === this.getVariationGridId()) {
	        this.updateSettingsCheckboxState();

	        if (grid.getRows().getCountDisplayed() <= 0) {
	          document.location.reload();
	        }
	      }
	    }
	  }, {
	    key: "updateSettingsCheckboxState",
	    value: function updateSettingsCheckboxState() {
	      var _this2 = this;

	      var popupContainer = this.getCardSettingsPopup().getContentContainer();
	      this.cardSettings.filter(function (item) {
	        return item.action === 'grid' && main_core.Type.isArray(item.columns);
	      }).forEach(function (item) {
	        var allColumnsExist = true;
	        item.columns.forEach(function (columnName) {
	          if (!_this2.getVariationGrid().getColumnHeaderCellByName(columnName)) {
	            allColumnsExist = false;
	          }
	        });
	        var checkbox = popupContainer.querySelector('input[data-setting-id="' + item.id + '"]');

	        if (main_core.Type.isDomNode(checkbox)) {
	          checkbox.checked = allColumnsExist;
	        }
	      });
	    }
	  }, {
	    key: "getSettingsButton",
	    value: function getSettingsButton() {
	      return BX.UI.ButtonManager.getByUniqid(this.settingsButtonId);
	    }
	  }, {
	    key: "bindCardSettingsButton",
	    value: function bindCardSettingsButton() {
	      var settingsButton = this.getSettingsButton();

	      if (settingsButton) {
	        main_core.Event.bind(settingsButton.getContainer(), 'click', this.showCardSettingsPopup.bind(this));
	      }
	    }
	  }, {
	    key: "getCardSettingsPopup",
	    value: function getCardSettingsPopup() {
	      if (!this.settingsPopup) {
	        this.settingsPopup = new main_popup.Popup(this._id, this.getSettingsButton().getContainer(), {
	          autoHide: true,
	          draggable: false,
	          offsetLeft: 0,
	          offsetTop: 0,
	          angle: {
	            position: 'top',
	            offset: 43
	          },
	          noAllPaddings: true,
	          bindOptions: {
	            forceBindPosition: true
	          },
	          closeByEsc: true,
	          content: this.prepareCardSettingsContent()
	        });
	      }

	      return this.settingsPopup;
	    }
	  }, {
	    key: "showCardSettingsPopup",
	    value: function showCardSettingsPopup() {
	      var _this3 = this;

	      var okCallback = function okCallback() {
	        return _this3.getCardSettingsPopup().show();
	      };

	      var variationGridInstance = main_core.Reflection.getClass('BX.Catalog.VariationGrid.Instance');

	      if (variationGridInstance) {
	        variationGridInstance.askToLossGridData(okCallback);
	      } else {
	        okCallback();
	      }
	    }
	  }, {
	    key: "prepareCardSettingsContent",
	    value: function prepareCardSettingsContent() {
	      var _this4 = this;

	      var content = main_core.Tag.render(_templateObject());
	      this.cardSettings.map(function (item) {
	        content.append(_this4.getSettingItem(item));
	      });
	      return content;
	    }
	  }, {
	    key: "getSettingItem",
	    value: function getSettingItem(item) {
	      var input = main_core.Tag.render(_templateObject2());
	      input.checked = item.checked;
	      input.dataset.settingId = item.id;
	      var setting = main_core.Tag.render(_templateObject3(), input, item.title, item.desc);
	      main_core.Event.bind(setting, 'change', this.setProductCardSetting.bind(this));
	      return setting;
	    }
	  }, {
	    key: "setProductCardSetting",
	    value: function setProductCardSetting(event) {
	      var settingItem = this.getCardSetting(event.target.dataset.settingId);

	      if (!settingItem) {
	        return;
	      }

	      var settingEnabled = event.target.checked;

	      if (settingItem.action === 'grid') {
	        this.requestGridSettings(settingItem, settingEnabled);
	      } else {
	        this.requestCardSettings(settingItem, settingEnabled);
	      }
	    }
	  }, {
	    key: "requestGridSettings",
	    value: function requestGridSettings(setting, enabled) {
	      var _this5 = this;

	      if (!this.getVariationGrid()) ;

	      var headers = [];
	      var cells = this.getVariationGrid().getRows().getHeadFirstChild().getCells();
	      Array.from(cells).forEach(function (header) {
	        if ('name' in header.dataset) {
	          headers.push(header.dataset.name);
	        }
	      });
	      BX.ajax.runComponentAction(this.componentName, 'setGridSetting', {
	        mode: 'class',
	        data: {
	          signedParameters: this.componentSignedParams,
	          settingId: setting.id,
	          selected: enabled,
	          currentHeaders: headers
	        }
	      }).then(function () {
	        setting.checked = enabled;

	        _this5.reloadVariationGrid();

	        _this5.getCardSettingsPopup().close();

	        var message = enabled ? main_core.Loc.getMessage('CPD_SETTING_ENABLED') : main_core.Loc.getMessage('CPD_SETTING_DISABLED');

	        _this5.showNotification(message.replace('#NAME#', setting.title), {
	          category: 'popup-settings'
	        });
	      });
	    }
	  }, {
	    key: "requestCardSettings",
	    value: function requestCardSettings(setting, enabled) {
	      var _this6 = this;

	      BX.ajax.runComponentAction(this.componentName, 'setCardSetting', {
	        mode: 'class',
	        data: {
	          signedParameters: this.componentSignedParams,
	          settingId: setting.id,
	          selected: enabled
	        }
	      }).then(function () {
	        setting.checked = enabled;

	        if (setting.id === 'CATALOG_PARAMETERS') {
	          var section = _this6.getEditorInstance().getControlByIdRecursive('catalog_parameters');

	          if (section) {
	            section.refreshLayout();
	          }
	        }

	        _this6.getCardSettingsPopup().close();

	        var message = enabled ? main_core.Loc.getMessage('CPD_SETTING_ENABLED') : main_core.Loc.getMessage('CPD_SETTING_DISABLED');

	        _this6.showNotification(message.replace('#NAME#', setting.title), {
	          category: 'popup-settings'
	        });
	      });
	    }
	  }, {
	    key: "onSliderMessage",
	    value: function onSliderMessage(event) {
	      var _event$getCompatData7 = event.getCompatData(),
	          _event$getCompatData8 = babelHelpers.slicedToArray(_event$getCompatData7, 1),
	          sliderEvent = _event$getCompatData8[0];

	      if (sliderEvent.getEventId() === 'Catalog.VariationCard::onCreate' || sliderEvent.getEventId() === 'Catalog.VariationCard::onUpdate') {
	        this.reloadVariationGrid();
	      }
	    }
	    /**
	     * @returns {BX.Catalog.VariationGrid|null}
	     */

	  }, {
	    key: "getVariationGridComponent",
	    value: function getVariationGridComponent() {
	      return main_core.Reflection.getClass('BX.Catalog.VariationGrid.Instance');
	    }
	  }, {
	    key: "reloadVariationGrid",
	    value: function reloadVariationGrid() {
	      var gridComponent = this.getVariationGridComponent();

	      if (gridComponent) {
	        gridComponent.reloadGrid();
	      }
	    }
	  }, {
	    key: "getVariationGridId",
	    value: function getVariationGridId() {
	      return this.variationGridId;
	    }
	  }, {
	    key: "getVariationGrid",
	    value: function getVariationGrid() {
	      if (!main_core.Reflection.getClass('BX.Main.gridManager.getInstanceById')) {
	        return null;
	      }

	      return BX.Main.gridManager.getInstanceById(this.getVariationGridId());
	    }
	  }]);
	  return ProductCard;
	}(catalog_entityCard.EntityCard);

	main_core.Reflection.namespace('BX.Catalog').ProductCard = ProductCard;

}((this.window = this.window || {}),BX,BX.Main,BX.Catalog.EntityCard,BX.Event));
//# sourceMappingURL=script.js.map
