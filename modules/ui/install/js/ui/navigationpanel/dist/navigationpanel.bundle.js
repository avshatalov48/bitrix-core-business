this.BX = this.BX || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3;

	var NavigationItem = /*#__PURE__*/function () {
	  function NavigationItem(options) {
	    babelHelpers.classCallCheck(this, NavigationItem);
	    this.id = options.id;
	    this.title = main_core.Type.isString(options.title) ? options.title : null;
	    this.active = main_core.Type.isBoolean(options.active) ? options.active : false;
	    this.events = options.events ? options.events : null;
	    this.link = options.link ? options.link : null;
	    this.container = null;
	    this.bindEvents();
	  }

	  babelHelpers.createClass(NavigationItem, [{
	    key: "getTitle",
	    value: function getTitle() {
	      if (!this.title) {
	        this.title = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-nav-panel__item-title\">", "</div>\t\n\t\t\t"])), this.title);
	      }

	      return this.title;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.container) {
	        this.container = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-nav-panel__item\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), this.title ? this.getTitle() : '');
	        this.active ? this.activate() : this.inactivate();
	        this.setEvents();
	      }

	      return this.container;
	    }
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      var _this = this;

	      main_core_events.EventEmitter.subscribe('BX.UI.NavigationPanel.Item:active', function (item) {
	        if (item.data !== _this) {
	          _this.inactivate();
	        }
	      });
	    }
	  }, {
	    key: "setEvents",
	    value: function setEvents() {
	      var _this2 = this;

	      if (this.events) {
	        var eventsKeys = Object.keys(this.events);

	        var _loop = function _loop(i) {
	          var eventKey = eventsKeys[i];

	          _this2.getContainer().addEventListener(eventKey, function () {
	            _this2.events[eventKey]();
	          });
	        };

	        for (var i = 0; i < eventsKeys.length; i++) {
	          _loop(i);
	        }
	      }

	      if (this.link) {
	        this.container = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<a class=\"ui-nav-panel__item\">\n\t\t\t\t\t", "\n\t\t\t\t</a>\n\t\t\t"])), this.title ? this.getTitle() : '');
	        var linksKeys = Object.keys(this.link);

	        for (var _i = 0; _i < linksKeys.length; _i++) {
	          var linksKey = linksKeys[_i];
	          this.container.setAttribute(linksKey, this.link[linksKey]);
	        }
	      }
	    }
	  }, {
	    key: "activate",
	    value: function activate() {
	      this.active = true;
	      this.getContainer().classList.add('--active');
	      main_core_events.EventEmitter.emit('BX.UI.NavigationPanel.Item:active', this);
	    }
	  }, {
	    key: "inactivate",
	    value: function inactivate() {
	      this.active = false;
	      this.getContainer().classList.remove('--active');
	      main_core_events.EventEmitter.emit('BX.UI.NavigationPanel.Item:inactive', this);
	    }
	  }]);
	  return NavigationItem;
	}();

	var _templateObject$1;

	var NavigationPanel = /*#__PURE__*/function () {
	  function NavigationPanel(options) {
	    babelHelpers.classCallCheck(this, NavigationPanel);
	    this.target = main_core.Type.isDomNode(options.target) ? options.target : null;
	    this.items = main_core.Type.isArray(options.items) ? options.items : [];
	    this.container = null;
	    this.keys = [];
	  }

	  babelHelpers.createClass(NavigationPanel, [{
	    key: "adjustItem",
	    value: function adjustItem() {
	      var _this = this;

	      this.items = this.items.map(function (item) {
	        _this.keys.push(item.id);

	        return new NavigationItem({
	          id: item.id ? item.id : null,
	          title: item.title ? item.title : null,
	          active: item.active ? item.active : false,
	          events: item.events ? item.events : null,
	          link: item.link ? item.link : null
	        });
	      });
	    }
	  }, {
	    key: "getItemById",
	    value: function getItemById(value) {
	      if (value) {
	        var id = this.keys.indexOf(value);
	        return this.items[id];
	      }
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.container) {
	        this.container = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-nav-panel ui-nav-panel__scope\"></div>\n\t\t\t"])));
	      }

	      return this.container;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _this2 = this;

	      this.items.forEach(function (item) {
	        if (item instanceof NavigationItem) {
	          _this2.getContainer().appendChild(item.getContainer());
	        }
	      });
	      main_core.Dom.clean(this.target);
	      this.target.appendChild(this.getContainer());
	    }
	  }, {
	    key: "init",
	    value: function init() {
	      this.adjustItem();
	      this.render();
	    }
	  }]);
	  return NavigationPanel;
	}();

	exports.NavigationPanel = NavigationPanel;

}((this.BX.UI = this.BX.UI || {}),BX,BX.Event));
//# sourceMappingURL=navigationpanel.bundle.js.map
