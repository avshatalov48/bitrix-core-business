(function (exports,main_core,catalog_entityCard,main_core_events) {
	'use strict';

	var VariationCard =
	/*#__PURE__*/
	function (_EntityCard) {
	  babelHelpers.inherits(VariationCard, _EntityCard);

	  function VariationCard(id) {
	    var _this;

	    var settings = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    babelHelpers.classCallCheck(this, VariationCard);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(VariationCard).call(this, id, settings));
	    main_core_events.EventEmitter.subscribe('BX.UI.EntityEditorSection:onLayout', _this.onSectionLayout.bind(babelHelpers.assertThisInitialized(_this)));
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
	      var _event$getCompatData = event.getCompatData(),
	          _event$getCompatData2 = babelHelpers.slicedToArray(_event$getCompatData, 2),
	          eventData = _event$getCompatData2[1];
	      /*if (eventData.id === 'catalog_parameters')
	      {
	      	eventData.visible = this.isCardSettingEnabled('CATALOG_PARAMETERS');
	      }*/

	    }
	  }]);
	  return VariationCard;
	}(catalog_entityCard.EntityCard);

	main_core.Reflection.namespace('BX.Catalog').VariationCard = VariationCard;

}((this.window = this.window || {}),BX,BX.Catalog.EntityCard,BX.Event));
//# sourceMappingURL=script.js.map
