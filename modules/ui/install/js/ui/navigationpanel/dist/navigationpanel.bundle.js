/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3;
	let NavigationItem = /*#__PURE__*/function () {
	  function NavigationItem({
	    id,
	    title,
	    active,
	    events,
	    link,
	    locked
	  }) {
	    babelHelpers.classCallCheck(this, NavigationItem);
	    this.id = id ? id : null;
	    this.title = main_core.Type.isString(title) ? title : null;
	    this.active = main_core.Type.isBoolean(active) ? active : false;
	    this.events = events ? events : null;
	    this.link = link ? link : null;
	    this.locked = main_core.Type.isBoolean(locked) ? locked : false;
	    this.linkContainer = null;
	    this.bindEvents();
	  }
	  babelHelpers.createClass(NavigationItem, [{
	    key: "getTitle",
	    value: function getTitle() {
	      if (!this.title) {
	        this.title = main_core.Tag.render(_t || (_t = _`
				<div class="ui-nav-panel__item-title">${0}</div>	
			`), this.title);
	      }
	      return this.title;
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.linkContainer) {
	        const id = this.id ? `id="ui-nav-panel-item-${this.id}"` : '';
	        this.linkContainer = main_core.Tag.render(_t2 || (_t2 = _`
				<div ${0} class="ui-nav-panel__item">
					${0}
				</div>
			`), id, this.title ? this.getTitle() : '');
	        this.active ? this.activate() : this.inactivate();
	        this.locked ? this.lock() : this.unLock();
	        this.setEvents();
	      }
	      return this.linkContainer;
	    }
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      main_core_events.EventEmitter.subscribe('BX.UI.NavigationPanel.Item:active', item => {
	        if (item.data !== this) {
	          this.inactivate();
	        }
	      });
	    }
	  }, {
	    key: "isLocked",
	    value: function isLocked() {
	      return this.locked;
	    }
	  }, {
	    key: "lock",
	    value: function lock() {
	      this.locked = true;
	      this.getContainer().classList.add('--locked');
	    }
	  }, {
	    key: "unLock",
	    value: function unLock() {
	      this.locked = false;
	      this.getContainer().classList.remove('--locked');
	    }
	  }, {
	    key: "setEvents",
	    value: function setEvents() {
	      if (this.events) {
	        const eventsKeys = Object.keys(this.events);
	        for (let i = 0; i < eventsKeys.length; i++) {
	          let eventKey = eventsKeys[i];
	          this.getContainer().addEventListener(eventKey, () => {
	            this.events[eventKey]();
	          });
	        }
	      }
	      if (this.link) {
	        this.container = main_core.Tag.render(_t3 || (_t3 = _`
				<a class="ui-nav-panel__item">
					${0}
				</a>
			`), this.title ? this.getTitle() : '');
	        const linksKeys = Object.keys(this.link);
	        for (let i = 0; i < linksKeys.length; i++) {
	          const linksKey = linksKeys[i];
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

	let _$1 = t => t,
	  _t$1;
	let NavigationPanel = /*#__PURE__*/function () {
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
	      this.items = this.items.map(item => {
	        this.keys.push(item.id);
	        return new NavigationItem({
	          id: item.id ? item.id : null,
	          title: item.title ? item.title : null,
	          active: item.active ? item.active : false,
	          events: item.events ? item.events : null,
	          link: item.link ? item.link : null,
	          locked: item.locked ? item.locked : false
	        });
	      });
	    }
	  }, {
	    key: "getItemById",
	    value: function getItemById(value) {
	      if (value) {
	        const id = this.keys.indexOf(value);
	        return this.items[id];
	      }
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.container) {
	        this.container = main_core.Tag.render(_t$1 || (_t$1 = _$1`
				<div class="ui-nav-panel ui-nav-panel__scope"></div>
			`));
	      }
	      return this.container;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      this.items.forEach(item => {
	        if (item instanceof NavigationItem) {
	          this.getContainer().appendChild(item.getContainer());
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
