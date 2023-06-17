(function (exports,main_core,calendar_util,main_core_events) {
	'use strict';

	var _templateObject;
	var NextEventList = /*#__PURE__*/function () {
	  function NextEventList() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, NextEventList);
	    babelHelpers.defineProperty(this, "DOM", {});
	    this.maxEntryAmount = options.maxEntryAmount || 5;
	    if (options && options.entries) {
	      this.renderList(options.entries);
	    } else {
	      this.displayEventList();
	    }
	    this.displayEventListDebounce = main_core.Runtime.debounce(this.displayEventList, 3000, this);
	    main_core.Event.bind(document, 'visibilitychange', this.checkDisplayEventList.bind(this));
	    main_core_events.EventEmitter.subscribe('SidePanel.Slider:onCloseComplete', this.checkDisplayEventList.bind(this));
	    main_core_events.EventEmitter.subscribe('onPullEvent-calendar', this.displayEventListDebounce);
	  }
	  babelHelpers.createClass(NextEventList, [{
	    key: "checkDisplayEventList",
	    value: function checkDisplayEventList() {
	      if (this.needReload) {
	        this.displayEventListDebounce();
	      }
	    }
	  }, {
	    key: "displayEventList",
	    value: function displayEventList() {
	      var _this = this;
	      if (this.isDisplayingNow()) {
	        this.showLoader();
	        this.getEventList().then(function (entryList) {
	          _this.hideLoader();
	          _this.renderList(entryList);
	        });
	      } else {
	        this.needReload = true;
	      }
	    }
	  }, {
	    key: "getEventList",
	    value: function getEventList() {
	      var _this2 = this;
	      return new Promise(function (resolve) {
	        BX.ajax.runAction('calendar.api.calendarentryajax.getnearestevents', {
	          data: {
	            ownerId: _this2.ownerId,
	            type: _this2.type,
	            futureDaysAmount: 60,
	            maxEntryAmount: _this2.maxEntryAmount
	          }
	        }).then(function (response) {
	          var _response$data;
	          resolve(response === null || response === void 0 ? void 0 : (_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.entries);
	        });
	      });
	    }
	  }, {
	    key: "showWidget",
	    value: function showWidget() {
	      this.getOuterWrap().style.display = '';
	    }
	  }, {
	    key: "hideWidget",
	    value: function hideWidget() {
	      this.getOuterWrap().style.display = 'none';
	    }
	  }, {
	    key: "showLoader",
	    value: function showLoader() {
	      this.hideLoader();
	      this.DOM.loader = this.getEventListWrap().appendChild(calendar_util.Util.getLoader(40, 'next-events-loader'));
	    }
	  }, {
	    key: "hideLoader",
	    value: function hideLoader() {
	      if (main_core.Type.isDomNode(this.DOM.loader)) {
	        main_core.Dom.remove(this.DOM.loader);
	      }
	    }
	  }, {
	    key: "renderList",
	    value: function renderList() {
	      var _this3 = this;
	      var entryList = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	      if (!main_core.Type.isArray(entryList)) {
	        entryList = [];
	      }
	      entryList = entryList.slice(0, this.maxEntryAmount);
	      main_core.Dom.clean(this.getEventListWrap());
	      var wrap = this.getEventListWrap();
	      entryList.forEach(function (entry, i) {
	        if (i === 0) {
	          _this3.setReloadTimeout(entry);
	        }
	        wrap.appendChild(_this3.renderEntry(entry));
	      });
	      if (entryList.length) {
	        this.showWidget();
	      } else {
	        this.hideWidget();
	      }
	      this.needReload = false;
	    }
	  }, {
	    key: "renderEntry",
	    value: function renderEntry(entry) {
	      var fromDate = BX.Calendar.Util.parseDate(entry['DATE_FROM']);
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<a href=\"", "\" class=\"sidebar-widget-item\">\n\t\t\t\t<span class=\"calendar-item-date\">", "</span>\n\t\t\t\t<span class=\"calendar-item-text\">\n\t\t\t\t\t<span class=\"calendar-item-link\">", "</span>\n\t\t\t\t</span>\n\t\t\t\t<span class=\"calendar-item-icon\">\n\t\t\t\t\t<span class=\"calendar-item-icon-day\">", "</span>\n\t\t\t\t\t<span class=\"calendar-item-icon-date\">", "</span>\n\t\t\t\t</span>\n\t\t\t</a>\n\t\t"])), main_core.Text.encode(entry['~URL']), entry['~FROM_TO_HTML'], main_core.Text.encode(entry['NAME']), main_core.Text.encode(entry['~WEEK_DAY']), fromDate.getDate());
	    }
	  }, {
	    key: "getOuterWrap",
	    value: function getOuterWrap() {
	      if (!this.DOM.outerWrap) {
	        this.DOM.outerWrap = document.querySelector('.sidebar-widget.sidebar-widget-calendar');
	      }
	      return this.DOM.outerWrap;
	    }
	  }, {
	    key: "getEventListWrap",
	    value: function getEventListWrap() {
	      if (!this.DOM.listWrap) {
	        this.DOM.listWrap = this.getOuterWrap().querySelector('.calendar-events-wrap');
	      }
	      return this.DOM.listWrap;
	    }
	  }, {
	    key: "setReloadTimeout",
	    value: function setReloadTimeout(entry) {
	      if (this.reloadTimeout) {
	        clearTimeout(this.reloadTimeout);
	        this.reloadTimeout = null;
	      }
	      var finishEventDate = BX.Calendar.Util.parseDate(entry['DATE_TO']);
	      if (main_core.Type.isDate(finishEventDate)) {
	        var currentDate = new Date();
	        var offset = Math.min(Math.max(finishEventDate.getTime() - currentDate.getTime() + 60000, 60000), 86400000);
	        this.reloadTimeout = setTimeout(this.displayEventList.bind(this), offset);
	      }
	    }
	  }, {
	    key: "isDisplayingNow",
	    value: function isDisplayingNow() {
	      return !document.hidden && !BX.SidePanel.Instance.getOpenSliders().length;
	    }
	  }]);
	  return NextEventList;
	}();
	main_core.Reflection.namespace('BX.Calendar').NextEventList = NextEventList;

}((this.window = this.window || {}),BX,BX.Calendar,BX.Event));
//# sourceMappingURL=script.js.map
