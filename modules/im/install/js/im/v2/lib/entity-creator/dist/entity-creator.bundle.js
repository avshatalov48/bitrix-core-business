this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,rest_client,im_v2_application_core,im_v2_const) {
	'use strict';

	const REQUEST_METHODS = Object.freeze({
	  task: im_v2_const.RestMethod.imChatTaskPrepare,
	  meeting: im_v2_const.RestMethod.imChatCalendarPrepare
	});
	const CALENDAR_ON_ENTRY_SAVE_EVENT = 'BX.Calendar:onEntrySave';
	var _chatId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("chatId");
	var _restClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _onCalendarEntrySaveHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onCalendarEntrySaveHandler");
	var _calendarSliderId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("calendarSliderId");
	var _createMeeting = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createMeeting");
	var _createTask = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createTask");
	var _requestPreparedParams = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestPreparedParams");
	var _openTaskSlider = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("openTaskSlider");
	var _openCalendarSlider = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("openCalendarSlider");
	var _onCalendarEntrySave = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onCalendarEntrySave");
	class EntityCreator {
	  constructor(chatId) {
	    Object.defineProperty(this, _onCalendarEntrySave, {
	      value: _onCalendarEntrySave2
	    });
	    Object.defineProperty(this, _openCalendarSlider, {
	      value: _openCalendarSlider2
	    });
	    Object.defineProperty(this, _openTaskSlider, {
	      value: _openTaskSlider2
	    });
	    Object.defineProperty(this, _requestPreparedParams, {
	      value: _requestPreparedParams2
	    });
	    Object.defineProperty(this, _createTask, {
	      value: _createTask2
	    });
	    Object.defineProperty(this, _createMeeting, {
	      value: _createMeeting2
	    });
	    Object.defineProperty(this, _chatId, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _restClient, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _onCalendarEntrySaveHandler, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _calendarSliderId, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient] = im_v2_application_core.Core.getRestClient();
	    babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId] = chatId;
	  }
	  createTaskForChat() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _createTask)[_createTask]();
	  }
	  createTaskForMessage(messageId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _createTask)[_createTask](messageId);
	  }
	  createMeetingForChat() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _createMeeting)[_createMeeting]();
	  }
	  createMeetingForMessage(messageId) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _createMeeting)[_createMeeting](messageId);
	  }
	}
	function _createMeeting2(messageId) {
	  const queryParams = {
	    CHAT_ID: babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId]
	  };
	  if (messageId) {
	    queryParams['MESSAGE_ID'] = messageId;
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _requestPreparedParams)[_requestPreparedParams](REQUEST_METHODS.meeting, queryParams).then(sliderParams => {
	    const {
	      params
	    } = sliderParams;
	    babelHelpers.classPrivateFieldLooseBase(this, _onCalendarEntrySaveHandler)[_onCalendarEntrySaveHandler] = babelHelpers.classPrivateFieldLooseBase(this, _onCalendarEntrySave)[_onCalendarEntrySave].bind(this, params.sliderId, messageId);
	    main_core_events.EventEmitter.subscribeOnce(CALENDAR_ON_ENTRY_SAVE_EVENT, babelHelpers.classPrivateFieldLooseBase(this, _onCalendarEntrySaveHandler)[_onCalendarEntrySaveHandler]);
	    return babelHelpers.classPrivateFieldLooseBase(this, _openCalendarSlider)[_openCalendarSlider](params);
	  });
	}
	function _createTask2(messageId) {
	  const queryParams = {
	    CHAT_ID: babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId]
	  };
	  if (messageId) {
	    queryParams['MESSAGE_ID'] = messageId;
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _requestPreparedParams)[_requestPreparedParams](REQUEST_METHODS.task, queryParams).then(sliderParams => {
	    const {
	      link,
	      params
	    } = sliderParams;
	    return babelHelpers.classPrivateFieldLooseBase(this, _openTaskSlider)[_openTaskSlider](link, params);
	  });
	}
	function _requestPreparedParams2(requestMethod, query) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient].callMethod(requestMethod, query).then(result => {
	    return result.data();
	  }).catch(error => {
	    console.error(error);
	  });
	}
	function _openTaskSlider2(sliderUri, sliderParams) {
	  BX.SidePanel.Instance.open(sliderUri, {
	    requestMethod: 'post',
	    requestParams: sliderParams,
	    cacheable: false
	  });
	}
	function _openCalendarSlider2(sliderParams) {
	  new (window.top.BX || window.BX).Calendar.SliderLoader(0, sliderParams).show();
	}
	function _onCalendarEntrySave2(sliderId, messageId, event) {
	  const eventData = event.getData();
	  if (eventData.sliderId !== sliderId) {
	    return;
	  }
	  const queryParams = {
	    CALENDAR_ID: eventData.responseData.entryId,
	    CHAT_ID: babelHelpers.classPrivateFieldLooseBase(this, _chatId)[_chatId]
	  };
	  if (messageId) {
	    queryParams['MESSAGE_ID'] = messageId;
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient].callMethod(im_v2_const.RestMethod.imChatCalendarAdd, queryParams).catch(error => {
	    console.error(error);
	  });
	}

	exports.EntityCreator = EntityCreator;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Event,BX,BX.Messenger.v2.Application,BX.Messenger.v2.Const));
//# sourceMappingURL=entity-creator.bundle.js.map
