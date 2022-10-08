this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,ui_entitySelector,main_core) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8;

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _dialog = /*#__PURE__*/new WeakMap();

	var _node = /*#__PURE__*/new WeakMap();

	var _input = /*#__PURE__*/new WeakMap();

	var _urlLandingAdd = /*#__PURE__*/new WeakMap();

	var _urlFolderAdd = /*#__PURE__*/new WeakMap();

	var _urlFormAdd = /*#__PURE__*/new WeakMap();

	var _siteType = /*#__PURE__*/new WeakMap();

	var _siteId = /*#__PURE__*/new WeakMap();

	var _items = /*#__PURE__*/new WeakMap();

	var _onSelect = /*#__PURE__*/new WeakMap();

	var _selectorContainer = /*#__PURE__*/new WeakMap();

	var _overlayElement = /*#__PURE__*/new WeakMap();

	var _overlayShown = /*#__PURE__*/new WeakMap();

	var _getDialog = /*#__PURE__*/new WeakSet();

	var _handleSearchClick = /*#__PURE__*/new WeakSet();

	var _showSelector = /*#__PURE__*/new WeakSet();

	var _hideSelector = /*#__PURE__*/new WeakSet();

	var _showOverlay = /*#__PURE__*/new WeakSet();

	var _onSearch = /*#__PURE__*/new WeakSet();

	var Selector = /*#__PURE__*/function () {
	  function Selector(options) {
	    babelHelpers.classCallCheck(this, Selector);

	    _classPrivateMethodInitSpec(this, _onSearch);

	    _classPrivateMethodInitSpec(this, _showOverlay);

	    _classPrivateMethodInitSpec(this, _hideSelector);

	    _classPrivateMethodInitSpec(this, _showSelector);

	    _classPrivateMethodInitSpec(this, _handleSearchClick);

	    _classPrivateMethodInitSpec(this, _getDialog);

	    _classPrivateFieldInitSpec(this, _dialog, {
	      writable: true,
	      value: null
	    });

	    _classPrivateFieldInitSpec(this, _node, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _input, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _urlLandingAdd, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _urlFolderAdd, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _urlFormAdd, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _siteType, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _siteId, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _items, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _onSelect, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _selectorContainer, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _overlayElement, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _overlayShown, {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.classPrivateFieldSet(this, _node, options.node);
	    babelHelpers.classPrivateFieldSet(this, _input, options.input);
	    babelHelpers.classPrivateFieldSet(this, _urlLandingAdd, options.urlLandingAdd || null);
	    babelHelpers.classPrivateFieldSet(this, _urlFolderAdd, options.urlFolderAdd || null);
	    babelHelpers.classPrivateFieldSet(this, _urlFormAdd, options.urlFormAdd || null);
	    babelHelpers.classPrivateFieldSet(this, _siteType, options.siteType);
	    babelHelpers.classPrivateFieldSet(this, _siteId, options.siteId);
	    this.folderId = options.folderId;
	    this.landingId = options.landingId;
	    babelHelpers.classPrivateFieldSet(this, _items, options.items || []);
	    babelHelpers.classPrivateFieldSet(this, _onSelect, options.onSelect);
	    babelHelpers.classPrivateFieldSet(this, _selectorContainer, BX('landing-selector'));

	    if (babelHelpers.classPrivateFieldGet(this, _node)) {
	      main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _input), 'click', _classPrivateMethodGet(this, _handleSearchClick, _handleSearchClick2).bind(this));
	      main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _input), 'input', _classPrivateMethodGet(this, _onSearch, _onSearch2).bind(this));
	    }
	  }

	  babelHelpers.createClass(Selector, [{
	    key: "onAddPage",
	    value: function onAddPage() {
	      alert(this.landingId);
	    }
	  }, {
	    key: "onAddFolder",
	    value: function onAddFolder() {
	      alert(this.folderId);
	    }
	  }]);
	  return Selector;
	}();

	function _getDialog2() {
	  if (!babelHelpers.classPrivateFieldGet(this, _dialog)) {
	    babelHelpers.classPrivateFieldSet(this, _dialog, new ui_entitySelector.Dialog({
	      targetNode: babelHelpers.classPrivateFieldGet(this, _node),
	      width: 565,
	      height: 300,
	      enableSearch: false,
	      dropdownMode: true,
	      showAvatars: true,
	      compactView: false,
	      dynamicLoad: true,
	      multiple: false,
	      context: 'landing',
	      entities: [{
	        id: 'landing',
	        options: {
	          siteType: babelHelpers.classPrivateFieldGet(this, _siteType),
	          siteId: babelHelpers.classPrivateFieldGet(this, _siteId),
	          landingId: this.landingId
	        }
	      }],
	      items: babelHelpers.classPrivateFieldGet(this, _items),
	      events: {
	        'onHide': _classPrivateMethodGet(this, _hideSelector, _hideSelector2).bind(this),
	        'Item:onSelect': babelHelpers.classPrivateFieldGet(this, _onSelect)
	      },
	      footer: [babelHelpers.classPrivateFieldGet(this, _urlLandingAdd) ? main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<a href=\"", "\" class=\"ui-selector-footer-link ui-selector-footer-link-add\">", "</a>"])), babelHelpers.classPrivateFieldGet(this, _urlLandingAdd), main_core.Loc.getMessage('LANDING_SELECTOR_ADD_PAGE')) : main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral([""]))), babelHelpers.classPrivateFieldGet(this, _urlLandingAdd) ? main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-selector-footer-conjunction\">", "</span>"])), main_core.Loc.getMessage('LANDING_SELECTOR_ADD_OR')) : main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral([""]))), babelHelpers.classPrivateFieldGet(this, _urlFolderAdd) ? main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<a href=\"", "\" class=\"ui-selector-footer-link\">", "</a>"])), babelHelpers.classPrivateFieldGet(this, _urlFolderAdd), main_core.Loc.getMessage('LANDING_SELECTOR_ADD_FOLDER')) : main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral([""]))), babelHelpers.classPrivateFieldGet(this, _urlFormAdd) ? main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["<a href=\"", "\" class=\"ui-selector-footer-link ui-selector-footer-link-add\">", "</a>"])), babelHelpers.classPrivateFieldGet(this, _urlFormAdd), main_core.Loc.getMessage('LANDING_SELECTOR_ADD_FORM')) : main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral([""])))]
	    }));
	  }

	  return babelHelpers.classPrivateFieldGet(this, _dialog);
	}

	function _handleSearchClick2() {
	  _classPrivateMethodGet(this, _showSelector, _showSelector2).call(this);
	}

	function _showSelector2() {
	  _classPrivateMethodGet(this, _getDialog, _getDialog2).call(this).show();

	  _classPrivateMethodGet(this, _showOverlay, _showOverlay2).call(this);

	  BX.addClass(babelHelpers.classPrivateFieldGet(this, _node), 'landing-selector-active');
	}

	function _hideSelector2() {
	  var _this = this;

	  if (babelHelpers.classPrivateFieldGet(this, _overlayShown) === true) {
	    BX.addClass(babelHelpers.classPrivateFieldGet(this, _overlayElement), 'landing-selector-overlay-hiding');
	    setTimeout(function () {
	      BX.removeClass(babelHelpers.classPrivateFieldGet(_this, _overlayElement), 'landing-selector-overlay-hiding');
	      BX.remove(babelHelpers.classPrivateFieldGet(_this, _overlayElement));
	    }, 200);
	    babelHelpers.classPrivateFieldSet(this, _overlayShown, false);
	  }

	  BX.removeClass(babelHelpers.classPrivateFieldGet(this, _node), 'landing-selector-active');
	}

	function _showOverlay2() {
	  if (!babelHelpers.classPrivateFieldGet(this, _overlayElement)) {
	    babelHelpers.classPrivateFieldSet(this, _overlayElement, BX.create('div', {
	      props: {
	        className: 'landing-selector-overlay'
	      }
	    }));
	  }

	  if (babelHelpers.classPrivateFieldGet(this, _overlayShown) !== true) {
	    document.querySelector('.landing-ui-panel.landing-ui-panel-top').appendChild(babelHelpers.classPrivateFieldGet(this, _overlayElement));
	    babelHelpers.classPrivateFieldSet(this, _overlayShown, true);
	  }
	}

	function _onSearch2(event) {
	  if (babelHelpers.classPrivateFieldGet(this, _dialog)) {
	    babelHelpers.classPrivateFieldGet(this, _dialog).show();
	    babelHelpers.classPrivateFieldGet(this, _dialog).search(event.srcElement.value);
	  }
	}

	exports.Selector = Selector;

}((this.BX.Landing.Component = this.BX.Landing.Component || {}),BX.UI.EntitySelector,BX));
//# sourceMappingURL=script.js.map
