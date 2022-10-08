this.BX = this.BX || {};
this.BX.Mail = this.BX.Mail || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3;

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _wrapper = /*#__PURE__*/new WeakMap();

	var _filter = /*#__PURE__*/new WeakMap();

	var _statusBtn = /*#__PURE__*/new WeakMap();

	var _counterBtn = /*#__PURE__*/new WeakMap();

	var _filterApi = /*#__PURE__*/new WeakMap();

	var _readAllBtn = /*#__PURE__*/new WeakMap();

	var _counter = /*#__PURE__*/new WeakMap();

	var _filterTitle = /*#__PURE__*/new WeakMap();

	var FilterToolbar = /*#__PURE__*/function () {
	  function FilterToolbar() {
	    var _this = this;

	    var config = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
	      wrapper: [],
	      filter: []
	    };
	    babelHelpers.classCallCheck(this, FilterToolbar);

	    _classPrivateFieldInitSpec(this, _wrapper, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _filter, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _statusBtn, {
	      writable: true,
	      value: false
	    });

	    _classPrivateFieldInitSpec(this, _counterBtn, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _filterApi, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _readAllBtn, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _counter, {
	      writable: true,
	      value: void 0
	    });

	    _classPrivateFieldInitSpec(this, _filterTitle, {
	      writable: true,
	      value: void 0
	    });

	    main_core_events.EventEmitter.subscribe('BX.Main.Filter:apply', function (event) {
	      var isSeen = babelHelpers.classPrivateFieldGet(_this, _filter).getFilterFieldsValues()['IS_SEEN'];

	      if (isSeen === 'N') {
	        _this.activateBtn();
	      } else {
	        _this.deactivateBtn();
	      }
	    });
	    babelHelpers.classPrivateFieldSet(this, _wrapper, config['wrapper']);
	    babelHelpers.classPrivateFieldSet(this, _filter, config['filter']);
	    babelHelpers.classPrivateFieldSet(this, _filterApi, babelHelpers.classPrivateFieldGet(this, _filter).getApi());
	  }

	  babelHelpers.createClass(FilterToolbar, [{
	    key: "setCount",
	    value: function setCount(num) {
	      num = Number(num);
	      num = isNaN(num) ? 0 : num;

	      if (num !== undefined) {
	        babelHelpers.classPrivateFieldGet(this, _counter).textContent = num;

	        if (num !== 0) {
	          babelHelpers.classPrivateFieldGet(this, _counter).classList.remove('mail-counter-zero');
	        } else {
	          babelHelpers.classPrivateFieldGet(this, _counter).classList.add('mail-counter-zero');
	        }
	      }
	    }
	  }, {
	    key: "activateBtn",
	    value: function activateBtn() {
	      babelHelpers.classPrivateFieldSet(this, _statusBtn, true);
	      babelHelpers.classPrivateFieldGet(this, _counterBtn).classList.add('mail-msg-counter-number-selected');
	    }
	  }, {
	    key: "deactivateBtn",
	    value: function deactivateBtn() {
	      babelHelpers.classPrivateFieldSet(this, _statusBtn, false);
	      babelHelpers.classPrivateFieldGet(this, _counterBtn).classList.remove('mail-msg-counter-number-selected');
	    }
	  }, {
	    key: "onClickFilterButton",
	    value: function onClickFilterButton() {
	      if (!babelHelpers.classPrivateFieldGet(this, _statusBtn)) {
	        this.activateBtn();
	        this.setUnreadFilter();
	      } else {
	        this.deactivateBtn();
	        this.removeUnreadFilter();
	      }
	    }
	  }, {
	    key: "removeUnreadFilter",
	    value: function removeUnreadFilter() {
	      if (!!babelHelpers.classPrivateFieldGet(this, _filter) && babelHelpers.classPrivateFieldGet(this, _filter) instanceof BX.Main.Filter) {
	        babelHelpers.classPrivateFieldGet(this, _filterApi).setFields({
	          'DIR': babelHelpers.classPrivateFieldGet(this, _filter).getFilterFieldsValues()['DIR']
	        });
	        babelHelpers.classPrivateFieldGet(this, _filterApi).apply();
	      }
	    }
	  }, {
	    key: "hideReadAllBtn",
	    value: function hideReadAllBtn() {
	      babelHelpers.classPrivateFieldGet(this, _readAllBtn).classList.add('mail-toolbar-hide-element');
	    }
	  }, {
	    key: "showReadAllBtn",
	    value: function showReadAllBtn() {
	      babelHelpers.classPrivateFieldGet(this, _readAllBtn).classList.remove('mail-toolbar-hide-element');
	    }
	  }, {
	    key: "hideCounter",
	    value: function hideCounter() {
	      babelHelpers.classPrivateFieldGet(this, _counterBtn).classList.add('mail-toolbar-hide-element');
	      babelHelpers.classPrivateFieldGet(this, _filterTitle).classList.add('mail-toolbar-hide-element');
	    }
	  }, {
	    key: "showCounter",
	    value: function showCounter() {
	      babelHelpers.classPrivateFieldGet(this, _counterBtn).classList.remove('mail-toolbar-hide-element');
	      babelHelpers.classPrivateFieldGet(this, _filterTitle).classList.remove('mail-toolbar-hide-element');
	    }
	  }, {
	    key: "setUnreadFilter",
	    value: function setUnreadFilter() {
	      if (!!babelHelpers.classPrivateFieldGet(this, _filter) && babelHelpers.classPrivateFieldGet(this, _filter) instanceof BX.Main.Filter) {
	        babelHelpers.classPrivateFieldGet(this, _filterApi).setFields({
	          'DIR': babelHelpers.classPrivateFieldGet(this, _filter).getFilterFieldsValues()['DIR'],
	          'IS_SEEN': 'N'
	        });
	        babelHelpers.classPrivateFieldGet(this, _filterApi).apply();
	      }
	    }
	  }, {
	    key: "build",
	    value: function build() {
	      var _this2 = this;
	      var mailFilterToolbar = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"mail-filter-toolbar\">\n\t\t\t<div class=\"mail-filter-counter\" data-role=\"mail-filter-counter\">\n\t\t\t\t<div data-role=\"mail-filter-title\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>"])), main_core.Loc.getMessage("MAIL_FILTER_TOOLBAR_TITLE"));
	      var counterBtn = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<span class=\"mail-toolbar-counter\">\n\t\t\t<span class=\"mail-msg-counter-number\" data-role=\"unread-counter-number\"></span>\n\t\t\t<span class=\"mail-msg-counter-text\">", "</span>\n\t\t\t<span class=\"mail-msg-counter-remove\"></span>\n\t\t</span>"])), main_core.Loc.getMessage("MAIL_FILTER_NOT_READ"));
	      var readAllBtn = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<span class=\"mail-toolbar-counter\">\n\t\t\t<span class=\"mail-msg-counter-text\">", "</span>\n\t\t</span>"])), main_core.Loc.getMessage("MAIL_FILTER_READ_ALL"));
	      babelHelpers.classPrivateFieldSet(this, _counter, counterBtn.querySelector('[data-role="unread-counter-number"]'));
	      babelHelpers.classPrivateFieldSet(this, _filterTitle, mailFilterToolbar.querySelector('[data-role="mail-filter-title"]'));
	      babelHelpers.classPrivateFieldSet(this, _readAllBtn, readAllBtn);
	      babelHelpers.classPrivateFieldSet(this, _counterBtn, counterBtn);

	      counterBtn.onclick = function () {
	        _this2.onClickFilterButton();
	      };

	      readAllBtn.onclick = function () {
	        BX.Mail.Client.Message.List['mail-client-list-manager'].onReadClick('all');

	        _this2.removeUnreadFilter();
	      };

	      var mailFilterCounter = mailFilterToolbar.querySelector('[data-role="mail-filter-counter"]');
	      mailFilterCounter.append(counterBtn);
	      mailFilterCounter.append(readAllBtn);
	      babelHelpers.classPrivateFieldGet(this, _wrapper).append(mailFilterToolbar);
	      main_core_events.EventEmitter.subscribe('BX.Mail.Home:updatingCounters', function (event) {
	        if (event['data']['name'] === 'dirs') {
	          var counters = event['data']['counters'];
	          var hidden = event['data']['hidden'];
	          var currentDir = event['data']['selectedDirectory'];
	          var currentFolderCount = counters[currentDir];

	          if (currentDir !== '') {
	            this.showReadAllBtn();
	          } else {
	            currentFolderCount = event['data']['total'];
	            this.hideReadAllBtn();
	          }

	          if (hidden[currentDir] && currentDir !== '') {
	            this.hideCounter();
	          } else {
	            this.setCount(currentFolderCount);
	            this.showCounter();
	          }
	        }
	      }.bind(this));
	    }
	  }]);
	  return FilterToolbar;
	}();

	exports.FilterToolbar = FilterToolbar;

}((this.BX.Mail.Client = this.BX.Mail.Client || {}),BX,BX.Event));
//# sourceMappingURL=filtertoolbar.bundle.js.map
