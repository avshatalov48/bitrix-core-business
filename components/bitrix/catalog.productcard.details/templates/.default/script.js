(function (exports,main_core,catalog_entityCard,main_core_events) {
	'use strict';

	var ProductCard = /*#__PURE__*/function (_EntityCard) {
	  babelHelpers.inherits(ProductCard, _EntityCard);

	  function ProductCard(id) {
	    var settings = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, ProductCard);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ProductCard).call(this, id, settings));
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
	  }]);
	  return ProductCard;
	}(catalog_entityCard.EntityCard);

	main_core.Reflection.namespace('BX.Catalog').ProductCard = ProductCard;

}((this.window = this.window || {}),BX,BX.Catalog.EntityCard,BX.Event));
//# sourceMappingURL=script.js.map
