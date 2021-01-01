this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_collection_basecollection) {
	'use strict';

	/**
	 * @memberOf BX.Landing.UI.Collection
	 */

	var FormCollection = /*#__PURE__*/function (_BaseCollection) {
	  babelHelpers.inherits(FormCollection, _BaseCollection);

	  function FormCollection() {
	    babelHelpers.classCallCheck(this, FormCollection);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FormCollection).apply(this, arguments));
	  }

	  babelHelpers.createClass(FormCollection, [{
	    key: "fetchFields",
	    value: function fetchFields() {
	      var collection = new landing_collection_basecollection.BaseCollection();
	      this.forEach(function (form) {
	        collection.push.apply(collection, babelHelpers.toConsumableArray(form.fields));
	      });
	      return collection;
	    }
	  }, {
	    key: "fetchChanges",
	    value: function fetchChanges() {
	      return this.filter(function (form) {
	        return form.isChanged();
	      });
	    }
	  }]);
	  return FormCollection;
	}(landing_collection_basecollection.BaseCollection);

	exports.FormCollection = FormCollection;

}((this.BX.Landing.UI.Collection = this.BX.Landing.UI.Collection || {}),BX.Landing.Collection));
//# sourceMappingURL=formcollection.bundle.js.map
