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
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "dictionary", null);

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
	      placeholder: landing_loc.Loc.getMessage('LANDING_ICON_PANEL_INPUT_PLACEHOLDER'),
	      textOnly: true,
	      onInput: _this.search
	    });
	    main_core.Dom.append(_this.layout, window.parent.document.body);

	    _this.initDictionary();

	    return _this;
	  }

	  babelHelpers.createClass(IconPanel, [{
	    key: "initDictionary",

	    /**
	     * Requests current lang dictionary from backend.
	     */
	    value: function initDictionary() {
	      var _this2 = this;

	      if (this.dictionary === null) {
	        this.dictionary = {};
	        var lang = landing_loc.Loc.getMessage('LANGUAGE_ID'); // available for 'ru' and 'de' languages only

	        if (lang !== 'ru' && lang !== 'de') {
	          return;
	        }

	        BX.ajax.loadJSON('/bitrix/js/landing/dicdata/' + lang + '.json', function (dictionary) {
	          babelHelpers.toConsumableArray(Object.keys(dictionary)).map(function (key) {
	            dictionary[key].toLowerCase().split(' ').map(function (word) {
	              if (word) {
	                _this2.dictionary[word] = key;
	              }
	            });
	          });
	        });
	      }
	    }
	    /**
	     * Returns translated word from loaded dictionary.
	     *
	     * @param {string} word Word to translate.
	     * @returns {null|string}
	     */

	  }, {
	    key: "translateWord",
	    value: function translateWord(word) {
	      if (this !== null && this !== void 0 && this.dictionary[word]) {
	        return this.dictionary[word];
	      }

	      return null;
	    }
	  }, {
	    key: "selectDefaultCategory",
	    value: function selectDefaultCategory() {
	      // todo: init current category and icon?
	      if (this.defaultCategory) {
	        this.onCategoryChange(this.defaultCategory);
	      }
	    }
	  }, {
	    key: "makeLayout",
	    value: function makeLayout() {
	      var _this3 = this;

	      if (main_core.Type.isStringFilled(this.content.innerHTML)) {
	        return;
	      }

	      main_core.Dom.append(this.searchField.getLayout(), this.sidebar);
	      IconPanel.getLibraries().then(function (libraries) {
	        libraries.forEach(function (_ref) {
	          var id = _ref.id,
	              text = _ref.name,
	              active = _ref.active,
	              categories = _ref.categories;

	          if (active === false) {
	            return;
	          }

	          if (!_this3.defaultCategory) {
	            _this3.defaultCategory = categories[0].id;
	          }

	          _this3.appendSidebarButton(new landing_ui_button_sidebarbutton.SidebarButton({
	            id: id,
	            text: text
	          }));

	          categories.forEach(function (category) {
	            _this3.appendSidebarButton(new landing_ui_button_sidebarbutton.SidebarButton({
	              id: category.id,
	              text: category.name,
	              onClick: _this3.onCategoryChange.bind(_this3, category.id),
	              child: true
	            }));
	          });
	        });

	        _this3.selectDefaultCategory();
	      }); // bottom buttons

	      this.appendFooterButton(new landing_ui_button_basebutton.BaseButton("save_icon", {
	        text: landing_loc.Loc.getMessage("LANDING_ICON_PANEL_BUTTON_CHOOSE"),
	        onClick: function onClick() {
	          if (_this3.iconList.getActiveIcon()) {
	            _this3.resolver({
	              iconOptions: _this3.iconList.getActiveOptions(),
	              iconClassName: _this3.iconList.getActiveIcon()
	            });
	          }

	          void _this3.hide();
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
	      var _this4 = this;

	      this.iconList = new landing_ui_card_iconlistcard.IconListCard();
	      this.iconList.setTitle(title);
	      items.forEach(function (item) {
	        if (main_core.Type.isObject(item)) {
	          var iconOptions = {
	            options: item.options ? item.options : {},
	            defaultOption: item.defaultOption ? item.defaultOption : ''
	          };

	          _this4.iconList.addItem(item.className, iconOptions);
	        } else {
	          _this4.iconList.addItem(item);
	        }
	      });
	      this.appendCard(this.iconList);
	    }
	  }, {
	    key: "onCategoryChange",
	    value: function onCategoryChange(id) {
	      var _this5 = this;

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
	              _this5.fillIconsList(category.items, category.name);
	            }
	          });
	        });
	      });
	    }
	  }, {
	    key: "search",
	    value: function search(query) {
	      var _this6 = this;

	      // todo: replaces ',' to space
	      // mega optimization!
	      if (query.trim().length < 2) {
	        this.selectDefaultCategory();
	        return;
	      }

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
	                var isFind = preparedQuery.some(function (queryWord) {
	                  var queryWordTranslated = _this6.translateWord(queryWord);

	                  return item.keywords.split(' ').find(function (word) {
	                    return collator.compare(queryWord, word) === 0 || collator.compare(queryWordTranslated, word) === 0;
	                  });
	                });

	                if (isFind) {
	                  result.push(item);
	                }
	              }
	            });
	          });
	        }); // print

	        var title = landing_loc.Loc.getMessage('LANDING_ICON_PANEL_TITLE_RESULT').replace('@query@', query.trim());

	        if (result.length > 0) {
	          _this6.fillIconsList(result, title);
	        } else {
	          var notFoundMessage;
	          _this6.iconList = new landing_ui_card_iconlistcard.IconListCard();

	          _this6.iconList.setTitle(title);

	          if (!/^[a-zA-Z0-9]+$/.test(query)) {
	            // another screen for trying English
	            notFoundMessage = _this6.getNotFoundMessage();
	          } else {
	            notFoundMessage = _this6.getNotFoundMessage();
	          }

	          main_core.Dom.append(notFoundMessage, _this6.iconList.getBody());

	          _this6.appendCard(_this6.iconList);
	        } // dbg
	        //const dateEnd = new Date();
	        //console.log('search at query"', query, '"was end at____', dateEnd.getSeconds(), dateEnd.getMilliseconds());

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
	          textMsgId = 'LANDING_ICON_PANEL_NOT_FOUND_DEFAULT';
	          imageClass = '--not_found';
	        } else if (IconPanel.SUPPORTED_LANG.indexOf(lang) !== -1) {
	          // todo: correct phrases
	          textMsgId = 'LANDING_ICON_PANEL_NOT_FOUND_SUPPORTED';
	          imageClass = '--not_found';
	        } else {
	          textMsgId = 'LANDING_ICON_PANEL_NOT_FOUND_INCORRECT';
	          imageClass = '--incorrect_lang';
	        }

	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-panel-icon-not-found\">\n\t\t\t\t<div class=\"landing-ui-panel-icon-not-found-image ", "\"></div>\n\t\t\t\t<div class=\"landing-ui-panel-icon-not-found-title\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>"])), imageClass, landing_loc.Loc.getMessage(textMsgId));
	      });
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      var _this7 = this;

	      return new Promise(function (resolve) {
	        _this7.resolver = resolve;

	        _this7.makeLayout();

	        void babelHelpers.get(babelHelpers.getPrototypeOf(IconPanel.prototype), "show", _this7).call(_this7);
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
	babelHelpers.defineProperty(IconPanel, "SUPPORTED_LANG", ['en', 'ru', 'de']);
	babelHelpers.defineProperty(IconPanel, "DEFAULT_LANG", 'en');
	babelHelpers.defineProperty(IconPanel, "cache", new main_core.Cache.MemoryCache());

	exports.IconPanel = IconPanel;

}((this.BX.Landing.UI.Panel = this.BX.Landing.UI.Panel || {}),BX.Landing.UI.Panel,BX.Landing.UI.Button,BX.Landing.UI.Card,BX.Landing.UI.Button,BX.Landing.UI.Field,BX.Landing,BX));
//# sourceMappingURL=iconpanel.bundle.js.map
