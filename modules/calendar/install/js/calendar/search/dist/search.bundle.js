this.BX = this.BX || {};
(function (exports,main_core,calendar_util,main_core_events) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3;

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }
	var Search = /*#__PURE__*/function () {
	  function Search(filterId) {
	    var counters = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
	    babelHelpers.classCallCheck(this, Search);
	    this.BX = BX; // for calendar in slider

	    this.filterId = filterId;
	    this.minSearchStringLength = 2;

	    if (counters) {
	      this.counters = [{
	        id: 'invitation',
	        className: 'calendar-counter-invitation',
	        pluralMessageId: 'EC_COUNTER_INVITATION_PLURAL_',
	        value: counters.invitation || 0
	      }];
	    }

	    this.filter = this.BX.Main.filterManager.getById(this.filterId);

	    if (this.filter) {
	      this.filterApi = this.filter.getApi();
	      this.applyFilterBinded = this.applyFilter.bind(this);
	      main_core_events.EventEmitter.subscribe('BX.Main.Filter:apply', this.applyFilterBinded);
	    }
	  }

	  babelHelpers.createClass(Search, [{
	    key: "getFilter",
	    value: function getFilter() {
	      return this.filter;
	    }
	  }, {
	    key: "updateCounters",
	    value: function updateCounters() {
	      var _this = this;

	      this.showCounters = false;
	      var calendarContext = calendar_util.Util.getCalendarContext();
	      this.BX.cleanNode(calendarContext.countersCont);
	      this.countersWrap = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"calendar-counter-title\"></div>"])));
	      main_core.Dom.append(this.countersWrap, calendarContext.countersCont);

	      var _iterator = _createForOfIteratorHelper(this.counters),
	          _step;

	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var counter = _step.value;

	          if (counter && counter.value > 0) {
	            this.showCounters = true;
	            break;
	          }
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }

	      if (this.showCounters) {
	        this.countersPage = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<span class=\"calendar-counter-page-name\">", "</span>"])), main_core.Loc.getMessage('EC_COUNTER_TOTAL'));
	        main_core.Dom.append(this.countersPage, this.countersWrap);

	        var _iterator2 = _createForOfIteratorHelper(this.counters),
	            _step2;

	        try {
	          var _loop = function _loop() {
	            var counter = _step2.value;

	            if (counter && counter.value > 0) {
	              var pluralNumber = main_core.Loc.getPluralForm(counter.value);
	              _this.countersContainer = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<span class=\"calendar-counter-container ", "\" data-bx-counter=\"", "\">\n\t\t\t\t\t\t<span class=\"calendar-counter-inner\">\n\t\t\t\t\t\t\t<span class=\"calendar-counter-number\">", "</span>\n\t\t\t\t\t\t\t<span class=\"calendar-counter-text\">\n\t\t\t\t\t\t\t\t ", "\n\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</span>"])), counter.className, counter.id, counter.value, main_core.Loc.getMessage(counter.pluralMessageId + pluralNumber));
	              main_core.Dom.append(_this.countersContainer, _this.countersWrap);
	              main_core.Event.bind(_this.countersContainer, 'click', function () {
	                _this.applyCounterEntries(counter.id);
	              });
	            }
	          };

	          for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	            _loop();
	          }
	        } catch (err) {
	          _iterator2.e(err);
	        } finally {
	          _iterator2.f();
	        }
	      } else {
	        this.countersWrap.innerHTML = main_core.Loc.getMessage('EC_NO_COUNTERS');
	      }
	    }
	  }, {
	    key: "setCountersValue",
	    value: function setCountersValue(counters) {
	      if (main_core.Type.isPlainObject(counters)) {
	        var _iterator3 = _createForOfIteratorHelper(this.counters),
	            _step3;

	        try {
	          for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
	            var counter = _step3.value;

	            if (!main_core.Type.isUndefined(counters[counter.id])) {
	              counter.value = counters[counter.id] || 0;
	            }
	          }
	        } catch (err) {
	          _iterator3.e(err);
	        } finally {
	          _iterator3.f();
	        }

	        this.updateCounters();
	      }
	    }
	  }, {
	    key: "displaySearchResult",
	    value: function displaySearchResult(response) {
	      var calendarContext = calendar_util.Util.getCalendarContext();
	      var entries = [];

	      var _iterator4 = _createForOfIteratorHelper(response.entries),
	          _step4;

	      try {
	        for (_iterator4.s(); !(_step4 = _iterator4.n()).done;) {
	          var entry = _step4.value;
	          entries.push(new window.BXEventCalendar.Entry(calendarContext, entry));
	        }
	      } catch (err) {
	        _iterator4.e(err);
	      } finally {
	        _iterator4.f();
	      }

	      calendarContext.getView().displayResult(entries);

	      if (response.counters) {
	        this.setCountersValue(response.counters);
	      }
	    }
	  }, {
	    key: "applyCounterEntries",
	    value: function applyCounterEntries(counterId) {
	      if (counterId === 'invitation') {
	        this.filterApi.setFilter({
	          preset_id: "filter_calendar_meeting_status_q"
	        });
	      }
	    }
	  }, {
	    key: "applyFilter",
	    value: function applyFilter(id, data, ctx, promise, params) {
	      if (params) {
	        params.autoResolve = false;
	      }

	      this.applyFilterHandler(promise).then(function () {});
	    }
	  }, {
	    key: "applyFilterHandler",
	    value: function applyFilterHandler(promise) {
	      var _this2 = this;

	      return new Promise(function (resolve) {
	        var calendarContext = calendar_util.Util.getCalendarContext();

	        if (_this2.isFilterEmpty()) {
	          if (calendarContext.getView().resetFilterMode) {
	            calendarContext.getView().resetFilterMode({
	              resetSearchFilter: false
	            });
	          }

	          if (promise) {
	            promise.fulfill();
	          }
	        } else {
	          calendarContext.setView('list', {
	            animation: false
	          });
	          calendarContext.getView().applyFilterMode();
	          BX.ajax.runAction('calendar.api.calendarajax.getFilterData', {
	            data: {
	              ownerId: calendarContext.util.config.ownerId,
	              userId: calendarContext.util.config.userId,
	              type: calendarContext.util.config.type
	            }
	          }).then(function (response) {
	            if (response.data.entries) {
	              if (!calendarContext.getView().filterMode) {
	                calendarContext.getView().applyFilterMode();

	                _this2.displaySearchResult(response.data);
	              } else {
	                _this2.displaySearchResult(response.data);
	              }
	            }

	            if (promise) {
	              promise.fulfill();
	            }

	            resolve(response.data);
	          }, function (response) {
	            resolve(response.data);
	          });
	        }
	      });
	    }
	  }, {
	    key: "isFilterEmpty",
	    value: function isFilterEmpty() {
	      var searchField = this.filter.getSearch();
	      return !searchField.getLastSquare() && (!searchField.getSearchString() || searchField.getSearchString().length < this.minSearchStringLength);
	    }
	  }, {
	    key: "resetFilter",
	    value: function resetFilter() {
	      this.filter.resetFilter();
	    }
	  }]);
	  return Search;
	}();

	exports.Search = Search;

}((this.BX.Calendar = this.BX.Calendar || {}),BX,BX.Calendar,BX.Event));
//# sourceMappingURL=search.bundle.js.map
