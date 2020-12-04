this.BX = this.BX || {};
(function (exports,calendar_entry,calendar_util,main_core) {
	'use strict';

	var CalendarSectionManager = /*#__PURE__*/function () {
	  function CalendarSectionManager() {
	    babelHelpers.classCallCheck(this, CalendarSectionManager);
	  }

	  babelHelpers.createClass(CalendarSectionManager, null, [{
	    key: "getNewEntrySectionId",
	    value: function getNewEntrySectionId() {
	      return CalendarSectionManager.newEntrySectionId;
	    }
	  }, {
	    key: "setNewEntrySectionId",
	    value: function setNewEntrySectionId(sectionId) {
	      CalendarSectionManager.newEntrySectionId = parseInt(sectionId);
	    }
	  }, {
	    key: "getSectionGroupList",
	    value: function getSectionGroupList() {
	      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var type = options.type,
	          ownerId = options.ownerId,
	          userId = options.userId,
	          followedUserList = options.trackingUsersList || calendar_util.Util.getFollowedUserList(userId),
	          sectionGroups = [],
	          title; // 1. Main group - depends from current view

	      if (type === 'user') {
	        if (userId === ownerId) {
	          title = main_core.Loc.getMessage('EC_SEC_SLIDER_MY_CALENDARS_LIST');
	        } else {
	          title = main_core.Loc.getMessage('EC_SEC_SLIDER_USER_CALENDARS_LIST');
	        }
	      } else if (type === 'group') {
	        title = main_core.Loc.getMessage('EC_SEC_SLIDER_GROUP_CALENDARS_LIST');
	      } else if (type === 'location') {
	        title = main_core.Loc.getMessage('EC_SEC_SLIDER_TYPE_LOCATION_LIST');
	      } else if (type === 'resource') {
	        title = main_core.Loc.getMessage('EC_SEC_SLIDER_TYPE_RESOURCE_LIST');
	      } else {
	        title = main_core.Loc.getMessage('EC_SEC_SLIDER_TITLE_COMP_CAL');
	      }

	      sectionGroups.push({
	        title: title,
	        type: type,
	        belongsToView: true
	      });

	      if (type !== 'user' || userId !== ownerId) {
	        sectionGroups.push({
	          title: main_core.Loc.getMessage('EC_SEC_SLIDER_MY_CALENDARS_LIST'),
	          type: 'user',
	          ownerId: userId
	        });
	      } // 2. Company calendar


	      if (type !== 'company' && type !== 'company_calendar') {
	        sectionGroups.push({
	          title: main_core.Loc.getMessage('EC_SEC_SLIDER_TITLE_COMP_CAL'),
	          type: 'company'
	        });
	      } // 3. Users calendars


	      if (main_core.Type.isArray(followedUserList)) {
	        followedUserList.forEach(function (user) {
	          if (parseInt(user.ID) !== ownerId || type !== 'user') {
	            sectionGroups.push({
	              title: BX.util.htmlspecialchars(user.FORMATTED_NAME),
	              type: 'user',
	              ownerId: parseInt(user.ID)
	            });
	          }
	        }, this);
	      } // 4. Groups calendars


	      sectionGroups.push({
	        title: main_core.Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_GROUP'),
	        type: 'group'
	      }); // 5. Resources calendars

	      sectionGroups.push({
	        title: main_core.Loc.getMessage('EC_SEC_SLIDER_TITLE_RESOURCE_CAL'),
	        type: 'resource'
	      }); // 6. Location calendars

	      sectionGroups.push({
	        title: main_core.Loc.getMessage('EC_SEC_SLIDER_TITLE_LOCATION_CAL'),
	        type: 'location'
	      });
	      return sectionGroups;
	    }
	  }]);
	  return CalendarSectionManager;
	}();
	babelHelpers.defineProperty(CalendarSectionManager, "newEntrySectionId", null);

	var CalendarSection = function CalendarSection() {
	  babelHelpers.classCallCheck(this, CalendarSection);
	};

	exports.CalendarSectionManager = CalendarSectionManager;
	exports.CalendarSection = CalendarSection;

}((this.BX.Calendar = this.BX.Calendar || {}),BX.Calendar,BX.Calendar,BX));
//# sourceMappingURL=calendarsection.bundle.js.map
