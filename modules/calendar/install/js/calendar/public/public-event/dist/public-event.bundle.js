/* eslint-disable */
this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
(function (exports,main_core,calendar_sharing_publicV2,calendar_util) {
	'use strict';

	var _params = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("params");
	var _icsFile = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("icsFile");
	var _handleAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleAction");
	var _getLayoutProps = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getLayoutProps");
	var _prepareMembers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareMembers");
	var _getTitle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getTitle");
	var _getIconClassName = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getIconClassName");
	var _getBottomButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getBottomButtons");
	var _acceptInvitation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("acceptInvitation");
	var _declineInvitation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("declineInvitation");
	var _handleDecisionAction = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleDecisionAction");
	var _getStatus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStatus");
	var _updateStatus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateStatus");
	var _getOwner = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getOwner");
	var _downloadIcsFile = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("downloadIcsFile");
	class PublicEvent {
	  constructor(params) {
	    Object.defineProperty(this, _downloadIcsFile, {
	      value: _downloadIcsFile2
	    });
	    Object.defineProperty(this, _getOwner, {
	      value: _getOwner2
	    });
	    Object.defineProperty(this, _updateStatus, {
	      value: _updateStatus2
	    });
	    Object.defineProperty(this, _getStatus, {
	      value: _getStatus2
	    });
	    Object.defineProperty(this, _handleDecisionAction, {
	      value: _handleDecisionAction2
	    });
	    Object.defineProperty(this, _declineInvitation, {
	      value: _declineInvitation2
	    });
	    Object.defineProperty(this, _acceptInvitation, {
	      value: _acceptInvitation2
	    });
	    Object.defineProperty(this, _getBottomButtons, {
	      value: _getBottomButtons2
	    });
	    Object.defineProperty(this, _getIconClassName, {
	      value: _getIconClassName2
	    });
	    Object.defineProperty(this, _getTitle, {
	      value: _getTitle2
	    });
	    Object.defineProperty(this, _prepareMembers, {
	      value: _prepareMembers2
	    });
	    Object.defineProperty(this, _getLayoutProps, {
	      value: _getLayoutProps2
	    });
	    Object.defineProperty(this, _handleAction, {
	      value: _handleAction2
	    });
	    Object.defineProperty(this, _params, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _icsFile, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _params)[_params] = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _handleAction)[_handleAction](params.action);
	  }
	  render() {
	    const eventLayout = new calendar_sharing_publicV2.EventLayout(babelHelpers.classPrivateFieldLooseBase(this, _getLayoutProps)[_getLayoutProps]());
	    babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].container.innerHTML = '';
	    babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].container.append(eventLayout.render());
	  }
	}
	function _handleAction2(action) {
	  if (action === 'accept') {
	    babelHelpers.classPrivateFieldLooseBase(this, _handleDecisionAction)[_handleDecisionAction]('Y');
	  }
	  if (action === 'decline') {
	    babelHelpers.classPrivateFieldLooseBase(this, _handleDecisionAction)[_handleDecisionAction]('N');
	  }
	  if (action === 'ics') {
	    babelHelpers.classPrivateFieldLooseBase(this, _downloadIcsFile)[_downloadIcsFile]();
	  }
	}
	function _getLayoutProps2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event) {
	    return {
	      eventNotFound: {
	        title: main_core.Loc.getMessage('CALENDAR_PUBLIC_EVENT_TITLE_NOT_ATTENDEES'),
	        subtitle: main_core.Loc.getMessage('CALENDAR_PUBLIC_EVENT_DESCRIPTION_NOT_ATTENDEES')
	      }
	    };
	  }
	  let offset = 0;
	  if (babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event.timezone) {
	    offset = (calendar_util.Util.getTimeZoneOffset() - calendar_util.Util.getTimeZoneOffset(babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event.timezone)) * 60 * 1000;
	  }
	  return {
	    eventName: babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event.name,
	    from: new Date(parseInt(babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event.timestampFrom) * 1000 + offset),
	    to: new Date(parseInt(babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event.timestampTo) * 1000 + offset),
	    timezone: babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event.timezone,
	    browserTimezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
	    isFullDay: babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event.isFullDay,
	    location: babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event.location,
	    description: babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event.description,
	    rruleDescription: babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event.rruleDescription,
	    members: babelHelpers.classPrivateFieldLooseBase(this, _prepareMembers)[_prepareMembers](),
	    files: babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event.files,
	    allAttendees: true,
	    filled: true,
	    onDeclineEvent: babelHelpers.classPrivateFieldLooseBase(this, _getStatus)[_getStatus]() === 'Y' ? babelHelpers.classPrivateFieldLooseBase(this, _declineInvitation)[_declineInvitation].bind(this) : null,
	    title: babelHelpers.classPrivateFieldLooseBase(this, _getTitle)[_getTitle](),
	    iconClassName: babelHelpers.classPrivateFieldLooseBase(this, _getIconClassName)[_getIconClassName](),
	    bottomButtons: babelHelpers.classPrivateFieldLooseBase(this, _getBottomButtons)[_getBottomButtons](),
	    poweredLabel: {
	      isRu: babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].isRu
	    }
	  };
	}
	function _prepareMembers2() {
	  var _babelHelpers$classPr;
	  if (((_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event.members) == null ? void 0 : _babelHelpers$classPr.length) === 1 && babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event.members[0].isOwner) {
	    return [];
	  }
	  return [...babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event.members].sort((member1, member2) => {
	    const value1 = member1.isOwner ? 1 : 0;
	    const value2 = member2.isOwner ? 1 : 0;
	    return value2 - value1;
	  });
	}
	function _getTitle2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event.isDeleted || !babelHelpers.classPrivateFieldLooseBase(this, _getStatus)[_getStatus]()) {
	    return main_core.Loc.getMessage('CALENDAR_PUBLIC_EVENT_MEETING_IS_CANCELLED');
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _getStatus)[_getStatus]() === 'Q') {
	    return main_core.Loc.getMessage('CALENDAR_PUBLIC_EVENT_YOU_WAS_INVITED');
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _getStatus)[_getStatus]() === 'Y') {
	    return main_core.Loc.getMessage('CALENDAR_PUBLIC_EVENT_YOU_ACCEPTED_MEETING');
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _getStatus)[_getStatus]() === 'N') {
	    return main_core.Loc.getMessage('CALENDAR_PUBLIC_EVENT_YOU_DECLINED_MEETING');
	  }
	  return '';
	}
	function _getIconClassName2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _getStatus)[_getStatus]() === 'N' || !babelHelpers.classPrivateFieldLooseBase(this, _getStatus)[_getStatus]() || babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event.isDeleted) {
	    return '--decline';
	  }
	  return '--accept';
	}
	function _getBottomButtons2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event.isDeleted) {
	    return {};
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _getStatus)[_getStatus]() === 'Q') {
	    return {
	      onAcceptInvitation: babelHelpers.classPrivateFieldLooseBase(this, _acceptInvitation)[_acceptInvitation].bind(this),
	      onDeclineInvitation: babelHelpers.classPrivateFieldLooseBase(this, _declineInvitation)[_declineInvitation].bind(this)
	    };
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _getStatus)[_getStatus]() === 'Y') {
	    return {
	      onDownloadIcs: babelHelpers.classPrivateFieldLooseBase(this, _downloadIcsFile)[_downloadIcsFile].bind(this)
	    };
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _getStatus)[_getStatus]() === 'N') {
	    return {
	      onAcceptInvitation: babelHelpers.classPrivateFieldLooseBase(this, _acceptInvitation)[_acceptInvitation].bind(this)
	    };
	  }
	  return {};
	}
	function _acceptInvitation2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _handleDecisionAction)[_handleDecisionAction]('Y');
	}
	function _declineInvitation2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _handleDecisionAction)[_handleDecisionAction]('N');
	}
	function _handleDecisionAction2(decision) {
	  BX.ajax.runAction('calendar.api.publicevent.handleDecision', {
	    data: {
	      decision,
	      eventId: babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event.id,
	      hash: babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event.hash
	    }
	  }).then(response => {
	    babelHelpers.classPrivateFieldLooseBase(this, _updateStatus)[_updateStatus](response.data);
	  });
	}
	function _getStatus2() {
	  const owner = babelHelpers.classPrivateFieldLooseBase(this, _getOwner)[_getOwner]();
	  return owner.status;
	}
	function _updateStatus2(status) {
	  const owner = babelHelpers.classPrivateFieldLooseBase(this, _getOwner)[_getOwner]();
	  owner.status = status;
	  this.render();
	}
	function _getOwner2() {
	  var _babelHelpers$classPr2;
	  return (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event.members.find(member => member.isOwner)) != null ? _babelHelpers$classPr2 : {};
	}
	async function _downloadIcsFile2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _icsFile)[_icsFile]) {
	    const response = await BX.ajax.runAction('calendar.api.publicevent.getIcsFileContent', {
	      data: {
	        eventId: babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event.id,
	        hash: babelHelpers.classPrivateFieldLooseBase(this, _params)[_params].event.hash
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _icsFile)[_icsFile] = response.data;
	  }
	  calendar_util.Util.downloadIcsFile(babelHelpers.classPrivateFieldLooseBase(this, _icsFile)[_icsFile], 'event');
	}

	exports.PublicEvent = PublicEvent;

}((this.BX.Calendar.Public = this.BX.Calendar.Public || {}),BX,BX.Calendar.Sharing,BX.Calendar));
//# sourceMappingURL=public-event.bundle.js.map
