(function (exports,main_core,catalog_entityCard,main_core_events,main_popup,ui_buttons) {
	'use strict';

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _isQuantityTraceNoticeShown = /*#__PURE__*/new WeakMap();

	var VariationCard = /*#__PURE__*/function (_EntityCard) {
	  babelHelpers.inherits(VariationCard, _EntityCard);

	  function VariationCard(id) {
	    var _this;

	    var settings = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, VariationCard);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(VariationCard).call(this, id, settings));

	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _isQuantityTraceNoticeShown, {
	      writable: true,
	      value: false
	    });

	    main_core_events.EventEmitter.subscribe('BX.Grid.SettingsWindow:save', function () {
	      return _this.postSliderMessage('onUpdate', {});
	    });
	    return _this;
	  }

	  babelHelpers.createClass(VariationCard, [{
	    key: "getEntityType",
	    value: function getEntityType() {
	      return 'Variation';
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
	        eventData.visible = this.isCardSettingEnabled('CATALOG_PARAMETERS');
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
	          content: main_core.Loc.getMessage('CPVD_QUANTITY_TRACE_NOTICE'),
	          overlay: true,
	          titleBar: main_core.Loc.getMessage('CPVD_QUANTITY_TRACE_NOTICE_TITLE'),
	          closeByEsc: true,
	          closeIcon: true,
	          buttons: [new ui_buttons.Button({
	            text: main_core.Loc.getMessage('CPVD_QUANTITY_TRACE_ACCEPT'),
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
	      section === null || section === void 0 ? void 0 : section.getChildren().forEach(function (field) {
	        if (_this2.hiddenFields.includes(field === null || field === void 0 ? void 0 : field.getId())) {
	          field.setVisible(false);
	        }
	      });
	    }
	  }]);
	  return VariationCard;
	}(catalog_entityCard.EntityCard);

	main_core.Reflection.namespace('BX.Catalog').VariationCard = VariationCard;

}((this.window = this.window || {}),BX,BX.Catalog.EntityCard,BX.Event,BX.Main,BX.UI));
//# sourceMappingURL=script.js.map
