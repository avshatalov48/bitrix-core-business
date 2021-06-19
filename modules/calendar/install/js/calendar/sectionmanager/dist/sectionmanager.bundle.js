this.BX = this.BX || {};
(function (exports,calendar_entry,calendar_util,main_core,main_core_events) {
	'use strict';

	var CalendarSection = /*#__PURE__*/function () {
	  function CalendarSection(data) {
	    babelHelpers.classCallCheck(this, CalendarSection);
	    this.updateData(data);
	    this.calendarContext = calendar_util.Util.getCalendarContext();
	    this.sectionManager = this.calendarContext.sectionManager;
	  }

	  babelHelpers.createClass(CalendarSection, [{
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "updateData",
	    value: function updateData(data) {
	      this.data = data || {};
	      this.type = data.CAL_TYPE || '';
	      this.ownerId = parseInt(data.OWNER_ID) || 0;
	      this.id = parseInt(data.ID);
	      this.color = this.data.COLOR;
	      this.name = this.data.NAME;
	    }
	  }, {
	    key: "isShown",
	    value: function isShown() {
	      return this.calendarContext.sectionManager.sectionIsShown(this.id);
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (!this.isShown()) {
	        var hiddenSections = this.calendarContext.sectionManager.getHiddenSections();
	        hiddenSections = BX.util.deleteFromArray(hiddenSections, BX.util.array_search(this.id, hiddenSections));
	        this.calendarContext.sectionManager.setHiddenSections(hiddenSections);
	        BX.userOptions.save('calendar', 'hidden_sections', 'hidden_sections', hiddenSections);
	      }
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      if (this.isShown()) {
	        var hiddenSections = this.calendarContext.sectionManager.getHiddenSections();
	        hiddenSections.push(this.id);
	        this.calendarContext.sectionManager.setHiddenSections(hiddenSections);
	        BX.userOptions.save('calendar', 'hidden_sections', 'hidden_sections', hiddenSections);
	      }
	    }
	  }, {
	    key: "remove",
	    value: function remove() {
	      var _this = this;

	      if (confirm(BX.message('EC_SEC_DELETE_CONFIRM'))) {
	        var EventAlias = calendar_util.Util.getBX().Event;
	        EventAlias.EventEmitter.emit('BX.Calendar.Section:delete', new EventAlias.BaseEvent({
	          data: {
	            sectionId: this.id
	          }
	        }));
	        BX.ajax.runAction('calendar.api.calendarajax.deleteCalendarSection', {
	          data: {
	            id: this.id
	          }
	        }).then(function (response) {
	          var sectionManager = calendar_util.Util.getCalendarContext().sectionManager;
	          var reload = true;
	          var section;

	          for (var i = 0; i < sectionManager.sections.length; i++) {
	            section = sectionManager.sections[i];

	            if (section.id !== _this.id && section.belongsToView()) {
	              reload = false;
	              break;
	            }
	          }

	          var calendar = calendar_util.Util.getCalendarContext();

	          if (!calendar || reload) {
	            return calendar_util.Util.getBX().reload();
	          }

	          calendar.reload();
	        }, function (response) {// this.calendar.displayError(response.errors);
	        });
	      }
	    }
	  }, {
	    key: "hideGoogle",
	    value: function hideGoogle() {
	      if (confirm(BX.message('EC_CAL_GOOGLE_HIDE_CONFIRM'))) {
	        this.hide();
	        BX.onCustomEvent(this.calendar, 'BXCalendar:onSectionDelete', [this.id]);
	        calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar.Section:delete', new main_core.Event.BaseEvent({
	          data: {
	            sectionId: this.id
	          }
	        }));
	        BX.ajax.runAction('calendar.api.calendarajax.hideExternalCalendarSection', {
	          data: {
	            id: this.id
	          }
	        }).then( // Success
	        BX.delegate(function (response) {
	          this.calendar.reload();
	        }, this), // Failure
	        BX.delegate(function (response) {
	          this.calendar.displayError(response.errors);
	        }, this));
	      }
	    }
	  }, {
	    key: "getLink",
	    value: function getLink() {
	      return this.data && this.data.LINK ? this.data.LINK : '';
	    }
	  }, {
	    key: "canBeConnectedToOutlook",
	    value: function canBeConnectedToOutlook() {
	      return !this.isPseudo() && this.data.OUTLOOK_JS && !(this.data.CAL_DAV_CAL && this.data.CAL_DAV_CON) && !BX.browser.IsMac();
	    }
	  }, {
	    key: "connectToOutlook",
	    value: function connectToOutlook() {
	      if (!window.jsOutlookUtils) {
	        BX.loadScript('/bitrix/js/calendar/outlook.js', BX.delegate(function () {
	          try {
	            eval(this.data.OUTLOOK_JS);
	          } catch (e) {}
	        }, this));
	      } else {
	        try {
	          eval(this.data.OUTLOOK_JS);
	        } catch (e) {}
	      }
	    }
	  }, {
	    key: "canDo",
	    value: function canDo(action) {
	      //action: access|add|edit|edit_section|view_full|view_time|view_title
	      if (this.isVirtual() && ['access', 'add', 'edit'].includes(action)) {
	        return false;
	      }

	      if (action === 'view_event') {
	        action = 'view_time';
	      }

	      return this.data.PERM && this.data.PERM[action];
	    }
	  }, {
	    key: "isSuperposed",
	    value: function isSuperposed() {
	      return !this.isPseudo() && !!this.data.SUPERPOSED;
	    }
	  }, {
	    key: "isPseudo",
	    value: function isPseudo() {
	      return false;
	    }
	  }, {
	    key: "isVirtual",
	    value: function isVirtual() {
	      return this.data.CAL_DAV_CAL && this.data.CAL_DAV_CAL.indexOf('@virtual/events/') !== -1 || this.data.GAPI_CALENDAR_ID && this.data.GAPI_CALENDAR_ID.indexOf('@group.v.calendar.google.com') !== -1 || this.data.EXTERNAL_TYPE === 'google_readonly' || this.data.EXTERNAL_TYPE === 'google_freebusy';
	    }
	  }, {
	    key: "isGoogle",
	    value: function isGoogle() {
	      return this.data.GAPI_CALENDAR_ID;
	    }
	  }, {
	    key: "isCalDav",
	    value: function isCalDav() {
	      return !this.isPseudo() && this.data.CAL_DAV_CAL && this.data.CAL_DAV_CON;
	    }
	  }, {
	    key: "isCompanyCalendar",
	    value: function isCompanyCalendar() {
	      return !this.isPseudo() && this.type !== 'user' && this.type !== 'group' && !this.ownerId;
	    }
	  }, {
	    key: "belongsToView",
	    value: function belongsToView() {
	      var calendarContext = calendar_util.Util.getCalendarContext();
	      return this.type === calendarContext.getCalendarType() && this.ownerId === calendarContext.getOwnerId();
	    }
	  }, {
	    key: "belongsToOwner",
	    value: function belongsToOwner() {
	      return this.belongsToUser(calendar_util.Util.getCalendarContext().getUserId());
	    }
	  }, {
	    key: "belongsToUser",
	    value: function belongsToUser(userId) {
	      return this.type === 'user' && this.ownerId === parseInt(userId) && this.data.ACTIVE !== 'N';
	    }
	  }, {
	    key: "isActive",
	    value: function isActive() {
	      return this.data.ACTIVE !== 'N';
	    }
	  }, {
	    key: "getType",
	    value: function getType() {
	      return this.type;
	    }
	  }, {
	    key: "getOwnerId",
	    value: function getOwnerId() {
	      return this.ownerId;
	    }
	  }]);
	  return CalendarSection;
	}();

	var CalendarTaskSection = /*#__PURE__*/function (_CalendarSection) {
	  babelHelpers.inherits(CalendarTaskSection, _CalendarSection);

	  function CalendarTaskSection() {
	    var data = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

	    var _ref = arguments.length > 1 ? arguments[1] : undefined,
	        type = _ref.type,
	        userId = _ref.userId,
	        ownerId = _ref.ownerId;

	    babelHelpers.classCallCheck(this, CalendarTaskSection);
	    var defaultColor = '#ff5b55';
	    var defaultName = main_core.Loc.getMessage('EC_SEC_MY_TASK_DEFAULT');

	    if (type === 'user' && userId !== ownerId) {
	      defaultName = main_core.Loc.getMessage('EC_SEC_USER_TASK_DEFAULT');
	    } else if (type === 'group') {
	      defaultName = main_core.Loc.getMessage('EC_SEC_GROUP_TASK_DEFAULT');
	    }

	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CalendarTaskSection).call(this, {
	      ID: 'tasks',
	      NAME: data.name || defaultName,
	      COLOR: data.color || defaultColor,
	      PERM: {
	        edit_section: true,
	        view_full: true,
	        view_time: true,
	        view_title: true
	      }
	    }));
	  }

	  babelHelpers.createClass(CalendarTaskSection, [{
	    key: "isPseudo",
	    value: function isPseudo() {
	      return true;
	    }
	  }, {
	    key: "updateData",
	    value: function updateData(data) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(CalendarTaskSection.prototype), "updateData", this).call(this, data);
	      this.id = data.ID;
	    }
	  }]);
	  return CalendarTaskSection;
	}(CalendarSection);

	var SectionManager = /*#__PURE__*/function () {
	  function SectionManager(data, config) {
	    babelHelpers.classCallCheck(this, SectionManager);
	    this.setSectons(data.sections);
	    this.setConfig(config);
	    this.addTaskSection();
	    main_core_events.EventEmitter.subscribeOnce('BX.Calendar.Section:delete', this.deleteSectionHandler.bind(this)); //BX.addCustomEvent("BXCalendar:onSectionDelete", BX.proxy(this.unsetSectionHandler, this));
	  }

	  babelHelpers.createClass(SectionManager, [{
	    key: "setSectons",
	    value: function setSectons() {
	      var _this = this;

	      var rawSections = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	      this.sections = [];
	      this.sectionIndex = {};
	      rawSections.forEach(function (sectionData) {
	        var section = new CalendarSection(sectionData);

	        if (section.canDo('view_time')) {
	          _this.sections.push(section);

	          _this.sectionIndex[section.getId()] = _this.sections.length - 1;
	        }
	      });
	    }
	  }, {
	    key: "sortSections",
	    value: function sortSections() {
	      var _this2 = this;

	      this.sectionIndex = {};
	      this.sections = this.sections.sort(function (a, b) {
	        if (main_core.Type.isFunction(a.isPseudo) && a.isPseudo()) {
	          return 1;
	        } else if (main_core.Type.isFunction(b.isPseudo) && b.isPseudo()) {
	          return -1;
	        }

	        return a.name.localeCompare(b.name);
	      });
	      this.sections.forEach(function (section, index) {
	        _this2.sectionIndex[section.getId()] = index;
	      });
	    }
	  }, {
	    key: "setConfig",
	    value: function setConfig(config) {
	      this.hiddenSections = config.hiddenSections || [];
	      this.calendarType = config.type;
	      this.ownerId = config.ownerId;
	      this.userId = config.userId;
	      this.defaultSectionAccess = config.new_section_access || {};
	      this.sectionAccessTasks = config.sectionAccessTasks;
	      this.showTasks = config.showTasks;
	      this.customizationData = config.sectionCustomization || {};
	    }
	  }, {
	    key: "addTaskSection",
	    value: function addTaskSection() {
	      if (this.showTasks) {
	        var taskSection = new CalendarTaskSection(this.customizationData['tasks' + this.ownerId], {
	          type: this.calendarType,
	          userId: this.userId,
	          ownerId: this.ownerId
	        });
	        this.sections.push(taskSection);
	        this.sectionIndex[taskSection.id] = this.sections.length - 1;
	      }
	    }
	  }, {
	    key: "getCalendarType",
	    value: function getCalendarType() {
	      return this.calendarType;
	    }
	  }, {
	    key: "handlePullChanges",
	    value: function handlePullChanges(params) {
	      var _params$fields;

	      if ((params === null || params === void 0 ? void 0 : (_params$fields = params.fields) === null || _params$fields === void 0 ? void 0 : _params$fields.CAL_TYPE) === 'location') {
	        this.handleLocationPullChanges(params);
	      } else if (params.command === 'delete_section') {
	        var sectionId = parseInt(params.fields.ID, 10);

	        if (this.sectionIndex[sectionId]) {
	          this.deleteSectionHandler(sectionId);
	          calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar.Section:pull-delete', new main_core.Event.BaseEvent({
	            data: {
	              sectionId: sectionId
	            }
	          }));
	        } else {
	          this.reloadData();
	        }
	      } else if (params.command === 'edit_section') {
	        this.reloadData().then(function () {
	          calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar.Section:pull-edit');
	        });
	        calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar:doRefresh');
	      } else {
	        this.reloadData();
	      }
	    }
	  }, {
	    key: "handleLocationPullChanges",
	    value: function handleLocationPullChanges(params) {}
	  }, {
	    key: "reloadData",
	    value: function reloadData() {
	      var _this3 = this;

	      return new Promise(function (resolve) {
	        BX.ajax.runAction('calendar.api.calendarajax.getSectionList', {
	          data: {
	            'type': _this3.calendarType,
	            'ownerId': _this3.ownerId
	          }
	        }).then(function (response) {
	          _this3.setSectons(response.data.sections || []);

	          if (response.data.config) {
	            _this3.setConfig(config);
	          }

	          _this3.addTaskSection();

	          resolve(response.data);
	        }, // Failure
	        function (response) {
	          //this.calendar.displayError(response.errors);
	          resolve(response.data);
	        });
	      });
	    }
	  }, {
	    key: "getSections",
	    value: function getSections() {
	      return this.sections;
	    }
	  }, {
	    key: "getSuperposedSectionList",
	    value: function getSuperposedSectionList() {
	      var i,
	          result = [];

	      for (i = 0; i < this.sections.length; i++) {
	        if (this.sections[i].isSuperposed() && this.sections[i].isActive()) {
	          result.push(this.sections[i]);
	        }
	      }

	      return result;
	    }
	  }, {
	    key: "getSectionListForEdit",
	    value: function getSectionListForEdit() {
	      var i,
	          result = [];

	      for (i = 0; i < this.sections.length; i++) {
	        if (this.sections[i].canDo('add') && !this.sections[i].isPseudo() && this.sections[i].isActive()) {
	          result.push(this.sections[i]);
	        }
	      }

	      return result;
	    }
	  }, {
	    key: "getSection",
	    value: function getSection(id) {
	      return this.sections[this.sectionIndex[id]] || {};
	    }
	  }, {
	    key: "getDefaultSectionName",
	    value: function getDefaultSectionName() {
	      return main_core.Loc.getMessage('EC_DEFAULT_SECTION_NAME');
	    }
	  }, {
	    key: "getDefaultSectionAccess",
	    value: function getDefaultSectionAccess() {
	      return this.defaultSectionAccess; // return this.calendar.util.config.new_section_access || {};
	    }
	  }, {
	    key: "saveSection",
	    value: function saveSection(name, color, access, params) {
	      var _this4 = this;

	      return new Promise(function (resolve) {
	        name = main_core.Type.isString(name) && name.trim() ? name.trim() : main_core.Loc.getMessage('EC_SEC_SLIDER_NEW_SECTION');

	        if (params.section.id) ;

	        var isCustomization = params.section.id && params.section.isPseudo();
	        BX.ajax.runAction('calendar.api.calendarajax.editCalendarSection', {
	          data: {
	            analyticsLabel: {
	              action: params.section.id ? 'editSection' : 'newSection',
	              type: params.section.type || _this4.calendarType
	            },
	            id: params.section.id || 0,
	            name: name,
	            type: params.section.type || _this4.calendarType,
	            ownerId: params.section.ownerId || _this4.ownerId,
	            color: color,
	            access: access || null,
	            userId: _this4.userId,
	            customization: isCustomization ? 'Y' : 'N'
	          }
	        }).then(function (response) {
	          if (isCustomization) {
	            BX.reload();
	            return;
	          }

	          var sectionList = response.data.sectionList || [];

	          _this4.setSectons(sectionList);

	          _this4.sortSections();

	          calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar.Section:edit', new main_core.Event.BaseEvent({
	            data: {
	              sectionList: sectionList
	            }
	          }));
	          resolve(response.data);
	        }, function (response) {
	          resolve(response.data);
	        });
	      });
	    }
	  }, {
	    key: "sectionIsShown",
	    value: function sectionIsShown(id) {
	      return !BX.util.in_array(id, this.hiddenSections);
	    }
	  }, {
	    key: "getHiddenSections",
	    value: function getHiddenSections() {
	      return this.hiddenSections || [];
	    }
	  }, {
	    key: "setHiddenSections",
	    value: function setHiddenSections(hiddenSections) {
	      this.hiddenSections = hiddenSections;
	    }
	  }, {
	    key: "getSectionsInfo",
	    value: function getSectionsInfo() {
	      var allActive = [];
	      var superposed = [];
	      var active = [];
	      var hidden = [];
	      this.sections.forEach(function (section) {
	        if (section.isShown()) {
	          if (section.isSuperposed()) {
	            superposed.push(section.id);
	          } else {
	            active.push(section.id);
	          }

	          allActive.push(section.id);
	        } else {
	          hidden.push(section.id);
	        }
	      });
	      return {
	        superposed: superposed,
	        active: active,
	        hidden: hidden,
	        allActive: allActive
	      };
	    }
	  }, {
	    key: "deleteSectionHandler",
	    value: function deleteSectionHandler(sectionId) {
	      if (this.sectionIndex[sectionId] !== undefined) {
	        this.sections = BX.util.deleteFromArray(this.sections, this.sectionIndex[sectionId]);

	        for (var i = 0; i < this.sections.length; i++) {
	          this.sectionIndex[this.sections[i].id] = i;
	        }
	      }
	    }
	  }, {
	    key: "getSectionAccessTasks",
	    value: function getSectionAccessTasks() {
	      return this.sectionAccessTasks;
	    }
	  }, {
	    key: "getDefaultSection",
	    value: function getDefaultSection() {
	      var calendarType = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	      var ownerId = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      calendarType = main_core.Type.isString(calendarType) ? calendarType : this.calendarType;
	      ownerId = main_core.Type.isNumber(ownerId) ? ownerId : this.ownerId;
	      var userSettings = calendar_util.Util.getUserSettings();
	      var key = calendarType + ownerId;
	      var defaultSectionId = userSettings.defaultSections[key] || userSettings.lastUsedSection;
	      var sections = this.getSectionListForEdit();
	      var section = sections.find(function (item) {
	        return item.type === calendarType && item.ownerId === ownerId && item.id === defaultSectionId;
	      });

	      if (!section) {
	        section = sections.find(function (item) {
	          return item.type === calendarType && item.ownerId === ownerId;
	        });
	      }

	      return section;
	    }
	  }, {
	    key: "setDefaultSection",
	    value: function setDefaultSection(sectionId) {
	      var section = this.getSection(parseInt(sectionId, 10));

	      if (section && section.type === this.calendarType && section.ownerId === this.ownerId) {
	        var userSettings = calendar_util.Util.getUserSettings();
	        var key = this.calendarType + this.ownerId;

	        if (userSettings.defaultSections[key] !== section.id) {
	          userSettings.defaultSections[key] = section.id;
	          calendar_util.Util.setUserSettings(userSettings);
	          BX.ajax.runAction('calendar.api.calendarajax.updateDefaultSectionId', {
	            data: {
	              'key': key,
	              'sectionId': sectionId
	            }
	          });
	        }
	      }
	    }
	  }], [{
	    key: "getNewEntrySectionId",
	    value: function getNewEntrySectionId() {
	      var calendarType = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	      var ownerId = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      var calendarContext = calendar_util.Util.getCalendarContext();

	      if (calendarContext && !calendarContext.isExternalMode()) {
	        var section = calendarContext.sectionManager.getDefaultSection(calendarType, ownerId);
	        return parseInt(section.id, 10);
	      }

	      return SectionManager.newEntrySectionId;
	    }
	  }, {
	    key: "setNewEntrySectionId",
	    value: function setNewEntrySectionId(sectionId) {
	      SectionManager.newEntrySectionId = parseInt(sectionId);
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
	        });
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
	  }, {
	    key: "saveDefaultSectionId",
	    value: function saveDefaultSectionId(sectionId) {
	      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var calendarContext = calendar_util.Util.getCalendarContext();

	      if (calendarContext) {
	        calendarContext.sectionManager.setDefaultSection(sectionId);
	      } else {
	        if (main_core.Type.isArray(options.sections) && options.calendarType && options.ownerId) {
	          var section = options.sections.find(function (item) {
	            var id = parseInt(item.ID || item.id, 10);
	            var ownerId = parseInt(item.OWNER_ID || item.ownerId, 10);
	            var type = item.CAL_TYPE || item.type;
	            return id === parseInt(sectionId, 10) && ownerId === parseInt(options.ownerId, 10) && type === options.calendarType;
	          });

	          if (section) {
	            var userSettings = calendar_util.Util.getUserSettings();
	            var key = options.calendarType + options.ownerId;

	            if (userSettings && userSettings.defaultSections[key] !== sectionId) {
	              userSettings.defaultSections[key] = sectionId;
	              calendar_util.Util.setUserSettings(userSettings);
	              SectionManager.newEntrySectionId = sectionId;
	              BX.ajax.runAction('calendar.api.calendarajax.updateDefaultSectionId', {
	                data: {
	                  'key': key,
	                  'sectionId': sectionId
	                }
	              });
	            }
	          }
	        }
	      }
	    }
	  }]);
	  return SectionManager;
	}();
	babelHelpers.defineProperty(SectionManager, "newEntrySectionId", null);

	exports.CalendarSection = CalendarSection;
	exports.SectionManager = SectionManager;

}((this.BX.Calendar = this.BX.Calendar || {}),BX.Calendar,BX.Calendar,BX,BX.Event));
//# sourceMappingURL=sectionmanager.bundle.js.map
