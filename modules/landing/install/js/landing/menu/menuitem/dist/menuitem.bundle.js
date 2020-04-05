this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports, main_core, landing_ui_form_menuitemform) {
	'use strict';

	/**
	 * @memberOf BX.Landing.Menu
	 */

	var MenuItem =
	/*#__PURE__*/
	function (_Event$EventEmitter) {
	  babelHelpers.inherits(MenuItem, _Event$EventEmitter);

	  function MenuItem() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, MenuItem);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(MenuItem).call(this, options));

	    _this.setEventNamespace('BX.Landing.Menu.MenuItem');

	    _this.layout = options.layout;
	    _this.children = options.children;
	    _this.selector = options.selector;
	    _this.depth = main_core.Text.toNumber(options.depth);
	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.nodes = options.nodes;
	    return _this;
	  }

	  babelHelpers.createClass(MenuItem, [{
	    key: "getForm",
	    value: function getForm() {
	      return new landing_ui_form_menuitemform.MenuItemForm({
	        selector: this.selector,
	        depth: this.depth,
	        fields: this.nodes.map(function (node) {
	          return node.getField();
	        })
	      });
	    }
	  }]);
	  return MenuItem;
	}(main_core.Event.EventEmitter);

	exports.MenuItem = MenuItem;

}(this.BX.Landing.Menu = this.BX.Landing.Menu || {}, BX, BX.Landing.UI.Form));
//# sourceMappingURL=menuitem.bundle.js.map
