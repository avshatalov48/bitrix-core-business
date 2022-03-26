this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_ui_panel_content,landing_ui_button_sidebarbutton,landing_ui_card_iconlistcard,landing_ui_button_basebutton,landing_ui_field_textfield,landing_loc,main_core) {
	'use strict';

	var _templateObject;
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

	    _this.iconList = null;
	    _this.search = main_core.Runtime.debounce(_this.search, 500).bind(babelHelpers.assertThisInitialized(_this)); // todo: add lupa icon after

	    _this.searchField = new landing_ui_field_textfield.TextField({
	      className: 'landing-ui-panel-icon-search',
	      placeholder: 'search...',
	      textOnly: true,
	      onInput: _this.search
	    });
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

	      main_core.Dom.append(this.searchField.getLayout(), this.sidebar);
	      IconPanel.getLibraries().then(function (libraries) {
	        var defaultCategory = null;
	        libraries.forEach(function (_ref) {
	          var id = _ref.id,
	              text = _ref.name,
	              active = _ref.active,
	              categories = _ref.categories;

	          if (active === false) {
	            return;
	          }

	          if (!defaultCategory) {
	            defaultCategory = categories[0].id;
	          }

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
	        }); // todo: init current category and icon?

	        if (defaultCategory) {
	          _this2.onCategoryChange(defaultCategory);
	        }
	      }); // bottom buttons

	      this.appendFooterButton(new landing_ui_button_basebutton.BaseButton("save_icon", {
	        text: landing_loc.Loc.getMessage("LANDING_ICON_PANEL_BUTTON_CHOOSE"),
	        onClick: function onClick() {
	          if (_this2.iconList.getActiveIcon()) {
	            _this2.resolver({
	              iconOptions: _this2.iconList.getActiveOptions(),
	              iconClassName: _this2.iconList.getActiveIcon()
	            });
	          }

	          void _this2.hide();
	        },
	        className: "landing-ui-button-content-save"
	      }));
	      this.appendFooterButton(new landing_ui_button_basebutton.BaseButton("cancel_icon", {
	        text: landing_loc.Loc.getMessage("LANDING_ICON_PANEL_BUTTON_CANCEL"),
	        onClick: this.hide.bind(this),
	        className: "landing-ui-button-content-cancel"
	      }));
	    }
	  }, {
	    key: "fillIconsList",
	    value: function fillIconsList(items, title) {
	      var _this3 = this;

	      this.iconList = new landing_ui_card_iconlistcard.IconListCard();
	      this.iconList.setTitle(title);
	      items.forEach(function (item) {
	        if (main_core.Type.isObject(item)) {
	          var iconOptions = {
	            options: item.options ? item.options : {},
	            defaultOption: item.defaultOption ? item.defaultOption : ''
	          };

	          _this3.iconList.addItem(item.className, iconOptions);
	        } else {
	          _this3.iconList.addItem(item);
	        }
	      });
	      this.appendCard(this.iconList);
	    }
	  }, {
	    key: "onCategoryChange",
	    value: function onCategoryChange(id) {
	      var _this4 = this;

	      this.content.innerHTML = '';

	      if (this.sidebarButtons.getActive()) {
	        this.sidebarButtons.getActive().deactivate();
	      }

	      this.sidebarButtons.get(id).activate();
	      IconPanel.getLibraries().then(function (libraries) {
	        libraries.forEach(function (library) {
	          if (library.active === false) {
	            return;
	          }

	          library.categories.forEach(function (category) {
	            if (id === category.id) {
	              _this4.fillIconsList(category.items, category.name);
	            }
	          });
	        });
	      });
	    }
	  }, {
	    key: "search",
	    value: function search(query) {
	      var _this5 = this;

	      // todo: replaces ',' to space
	      // mega optimization!
	      if (query.trim().length < 2) {
	        return;
	      } // dbg


	      var date = new Date();
	      console.log('search at query "', query, '"was started at', date.getSeconds(), date.getMilliseconds());
	      this.content.innerHTML = '';

	      if (this.sidebarButtons.getActive()) {
	        this.sidebarButtons.getActive().deactivate();
	      } // todo: need loader?


	      IconPanel.getLibraries().then(function (libraries) {
	        var result = []; // todo: can set language_id to collator?

	        var collator = new Intl.Collator(undefined, {
	          usage: 'search',
	          sensitivity: 'base',
	          ignorePunctuation: true
	        });
	        var preparedQuery = query.toLowerCase().trim().split(' ');

	        if (preparedQuery.length === 0) {
	          return;
	        }

	        libraries.forEach(function (library) {
	          if (library.active === false) {
	            return;
	          }

	          library.categories.forEach(function (category) {
	            category.items.forEach(function (item) {
	              if (main_core.Type.isObject(item) && item.keywords && item.keywords !== '') {
	                var isFind = preparedQuery.every(function (queryWord) {
	                  return item.keywords.split(' ').find(function (word) {
	                    return collator.compare(queryWord, word) === 0;
	                  });
	                });

	                if (isFind) {
	                  result.push(item);
	                }
	              }
	            });
	          });
	        }); // print

	        var title = 'Search result "' + query.trim() + '"';

	        if (result.length > 0) {
	          _this5.fillIconsList(result, title);
	        } else {
	          _this5.iconList = new landing_ui_card_iconlistcard.IconListCard();

	          _this5.iconList.setTitle(title);

	          main_core.Dom.append(_this5.getNotFoundMessage(), _this5.iconList.getBody());

	          _this5.appendCard(_this5.iconList);
	        } // dbg


	        var dateEnd = new Date();
	        console.log('search at query"', query, '"was end at____', dateEnd.getSeconds(), dateEnd.getMilliseconds());
	      });
	    }
	  }, {
	    key: "getNotFoundMessage",
	    value: function getNotFoundMessage() {
	      // todo: remove unnecessary phrases for diff langs
	      return IconPanel.cache.remember('notFoundMsg', function () {
	        var textMsgId, imageClass;
	        var lang = landing_loc.Loc.getMessage('LANGUAGE_ID');

	        if (lang === IconPanel.DEFAULT_LANG) {
	          textMsgId = 'LANDING_ICON_PANEL_NOT_FOUND_EN';
	          imageClass = '--en';
	        } else if (IconPanel.SUPPORTED_LANG.indexOf(landing_loc.Loc.getMessage('LANGUAGE_ID')) !== -1) {
	          // todo: correct phrases
	          textMsgId = 'LANDING_ICON_PANEL_NOT_FOUND_EN';
	          imageClass = '--not_found';
	        } else {
	          textMsgId = 'LANDING_ICON_PANEL_NOT_FOUND_OTHER';
	          imageClass = '--incorrect_lang';
	        }

	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-panel-icon-not-found\">\n\t\t\t\t<div class=\"landing-ui-panel-icon-not-found-image ", "\"></div>\n\t\t\t\t<div class=\"landing-ui-panel-icon-not-found-title\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>"])), imageClass, landing_loc.Loc.getMessage(textMsgId));
	      });
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      var _this6 = this;

	      return new Promise(function (resolve) {
	        _this6.resolver = resolve;

	        _this6.makeLayout();

	        void babelHelpers.get(babelHelpers.getPrototypeOf(IconPanel.prototype), "show", _this6).call(_this6);
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
	        return main_core.Runtime.loadExtension(['landing.icon.fontawesome', 'landing.icon.fontawesome6_brands', 'landing.icon.fontawesome6_1', 'landing.icon.fontawesome6_2', 'landing.icon.fontawesome6_3', 'landing.icon.etlineicons', 'landing.icon.hsicons', 'landing.icon.simpleline', 'landing.icon.simplelinepro1', 'landing.icon.simplelinepro2']).then(Object.values);
	      });
	    }
	  }]);
	  return IconPanel;
	}(landing_ui_panel_content.Content);
	babelHelpers.defineProperty(IconPanel, "SUPPORTED_LANG", ['en', 'ru']);
	babelHelpers.defineProperty(IconPanel, "DEFAULT_LANG", 'en');
	babelHelpers.defineProperty(IconPanel, "cache", new main_core.Cache.MemoryCache());

	exports.IconPanel = IconPanel;

}((this.BX.Landing.UI.Panel = this.BX.Landing.UI.Panel || {}),BX.Landing.UI.Panel,BX.Landing.UI.Button,BX.Landing.UI.Card,BX.Landing.UI.Button,BX.Landing.UI.Field,BX.Landing,BX));
//# sourceMappingURL=iconpanel.bundle.js.map
