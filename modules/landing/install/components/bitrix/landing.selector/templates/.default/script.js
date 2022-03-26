this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,ui_entitySelector,main_core) {
	'use strict';

	function _templateObject6() {
	  var data = babelHelpers.taggedTemplateLiteral([""]);

	  _templateObject6 = function _templateObject6() {
	    return data;
	  };

	  return data;
	}

	function _templateObject5() {
	  var data = babelHelpers.taggedTemplateLiteral(["<a href=\"", "\" class=\"ui-selector-footer-link\">", "</a>"]);

	  _templateObject5 = function _templateObject5() {
	    return data;
	  };

	  return data;
	}

	function _templateObject4() {
	  var data = babelHelpers.taggedTemplateLiteral([""]);

	  _templateObject4 = function _templateObject4() {
	    return data;
	  };

	  return data;
	}

	function _templateObject3() {
	  var data = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-selector-footer-conjunction\">", "</span>"]);

	  _templateObject3 = function _templateObject3() {
	    return data;
	  };

	  return data;
	}

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral([""]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["<a href=\"", "\" class=\"ui-selector-footer-link ui-selector-footer-link-add\">", "</a>"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _dialog = new WeakMap();

	var _node = new WeakMap();

	var _input = new WeakMap();

	var _urlLandingAdd = new WeakMap();

	var _urlFolderAdd = new WeakMap();

	var _siteType = new WeakMap();

	var _siteId = new WeakMap();

	var _items = new WeakMap();

	var _onSelect = new WeakMap();

	var _selectorContainer = new WeakMap();

	var _overlayElement = new WeakMap();

	var _overlayShown = new WeakMap();

	var _getDialog = new WeakSet();

	var _handleSearchClick = new WeakSet();

	var _showSelector = new WeakSet();

	var _hideSelector = new WeakSet();

	var _showOverlay = new WeakSet();

	var _onSearch = new WeakSet();

	var Selector = /*#__PURE__*/function () {
	  function Selector(options) {
	    babelHelpers.classCallCheck(this, Selector);

	    _onSearch.add(this);

	    _showOverlay.add(this);

	    _hideSelector.add(this);

	    _showSelector.add(this);

	    _handleSearchClick.add(this);

	    _getDialog.add(this);

	    _dialog.set(this, {
	      writable: true,
	      value: null
	    });

	    _node.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _input.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _urlLandingAdd.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _urlFolderAdd.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _siteType.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _siteId.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _items.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _onSelect.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _selectorContainer.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _overlayElement.set(this, {
	      writable: true,
	      value: void 0
	    });

	    _overlayShown.set(this, {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.classPrivateFieldSet(this, _node, options.node);
	    babelHelpers.classPrivateFieldSet(this, _input, options.input);
	    babelHelpers.classPrivateFieldSet(this, _urlLandingAdd, options.urlLandingAdd || null);
	    babelHelpers.classPrivateFieldSet(this, _urlFolderAdd, options.urlFolderAdd || null);
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

	var _getDialog2 = function _getDialog2() {
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
	      footer: [babelHelpers.classPrivateFieldGet(this, _urlLandingAdd) ? main_core.Tag.render(_templateObject(), babelHelpers.classPrivateFieldGet(this, _urlLandingAdd), main_core.Loc.getMessage('LANDING_SELECTOR_ADD_PAGE')) : main_core.Tag.render(_templateObject2()), babelHelpers.classPrivateFieldGet(this, _urlLandingAdd) ? main_core.Tag.render(_templateObject3(), main_core.Loc.getMessage('LANDING_SELECTOR_ADD_OR')) : main_core.Tag.render(_templateObject4()), babelHelpers.classPrivateFieldGet(this, _urlFolderAdd) ? main_core.Tag.render(_templateObject5(), babelHelpers.classPrivateFieldGet(this, _urlFolderAdd), main_core.Loc.getMessage('LANDING_SELECTOR_ADD_FOLDER')) : main_core.Tag.render(_templateObject6())]
	    }));
	  }

	  return babelHelpers.classPrivateFieldGet(this, _dialog);
	};

	var _handleSearchClick2 = function _handleSearchClick2() {
	  _classPrivateMethodGet(this, _showSelector, _showSelector2).call(this);
	};

	var _showSelector2 = function _showSelector2() {
	  _classPrivateMethodGet(this, _getDialog, _getDialog2).call(this).show();

	  _classPrivateMethodGet(this, _showOverlay, _showOverlay2).call(this);

	  BX.addClass(babelHelpers.classPrivateFieldGet(this, _node), 'landing-selector-active');
	};

	var _hideSelector2 = function _hideSelector2() {
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
	};

	var _showOverlay2 = function _showOverlay2() {
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
	};

	var _onSearch2 = function _onSearch2(event) {
	  if (babelHelpers.classPrivateFieldGet(this, _dialog)) {
	    babelHelpers.classPrivateFieldGet(this, _dialog).show();
	    babelHelpers.classPrivateFieldGet(this, _dialog).search(event.srcElement.value);
	  }
	};

	exports.Selector = Selector;

}((this.BX.Landing.Component = this.BX.Landing.Component || {}),BX.UI.EntitySelector,BX));
//# sourceMappingURL=script.js.map
