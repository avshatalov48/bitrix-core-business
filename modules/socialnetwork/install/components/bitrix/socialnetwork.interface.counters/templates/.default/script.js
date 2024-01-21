this.BX = this.BX || {};
this.BX.Socialnetwork = this.BX.Socialnetwork || {};
(function (exports,pull_client,ui_counterpanel,ui_cnt,main_core,main_core_events) {
	'use strict';

	var Filter = /*#__PURE__*/function () {
	  function Filter(options) {
	    babelHelpers.classCallCheck(this, Filter);
	    this.filterId = options.filterId;
	    this.filterManager = BX.Main.filterManager.getById(this.filterId);
	    this.countersManager = options.countersManager;
	    setTimeout(this.updateFields.bind(this), 100);
	  }
	  babelHelpers.createClass(Filter, [{
	    key: "updateFields",
	    value: function updateFields() {
	      var filterManager = this.getFilter();
	      if (!filterManager) {
	        return;
	      }
	      this.presetId = filterManager.getPreset().getCurrentPresetId();
	      this.fields = filterManager.getFilterFieldsValues();
	      this.countersManager.activateCountersByFilter();
	    }
	  }, {
	    key: "isFilteredByPresetId",
	    value: function isFilteredByPresetId(presetId) {
	      return presetId === this.presetId;
	    }
	  }, {
	    key: "isFilteredByFields",
	    value: function isFilteredByFields(filterFields) {
	      var _this = this;
	      var result = false;
	      var breakNeeded = false;
	      Object.entries(filterFields).map(function (_ref) {
	        var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	          field = _ref2[0],
	          value = _ref2[1];
	        if (!breakNeeded && !main_core.Type.isUndefined(_this.fields[field])) {
	          result = _this.fields[field] === value;
	          if (!result) {
	            breakNeeded = true;
	          }
	        }
	      });
	      return result;
	    }
	  }, {
	    key: "getFilter",
	    value: function getFilter() {
	      return this.filterManager;
	    }
	  }]);
	  return Filter;
	}();

	var Counters = /*#__PURE__*/function () {
	  function Counters(options) {
	    babelHelpers.classCallCheck(this, Counters);
	    this.userId = options.userId;
	    this.targetUserId = options.targetUserId;
	    this.entityType = options.entityType || '';
	    this.entityId = parseInt(options.entityId || 0);
	    this.role = options.role;
	    this.entityTitle = options.entityTitle || '';
	    this.counters = options.counters;
	    this.renderTo = options.renderTo;
	    this.signedParameters = options.signedParameters;
	    this.initialCounter = options.initialCounter || '';
	    this.panel = null;
	    this.tasksCounter = {};
	    this.bindEvents();
	    this.setData(this.counters);
	    this.initPull();
	    this.filter = new Filter({
	      filterId: options.filterId,
	      countersManager: this
	    });
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
	        moduleId: 'main',
	        callback: function callback(data) {
	          return _this.processPullEvent(data);
	        }
	      });
	      pull_client.PULL.subscribe({
	        moduleId: 'socialnetwork',
	        callback: function callback(data) {
	          return _this.processPullEvent(data);
	        }
	      });
	      pull_client.PULL.subscribe({
	        moduleId: 'tasks',
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
	      this.filter.updateFields();
	    }
	  }, {
	    key: "activateCountersByFilter",
	    value: function activateCountersByFilter() {
	      var _this3 = this;
	      if (!this.panel) {
	        return;
	      }
	      this.counterItems.forEach(function (counter) {
	        if (main_core.Type.isObject(counter.filterFields)) {
	          _this3.filter.isFilteredByFields(counter.filterFields) ? _this3.panel.getItemById(counter.type).activate() : _this3.panel.getItemById(counter.type).deactivate();
	        } else if (main_core.Type.isStringFilled(counter.filterPresetId)) {
	          _this3.filter.isFilteredByPresetId(counter.filterPresetId) ? _this3.panel.getItemById(counter.type).activate() : _this3.panel.getItemById(counter.type).deactivate();
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
	          _this4.counterItems.forEach(function (counter) {
	            if (counter.type === type) {
	              var item = _this4.panel.getItemById(counter.type);
	              item.updateValue(value.all);
	              var baseColor = 'GRAY';
	              switch (type) {
	                case 'workgroup_requests_in':
	                  baseColor = 'DANGER';
	                  break;
	                case 'workgroup_requests_out':
	                  baseColor = 'SUCCESS';
	                  break;
	              }
	              item.updateColor(value.all > 0 ? baseColor : 'GRAY');
	            }
	          });
	        });
	      } else if (this.entityType === 'workgroup_list') {
	        if (main_core.Type.isPlainObject(data[main_core.Loc.getMessage('SITE_ID')]) && !main_core.Type.isUndefined(data[main_core.Loc.getMessage('SITE_ID')]['**SG0'])) {
	          this.counterItems.forEach(function (counter) {
	            if (counter.type === 'workgroup_list_livefeed') {
	              var item = _this4.panel.getItemById(counter.type);
	              var value = Number(data[main_core.Loc.getMessage('SITE_ID')]['**SG0']);
	              item.updateValue(value);
	              item.updateColor(value > 0 ? 'DANGER' : 'GRAY');
	            }
	          });
	        } else if (!main_core.Type.isUndefined(data.projects_major) || !main_core.Type.isUndefined(data.scrum_total_comments)) {
	          if (!main_core.Type.isUndefined(data.projects_major)) {
	            this.tasksCounter.projects_major = Number(data.projects_major);
	          }
	          if (!main_core.Type.isUndefined(data.scrum_total_comments)) {
	            this.tasksCounter.scrum_total_comments = Number(data.scrum_total_comments);
	          }
	          this.counterItems.forEach(function (counter) {
	            if (counter.type === 'workgroup_list_tasks') {
	              var sum = 0;
	              Object.entries(_this4.tasksCounter).map(function (_ref3) {
	                var _ref4 = babelHelpers.slicedToArray(_ref3, 1),
	                  key = _ref4[0];
	                sum += _this4.tasksCounter[key];
	              });
	              var item = _this4.panel.getItemById(counter.type);
	              item.updateValue(sum);
	              item.updateColor(sum > 0 ? 'DANGER' : 'GRAY');
	            }
	          });
	        }
	      }
	    }
	  }, {
	    key: "getCounterItem",
	    value: function getCounterItem(param) {
	      var counterData = {
	        type: param.type,
	        activeByDefault: param.type === this.initialCounter,
	        filter: this.filter
	      };
	      if (main_core.Type.isObject(param.filterFields)) {
	        var _Object$entries$pop = Object.entries(param.filterFields).pop(),
	          _Object$entries$pop2 = babelHelpers.slicedToArray(_Object$entries$pop, 2),
	          key = _Object$entries$pop2[0],
	          value = _Object$entries$pop2[1];
	        counterData.filterField = key;
	        counterData.filterValue = value;
	      } else if (main_core.Type.isStringFilled(param.filterPresetId)) {
	        counterData.filterPresetId = param.filterPresetId;
	      }
	      return {
	        id: param.type,
	        title: param.name,
	        value: param.count,
	        color: param.color,
	        eventsForActive: {
	          click: function click() {
	            main_core_events.EventEmitter.emit('Socialnetwork.Toolbar:onItem', {
	              counter: counterData
	            });
	          },
	          mouseenter: function mouseenter() {},
	          anyEvent: function anyEvent() {}
	        },
	        eventsForUnActive: {
	          click: function click() {
	            main_core_events.EventEmitter.emit('Socialnetwork.Toolbar:onItem', {
	              counter: counterData
	            });
	          }
	        }
	      };
	    }
	  }, {
	    key: "setData",
	    value: function setData(counters) {
	      var _this5 = this;
	      this.counterItems = [];
	      Object.entries(counters).forEach(function (_ref5) {
	        var _ref6 = babelHelpers.slicedToArray(_ref5, 2),
	          type = _ref6[0],
	          data = _ref6[1];
	        _this5.counterItems.push({
	          type: type,
	          name: data.TITLE,
	          count: _this5.getCounterSum(data.VALUE),
	          color: data.STYLE,
	          filterPresetId: data.FILTER_PRESET_ID,
	          filterFields: data.FILTER_FIELDS
	        });
	      });
	    }
	  }, {
	    key: "getCounterSum",
	    value: function getCounterSum(counterValues) {
	      var result = 0;
	      Object.entries(counterValues).map(function (_ref7) {
	        var _ref8 = babelHelpers.slicedToArray(_ref7, 2),
	          value = _ref8[1];
	        result += Number(value);
	      });
	      return result;
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var _this6 = this;
	      this.panel = new ui_counterpanel.CounterPanel({
	        target: this.renderTo,
	        multiselect: true,
	        items: this.counterItems.map(function (item) {
	          return _this6.getCounterItem(item);
	        })
	      });
	      this.panel.init();
	    }
	  }]);
	  return Counters;
	}();
	babelHelpers.defineProperty(Counters, "updateTimeout", false);
	babelHelpers.defineProperty(Counters, "needUpdate", false);
	babelHelpers.defineProperty(Counters, "timeoutTTL", 5000);

	exports.Counters = Counters;

}((this.BX.Socialnetwork.Interface = this.BX.Socialnetwork.Interface || {}),BX,BX.UI,BX.UI,BX,BX.Event));
//# sourceMappingURL=script.js.map
