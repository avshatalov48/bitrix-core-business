this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_ui_panel_content,landing_ui_button_sidebarbutton,landing_loc,main_core) {
	'use strict';

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t\t\t<div class=\"landing-ui-card landing-ui-card-icon\">\n\t\t\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t\t\t\t<span class=\"", "\" onclick=\"", "\"></span>\n\t\t\t\t\t\t\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	/**
	 * @memberOf BX.Landing.UI.Panel
	 */

	var IconPanel = /*#__PURE__*/function (_Content) {
	  babelHelpers.inherits(IconPanel, _Content);

	  function IconPanel() {
	    var _babelHelpers$getProt;

	    var _this;

	    babelHelpers.classCallCheck(this, IconPanel);

	    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	      args[_key] = arguments[_key];
	    }

	    _this = babelHelpers.possibleConstructorReturn(this, (_babelHelpers$getProt = babelHelpers.getPrototypeOf(IconPanel)).call.apply(_babelHelpers$getProt, [this].concat(args)));

	    _this.setEventNamespace('BX.Landing.UI.Panel.IconPanel');

	    _this.setTitle(landing_loc.Loc.getMessage('LANDING_ICONS_SLIDER_TITLE'));

	    main_core.Dom.addClass(_this.layout, 'landing-ui-panel-icon');
	    main_core.Dom.addClass(_this.overlay, 'landing-ui-panel-icon');
	    main_core.Dom.attr(_this.layout, 'hidden', true);
	    main_core.Dom.attr(_this.overlay, 'hidden', true);

	    _this.resolver = function () {};

	    main_core.Dom.append(_this.layout, document.body);
	    return _this;
	  }

	  babelHelpers.createClass(IconPanel, [{
	    key: "makeLayout",
	    value: function makeLayout() {
	      var _this2 = this;

	      if (main_core.Type.isStringFilled(this.content.innerHTML)) {
	        return;
	      }

	      IconPanel.getLibraries().then(function (libraries) {
	        libraries.forEach(function (_ref) {
	          var id = _ref.id,
	              text = _ref.name,
	              categories = _ref.categories;

	          _this2.appendSidebarButton(new landing_ui_button_sidebarbutton.SidebarButton({
	            id: id,
	            text: text
	          }));

	          categories.forEach(function (category) {
	            _this2.appendSidebarButton(new landing_ui_button_sidebarbutton.SidebarButton({
	              id: category.id,
	              text: category.name,
	              onClick: _this2.onCategoryChange.bind(_this2, category.id),
	              child: true
	            }));
	          });
	        });

	        _this2.onCategoryChange(libraries[0].categories[0].id);
	      });
	    }
	  }, {
	    key: "onCategoryChange",
	    value: function onCategoryChange(id) {
	      var _this3 = this;

	      this.content.innerHTML = '';
	      IconPanel.getLibraries().then(function (libraries) {
	        libraries.forEach(function (library) {
	          library.categories.forEach(function (category) {
	            if (id === category.id) {
	              var map = new Map();
	              var categoryCard = new BX.Landing.UI.Card.BaseCard({
	                title: category.name,
	                className: 'landing-ui-card-icons'
	              });
	              category.items.forEach(function (item) {
	                var icon = main_core.Tag.render(_templateObject(), item, _this3.onChange.bind(_this3, item));
	                var iconLayout = main_core.Tag.render(_templateObject2(), icon);
	                main_core.Dom.append(iconLayout, categoryCard.body);
	                var styles = getComputedStyle(icon, ':before');
	                requestAnimationFrame(function () {
	                  var content = styles.getPropertyValue('content');

	                  if (map.has(content)) {
	                    iconLayout.hidden = true;
	                  } else {
	                    map.set(content, true);
	                  }
	                });
	              });

	              _this3.appendCard(categoryCard);
	            }
	          });
	        });
	      });
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(icon) {
	      this.resolver(icon);
	      void this.hide();
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      var _this4 = this;

	      return new Promise(function (resolve) {
	        _this4.resolver = resolve;

	        _this4.makeLayout();

	        void babelHelpers.get(babelHelpers.getPrototypeOf(IconPanel.prototype), "show", _this4).call(_this4);
	      });
	    }
	  }], [{
	    key: "getInstance",
	    value: function getInstance() {
	      if (!IconPanel.instance) {
	        IconPanel.instance = new IconPanel();
	      }

	      return IconPanel.instance;
	    }
	  }, {
	    key: "getLibraries",
	    value: function getLibraries() {
	      return IconPanel.cache.remember('libraries', function () {
	        return main_core.Runtime.loadExtension(['landing.icon.fontawesome', 'landing.icon.etlineicons', 'landing.icon.hsicons', 'landing.icon.simpleline', 'landing.icon.simplelinepro1', 'landing.icon.simplelinepro2']).then(Object.values);
	      });
	    }
	  }]);
	  return IconPanel;
	}(landing_ui_panel_content.Content);
	babelHelpers.defineProperty(IconPanel, "cache", new main_core.Cache.MemoryCache());

	exports.IconPanel = IconPanel;

}((this.BX.Landing.UI.Panel = this.BX.Landing.UI.Panel || {}),BX.Landing.UI.Panel,BX.Landing.UI.Button,BX.Landing,BX));
//# sourceMappingURL=iconpanel.bundle.js.map
