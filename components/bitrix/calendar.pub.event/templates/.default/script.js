(function (exports,main_core) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7;
	var CalendarEvent = /*#__PURE__*/function () {
	  function CalendarEvent() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, CalendarEvent);
	    this.eventId = options.eventId;
	    this.hasDecision = options.hasDecision;
	    this.isPositiveDecision = options.isPositiveDecision;
	    this.hash = options.hash;
	    this.downnoloadLink = options.downloadLink;
	    this.decisionButtonsBlock = document.querySelector('.calendar-pub-event-btn-container');
	    this.titleBlock = document.querySelector('.calendar-pub-event-title-main');
	    this.eventWrapper = document.querySelector('.calendar-pub-event-wrap');
	    this.listBoxWrapper = document.querySelector('.calendar-pub-event-user-list-box');
	    this.decisionBlockWrapper = decisionBlock;
	    this.buttonsContainer = buttonsContainer;
	    this.init();
	  }
	  babelHelpers.createClass(CalendarEvent, [{
	    key: "init",
	    value: function init() {
	      this.initWrappersForButtons();
	      this.initHandler();
	    }
	  }, {
	    key: "initWrappersForButtons",
	    value: function initWrappersForButtons() {
	      this.primaryButtonWrapper = this.buttonsContainer.children[0];
	      this.secondButtonWrapper = this.buttonsContainer.children[1];
	    }
	  }, {
	    key: "initHandler",
	    value: function initHandler() {
	      if (this.hasDecision) {
	        this.initChangeDecisionButton();
	        main_core.Dom.append(this.changeDecisionButton, this.primaryButtonWrapper);
	        if (this.isPositiveDecision) {
	          this.initDownloadButton();
	          main_core.Dom.append(this.downloadButton, this.secondButtonWrapper);
	        }
	      } else {
	        this.initAcceptButton();
	        this.initDeclineButton();
	        main_core.Dom.append(this.acceptDecisionButton, this.primaryButtonWrapper);
	        main_core.Dom.append(this.declineDecisionButton, this.secondButtonWrapper);
	      }
	      this.initListBoxHandlers();
	    }
	  }, {
	    key: "initChangeDecisionButton",
	    value: function initChangeDecisionButton() {
	      var _this = this;
	      this.changeDecisionButton = this.getChangeDecisionButton();
	      this.changeDecisionButton.addEventListener('click', function () {
	        _this.changeStateWithoutDecision();
	      });
	    }
	  }, {
	    key: "initAcceptButton",
	    value: function initAcceptButton() {
	      var _this2 = this;
	      this.acceptDecisionButton = this.getAcceptDecisionButton();
	      this.acceptDecisionButton.addEventListener('click', function () {
	        _this2.changeStateWithDecision(true);
	      });
	    }
	  }, {
	    key: "initDeclineButton",
	    value: function initDeclineButton() {
	      var _this3 = this;
	      this.declineDecisionButton = this.getDeclineDecisionButton();
	      this.declineDecisionButton.addEventListener('click', function () {
	        _this3.changeStateWithDecision(false);
	      });
	    }
	  }, {
	    key: "changeStateWithDecision",
	    value: function changeStateWithDecision(decision) {
	      var _this4 = this;
	      this.hasDecision = true;
	      main_core.Dom.remove(this.acceptDecisionButton);
	      this.acceptDecisionButton = undefined;
	      main_core.Dom.remove(this.declineDecisionButton);
	      this.declineDecisionButton = undefined;
	      this.showChangeDecisionButton();
	      if (decision) {
	        this.showAcceptDecisionBlock();
	        this.showDownloadButton();
	      } else {
	        this.showDeclineDecisionBlock();
	      }
	      this.isPositiveDecision = decision;
	      BX.ajax.runComponentAction('bitrix:calendar.pub.event', 'handleDecision', {
	        mode: 'class',
	        data: {
	          'decision': decision ? 'Y' : 'N',
	          'eventId': this.eventId,
	          'hash': this.hash
	        }
	      }).then(function (response) {
	        if (response.data.attendeesList.length > 0) {
	          _this4.rebuildUserList(response.data.attendeesList);
	        }
	      });
	    }
	  }, {
	    key: "showChangeDecisionButton",
	    value: function showChangeDecisionButton() {
	      if (!this.changeDecisionButton) {
	        this.initChangeDecisionButton();
	      }
	      main_core.Dom.append(this.changeDecisionButton, this.primaryButtonWrapper);
	    }
	  }, {
	    key: "getChangeDecisionButton",
	    value: function getChangeDecisionButton() {
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button id=\"changeDecisionButton\" class=\"ui-btn ui-btn-round ui-btn-lg ui-btn-success calendar-pub-event-btn calendar-pub-event-btn-change-decision\">\n\t\t\t\t", "\n\t\t\t</button>\n\t\t"])), main_core.Loc.getMessage('EC_CALENDAR_CHANGE_DECISION_TITLE'));
	    }
	  }, {
	    key: "showAcceptDecisionBlock",
	    value: function showAcceptDecisionBlock() {
	      var decisionBlock = this.decisionBlockWrapper.children[1];
	      decisionBlock.innerText = main_core.Loc.getMessage('EC_CALENDAR_PUB_EVENT_DECISION_YES');
	      main_core.Dom.removeClass(this.eventWrapper, 'calendar-pub-event--decline ');
	      main_core.Dom.addClass(this.eventWrapper, 'calendar-pub-event--accept');
	    }
	  }, {
	    key: "showDeclineDecisionBlock",
	    value: function showDeclineDecisionBlock() {
	      var decisionBlock = this.decisionBlockWrapper.children[1];
	      decisionBlock.innerText = main_core.Loc.getMessage('EC_CALENDAR_PUB_EVENT_DECISION_NO');
	      main_core.Dom.removeClass(this.eventWrapper, 'calendar-pub-event--accept');
	      main_core.Dom.addClass(this.eventWrapper, 'calendar-pub-event--decline ');
	    }
	  }, {
	    key: "changeStateWithoutDecision",
	    value: function changeStateWithoutDecision() {
	      main_core.Dom.remove(this.changeDecisionButton);
	      this.changeDecisionButton = undefined;
	      if (this.downloadButton) {
	        main_core.Dom.remove(this.downloadButton);
	        this.downloadButton = undefined;
	      }
	      this.showAcceptDecisionButton();
	      this.showDeclineDecisionButton();
	    }
	  }, {
	    key: "showAcceptDecisionButton",
	    value: function showAcceptDecisionButton() {
	      if (!this.acceptDecisionButton) {
	        this.initAcceptButton();
	      }
	      main_core.Dom.append(this.acceptDecisionButton, this.primaryButtonWrapper);
	    }
	  }, {
	    key: "getAcceptDecisionButton",
	    value: function getAcceptDecisionButton() {
	      return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button id=\"acceptDecisionButton\" class=\"ui-btn ui-btn-round ui-btn-lg ui-btn-success calendar-pub-event-btn\">\n\t\t\t\t", "\n\t\t\t</button>\n\t\t"])), main_core.Loc.getMessage('EC_CALENDAR_DECISION_TITLE_YES'));
	    }
	  }, {
	    key: "showDeclineDecisionButton",
	    value: function showDeclineDecisionButton() {
	      if (!this.declineDecisionButton) {
	        this.initDeclineButton();
	      }
	      main_core.Dom.append(this.declineDecisionButton, this.secondButtonWrapper);
	    }
	  }, {
	    key: "getDeclineDecisionButton",
	    value: function getDeclineDecisionButton() {
	      return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<button id=\"declineDecisionButton\" class=\"ui-btn ui-btn-link ui-btn-lg calendar-pub-event-btn\" data-decision=\"N\">\n\t\t\t", "\n\t\t</button>\n\t\t"])), main_core.Loc.getMessage('EC_CALENDAR_DECISION_TITLE_NO'));
	    }
	  }, {
	    key: "initListBoxHandlers",
	    value: function initListBoxHandlers() {
	      this.initAttendeesListBoxHandlers();
	      this.initAttachmentsListBoxHandlers();
	    }
	  }, {
	    key: "initAttendeesListBoxHandlers",
	    value: function initAttendeesListBoxHandlers() {
	      var attendeesListButton = document.querySelector('.calendar-pub-event-user-list-btn');
	      var contentBox = document.querySelector('.calendar-pub-event-user-list-content');
	      if (main_core.Type.isDomNode(attendeesListButton)) {
	        attendeesListButton.addEventListener('click', function () {
	          var contentHeight = contentBox.scrollHeight;
	          contentBox.style.height = contentHeight + 'px';
	          contentBox.style.maxHeight = contentHeight + 'px';
	          attendeesListButton.style.display = 'none';
	        });
	      }
	    }
	  }, {
	    key: "initAttachmentsListBoxHandlers",
	    value: function initAttachmentsListBoxHandlers() {
	      var attachmentBtn = document.querySelector('.calendar-pub-event-user-attachment-btn');
	      var attachmentContentBox = document.querySelector('.calendar-pub-event-user-attachment-content');
	      if (main_core.Type.isDomNode(attachmentBtn)) {
	        attachmentBtn.addEventListener('click', function () {
	          var contentHeight = attachmentContentBox.scrollHeight;
	          attachmentContentBox.style.height = contentHeight + 'px';
	          attachmentContentBox.style.maxHeight = contentHeight + 'px';
	          attachmentBtn.style.display = 'none';
	        });
	      }
	    }
	  }, {
	    key: "initDownloadButton",
	    value: function initDownloadButton() {
	      this.downloadButton = this.getDownloadButton();
	    }
	  }, {
	    key: "showDownloadButton",
	    value: function showDownloadButton() {
	      this.initDownloadButton();
	      main_core.Dom.append(this.downloadButton, this.secondButtonWrapper);
	    }
	  }, {
	    key: "getDownloadButton",
	    value: function getDownloadButton() {
	      return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<a id=\"downloadButton\" href=\"", "\" class=\"ui-btn ui-btn-link ui-btn-lg calendar-pub-event-btn\" >\n\t\t\t", "\n\t\t</a>\n\t\t"])), BX.util.htmlspecialchars(this.downnoloadLink), main_core.Loc.getMessage('EC_CALENDAR_ICAL_INVITATION_DOWNLOAD_INVITATION'));
	    }
	  }, {
	    key: "getDecisionBlock",
	    value: function getDecisionBlock() {
	      if (this.hasDecision) {
	        return document.querySelector('.calendar-pub-event-desc');
	      }
	      return null;
	    }
	  }, {
	    key: "rebuildUserList",
	    value: function rebuildUserList(attendeesList) {
	      var _this5 = this;
	      if (main_core.Type.isArray(attendeesList)) {
	        var userListContainer = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"calendar-pub-event-user-list-content\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), attendeesList.map(function (attendee) {
	          return main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t<div class=\"calendar-pub-event-user-list-item ", "\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t"])), _this5.getAdditionalClassForAttendeesList(attendee['status']), attendee['name']);
	        }));
	        var oldAttendeesContainer = document.querySelector('.calendar-pub-event-user-list-content');
	        var oldAttendeesListButton = document.querySelector('.calendar-pub-event-user-list-btn');
	        if (main_core.Type.isDomNode(oldAttendeesContainer)) {
	          var wrapper = oldAttendeesContainer.parentElement;
	          main_core.Dom.remove(oldAttendeesContainer);
	          if (main_core.Type.isDomNode(oldAttendeesListButton)) {
	            main_core.Dom.remove(oldAttendeesListButton);
	          }
	          main_core.Dom.append(userListContainer, wrapper);
	          if (attendeesList.length > 3) {
	            var attendeesListButton = main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t\t<div data-button=\"users\" class=\"calendar-pub-event-user-list-btn\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t<span>(", ")</span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t"])), main_core.Loc.getMessage('EC_CALENDAR_PUB_EVENT_ALL_ATTENDEES_TITLE'), attendeesList.length);
	            main_core.Dom.append(attendeesListButton, wrapper);
	            this.initAttendeesListBoxHandlers();
	          }
	        }
	      }
	    }
	  }, {
	    key: "getAdditionalClassForAttendeesList",
	    value: function getAdditionalClassForAttendeesList(status) {
	      switch (status) {
	        case 'ACCEPTED':
	          return 'calendar-pub-event-user--accept';
	        case 'DECLINED':
	          return 'calendar-pub-event-user--cancel';
	        default:
	          return 'calendar-pub-event-user--waiting';
	      }
	    }
	  }]);
	  return CalendarEvent;
	}();
	main_core.Reflection.namespace('BX.Calendar.Pub').CalendarEvent = CalendarEvent;

}((this.window = this.window || {}),BX));
//# sourceMappingURL=script.js.map
