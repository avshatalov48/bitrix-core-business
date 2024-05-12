this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
(function (exports,main_core,calendar_sharing_publicV2,calendar_util,main_date) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6,
	  _t7,
	  _t8;
	var _layout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("layout");
	var _eventData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("eventData");
	var _widgetDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("widgetDate");
	var _initEventData = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initEventData");
	var _getContent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getContent");
	var _getNodeIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeIcon");
	var _getNodeBackWrapper = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeBackWrapper");
	var _createIcon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createIcon");
	var _getEventNameNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getEventNameNode");
	var _getStateTitleNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStateTitleNode");
	var _getAdditionalBlockNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getAdditionalBlockNode");
	var _createAdditionalBlockContentByState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createAdditionalBlockContentByState");
	var _getNodeWidgetDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getNodeWidgetDate");
	class DeletedViewForm {
	  constructor(entryId) {
	    Object.defineProperty(this, _getNodeWidgetDate, {
	      value: _getNodeWidgetDate2
	    });
	    Object.defineProperty(this, _createAdditionalBlockContentByState, {
	      value: _createAdditionalBlockContentByState2
	    });
	    Object.defineProperty(this, _getAdditionalBlockNode, {
	      value: _getAdditionalBlockNode2
	    });
	    Object.defineProperty(this, _getStateTitleNode, {
	      value: _getStateTitleNode2
	    });
	    Object.defineProperty(this, _getEventNameNode, {
	      value: _getEventNameNode2
	    });
	    Object.defineProperty(this, _createIcon, {
	      value: _createIcon2
	    });
	    Object.defineProperty(this, _getNodeBackWrapper, {
	      value: _getNodeBackWrapper2
	    });
	    Object.defineProperty(this, _getNodeIcon, {
	      value: _getNodeIcon2
	    });
	    Object.defineProperty(this, _getContent, {
	      value: _getContent2
	    });
	    Object.defineProperty(this, _initEventData, {
	      value: _initEventData2
	    });
	    Object.defineProperty(this, _layout, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _eventData, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _widgetDate, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _eventData)[_eventData] = {
	      eventId: entryId,
	      from: null,
	      to: null,
	      timezone: null,
	      isFullDay: false,
	      canceledTimestamp: null,
	      canceledUserName: null,
	      canceledUserId: null,
	      eventName: null
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout] = {
	      back: null,
	      widgetDate: null,
	      eventName: null,
	      icon: null,
	      stateTitle: null,
	      additionalBlock: null,
	      bottomButton: null
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _widgetDate)[_widgetDate] = new calendar_sharing_publicV2.WidgetDate();
	  }
	  initInSlider(slider, promiseResolve) {
	    this.createContent(slider).then(html => {
	      if (main_core.Type.isFunction(promiseResolve)) {
	        promiseResolve(html);
	      }
	    });
	  }
	  createContent(slider) {
	    return new Promise(resolve => {
	      BX.ajax.runAction('calendar.api.sharingajax.getDeletedSharedEvent', {
	        data: {
	          entryId: parseInt(babelHelpers.classPrivateFieldLooseBase(this, _eventData)[_eventData].eventId, 10)
	        }
	      }).then(response => {
	        const entry = response.data.entry;
	        const link = response.data.link;
	        const userTimezone = response.data.userTimezone;
	        babelHelpers.classPrivateFieldLooseBase(this, _initEventData)[_initEventData]({
	          eventId: entry.ID,
	          timestampFromUTC: entry.timestampFromUTC,
	          timestampToUTC: entry.timestampToUTC,
	          timezone: userTimezone,
	          isFullDay: entry.DT_SKIP_TIME === 'Y',
	          eventName: entry.NAME,
	          canceledTimestamp: link.canceledTimestamp,
	          canceledUserId: entry.canceledUserId,
	          canceledUserName: entry.HOST_NAME
	        });
	        const deletedViewSliderRoot = main_core.Tag.render(_t || (_t = _`
					<div class="calendar-deleted-event-view-slider-root">
						<div class="calendar-pub__block calendar-pub__state">
							${0}
						</div>
					</div>
				`), babelHelpers.classPrivateFieldLooseBase(this, _getContent)[_getContent]());
	        slider.sliderContent = deletedViewSliderRoot;
	        resolve(deletedViewSliderRoot);
	      });
	    });
	  }
	}
	function _initEventData2(initialEventData) {
	  babelHelpers.classPrivateFieldLooseBase(this, _eventData)[_eventData].eventId = initialEventData.eventId;
	  babelHelpers.classPrivateFieldLooseBase(this, _eventData)[_eventData].from = calendar_util.Util.getTimezoneDateFromTimestampUTC(parseInt(initialEventData.timestampFromUTC, 10) * 1000, initialEventData.timezone);
	  babelHelpers.classPrivateFieldLooseBase(this, _eventData)[_eventData].to = calendar_util.Util.getTimezoneDateFromTimestampUTC(parseInt(initialEventData.timestampToUTC, 10) * 1000, initialEventData.timezone);
	  babelHelpers.classPrivateFieldLooseBase(this, _eventData)[_eventData].timezone = initialEventData.timezone;
	  babelHelpers.classPrivateFieldLooseBase(this, _eventData)[_eventData].isFullDay = initialEventData.isFullDay;
	  babelHelpers.classPrivateFieldLooseBase(this, _eventData)[_eventData].eventName = initialEventData.eventName;
	  babelHelpers.classPrivateFieldLooseBase(this, _eventData)[_eventData].canceledTimestamp = initialEventData.canceledTimestamp;
	  babelHelpers.classPrivateFieldLooseBase(this, _eventData)[_eventData].canceledUserName = initialEventData.canceledUserName;
	  babelHelpers.classPrivateFieldLooseBase(this, _eventData)[_eventData].canceledUserId = initialEventData.canceledUserId;
	}
	function _getContent2() {
	  return main_core.Tag.render(_t2 || (_t2 = _`
			<div class="calendar-sharing__form-result">
				${0}
				<div class="calendar-sharing__calendar-block --form --center">
					${0}
					${0}
					${0}
				</div>

				<div class="calendar-sharing__calendar-block --form --center">
					${0}
				</div>

				<div class="calendar-sharing__calendar-block --form --center">
					${0}
				</div>

				<div class="calendar-sharing__calendar-block --top-auto">
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _getNodeBackWrapper)[_getNodeBackWrapper](), babelHelpers.classPrivateFieldLooseBase(this, _getNodeIcon)[_getNodeIcon](), babelHelpers.classPrivateFieldLooseBase(this, _getEventNameNode)[_getEventNameNode](), babelHelpers.classPrivateFieldLooseBase(this, _getStateTitleNode)[_getStateTitleNode](), babelHelpers.classPrivateFieldLooseBase(this, _getNodeWidgetDate)[_getNodeWidgetDate](), babelHelpers.classPrivateFieldLooseBase(this, _getAdditionalBlockNode)[_getAdditionalBlockNode]());
	}
	function _getNodeIcon2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].icon) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].icon = babelHelpers.classPrivateFieldLooseBase(this, _createIcon)[_createIcon]();
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].icon;
	}
	function _getNodeBackWrapper2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].back) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].back = main_core.Tag.render(_t3 || (_t3 = _`<div class="calendar-sharing__calendar-bar --no-margin"></div>`));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].back;
	}
	function _createIcon2() {
	  const result = main_core.Tag.render(_t4 || (_t4 = _`
			<div class="calendar-sharing__form-result_icon"></div>
		`));
	  main_core.Dom.addClass(result, '--decline');
	  return result;
	}
	function _getEventNameNode2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].eventName) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].eventName = main_core.Tag.render(_t5 || (_t5 = _`
				<div class="calendar-pub-ui__typography-title --center --line-height-normal">
					${0}
				</div>
			`), main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _eventData)[_eventData].eventName));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].eventName;
	}
	function _getStateTitleNode2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].stateTitle) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].stateTitle = main_core.Tag.render(_t6 || (_t6 = _`
				<div class="calendar-pub-ui__typography-s --center">
					${0}
				</div>
			`), main_core.Loc.getMessage('CALENDAR_SHARING_MEETING_CANCELED'));
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].stateTitle;
	}
	function _getAdditionalBlockNode2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].additionalBlock) {
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].additionalBlock = babelHelpers.classPrivateFieldLooseBase(this, _createAdditionalBlockContentByState)[_createAdditionalBlockContentByState]();
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].additionalBlock;
	}
	function _createAdditionalBlockContentByState2() {
	  let result = '';
	  const date = calendar_util.Util.getTimezoneDateFromTimestampUTC(parseInt(babelHelpers.classPrivateFieldLooseBase(this, _eventData)[_eventData].canceledTimestamp, 10) * 1000, babelHelpers.classPrivateFieldLooseBase(this, _eventData)[_eventData].timezone);
	  if (babelHelpers.classPrivateFieldLooseBase(this, _eventData)[_eventData].canceledTimestamp && babelHelpers.classPrivateFieldLooseBase(this, _eventData)[_eventData].canceledUserName && date) {
	    const dayMonthFormat = main_date.DateTimeFormat.getFormat('DAY_MONTH_FORMAT');
	    const shortTimeFormat = main_date.DateTimeFormat.getFormat('SHORT_TIME_FORMAT');
	    const format = `${dayMonthFormat} ${shortTimeFormat}`;
	    result = main_core.Tag.render(_t7 || (_t7 = _`
				<div class="calendar-pub__form-status">
					<div class="calendar-pub__form-status_text">
						${0}: <a href="/company/personal/user/${0}/" target="_blank" class="calendar-sharing-deletedviewform_open-profile">${0}</a>
						<br>
						${0}
					</div>
				</div>
			`), main_core.Loc.getMessage('CALENDAR_SHARING_WHO_CANCELED'), babelHelpers.classPrivateFieldLooseBase(this, _eventData)[_eventData].canceledUserId, main_core.Text.encode(babelHelpers.classPrivateFieldLooseBase(this, _eventData)[_eventData].canceledUserName), main_date.DateTimeFormat.format(format, date.getTime() / 1000));
	  } else {
	    result = main_core.Tag.render(_t8 || (_t8 = _`
				<div></div>
			`));
	  }
	  return result;
	}
	function _getNodeWidgetDate2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].widgetDate) {
	    babelHelpers.classPrivateFieldLooseBase(this, _widgetDate)[_widgetDate].updateValue({
	      from: babelHelpers.classPrivateFieldLooseBase(this, _eventData)[_eventData].from,
	      to: babelHelpers.classPrivateFieldLooseBase(this, _eventData)[_eventData].to,
	      timezone: babelHelpers.classPrivateFieldLooseBase(this, _eventData)[_eventData].timezone,
	      isFullDay: babelHelpers.classPrivateFieldLooseBase(this, _eventData)[_eventData].isFullDay
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].widgetDate = babelHelpers.classPrivateFieldLooseBase(this, _widgetDate)[_widgetDate].render();
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _layout)[_layout].widgetDate;
	}

	exports.DeletedViewForm = DeletedViewForm;

}((this.BX.Calendar.Sharing = this.BX.Calendar.Sharing || {}),BX,BX.Calendar.Sharing,BX.Calendar,BX.Main));
//# sourceMappingURL=deletedviewform.bundle.js.map
