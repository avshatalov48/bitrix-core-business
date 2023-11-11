this.BX = this.BX || {};
(function (exports,calendar_util,calendar_sectionmanager,main_core,main_core_events) {
	'use strict';

	class CalendarSection {
	  constructor(data) {
	    this.updateData(data);
	    this.calendarContext = calendar_util.Util.getCalendarContext();
	  }
	  getId() {
	    return this.id;
	  }
	  updateData(data) {
	    this.data = data || {};
	    this.type = data.CAL_TYPE || '';
	    this.ownerId = parseInt(data.OWNER_ID) || 0;
	    this.id = parseInt(data.ID);
	    this.color = this.data.COLOR;
	    this.name = this.data.NAME;
	  }
	  isShown() {
	    return this.calendarContext.sectionManager.sectionIsShown(this.id);
	  }
	  show() {
	    if (!this.isShown()) {
	      let hiddenSections = this.calendarContext.sectionManager.getHiddenSections();
	      hiddenSections = hiddenSections.filter(sectionId => {
	        return sectionId !== this.id;
	      }, this);
	      this.calendarContext.sectionManager.setHiddenSections(hiddenSections);
	      this.calendarContext.sectionManager.saveHiddenSections();
	    }
	  }
	  hide() {
	    if (this.isShown()) {
	      const hiddenSections = this.calendarContext.sectionManager.getHiddenSections();
	      hiddenSections.push(this.id);
	      this.calendarContext.sectionManager.setHiddenSections(hiddenSections);
	      this.calendarContext.sectionManager.saveHiddenSections();
	    }
	  }
	  remove() {
	    const EventAlias = calendar_util.Util.getBX().Event;
	    EventAlias.EventEmitter.emit('BX.Calendar.Section:delete', new EventAlias.BaseEvent({
	      data: {
	        sectionId: this.id
	      }
	    }));
	    BX.ajax.runAction('calendar.api.calendarajax.deleteCalendarSection', {
	      data: {
	        id: this.id
	      }
	    }).then(response => {
	      return this.updateListAfterDelete();
	    }, response => {
	      // this.calendar.displayError(response.errors);
	    });
	  }
	  hideSyncSection() {
	    this.hide();
	    BX.onCustomEvent(this.calendar, 'BXCalendar:onSectionDelete', [this.id]);
	    calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar.Section:delete', new main_core.Event.BaseEvent({
	      data: {
	        sectionId: this.id
	      }
	    }));

	    //hideExternalCalendarSection
	    BX.ajax.runAction('calendar.api.calendarajax.setSectionStatus', {
	      data: {
	        sectionStatus: {
	          [this.id]: false
	        }
	      }
	    }).then(response => {
	      return this.updateListAfterDelete();
	    }, response => {
	      // this.calendar.displayError(response.errors);
	    });
	  }
	  hideExternalCalendarSection() {
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
	    }).then(response => {
	      return this.updateListAfterDelete();
	    }, response => {
	      // this.calendar.displayError(response.errors);
	    });
	  }
	  getLink() {
	    return this.data && this.data.LINK ? this.data.LINK : '';
	  }
	  canBeConnectedToOutlook() {
	    return !this.isPseudo() && this.data.OUTLOOK_JS && !(this.data.CAL_DAV_CAL && this.data.CAL_DAV_CON) && !BX.browser.IsMac();
	  }
	  connectToOutlook() {
	    BX.ajax.runAction('calendar.api.syncajax.getOutlookLink', {
	      data: {
	        id: this.id
	      }
	    }).then(response => {
	      const url = response.data.result;
	      eval(url);
	    }, response => {
	      // this.calendar.displayError(response.errors);
	    });
	  }
	  canDo(action) {
	    //action: access|add|edit|edit_section|view_full|view_time|view_title
	    if (this.isVirtual() && ['access', 'add', 'edit'].includes(action)) {
	      return false;
	    }
	    return this.hasPermission(action);
	  }
	  hasPermission(action) {
	    if (action === 'view_event') {
	      action = 'view_time';
	    }
	    if (!this.data.PERM[action]) {
	      return false;
	    }
	    return this.data.PERM && this.data.PERM[action];
	  }
	  isSuperposed() {
	    return !this.isPseudo() && !!this.data.SUPERPOSED;
	  }
	  isPseudo() {
	    return false;
	  }
	  isVirtual() {
	    return this.data.CAL_DAV_CAL && this.data.CAL_DAV_CAL.indexOf('@virtual/events/') !== -1 || this.data.GAPI_CALENDAR_ID && this.data.GAPI_CALENDAR_ID.indexOf('@group.v.calendar.google.com') !== -1 || this.data.EXTERNAL_TYPE === 'google_readonly' || this.data.EXTERNAL_TYPE === 'google_freebusy';
	  }
	  isGoogle() {
	    const googleTypes = ['google_readonly', 'google', 'google_write_read', 'google_freebusy'];
	    return !this.isPseudo() && googleTypes.includes(this.data.EXTERNAL_TYPE);
	  }
	  isCalDav() {
	    return !this.isPseudo() && this.data.CAL_DAV_CAL && this.data.CAL_DAV_CON;
	  }
	  isIcloud() {
	    return !this.isPseudo() && this.data.EXTERNAL_TYPE === 'icloud';
	  }
	  isOffice365() {
	    return !this.isPseudo() && this.data.EXTERNAL_TYPE === 'office365';
	  }
	  isArchive() {
	    return !this.isPseudo() && this.data.EXTERNAL_TYPE === 'archive';
	  }
	  isExchange() {
	    return !this.isPseudo() && this.data['IS_EXCHANGE'];
	  }
	  isCompanyCalendar() {
	    return !this.isPseudo() && this.type !== 'user' && this.type !== 'group' && !this.ownerId;
	  }
	  isGroupCalendar() {
	    return !this.isPseudo() && this.type === 'group';
	  }
	  hasConnection() {
	    return !this.isPseudo() && this.data.connectionLinks && this.data.connectionLinks.length;
	  }
	  isLocationRoom() {
	    return this.type === 'location';
	  }
	  belongsToView() {
	    const calendarContext = calendar_util.Util.getCalendarContext();
	    return this.type === calendarContext.getCalendarType() && this.ownerId === calendarContext.getOwnerId();
	  }
	  belongsToOwner() {
	    return this.belongsToUser(calendar_util.Util.getCalendarContext().getUserId());
	  }
	  belongsToUser(userId) {
	    return this.type === 'user' && this.ownerId === parseInt(userId) && this.data.ACTIVE !== 'N';
	  }
	  getExternalType() {
	    return this.data.EXTERNAL_TYPE ? this.data.EXTERNAL_TYPE : this.isCalDav() ? 'caldav' : '';
	  }
	  getConnectionLinks() {
	    return main_core.Type.isArray(this.data.connectionLinks) ? this.data.connectionLinks : [];
	  }
	  externalTypeIsLocal() {
	    return this.getExternalType() === calendar_sectionmanager.SectionManager.EXTERNAL_TYPE_LOCAL || this.isCompanyCalendar() || this.isGroupCalendar();
	  }
	  isPrimaryForConnection() {
	    return !this.externalTypeIsLocal() && this.getConnectionLinks().find(connection => {
	      return connection.isPrimary === 'Y';
	    });
	  }
	  isActive() {
	    return this.data.ACTIVE !== 'N';
	  }
	  getType() {
	    return this.type;
	  }
	  getOwnerId() {
	    return this.ownerId;
	  }
	  getConnectionIdList() {
	    const connectionIdList = [];
	    let connectionId = parseInt(this.data.CAL_DAV_CON, 10);
	    if (connectionId) {
	      connectionIdList.push(connectionId);
	    }
	    return connectionIdList;
	  }
	  updateListAfterDelete() {
	    const sectionManager = calendar_util.Util.getCalendarContext().sectionManager;
	    let reload = true;
	    let section;
	    for (let i = 0; i < sectionManager.sections.length; i++) {
	      section = sectionManager.sections[i];
	      if (section.id !== this.id && section.belongsToView() && !section.isGoogle() && !section.isIcloud() && !section.isOffice365() && !section.isCalDav() && !section.isArchive()) {
	        reload = false;
	        break;
	      }
	    }
	    const calendar = calendar_util.Util.getCalendarContext();
	    if (!calendar || reload) {
	      return calendar_util.Util.getBX().reload();
	    }
	    calendar.reload();
	  }
	}

	class CalendarTaskSection extends CalendarSection {
	  constructor(data = {}, {
	    type,
	    userId,
	    ownerId
	  }) {
	    const defaultColor = '#ff5b55';
	    let belongToUser = false;
	    let defaultName = main_core.Loc.getMessage('EC_SEC_USER_TASK_DEFAULT');
	    if (type === 'user' && userId === ownerId) {
	      defaultName = main_core.Loc.getMessage('EC_SEC_MY_TASK_DEFAULT');
	      belongToUser = true;
	    } else if (type === 'group') {
	      defaultName = main_core.Loc.getMessage('EC_SEC_GROUP_TASK_DEFAULT');
	    }
	    super({
	      ID: 'tasks',
	      NAME: data.name || defaultName,
	      COLOR: data.color || defaultColor,
	      PERM: {
	        edit_section: true,
	        view_full: true,
	        view_time: true,
	        view_title: true
	      }
	    });
	    this.isUserTaskSection = belongToUser;
	  }
	  isPseudo() {
	    return true;
	  }
	  taskSectionBelongToUser() {
	    return this.isUserTaskSection;
	  }
	  updateData(data) {
	    super.updateData(data);
	    this.id = data.ID;
	  }
	}

	class SectionManager {
	  constructor(data, config) {
	    this.setSections(data.sections);
	    this.setConfig(config);
	    this.addTaskSection();
	    this.sortSections();
	    main_core_events.EventEmitter.subscribeOnce('BX.Calendar.Section:delete', event => {
	      this.deleteSectionHandler(event.data.sectionId);
	    });
	    this.reloadDataDebounce = main_core.Runtime.debounce(this.reloadData, SectionManager.RELOAD_DELAY, this);
	  }
	  setSections(rawSections = []) {
	    this.sections = [];
	    this.sectionIndex = {};
	    rawSections.forEach(sectionData => {
	      const section = new CalendarSection(sectionData);
	      if (section.canDo('view_time')) {
	        this.sections.push(section);
	        this.sectionIndex[section.getId()] = this.sections.length - 1;
	      }
	    });
	  }
	  sortSections() {
	    this.sectionIndex = {};
	    this.sections = this.sections.sort((a, b) => {
	      if (main_core.Type.isFunction(a.isPseudo) && a.isPseudo()) {
	        return 1;
	      } else if (main_core.Type.isFunction(b.isPseudo) && b.isPseudo()) {
	        return -1;
	      }
	      return a.name.localeCompare(b.name);
	    });
	    this.sections.forEach((section, index) => {
	      this.sectionIndex[section.getId()] = index;
	    });
	  }
	  setConfig(config) {
	    this.setHiddenSections(config.hiddenSections);
	    this.calendarType = config.type;
	    this.ownerId = config.ownerId;
	    this.ownerName = config.ownerName || '';
	    this.userId = config.userId;
	    this.defaultSectionAccess = config.new_section_access || {};
	    this.sectionAccessTasks = config.sectionAccessTasks;
	    this.showTasks = config.showTasks;
	    this.customizationData = config.sectionCustomization || {};
	    this.meetSectionId = parseInt(config.meetSectionId, 10);
	  }
	  addTaskSection() {
	    if (this.showTasks) {
	      const taskSection = new CalendarTaskSection(this.customizationData['tasks' + this.ownerId], {
	        type: this.calendarType,
	        userId: this.userId,
	        ownerId: this.ownerId
	      });
	      this.sections.push(taskSection);
	      this.sectionIndex[taskSection.id] = this.sections.length - 1;
	    }
	  }
	  getCalendarType() {
	    return this.calendarType;
	  }
	  handlePullChanges(params) {
	    if (params.command === 'delete_section') {
	      const sectionId = parseInt(params.fields.ID, 10);
	      if (this.sectionIndex[sectionId]) {
	        this.deleteSectionHandler(sectionId);
	        calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar.Section:pull-delete', new main_core.Event.BaseEvent({
	          data: {
	            sectionId: sectionId
	          }
	        }));
	      } else {
	        this.reloadDataDebounce();
	      }
	    } else if (params.command === 'edit_section') {
	      this.reloadDataDebounce();
	      calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar:doRefresh');
	    } else if (params.command === 'hidden_sections_updated') {
	      this.setHiddenSections(params.hiddenSections);
	      this.reloadDataDebounce();
	    } else {
	      this.reloadDataDebounce();
	    }
	  }
	  reloadData() {
	    BX.ajax.runAction('calendar.api.calendarajax.getSectionList', {
	      data: {
	        'type': this.calendarType,
	        'ownerId': this.ownerId
	      }
	    }).then(response => {
	      this.setSections(response.data.sections || []);
	      this.sortSections();
	      if (response.data.config) {
	        this.setConfig(config);
	      }
	      this.addTaskSection();
	      calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar.Section:pull-reload-data');
	    });
	  }
	  getSections() {
	    return this.sections;
	  }
	  getSuperposedSectionList() {
	    var i,
	      result = [];
	    for (i = 0; i < this.sections.length; i++) {
	      if (this.sections[i].isSuperposed() && this.sections[i].isActive()) {
	        result.push(this.sections[i]);
	      }
	    }
	    return result;
	  }
	  getSectionListForEdit() {
	    const result = [];
	    for (let i = 0; i < this.sections.length; i++) {
	      if (this.sections[i].canDo('edit') && !this.sections[i].isPseudo() && this.sections[i].isActive() && !this.sections[i].isLocationRoom()) {
	        result.push(this.sections[i]);
	      }
	    }
	    return result;
	  }
	  getSection(id) {
	    return this.sections[this.sectionIndex[id]] || {};
	  }
	  getDefaultSectionName() {
	    return main_core.Loc.getMessage('EC_DEFAULT_SECTION_NAME');
	  }
	  getDefaultSectionAccess() {
	    return this.defaultSectionAccess;
	    // return this.calendar.util.config.new_section_access || {};
	  }

	  saveSection(name, color, access, params) {
	    return new Promise(resolve => {
	      var _params$section;
	      name = main_core.Type.isString(name) && name.trim() ? name.trim() : main_core.Loc.getMessage('EC_SEC_SLIDER_NEW_SECTION');
	      if (params.section.id) ;
	      const isCustomization = params.section.id && params.section.isPseudo();
	      BX.ajax.runAction('calendar.api.calendarajax.editCalendarSection', {
	        data: {
	          analyticsLabel: {
	            action: params.section.id ? 'editSection' : 'newSection',
	            type: params.section.type || this.calendarType
	          },
	          id: params.section.id || 0,
	          name: name,
	          type: params.section.type || this.calendarType,
	          ownerId: params.section.ownerId || this.ownerId,
	          color: color,
	          access: access || null,
	          userId: this.userId,
	          customization: isCustomization ? 'Y' : 'N',
	          external_type: params != null && (_params$section = params.section) != null && _params$section.id ? params.section.getExternalType() : 'local'
	        }
	      }).then(response => {
	        if (isCustomization) {
	          BX.reload();
	          return;
	        }
	        const sectionList = response.data.sectionList || [];
	        this.setSections(sectionList);
	        this.sortSections();
	        this.addTaskSection();
	        calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar.Section:edit', new main_core.Event.BaseEvent({
	          data: {
	            sectionList: sectionList
	          }
	        }));
	        resolve(response.data);
	      }, response => {
	        BX.Calendar.Util.displayError(response.errors);
	        resolve(response.data);
	      });
	    });
	  }
	  sectionIsShown(id) {
	    return !BX.util.in_array(id, this.hiddenSections);
	  }
	  getHiddenSections() {
	    return this.hiddenSections;
	  }
	  setHiddenSections(hiddenSections) {
	    this.hiddenSections = [];
	    if (main_core.Type.isArray(hiddenSections)) {
	      hiddenSections.forEach(id => {
	        this.hiddenSections.push(id === 'tasks' ? id : parseInt(id));
	      });
	    }
	  }
	  saveHiddenSections() {
	    const calendarContext = calendar_util.Util.getCalendarContext();
	    const optionName = calendarContext.util.userIsOwner() ? 'hidden_sections' : 'hidden_sections_' + calendarContext.util.type;
	    BX.userOptions.save('calendar', optionName, optionName, this.hiddenSections);
	  }
	  getSectionsInfo() {
	    const allActive = [];
	    const superposed = [];
	    const active = [];
	    const hidden = [];
	    this.sections.forEach(section => {
	      if (section.isShown() && this.calendarType === 'location' && section.type === 'location') {
	        if (section.isSuperposed()) {
	          superposed.push(section.id);
	        } else {
	          active.push(section.id);
	        }
	        allActive.push(section.id);
	      } else if (section.isShown() && this.calendarType !== 'location') {
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
	      superposed,
	      active,
	      hidden,
	      allActive
	    };
	  }
	  deleteSectionHandler(sectionId) {
	    if (this.sectionIndex[sectionId] !== undefined) {
	      this.sections = BX.util.deleteFromArray(this.sections, this.sectionIndex[sectionId]);
	      this.sectionIndex = {};
	      for (let i = 0; i < this.sections.length; i++) {
	        this.sectionIndex[this.sections[i].id] = i;
	      }
	    }
	  }
	  static getNewEntrySectionId(calendarType = null, ownerId = null) {
	    const calendarContext = calendar_util.Util.getCalendarContext();
	    if (calendarContext && !calendarContext.isExternalMode()) {
	      calendarType = calendarType || calendarContext.util.type;
	      if (calendarType === 'location') {
	        const section = calendarContext.sectionManager.getDefaultSection('user', calendarContext.util.userId);
	        return parseInt(section == null ? void 0 : section.id, 10);
	      } else {
	        const section = calendarContext.sectionManager.getDefaultSection(calendarType, ownerId);
	        return parseInt(section == null ? void 0 : section.id, 10);
	      }
	    }
	    if (SectionManager.newEntrySectionId) {
	      return SectionManager.newEntrySectionId;
	    }
	    return null;
	  }
	  static setNewEntrySectionId(sectionId) {
	    SectionManager.newEntrySectionId = parseInt(sectionId);
	  }
	  static getSectionGroupList(options = {}) {
	    let type = options.type,
	      ownerId = options.ownerId,
	      userId = options.userId,
	      followedUserList = options.trackingUsersList || calendar_util.Util.getFollowedUserList(userId),
	      sectionGroups = [],
	      title;

	    // 1. Main group - depends from current view
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
	    }

	    // 2. Company calendar
	    if (type !== 'company' && type !== 'company_calendar' && type !== 'calendar_company') {
	      sectionGroups.push({
	        title: main_core.Loc.getMessage('EC_SEC_SLIDER_TITLE_COMP_CAL'),
	        type: 'company'
	      });
	    }

	    // 3. Users calendars
	    if (main_core.Type.isArray(followedUserList)) {
	      followedUserList.forEach(user => {
	        if (parseInt(user.ID) !== ownerId || type !== 'user') {
	          sectionGroups.push({
	            title: BX.util.htmlspecialchars(user.FORMATTED_NAME),
	            type: 'user',
	            ownerId: parseInt(user.ID)
	          });
	        }
	      });
	    }

	    // 4. Groups calendars
	    sectionGroups.push({
	      title: main_core.Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_GROUP'),
	      type: 'group'
	    });

	    // 5. Resources calendars
	    sectionGroups.push({
	      title: main_core.Loc.getMessage('EC_SEC_SLIDER_TITLE_RESOURCE_CAL'),
	      type: 'resource'
	    });

	    // 6. Location calendars
	    sectionGroups.push({
	      title: main_core.Loc.getMessage('EC_SEC_SLIDER_TITLE_LOCATION_CAL'),
	      type: 'location'
	    });
	    return sectionGroups;
	  }
	  getSectionAccessTasks() {
	    return this.sectionAccessTasks;
	  }
	  getDefaultSection(calendarType = null, ownerId = null) {
	    let sections = this.getSectionListForEdit();
	    calendarType = main_core.Type.isString(calendarType) ? calendarType : this.calendarType;
	    ownerId = main_core.Type.isNumber(ownerId) ? ownerId : this.ownerId;
	    let section;
	    if (calendarType === 'user') {
	      const defaultSectionId = this.meetSectionId;
	      section = sections.find(item => {
	        return item.type === calendarType && item.ownerId === ownerId && item.id === defaultSectionId;
	      });
	    } else {
	      sections = sections.sort((section1, section2) => section1.id - section2.id);
	    }
	    if (!section) {
	      section = sections.find(item => {
	        return item.type === calendarType && item.ownerId === ownerId && item.canDo('edit');
	      });
	    }
	    return section;
	  }
	  setDefaultSection(sectionId) {
	    const section = this.getSection(parseInt(sectionId, 10));
	    if (section && section.type === this.calendarType && section.ownerId === this.ownerId) {
	      const userSettings = calendar_util.Util.getUserSettings();
	      const key = this.calendarType + this.ownerId;
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
	  static saveDefaultSectionId(sectionId, options = {}) {
	    const calendarContext = calendar_util.Util.getCalendarContext();
	    if (calendarContext) {
	      calendarContext.sectionManager.setDefaultSection(sectionId);
	    } else {
	      if (main_core.Type.isArray(options.sections) && options.calendarType && options.ownerId) {
	        const section = options.sections.find(item => {
	          const id = parseInt(item.ID || item.id, 10);
	          const ownerId = parseInt(item.OWNER_ID || item.ownerId, 10);
	          const type = item.CAL_TYPE || item.type;
	          return id === parseInt(sectionId, 10) && ownerId === parseInt(options.ownerId, 10) && type === options.calendarType;
	        });
	        if (section) {
	          const userSettings = calendar_util.Util.getUserSettings();
	          const key = options.calendarType + options.ownerId;
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
	  static getSectionExternalConnection(section, sectionExternalType) {
	    const calendarContext = calendar_util.Util.getCalendarContext();
	    const linkList = section.getConnectionLinks();
	    let provider = undefined;
	    let connection = undefined;
	    let connectionId = linkList.length ? parseInt(linkList[0].id) : parseInt(section.data.CAL_DAV_CON, 10);
	    if (connectionId && calendarContext && calendarContext.syncInterface) {
	      [provider, connection] = calendarContext.syncInterface.getProviderById(connectionId);
	      if (connection && (!linkList.length || connection.getType() === sectionExternalType)) {
	        return connection;
	      }
	    }
	    return null;
	  }
	}
	SectionManager.newEntrySectionId = null;
	SectionManager.EXTERNAL_TYPE_LOCAL = 'local';
	SectionManager.RELOAD_DELAY = 1000;

	exports.CalendarSection = CalendarSection;
	exports.SectionManager = SectionManager;

}((this.BX.Calendar = this.BX.Calendar || {}),BX.Calendar,BX.Calendar,BX,BX.Event));
//# sourceMappingURL=sectionmanager.bundle.js.map
