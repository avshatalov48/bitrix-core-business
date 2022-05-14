this.BX = this.BX || {};
this.BX.Socialnetwork = this.BX.Socialnetwork || {};
(function (exports,pull_client,main_core,main_core_events) {
	'use strict';

	var Filter = /*#__PURE__*/function () {
	  function Filter(options) {
	    babelHelpers.classCallCheck(this, Filter);
	    this.filterId = options.filterId;
	    this.filterManager = BX.Main.filterManager.getById(this.filterId);
	    this.bindEvents();
	    setTimeout(this.updateFields.bind(this), 100);
	  }

	  babelHelpers.createClass(Filter, [{
	    key: "bindEvents",
	    value: function bindEvents() {
	      main_core_events.EventEmitter.subscribe('BX.Main.Filter:apply', this.onFilterApply.bind(this));
	    }
	  }, {
	    key: "onFilterApply",
	    value: function onFilterApply() {
	      this.updateFields();
	    }
	  }, {
	    key: "updateFields",
	    value: function updateFields() {
	      var filterManager = this.getFilter();

	      if (!filterManager) {
	        return;
	      }

	      this.presetId = filterManager.getPreset().getCurrentPresetId();
	    }
	  }, {
	    key: "isFilteredByPresetId",
	    value: function isFilteredByPresetId(presetId) {
	      return presetId === this.presetId;
	    }
	  }, {
	    key: "getFilter",
	    value: function getFilter() {
	      return this.filterManager;
	    }
	  }]);
	  return Filter;
	}();

	var _templateObject, _templateObject2, _templateObject3, _templateObject4;

	var CountersItem = /*#__PURE__*/function () {
	  function CountersItem(options) {
	    babelHelpers.classCallCheck(this, CountersItem);
	    this.count = options.count;
	    this.name = options.name;
	    this.type = options.type;
	    this.color = options.color;
	    this.filterPresetId = options.filterPresetId;
	    this.filter = options.filter;
	    this.activeByDefault = !!options.activeByDefault;
	    this.$container = null;
	    this.$remove = null;
	    this.$counter = null;
	    this.bindEvents();
	  }

	  babelHelpers.createClass(CountersItem, [{
	    key: "bindEvents",
	    value: function bindEvents() {
	      var _this = this;

	      main_core_events.EventEmitter.subscribe('BX.Socialnetwork.Interface.Counters:active', function (param) {
	        _this !== param.data ? _this.unActive() : null;
	      });
	    }
	  }, {
	    key: "getCounter",
	    value: function getCounter() {
	      if (!this.$counter) {
	        var count = this.count > 99 ? '99+' : this.count;
	        this.$counter = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"sonet-counters--item-num ", "\">\n\t\t\t\t\t<div class=\"sonet-counters--item-num-text --stop --without-animate\">", "</div>\n\t\t\t\t</div>\n\t\t\t"])), this.getCounterColor(), count);
	      }

	      return this.$counter;
	    }
	  }, {
	    key: "getCounterColor",
	    value: function getCounterColor() {
	      if (!this.color) {
	        return null;
	      }

	      return "--".concat(this.color);
	    }
	  }, {
	    key: "animateCounter",
	    value: function animateCounter(start, value) {
	      var _this2 = this;

	      if (start > 99 && value > 99) {
	        return;
	      }

	      value > 99 ? value = 99 : null;

	      if (start > 99) {
	        start = 99;
	      }

	      var duration = start - value;

	      if (duration < 0) {
	        duration = duration * -1;
	      }

	      this.$counter.innerHTML = '';
	      this.getCounter().classList.remove('--update');
	      this.getCounter().classList.remove('--update-multi');

	      if (duration > 5) {
	        setTimeout(function () {
	          _this2.getCounter().style.animationDuration = duration * 50 + 'ms';

	          _this2.getCounter().classList.add('--update-multi');
	        });
	      }

	      var timer = setInterval(function () {
	        value < start ? start-- : start++;
	        var node = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"sonet-counters--item-num-text ", "\">", "</div>\n\t\t\t"])), value < start ? '--decrement' : '', start);

	        if (start === value) {
	          node.classList.add('--stop');

	          if (duration < 5) {
	            _this2.getCounter().classList.add('--update');
	          }

	          clearInterval(timer);
	          start === 0 ? _this2.fade() : _this2.unFade();
	        }

	        if (start !== value) {
	          main_core.Event.bind(node, 'animationend', function () {
	            node.parentNode.removeChild(node);
	          });
	        }

	        _this2.$counter.appendChild(node);
	      }, 50);
	    }
	  }, {
	    key: "updateCount",
	    value: function updateCount(param) {
	      if (this.count === param) {
	        return;
	      }

	      this.animateCounter(this.count, param);
	      this.count = param;
	    }
	  }, {
	    key: "getRemove",
	    value: function getRemove() {
	      if (!this.$remove) {
	        this.$remove = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"sonet-counters--item-remove\"></div>\n\t\t\t"])));
	      }

	      return this.$remove;
	    }
	  }, {
	    key: "fade",
	    value: function fade() {
	      this.getContainer().classList.add('--fade');
	    }
	  }, {
	    key: "unFade",
	    value: function unFade() {
	      this.getContainer().classList.remove('--fade');
	    }
	  }, {
	    key: "active",
	    value: function active(node) {
	      var targetNode = main_core.Type.isDomNode(node) ? node : this.getContainer();
	      targetNode.classList.add('--hover');
	      main_core_events.EventEmitter.emit('BX.Socialnetwork.Interface.Counters:active', this);
	    }
	  }, {
	    key: "unActive",
	    value: function unActive(node) {
	      var targetNode = main_core.Type.isDomNode(node) ? node : this.getContainer();
	      targetNode.classList.remove('--hover');
	      main_core_events.EventEmitter.emit('BX.Socialnetwork.Interface.Counters:unActive', this);
	    }
	  }, {
	    key: "adjustClick",
	    value: function adjustClick() {
	      main_core_events.EventEmitter.emit('Socialnetwork.Toolbar:onItem', {
	        counter: this
	      });
	      this.$container.classList.contains('--hover') ? this.unActive() : this.active();
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (!this.$container) {
	        this.$container = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"sonet-counters--item ", "\">\n\t\t\t\t\t<div class=\"sonet-counters--item-wrapper\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t<div class=\"sonet-counters--item-title\">", "</div>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), Number(this.count) === 0 ? ' --fade' : '', this.getCounter(), this.name, this.getRemove());

	        if (this.filter.isFilteredByPresetId(this.filterPresetId) || this.activeByDefault) {
	          this.active(this.$container);
	        }

	        main_core.Event.bind(this.$container, 'click', this.adjustClick.bind(this));
	      }

	      return this.$container;
	    }
	  }]);
	  return CountersItem;
	}();

	var _templateObject$1, _templateObject2$1;
	var Counters = /*#__PURE__*/function () {
	  babelHelpers.createClass(Counters, null, [{
	    key: "counterTypes",
	    get: function get() {
	      return {
	        workgroup_detail: ['workgroup_requests_in', 'workgroup_requests_out']
	      };
	    }
	  }]);

	  function Counters(options) {
	    babelHelpers.classCallCheck(this, Counters);
	    this.userId = options.userId;
	    this.targetUserId = options.targetUserId;
	    this.entityType = options.entityType || '';
	    this.entityId = parseInt(options.entityId || 0);
	    this.role = options.role;
	    this.entityTitle = options.entityTitle || '';
	    this.counters = options.counters;
	    this.initialCounterTypes = options.counterTypes;
	    this.renderTo = options.renderTo;
	    this.signedParameters = options.signedParameters;
	    this.initialCounter = options.initialCounter || '';
	    this.$other = {
	      cropped: null,
	      layout: null
	    };
	    this.$entityHead = null;
	    this.filter = new Filter({
	      filterId: options.filterId
	    });
	    this.bindEvents();
	    this.setData(this.counters);
	    this.initPull();
	  }

	  babelHelpers.createClass(Counters, [{
	    key: "isWorkgroupList",
	    value: function isWorkgroupList() {
	      return false;
	    }
	  }, {
	    key: "initPull",
	    value: function initPull() {
	      var _this = this;

	      pull_client.PULL.subscribe({
	        moduleId: 'socialnetwork',
	        callback: function callback(data) {
	          return _this.processPullEvent(data);
	        }
	      });
	    }
	  }, {
	    key: "extendWatch",
	    value: function extendWatch() {
	      var _this2 = this;

	      if (this.isWorkgroupList()) {
	        var tagId = 'WORKGROUP_LIST';
	        BX.PULL.extendWatch(tagId, true);
	        setTimeout(function () {
	          return _this2.extendWatch();
	        }, 29 * 60 * 1000);
	      }
	    }
	  }, {
	    key: "processPullEvent",
	    value: function processPullEvent(data) {
	      var eventHandlers = {
	        user_counter: this.onUserCounter.bind(this)
	      };
	      var has = Object.prototype.hasOwnProperty;
	      var command = data.command,
	          params = data.params;

	      if (has.call(eventHandlers, command)) {
	        var method = eventHandlers[command];

	        if (method) {
	          method.apply(this, [params]);
	        }
	      }
	    }
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      main_core_events.EventEmitter.subscribe('BX.Main.Filter:apply', this.onFilterApply.bind(this));
	    }
	  }, {
	    key: "onFilterApply",
	    value: function onFilterApply() {
	      var _this3 = this;

	      this.filter.updateFields();
	      Object.values(this.counterItems).forEach(function (counter) {
	        if (counter) {
	          _this3.filter.isFilteredByPresetId(counter.filterPresetId) ? counter.active() : counter.unActive();
	        }
	      });
	    }
	  }, {
	    key: "onUserCounter",
	    value: function onUserCounter(data) {
	      var _this4 = this;

	      var has = Object.prototype.hasOwnProperty;

	      if (this.entityType === 'workgroup_detail') {
	        if (!has.call(data, 'workgroupId') || !has.call(data, 'values') || this.entityId !== parseInt(data.workgroupId) || this.userId !== Number(data.userId)) {
	          return;
	        }

	        Object.entries(data.values).forEach(function (_ref) {
	          var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	              type = _ref2[0],
	              value = _ref2[1];

	          if (_this4.counterItems[type]) {
	            _this4.counterItems[type].updateCount(value);
	          }
	        });
	      }
	    }
	  }, {
	    key: "getCounterItem",
	    value: function getCounterItem(param) {
	      return new CountersItem({
	        count: param.count,
	        name: param.name,
	        type: param.type,
	        color: param.color,
	        filterPresetId: param.filterPresetId,
	        activeByDefault: param.type === this.initialCounter,
	        filter: this.filter
	      });
	    }
	  }, {
	    key: "setData",
	    value: function setData(counters) {
	      var _this5 = this;

	      this.counterItems = {};
	      var availableTypes = babelHelpers.toConsumableArray(Counters.counterTypes.workgroup_detail);
	      Object.entries(counters).forEach(function (_ref3) {
	        var _ref4 = babelHelpers.slicedToArray(_ref3, 2),
	            type = _ref4[0],
	            data = _ref4[1];

	        if (!availableTypes.includes(type)) {
	          return;
	        }

	        _this5.counterItems[type] = _this5.getCounterItem({
	          type: type,
	          name: data.TITLE,
	          count: Number(data.VALUE),
	          color: data.STYLE,
	          filterPresetId: data.FILTER_PRESET_ID
	        });
	      });
	    }
	  }, {
	    key: "isCroppedBlock",
	    value: function isCroppedBlock(node) {
	      if (node) {
	        return node.classList.contains('--cropp');
	      }
	    }
	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      var content = [];
	      Object.values(this.counterItems).forEach(function (counter) {
	        return content.push(counter.getContainer());
	      });
	      this.$entityHead = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sonet-counters--group-head\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.entityTitle);
	      this.$element = main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"sonet-counters sonet-counters--scope\">\n\t\t\t\t<div class=\"sonet-counters--group\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"sonet-counters--group-content\">", "</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), this.$entityHead, content);
	      return this.$element;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var node = this.getContainer();
	      var fakeNode = node.cloneNode(true);
	      fakeNode.classList.add('sonet-counters');
	      fakeNode.style.position = 'fixed';
	      fakeNode.style.opacity = '0';
	      fakeNode.style.width = 'auto';
	      fakeNode.style.pointerEvents = 'none';
	      document.body.appendChild(fakeNode);
	      this.nodeWidth = fakeNode.offsetWidth;
	      document.body.removeChild(fakeNode);
	      main_core.Dom.replace(this.renderTo.firstChild, node);
	    }
	  }]);
	  return Counters;
	}();
	babelHelpers.defineProperty(Counters, "updateTimeout", false);
	babelHelpers.defineProperty(Counters, "needUpdate", false);
	babelHelpers.defineProperty(Counters, "timeoutTTL", 5000);

	exports.Counters = Counters;

}((this.BX.Socialnetwork.Interface = this.BX.Socialnetwork.Interface || {}),BX,BX,BX.Event));
//# sourceMappingURL=script.js.map
