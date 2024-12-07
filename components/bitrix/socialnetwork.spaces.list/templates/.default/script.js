this.BX = this.BX || {};
this.BX.Socialnetwork = this.BX.Socialnetwork || {};
(function (exports,ui_vue3,ui_notification,ui_dialogs_messagebox,main_popup,main_date,socialnetwork_controller,ui_avatarEditor,main_loader,ui_vue3_vuex,main_core,main_core_events,pull_client) {
	'use strict';

	var _dontShowCollapseMenuAhaMoment = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dontShowCollapseMenuAhaMoment");
	var _showSpotlight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showSpotlight");
	var _showAhaMoment = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showAhaMoment");
	class LeftMenuAhaMoment {
	  constructor() {
	    Object.defineProperty(this, _showAhaMoment, {
	      value: _showAhaMoment2
	    });
	    Object.defineProperty(this, _showSpotlight, {
	      value: _showSpotlight2
	    });
	    Object.defineProperty(this, _dontShowCollapseMenuAhaMoment, {
	      value: _dontShowCollapseMenuAhaMoment2
	    });
	  }
	  showAhaMoment() {
	    const menuSwitcherNode = document.querySelector('.menu-items-header .menu-switcher');
	    if (main_core.Type.isDomNode(menuSwitcherNode)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _showSpotlight)[_showSpotlight](menuSwitcherNode);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _dontShowCollapseMenuAhaMoment)[_dontShowCollapseMenuAhaMoment]();
	  }
	}
	function _dontShowCollapseMenuAhaMoment2() {
	  main_core.ajax.runAction('socialnetwork.api.ahamoment.dontShowCollapseMenuAhaMoment');
	}
	function _showSpotlight2(targetElement) {
	  main_core.Runtime.loadExtension(['spotlight', 'ui.tour']).then(() => {
	    const spotlight = new BX.SpotLight({
	      targetElement,
	      targetVertex: 'middle-center'
	    });
	    main_core.Dom.addClass(targetElement, '--active');
	    spotlight.bindEvents({
	      onTargetEnter: () => {
	        main_core.Dom.removeClass(targetElement, '--active');
	        spotlight.close();
	      }
	    });
	    spotlight.setColor('#2fc6f6');
	    spotlight.show();
	    babelHelpers.classPrivateFieldLooseBase(this, _showAhaMoment)[_showAhaMoment](targetElement, spotlight);
	  });
	}
	async function _showAhaMoment2(node, spotlight) {
	  const {
	    Guide
	  } = await main_core.Runtime.loadExtension('ui.tour');
	  const guide = new Guide({
	    simpleMode: true,
	    onEvents: true,
	    steps: [{
	      target: node,
	      title: main_core.Loc.getMessage('SOCIALNETWORK_SPACES_COLLAPSE_MENU_AHA_MOMENT_TITLE'),
	      text: main_core.Loc.getMessage('SOCIALNETWORK_SPACES_COLLAPSE_MENU_AHA_MOMENT_TEXT'),
	      position: 'bottom',
	      condition: {
	        top: true,
	        bottom: false,
	        color: 'primary'
	      }
	    }]
	  });
	  guide.showNextStep();
	  const guidePopup = guide.getPopup();
	  guidePopup.setWidth(380);
	  guidePopup.getContentContainer().style.paddingRight = getComputedStyle(guidePopup.closeIcon)['width'];
	  guidePopup.setAngle({
	    offset: node.offsetWidth / 2 - 5
	  });
	  guidePopup.subscribe('onClose', () => spotlight.close());
	  guidePopup.setAutoHide(true);
	  guidePopup.getPopupContainer().style.marginLeft = '5px';
	  guidePopup.angle.element.style.left = '-1px';
	}

	const AddFormStore = {
	  state() {
	    return {
	      avatarColors: [],
	      avatarColor: '29AD49'
	    };
	  },
	  actions: {
	    setAvatarColors: (store, colors) => {
	      store.commit('setAvatarColors', colors);
	    },
	    setAvatarColor: (store, color) => {
	      store.commit('setAvatarColor', color);
	    }
	  },
	  mutations: {
	    setAvatarColors: (state, colors) => {
	      state.avatarColors = colors;
	    },
	    setAvatarColor: (state, color) => {
	      state.avatarColor = color;
	    }
	  },
	  getters: {
	    avatarColors: state => {
	      return state.avatarColors;
	    },
	    previousAvatarColor: state => {
	      return state.avatarColor;
	    }
	  }
	};

	class Client {
	  static async loadSpaces(data) {
	    const componentName = 'bitrix:socialnetwork.spaces.list';
	    const actionName = 'loadSpaces';
	    const response = await main_core.ajax.runComponentAction(componentName, actionName, {
	      mode: 'class',
	      data
	    });
	    return response.data;
	  }
	  static async reloadSpaces(data) {
	    const componentName = 'bitrix:socialnetwork.spaces.list';
	    const actionName = 'reloadSpaces';
	    const response = await main_core.ajax.runComponentAction(componentName, actionName, {
	      mode: 'class',
	      data
	    });
	    return response.data;
	  }
	  static async searchSpaces(data) {
	    const componentName = 'bitrix:socialnetwork.spaces.list';
	    const actionName = 'searchSpaces';
	    const response = await main_core.ajax.runComponentAction(componentName, actionName, {
	      mode: 'class',
	      data
	    });
	    return response.data;
	  }
	  static async loadRecentSearchSpaces() {
	    const componentName = 'bitrix:socialnetwork.spaces.list';
	    const actionName = 'loadRecentSearchSpaces';
	    const response = await main_core.ajax.runComponentAction(componentName, actionName, {
	      mode: 'class'
	    });
	    return response.data;
	  }
	  static async addSpaceToRecentSearch(spaceId) {
	    const componentName = 'bitrix:socialnetwork.spaces.list';
	    const actionName = 'addSpaceToRecentSearch';
	    const response = await main_core.ajax.runComponentAction(componentName, actionName, {
	      mode: 'class',
	      data: {
	        spaceId
	      }
	    });
	    return response.data;
	  }
	  static async loadSpaceData(spaceId) {
	    const componentName = 'bitrix:socialnetwork.spaces.list';
	    const actionName = 'loadSpaceData';
	    const response = await main_core.ajax.runComponentAction(componentName, actionName, {
	      mode: 'class',
	      data: {
	        spaceId
	      }
	    });
	    return response.data;
	  }
	  static async loadSpacesData(spaceIds) {
	    const componentName = 'bitrix:socialnetwork.spaces.list';
	    const actionName = 'loadSpacesData';
	    const response = await main_core.ajax.runComponentAction(componentName, actionName, {
	      mode: 'class',
	      data: {
	        spaceIds
	      }
	    });
	    return response.data;
	  }
	  static async loadSpaceTheme(spaceId) {
	    const componentName = 'bitrix:socialnetwork.spaces.list';
	    const actionName = 'loadSpaceTheme';
	    const response = await main_core.ajax.runComponentAction(componentName, actionName, {
	      mode: 'class',
	      data: {
	        spaceId
	      }
	    });
	    return response.data;
	  }
	}

	var _selectedSpaceId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selectedSpaceId");
	class RecentService {
	  constructor() {
	    Object.defineProperty(this, _selectedSpaceId, {
	      writable: true,
	      value: void 0
	    });
	    this.hasMoreSpacesToLoad = true;
	  }
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  setSelectedSpaceId(selectedSpaceId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedSpaceId)[_selectedSpaceId] = selectedSpaceId;
	  }
	  getSelectedSpaceId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _selectedSpaceId)[_selectedSpaceId];
	  }
	  async loadSpaces(data) {
	    const fields = {};
	    fields.loadedSpacesCount = data.loadedSpacesCount;
	    fields.mode = data.filterMode;
	    fields.searchString = '';
	    const result = await Client.loadSpaces(fields);
	    this.hasMoreSpacesToLoad = result.hasMoreSpacesToLoad;
	    return result;
	  }
	  canLoadSpaces() {
	    return this.hasMoreSpacesToLoad;
	  }
	  async reloadSpaces(data) {
	    const fields = {};
	    fields.mode = data.filterMode;
	    const result = await Client.reloadSpaces(fields);
	    this.hasMoreSpacesToLoad = result.hasMoreSpacesToLoad;
	    return result;
	  }
	}
	RecentService.instance = null;

	const Modes = Object.freeze({
	  recent: 'recent',
	  recentSearch: 'recentSearch',
	  search: 'search'
	});

	const FilterModeTypes = Object.freeze({
	  my: 'my',
	  other: 'other',
	  all: 'all'
	});
	const FilterModes = Object.freeze([{
	  type: FilterModeTypes.my,
	  nameMessageId: 'SOCIALNETWORK_SPACES_LIST_FILTER_MODE_MY_TITLE',
	  descriptionMessageId: 'SOCIALNETWORK_SPACES_LIST_FILTER_MODE_MY_DESCRIPTION'
	}, {
	  type: FilterModeTypes.other,
	  nameMessageId: 'SOCIALNETWORK_SPACES_LIST_FILTER_MODE_OTHER_TITLE',
	  descriptionMessageId: 'SOCIALNETWORK_SPACES_LIST_FILTER_MODE_OTHER_DESCRIPTION'
	}, {
	  type: FilterModeTypes.all,
	  nameMessageId: 'SOCIALNETWORK_SPACES_LIST_FILTER_MODE_ALL_TITLE',
	  descriptionMessageId: 'SOCIALNETWORK_SPACES_LIST_FILTER_MODE_ALL_DESCRIPTION'
	}]);

	const SpaceViewModeTypes = Object.freeze({
	  open: 'open',
	  closed: 'closed',
	  secret: 'secret'
	});
	const SpaceViewModes = Object.freeze([{
	  type: SpaceViewModeTypes.open,
	  nameMessageId: 'SOCIALNETWORK_SPACES_LIST_SPACE_VIEW_MODE_OPEN_TITLE',
	  descriptionMessageId: 'SOCIALNETWORK_SPACES_LIST_SPACE_VIEW_MODE_OPEN_DESCRIPTION'
	}, {
	  type: SpaceViewModeTypes.closed,
	  nameMessageId: 'SOCIALNETWORK_SPACES_LIST_SPACE_VIEW_MODE_CLOSED_TITLE',
	  descriptionMessageId: 'SOCIALNETWORK_SPACES_LIST_SPACE_VIEW_MODE_CLOSED_DESCRIPTION'
	}, {
	  type: SpaceViewModeTypes.secret,
	  nameMessageId: 'SOCIALNETWORK_SPACES_LIST_SPACE_VIEW_MODE_SECRET_TITLE',
	  descriptionMessageId: 'SOCIALNETWORK_SPACES_LIST_SPACE_VIEW_MODE_SECRET_DESCRIPTION'
	}]);
	const SpaceUserRoles = Object.freeze({
	  nonMember: 'nonMember',
	  applicant: 'applicant',
	  invited: 'invited',
	  member: 'member'
	});
	const SpaceCommonToCommentActivityTypes = Object.freeze({
	  calendar: 'calendar_comment',
	  task: 'task_comment',
	  livefeed: 'livefeed_comment'
	});

	class Helper {
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  buildSpaces(spaces) {
	    return spaces.map(spaceData => ({
	      id: parseInt(spaceData.id, 10),
	      name: spaceData.name,
	      isPinned: spaceData.isPinned,
	      isSelected: RecentService.getInstance().getSelectedSpaceId() === parseInt(spaceData.id, 10),
	      recentActivity: this.buildRecentActivity(spaceData.recentActivityData),
	      avatar: spaceData.avatar,
	      visibilityType: spaceData.visibilityType,
	      counter: parseInt(spaceData.counter, 10),
	      lastSearchDate: new Date(this.convertTimestampFromPhp(spaceData.lastSearchDateTimestamp)),
	      lastSearchDateTimestamp: spaceData.lastSearchDateTimestamp * 1000,
	      userRole: spaceData.userRole,
	      follow: spaceData.follow,
	      theme: [],
	      permissions: spaceData.permissions
	    }));
	  }
	  convertTimestampFromPhp(timestamp) {
	    return parseInt(timestamp, 10) * 1000;
	  }
	  buildInvitations(invitations) {
	    return invitations.map(invitationData => ({
	      spaceId: parseInt(invitationData.spaceId, 10),
	      message: invitationData.message,
	      invitationDateTimestamp: this.convertTimestampFromPhp(invitationData.invitationDateTimestamp),
	      invitationDate: new Date(this.convertTimestampFromPhp(invitationData.invitationDateTimestamp))
	    }));
	  }
	  buildRecentActivity(recentActivityData) {
	    const recentActivity = {};
	    recentActivity.description = recentActivityData.description;
	    recentActivity.typeId = recentActivityData.typeId;
	    recentActivity.entityId = parseInt(recentActivityData.entityId, 10);
	    recentActivity.timestamp = this.convertTimestampFromPhp(recentActivityData.timestamp);
	    recentActivity.date = new Date(recentActivity.timestamp);
	    recentActivity.secondaryEntityId = recentActivityData.secondaryEntityId;
	    return recentActivity;
	  }
	  getStringCapitalized(string) {
	    return string[0].toUpperCase() + string.slice(1);
	  }
	  getModelNameByListViewMode(mode) {
	    let result = `${mode}ListSpaceIds`;
	    if (mode === Modes.search) {
	      result = 'searchResultFromServerSpaceIds';
	    }
	    return result;
	  }
	  doAddSpaceToRecentList(space, lastRecentSpace, filterMode) {
	    const doDateActivityFits = lastRecentSpace.recentActivity.date < space.recentActivity.date;
	    const doUserRoleFitsFilterMode = this.doSpaceUserRoleFitsFilterMode(space.userRole, filterMode);
	    return doDateActivityFits && doUserRoleFitsFilterMode || space.id === 0;
	  }
	  doSpaceUserRoleFitsFilterMode(userRole, filterMode) {
	    if ([SpaceUserRoles.nonMember, SpaceUserRoles.applicant].includes(userRole) && filterMode === FilterModeTypes.my) {
	      return false;
	    }
	    if (userRole === SpaceUserRoles.member && filterMode === FilterModeTypes.other) {
	      return false;
	    }
	    return userRole !== SpaceUserRoles.invited;
	  }
	}
	Helper.instance = null;

	const MainStore = {
	  state() {
	    return {
	      spaces: new Map(),
	      recentListSpaceIds: new Set(),
	      searchResultFromServerSpaceIds: new Set(),
	      searchResultFromLoadedSpaceIds: new Set(),
	      invitations: new Map(),
	      invitationSpaceIds: new Set(),
	      recentSearchListSpaceIds: new Set(),
	      selectedFilterModeType: '',
	      spacesListState: '',
	      canCreateGroup: false
	    };
	  },
	  actions: {
	    setSpaces: (store, spaces) => {
	      store.commit('setSpaces', Helper.getInstance().buildSpaces(spaces));
	    },
	    setInvitations: (store, invitations) => {
	      store.commit('setInvitations', Helper.getInstance().buildInvitations(invitations));
	    },
	    setCanCreateGroup: (store, canCreateGroup) => {
	      store.commit('setCanCreateGroup', canCreateGroup);
	    },
	    setRecentSpaceIds: (store, spaceIds) => {
	      store.commit('setRecentListSpaceIds', spaceIds);
	    },
	    setInvitationSpaceIds: (store, spacesIds) => {
	      store.commit('setInvitationSpaceIds', spacesIds);
	    },
	    setRecentSearchSpaceIds: (store, spaceIds) => {
	      store.commit('setRecentSearchListSpaceIds', spaceIds);
	    },
	    addSpaces: (store, spaces) => {
	      store.commit('addSpaces', Helper.getInstance().buildSpaces(spaces));
	    },
	    setSelectedFilterModeType: (store, selectedFilterModeType) => {
	      store.commit('setSelectedFilterModeType', selectedFilterModeType);
	    },
	    setSelectedSpace: (store, selectedSpaceId) => {
	      const previousSelectedSpaceId = RecentService.getInstance().getSelectedSpaceId();
	      RecentService.getInstance().setSelectedSpaceId(selectedSpaceId);
	      store.commit('setSelectedSpace', {
	        spaceId: previousSelectedSpaceId,
	        selected: false
	      });
	      store.commit('setSelectedSpace', {
	        spaceId: selectedSpaceId,
	        selected: true
	      });
	    },
	    setSpacesListState: (store, spacesListState) => {
	      store.commit('setSpacesListState', spacesListState);
	    },
	    setLocalSearchResult: (store, spaceIds) => {
	      store.commit('setSearchResultFromLoadedSpaceIds', spaceIds);
	    },
	    clearSpacesViewByMode: (store, mode) => {
	      const storeHelper = Helper.getInstance();
	      const modelName = storeHelper.getModelNameByListViewMode(mode);
	      const modelNameCapitalized = storeHelper.getStringCapitalized(modelName);
	      store.commit(`set${modelNameCapitalized}`, []);
	    },
	    addSpacesToView: (store, data) => {
	      const storeHelper = Helper.getInstance();
	      const changedModel = storeHelper.getModelNameByListViewMode(data.mode);
	      const changedModelCapitalized = storeHelper.getStringCapitalized(changedModel);
	      const spaces = storeHelper.buildSpaces(data.spaces);
	      const addedSpaceIds = spaces.map(space => space.id);
	      const newSpaceIds = [...store.state[changedModel], ...addedSpaceIds];
	      store.commit(`set${changedModelCapitalized}`, newSpaceIds);
	      store.commit('addSpaces', spaces);
	    },
	    pinSpace: (store, data) => {
	      store.commit('pinSpace', data);
	    },
	    changeUserRole: (store, data) => {
	      store.commit('changeUserRole', data);
	    },
	    deleteInvitationFromStore: (store, data) => {
	      const spaceId = data.spaceId;
	      store.commit('deleteInvitationBySpaceId', spaceId);
	    },
	    deleteSpaceFromStore: (store, data) => {
	      const spaceId = data.spaceId;
	      store.commit('deleteSpaceById', spaceId);
	    },
	    updateCounters: (store, data) => {
	      const userId = data.userId;
	      const total = data.total;
	      BX.ready(() => {
	        if (BX.getClass('BX.Intranet.LeftMenu')) {
	          data.spaces.forEach(space => {
	            if (space.id === 0) {
	              const leftMenuCounters = {
	                spaces: total,
	                sonet_total: space.metrics.countersLiveFeedTotal
	              };
	              BX.Intranet.LeftMenu.updateCounters(leftMenuCounters, false);
	            }
	          });
	        }
	      });

	      // empty the existing space counters
	      store.getters.spaces.forEach(space => {
	        store.commit('updateCounter', {
	          userId,
	          spaceId: space.id,
	          counter: 0,
	          tasksTotal: 0,
	          calendarTotal: 0,
	          workGroupTotal: 0,
	          lifeFeedTotal: 0
	        });
	      });
	      data.spaces.forEach(space => {
	        store.commit('updateCounter', {
	          userId,
	          spaceId: space.id,
	          counter: space.total,
	          tasksTotal: space.metrics.countersTasksTotal,
	          calendarTotal: space.metrics.countersCalendarTotal,
	          workGroupTotal: space.metrics.countersWorkGroupRequestTotal,
	          lifeFeedTotal: space.metrics.countersLiveFeedTotal
	        });
	      });
	    },
	    updateSpaceData: (store, data) => {
	      if (data.checkInvitation !== false) {
	        if (data.space && data.isInvitation) {
	          store.commit('addInvitations', Helper.getInstance().buildInvitations([data.invitation]));
	        } else {
	          store.commit('deleteInvitationBySpaceId', data.spaceId);
	        }
	      }
	      if (data.space) {
	        const helper = Helper.getInstance();
	        const space = helper.buildSpaces([data.space]).pop();
	        const lastRecentSpace = store.getters.recentSpaces[store.getters.recentSpaces.length - 1];
	        store.commit('addSpaces', [space]);
	        if (helper.doAddSpaceToRecentList(space, lastRecentSpace, store.state.selectedFilterModeType)) {
	          store.commit('addRecentListSpaceId', space.id);
	        } else if (!helper.doSpaceUserRoleFitsFilterMode(space.userRole, store.state.selectedFilterModeType)) {
	          store.commit('removeRecentListSpaceId', space.id);
	        }
	      } else {
	        store.commit('deleteSpaceById', data.spaceId);
	      }
	    },
	    updateSpaceRecentActivityData: (store, recentActivityData) => {
	      store.commit('updateSpaceRecentActivityData', recentActivityData);
	      const space = store.state.spaces.get(recentActivityData.spaceId);
	      if (!space) {
	        return;
	      }
	      const helper = Helper.getInstance();
	      const lastRecentSpace = store.getters.recentSpaces[store.getters.recentSpaces.length - 1];
	      if (helper.doAddSpaceToRecentList(space, lastRecentSpace, store.state.selectedFilterModeType)) {
	        store.commit('addRecentListSpaceId', space.id);
	      }
	    }
	  },
	  mutations: {
	    setSpaces: (state, spaces) => {
	      state.spaces.clear();
	      spaces.forEach(space => state.spaces.set(space.id, space));
	    },
	    addSpaces: (state, spaces) => {
	      spaces.forEach(space => state.spaces.set(space.id, space));
	    },
	    setRecentListSpaceIds: (state, spaceIds) => {
	      // eslint-disable-next-line no-param-reassign
	      state.recentListSpaceIds = new Set(spaceIds);
	    },
	    addRecentListSpaceId: (state, spaceId) => {
	      state.recentListSpaceIds.add(spaceId);
	    },
	    removeRecentListSpaceId: (state, spaceId) => {
	      state.recentListSpaceIds.delete(spaceId);
	    },
	    setInvitations: (state, invitations) => {
	      state.invitations.clear();
	      invitations.forEach(invitation => state.invitations.set(invitation.spaceId, invitation));
	    },
	    setInvitationSpaceIds: (state, spaceIds) => {
	      // eslint-disable-next-line no-param-reassign
	      state.invitationSpaceIds = new Set(spaceIds);
	    },
	    addInvitations: (state, invitations) => {
	      invitations.forEach(invitation => {
	        state.invitationSpaceIds.add(invitation.spaceId);
	        state.invitations.set(invitation.spaceId, invitation);
	      });
	    },
	    setCanCreateGroup: (state, canCreateGroup) => {
	      // eslint-disable-next-line no-param-reassign
	      state.canCreateGroup = canCreateGroup;
	    },
	    setSearchResultFromServerSpaceIds: (state, spaceIds) => {
	      // eslint-disable-next-line no-param-reassign
	      state.searchResultFromServerSpaceIds = new Set(spaceIds);
	    },
	    setSearchResultFromLoadedSpaceIds: (state, spaceIds) => {
	      // eslint-disable-next-line no-param-reassign
	      state.searchResultFromLoadedSpaceIds = new Set(spaceIds);
	    },
	    setRecentSearchListSpaceIds: (state, spaceIds) => {
	      // eslint-disable-next-line no-param-reassign
	      state.recentSearchListSpaceIds = new Set(spaceIds);
	    },
	    setSelectedFilterModeType: (state, selectedFilterModeType) => {
	      // eslint-disable-next-line no-param-reassign
	      state.selectedFilterModeType = selectedFilterModeType;
	    },
	    setSelectedSpace: (state, selectedState) => {
	      const space = state.spaces.get(selectedState.spaceId);
	      if (space) {
	        space.isSelected = selectedState.selected;
	      }
	    },
	    setSpacesListState: (state, spacesListState) => {
	      // eslint-disable-next-line no-param-reassign
	      state.spacesListState = spacesListState;
	    },
	    pinSpace: (state, data) => {
	      const space = state.spaces.get(data.spaceId);
	      space.isPinned = data.isPinned;
	      state.spaces.set(space.id, space);
	    },
	    changeUserRole: (state, data) => {
	      const space = state.spaces.get(data.spaceId);
	      space.userRole = data.userRole;
	    },
	    deleteSpaceById: (state, spaceId) => {
	      state.spaces.delete(spaceId);
	      state.recentListSpaceIds.delete(spaceId);
	      state.searchResultFromServerSpaceIds.delete(spaceId);
	      state.searchResultFromLoadedSpaceIds.delete(spaceId);
	      state.recentSearchListSpaceIds.delete(spaceId);
	    },
	    deleteInvitationBySpaceId: (state, spaceId) => {
	      state.invitations.delete(spaceId);
	      state.invitationSpaceIds.delete(spaceId);
	    },
	    updateCounter: (state, data) => {
	      var _state$spaces$get;
	      const spaceId = data.spaceId;
	      const counter = data.counter;
	      const space = (_state$spaces$get = state.spaces.get(spaceId)) != null ? _state$spaces$get : {};
	      space.counter = counter;
	    },
	    updateSpaceRecentActivityData: (state, recentActivityData) => {
	      const space = state.spaces.get(recentActivityData.spaceId);
	      if (!space) {
	        return;
	      }
	      space.recentActivity = Helper.getInstance().buildRecentActivity(recentActivityData);
	    }
	  },
	  getters: {
	    spaces: state => {
	      return [...state.spaces.values()];
	    },
	    invitations: state => {
	      return [...state.invitations.values()];
	    },
	    spaceInvitations: (state, getters) => {
	      const spacesMap = state.spaces;
	      const invitations = getters.invitations;
	      return invitations.map(invitation => {
	        const space = spacesMap.get(invitation.spaceId);
	        const spaceInvitationFields = {
	          recentActivity: {
	            ...space.recentActivity,
	            description: invitation.message,
	            date: invitation.invitationDate,
	            timestamp: invitation.invitationDate.getTime()
	          },
	          counter: 1
	        };
	        return {
	          ...space,
	          ...spaceInvitationFields
	        };
	      }).sort((a, b) => {
	        return b.recentActivity.date - a.recentActivity.date;
	      });
	    },
	    canCreateGroup: state => {
	      return state.canCreateGroup;
	    },
	    spacesListState: state => {
	      return state.spacesListState;
	    },
	    recentSpacesUnordered: (state, getters) => {
	      const spaces = getters.spaces;
	      const unsortedRecentSpaces = spaces.filter(space => {
	        return state.recentListSpaceIds.has(space.id) && !state.invitationSpaceIds.has(space.id);
	      });
	      return unsortedRecentSpaces.sort((a, b) => {
	        return b.recentActivity.date - a.recentActivity.date;
	      });
	    },
	    recentSpaces: (state, getters) => {
	      let result = [];
	      switch (state.selectedFilterModeType) {
	        case FilterModeTypes.my:
	          result = getters.myRecentSpaces;
	          break;
	        case FilterModeTypes.other:
	          result = getters.otherRecentSpaces;
	          break;
	        case FilterModeTypes.all:
	          result = getters.allRecentSpaces;
	          break;
	        default:
	          break;
	      }
	      return result;
	    },
	    myRecentSpaces: (state, getters) => {
	      return [...getters.pinnedSpacesFromRecent, getters.commonSpaceFromRecent, ...getters.notPinnedSpacesWithoutCommonFromRecent];
	    },
	    otherRecentSpaces: (state, getters) => {
	      return [...getters.spacesWithoutCommonFromRecent];
	    },
	    allRecentSpaces: (state, getters) => {
	      return [getters.commonSpaceFromRecent, ...getters.spacesWithoutCommonFromRecent];
	    },
	    commonSpaceFromRecent: (state, getters) => {
	      return getters.recentSpacesUnordered.find(space => space.id === 0);
	    },
	    spacesWithoutCommonFromRecent: (state, getters) => {
	      return getters.recentSpacesUnordered.filter(space => {
	        return space.id !== getters.commonSpaceFromRecent.id;
	      });
	    },
	    pinnedSpacesFromRecent: (state, getters) => {
	      return getters.recentSpacesUnordered.filter(space => space.isPinned);
	    },
	    notPinnedSpacesWithoutCommonFromRecent: (state, getters) => {
	      return getters.spacesWithoutCommonFromRecent.filter(space => !space.isPinned);
	    },
	    searchSpaces: (state, getters) => {
	      const spaces = getters.spaces;
	      const searchResultIds = new Set([...state.searchResultFromLoadedSpaceIds, ...state.searchResultFromServerSpaceIds]);
	      return spaces.filter(space => searchResultIds.has(space.id));
	    },
	    spacesLoadedByCurrentSearchQueryCount: state => {
	      return state.searchResultFromServerSpaceIds.size;
	    },
	    recentSearchSpaces: (state, getters) => {
	      const unsortedRecentSearchSpaces = getters.spaces.filter(space => {
	        return state.recentSearchListSpaceIds.has(space.id);
	      });
	      return unsortedRecentSearchSpaces.sort((a, b) => {
	        return b.lastSearchDate - a.lastSearchDate;
	      });
	    },
	    recentSpacesCountForLoad: (state, getters) => {
	      // Do this subtraction because of common space.
	      // It is selected bypassing the sorting
	      return getters.recentSpaces.length - 1;
	    },
	    recentSearchSpacesCountForLoad: (state, getters) => {
	      return getters.recentSearchSpaces.length;
	    },
	    searchSpacesCountForLoad: (state, getters) => {
	      return getters.spacesLoadedByCurrentSearchQueryCount;
	    }
	  }
	};

	const Store = ui_vue3_vuex.createStore({
	  modules: {
	    addForm: AddFormStore,
	    main: MainStore
	  }
	});

	class LinkManager {
	  static getSpaceLink(spaceId) {
	    let path = LinkManager.commonSpacePath;
	    if (spaceId > 0) {
	      path = LinkManager.groupPath.replace('#group_id#', spaceId);
	    }
	    return path;
	  }
	}
	LinkManager.groupPath = '';
	LinkManager.commonSpacePath = '';

	const SpacesListStates = Object.freeze({
	  default: 'default',
	  collapsed: 'collapsed',
	  expanded: 'expanded'
	});

	const EventTypes = Object.freeze({
	  showLoader: 'socialnetwork:spacesList:showLoader',
	  hideLoader: 'socialnetwork:spacesList:hideLoader',
	  tryToLoadSpacesIfHasNoScrollbar: 'socialnetwork:spacesList:tryToLoadSpacesIfHasNoScrollbar',
	  spaceListScroll: 'socialnetwork:spacesList:onScroll',
	  spaceListShown: 'socialnetwork:spacesList:onShown',
	  showUpperSpaceAddForm: 'socialnetwork:spacesList:showUpperSpaceAddForm',
	  showBtnToggleBlock: 'socialnetwork:spacesList:showBtnToggleBlock',
	  hideSpaceAddForm: 'socialnetwork:spacesList:hideSpaceAddForm',
	  updateCounters: 'socialnetwork:spacesList:updateCounters',
	  changeSpace: 'socialnetwork:spacesList:changeSpace',
	  changeUserRole: 'socialnetwork:spacesList:changeUserRole',
	  changeSubscription: 'socialnetwork:spacesList:changeSubscription',
	  pinChanged: 'socialnetwork:spacesList:pinChanged',
	  changeSpaceListState: 'socialnetwork:spacesList:changeSpaceListState',
	  recentActivityUpdate: 'socialnetwork:spacesList:recentActivityUpdate',
	  recentActivityDelete: 'socialnetwork:spacesList:recentActivityDelete',
	  recentActivityRemoveFromSpace: 'socialnetwork:spacesList:recentActivityRemoveFromSpace',
	  openSpaceFromContextMenu: 'socialnetwork:spacesList:openSpaceFromContextMenu',
	  changeMode: 'socialnetwork:spacesList:changeMode'
	});

	const POPUP_CONTAINER_PREFIX = '#popup-window-content-';
	// @vue/component
	const BasePopup = {
	  name: 'BasePopup',
	  props: {
	    id: {
	      type: String,
	      required: true
	    },
	    config: {
	      type: Object,
	      required: false,
	      default() {
	        return {};
	      }
	    }
	  },
	  emits: ['close'],
	  computed: {
	    popupContainer() {
	      return `${POPUP_CONTAINER_PREFIX}${this.id}`;
	    }
	  },
	  created() {
	    this.instance = this.getPopupInstance();
	    this.instance.show();
	  },
	  mounted() {
	    this.instance.adjustPosition({
	      forceBindPosition: true,
	      position: this.getPopupConfig().bindOptions.position
	    });
	  },
	  beforeUnmount() {
	    if (!this.instance) {
	      return;
	    }
	    this.closePopup();
	  },
	  methods: {
	    getPopupInstance() {
	      if (!this.instance) {
	        var _PopupManager$getPopu;
	        (_PopupManager$getPopu = main_popup.PopupManager.getPopupById(this.id)) == null ? void 0 : _PopupManager$getPopu.destroy();
	        this.instance = new main_popup.Popup(this.getPopupConfig());
	      }
	      return this.instance;
	    },
	    getDefaultConfig() {
	      return {
	        id: this.id,
	        className: 'ui-test-popup',
	        autoHide: true,
	        animation: 'fading-slide',
	        bindOptions: {
	          position: 'bottom'
	        },
	        cacheable: false,
	        events: {
	          onPopupClose: this.closePopup.bind(this)
	        }
	      };
	    },
	    getPopupConfig() {
	      var _this$config$offsetTo, _this$config$bindOpti;
	      const defaultConfig = this.getDefaultConfig();
	      const modifiedOptions = {};
	      const defaultClassName = defaultConfig.className;
	      if (this.config.className) {
	        modifiedOptions.className = `${defaultClassName} ${this.config.className}`;
	      }
	      const offsetTop = (_this$config$offsetTo = this.config.offsetTop) != null ? _this$config$offsetTo : defaultConfig.offsetTop;
	      // adjust for default popup margin for shadow
	      if (((_this$config$bindOpti = this.config.bindOptions) == null ? void 0 : _this$config$bindOpti.position) === 'top' && main_core.Type.isNumber(this.config.offsetTop)) {
	        modifiedOptions.offsetTop = offsetTop - 10;
	      }
	      return {
	        ...defaultConfig,
	        ...this.config,
	        ...modifiedOptions
	      };
	    },
	    closePopup() {
	      this.$emit('close');
	      this.instance.destroy();
	      this.instance = null;
	    },
	    enableAutoHide() {
	      this.getPopupInstance().setAutoHide(true);
	    },
	    disableAutoHide() {
	      this.getPopupInstance().setAutoHide(false);
	    },
	    adjustPosition() {
	      this.getPopupInstance().adjustPosition({
	        forceBindPosition: true,
	        position: this.getPopupConfig().bindOptions.position
	      });
	    }
	  },
	  template: `
		<Teleport :to="popupContainer">
			<slot
				:adjustPosition="adjustPosition"
				:enableAutoHide="enableAutoHide"
				:disableAutoHide="disableAutoHide"
			></slot>
		</Teleport>
	`
	};

	// @vue/component

	const PopupMenuOption = {
	  props: {
	    option: {
	      type: Object,
	      default: () => {}
	    },
	    isSelected: Boolean
	  },
	  emits: ['changeSelectedOption'],
	  computed: {
	    active() {
	      return this.isSelected ? '--active' : '';
	    },
	    iconClass() {
	      return `--${this.option.type}-spaces`;
	    },
	    dataId() {
	      return `spaces-list-popup-menu-option-${this.option.type}`;
	    }
	  },
	  template: `
		<div
			@click="this.$emit('changeSelectedOption', option.type)"
			class="sn-spaces__popup-menu_item"
			:class="active"
			:data-id="dataId"
		>
			<div class="sn-spaces__popup-menu_item-icon" :class="iconClass"/>
			<div class="sn-spaces__popup-menu_item-info">
				<div class="sn-spaces__popup-menu_item-name">{{ option.name }}</div>
				<div class="sn-spaces__popup-menu_item-description">{{ option.description }}</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const PopupMenuButton = {
	  props: {
	    config: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['popupMenuButtonClick'],
	  computed: {
	    doShowIcon() {
	      return main_core.Type.isStringFilled(this.config.class);
	    }
	  },
	  template: `
		<div
			class="sn-spaces__popup-menu_item sn-spaces__popup-menu_item-btn"
			data-id="spaces-popup-menu-button"
			@click="this.$emit('popupMenuButtonClick')"
		>
			<div v-if="doShowIcon" class="ui-icon-set" :class="config.class"></div>
			{{config.text}}
		</div>
	`
	};

	// @vue/component
	const PopupMenuContent = {
	  name: 'ModePopupContent',
	  components: {
	    PopupMenuOption,
	    PopupMenuButton
	  },
	  props: {
	    options: {
	      type: Array,
	      required: true
	    },
	    selectedOption: {
	      type: String,
	      required: false,
	      default: ''
	    },
	    hint: {
	      type: String,
	      required: false,
	      default: ''
	    },
	    button: {
	      type: Object,
	      required: false,
	      default: () => {}
	    }
	  },
	  emits: ['closePopup', 'changeSelectedOption', 'popupMenuButtonClick'],
	  computed: {
	    doShowHint() {
	      return this.hint.length > 0;
	    },
	    doShowButton() {
	      var _this$button, _this$button$text;
	      return ((_this$button = this.button) == null ? void 0 : (_this$button$text = _this$button.text) == null ? void 0 : _this$button$text.length) > 0;
	    }
	  },
	  methods: {
	    onChangeSelectedOption(newSelectedOption) {
	      this.$emit('closePopup');
	      this.$emit('changeSelectedOption', newSelectedOption);
	    },
	    onPopupMenuButtonClick() {
	      this.$emit('closePopup');
	      this.$emit('popupMenuButtonClick');
	    }
	  },
	  template: `
		<div class="sn-spaces__popup-menu">
			<PopupMenuOption
				v-for="option in options"
				:option="option"
				:key="option.type"
				:isSelected="option.type === this.selectedOption"
				@changeSelectedOption="onChangeSelectedOption"
			/>
			<div v-if="doShowHint" class="sn-spaces__popup-menu_hint">
				{{hint}}
			</div>
			<PopupMenuButton
				v-if="doShowButton"
				:config="button"
				@popupMenuButtonClick="onPopupMenuButtonClick"
			/>
		</div>
	`
	};

	// @vue/component
	const POPUP_ID = 'socialnetwork-spaces-list-mode-popup';
	const PopupMenu = {
	  components: {
	    BasePopup,
	    PopupMenuContent
	  },
	  props: {
	    bindElement: {
	      type: Object,
	      required: true
	    },
	    context: {
	      type: String,
	      required: true
	    },
	    options: {
	      type: Array,
	      required: true
	    },
	    selectedOption: {
	      type: String,
	      required: false,
	      default: ''
	    },
	    button: {
	      type: Object,
	      required: false,
	      default: () => {}
	    },
	    hint: {
	      type: String,
	      required: false,
	      default: ''
	    }
	  },
	  computed: {
	    POPUP_ID() {
	      return `${POPUP_ID}-${this.context}`;
	    },
	    config() {
	      return {
	        className: 'ui-test-popup',
	        width: 343,
	        closeIcon: false,
	        closeByEsc: true,
	        overlay: true,
	        padding: 0,
	        animation: 'fading-slide',
	        bindElement: this.bindElement
	      };
	    }
	  },
	  emits: ['close', 'changeSelectedOption', 'popupMenuButtonClick'],
	  methods: {
	    onChangeSelectedOption(newSelectedOption) {
	      this.$emit('changeSelectedOption', newSelectedOption);
	    },
	    onPopupMenuButtonClick() {
	      this.$emit('popupMenuButtonClick');
	    }
	  },
	  template: `
		<BasePopup
			:config="config"
			@close="$emit('close')"
			v-slot="{enableAutoHide, disableAutoHide}"
			:id="POPUP_ID"
		>
			<PopupMenuContent
				:options="options"
				:selectedOption="selectedOption"
				:hint="hint"
				@closePopup="$emit('close')"
				@enableAutoHide="enableAutoHide"
				@disableAutoHide="disableAutoHide"
				@changeSelectedOption="onChangeSelectedOption"
				@popupMenuButtonClick="onPopupMenuButtonClick"
				:button="button"
			/>
		</BasePopup>
	`
	};

	// @vue/component
	const RecentHeader = {
	  components: {
	    PopupMenu
	  },
	  props: {
	    canCreateGroup: Boolean
	  },
	  emits: ['changeMode'],
	  data() {
	    return {
	      showModePopup: false,
	      isSpaceListScrolled: false,
	      filterModes: FilterModes
	    };
	  },
	  computed: {
	    scrollClass() {
	      return this.isSpaceListScrolled ? '--scroll-content' : '';
	    },
	    selectedFilterModeType() {
	      return this.$store.state.main.selectedFilterModeType;
	    },
	    title() {
	      const selectedType = this.selectedFilterModeType;
	      const selectedMode = this.filterModes.find(mode => mode.type === selectedType);
	      return selectedMode ? this.loc(selectedMode.nameMessageId) : this.loc('SOCIALNETWORK_SPACES_TITLE');
	    },
	    selectFilterModeButtonIconModifier() {
	      return `--${this.selectedFilterModeType}`;
	    },
	    popupMenuOptions() {
	      return this.filterModes.map(filterMode => ({
	        type: filterMode.type,
	        name: this.loc(filterMode.nameMessageId),
	        description: this.loc(filterMode.descriptionMessageId)
	      }));
	    },
	    popupMenuButton() {
	      if (!this.canCreateGroup) {
	        return null;
	      }
	      return {
	        text: this.loc('SOCIALNETWORK_SPACES_LIST_MODE_POPUP_NEW_SPACE_BUTTON'),
	        class: '--plus-30'
	      };
	    }
	  },
	  created() {
	    this.$bitrix.eventEmitter.subscribe(EventTypes.spaceListScroll, this.handleListChanges);
	    this.$bitrix.eventEmitter.subscribe(EventTypes.spaceListShown, this.handleListChanges);
	  },
	  beforeUnmount() {
	    this.$bitrix.eventEmitter.unsubscribe(EventTypes.spaceListScroll, this.handleListChanges);
	    this.$bitrix.eventEmitter.unsubscribe(EventTypes.spaceListShown, this.handleListChanges);
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    },
	    openPopup() {
	      this.showModePopup = true;
	    },
	    handleListChanges(event) {
	      const isSpaceListScrolled = event.data.isSpaceListScrolled;
	      const mode = event.data.mode;
	      if (mode === Modes.recent) {
	        this.isSpaceListScrolled = isSpaceListScrolled;
	      }
	    },
	    enableSearch() {
	      this.$emit('changeMode', Modes.recentSearch);
	    },
	    async onChangeSelectedFilterMode(filterMode) {
	      if (this.selectedFilterModeType !== filterMode) {
	        const recentService = RecentService.getInstance();
	        this.$bitrix.eventEmitter.emit(EventTypes.showLoader, Modes.recent);
	        const result = await recentService.reloadSpaces({
	          filterMode
	        });
	        this.$store.dispatch('setSelectedFilterModeType', filterMode);
	        this.$store.dispatch('clearSpacesViewByMode', Modes.recent);
	        this.$store.dispatch('addSpacesToView', {
	          mode: Modes.recent,
	          spaces: result.spaces
	        });
	        this.$bitrix.eventEmitter.emit(EventTypes.hideLoader, Modes.recent);
	      }
	    },
	    onCreateSpaceButtonClick() {
	      this.$bitrix.eventEmitter.emit(EventTypes.showUpperSpaceAddForm);
	    },
	    showBtnArrow() {
	      this.$bitrix.eventEmitter.emit(EventTypes.showBtnToggleBlock);
	    }
	  },
	  template: `
		<div 
			class="sn-spaces__list-header" 
			:class="scrollClass"
			@mouseenter="showBtnArrow"
		>
			<div
				@click="openPopup"
				class="sn-spaces__list-header_name"
				ref="spaces-list-header-name"
				data-id="spaces-header-title"
			>
				<div class="sn-spaces__list-header_name-block">
					<div class="sn-spaces__list-header_title">
						{{ title }}
					</div>
					<div class="ui-icon-set --chevron-down" style='--ui-icon-set__icon-size: 15px;'>
					</div>
				</div>
				<div class="sn-spaces__list-header_btn-spaces" :class="selectFilterModeButtonIconModifier"></div>
			</div>
			<PopupMenu
				:options="popupMenuOptions"
				context="space-recent-header"
				:bind-element="$refs['spaces-list-header-name'] || {}"
				:selectedOption="selectedFilterModeType"
				:hint="loc('SOCIALNETWORK_SPACES_LIST_MODE_POPUP_BOTTOM_DESCRIPTION')"
				@changeSelectedOption="onChangeSelectedFilterMode"
				@popupMenuButtonClick="onCreateSpaceButtonClick"
				v-if="showModePopup"
				@close="showModePopup = false"
				:button="popupMenuButton"
			/>
			<button
				class="ui-btn ui-btn-light ui-btn-round ui-btn-xs sn-spaces__list-header_btn-search"
				@click="enableSearch"
				data-id="spaces-search-button"
			>
				<div class="ui-icon-set --search-2"></div>
			</button>
			<button
				v-if="canCreateGroup"
				class="ui-btn ui-btn-light ui-btn-round ui-btn-xs sn-spaces__list-header_btn-add"
				@click="onCreateSpaceButtonClick"
				data-id="spaces-header-add-space-button"
			>
				<div class="ui-icon-set --plus-30"></div>
			</button>
		</div>
	`
	};

	const MINIMUM_QUERY_LENGTH_FOR_LOAD = 3;
	class SearchService {
	  constructor() {
	    this.hasMoreSpacesToLoad = true;
	    this.searchString = '';
	  }
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  async loadSpaces(data) {
	    const fields = {};
	    fields.loadedSpacesCount = data.loadedSpacesCount;
	    fields.mode = FilterModeTypes.all;
	    fields.searchString = this.searchString;
	    const result = await Client.searchSpaces(fields);
	    this.hasMoreSpacesToLoad = result.hasMoreSpacesToLoad;
	    return result;
	  }
	  canLoadSpaces() {
	    return this.hasMoreSpacesToLoad && this.searchString.length >= MINIMUM_QUERY_LENGTH_FOR_LOAD;
	  }
	}
	SearchService.instance = null;

	const SearchHeader = {
	  data() {
	    return {
	      searchQuery: '',
	      isSpaceListScrolled: false
	    };
	  },
	  created() {
	    this.startSearchDebounced = main_core.Runtime.debounce(this.startSearch, 500, this);
	    this.$bitrix.eventEmitter.subscribe(EventTypes.spaceListScroll, this.handleListChanges);
	    this.$bitrix.eventEmitter.subscribe(EventTypes.spaceListShown, this.handleListChanges);
	  },
	  beforeUnmount() {
	    this.$bitrix.eventEmitter.unsubscribe(EventTypes.spaceListScroll, this.handleListChanges);
	    this.$bitrix.eventEmitter.unsubscribe(EventTypes.spaceListShown, this.handleListChanges);
	  },
	  computed: {
	    ...ui_vue3_vuex.mapGetters({
	      spaces: 'spaces'
	    }),
	    scrollClass() {
	      return this.isSpaceListScrolled ? '--scroll-content' : '';
	    }
	  },
	  methods: {
	    loc(message) {
	      return this.$bitrix.Loc.getMessage(message);
	    },
	    closeSearch() {
	      this.searchQuery = '';
	      this.$emit('changeMode', Modes.recent);
	    },
	    handleListChanges(event) {
	      const isSpaceListScrolled = event.data.isSpaceListScrolled;
	      const mode = event.data.mode;
	      if ([Modes.search, Modes.recentSearch].includes(mode)) {
	        this.isSpaceListScrolled = isSpaceListScrolled;
	      }
	    },
	    onInputChange() {
	      SearchService.getInstance().searchString = this.searchQuery;
	      if (this.searchQuery.length === 0) {
	        this.$emit('changeMode', Modes.recentSearch);
	      } else {
	        this.startSearchDebounced();
	      }
	    },
	    startSearch() {
	      if (this.searchQuery.length > 0) {
	        SearchService.getInstance().hasMoreSpacesToLoad = true;
	        this.$store.dispatch('clearSpacesViewByMode', Modes.search);
	        const searchResult = this.spaces.filter(space => {
	          return space.name.toLowerCase().includes(this.searchQuery.toLowerCase());
	        });
	        const spaceIds = searchResult.map(space => space.id);
	        this.$store.dispatch('setLocalSearchResult', spaceIds);
	        this.$emit('changeMode', Modes.search);
	        setTimeout(() => {
	          this.$bitrix.eventEmitter.emit(EventTypes.tryToLoadSpacesIfHasNoScrollbar, Modes.search);
	        }, 80);
	      }
	    }
	  },
	  mounted() {
	    this.$refs.input.focus();
	  },
	  template: `
		<div class="sn-spaces__list-header" :class="scrollClass">
			<div class="sn-spaces__search ui-ctl ui-ctl-textbox ui-ctl-after-icon ui-ctl-w100 ui-ctl-sm">
				<input
					type="text"
					class="ui-ctl-element"
					:placeholder="loc('SOCIALNETWORK_SPACES_LIST_SEARCH_INPUT_PLACEHOLDER')"
					v-model.trim="searchQuery"
					ref="input"
					@input="onInputChange"
					data-id="spaces-search-input"
				>
				<button
					class="sn-spaces__search-clear ui-ctl-after"
					@click="closeSearch"
					data-id="spaces-close-search-button"
				>
					<div class="ui-icon-set --cross-circle-70"></div>
				</button>
			</div>
		</div>
	`
	};

	class ContextItem extends main_core_events.EventEmitter {
	  constructor(options) {
	    super();
	    this.setEventNamespace('BX.Socialnetwork.Spaces.ContextItem');
	    this.message = options.message;
	    this.spaceId = options.spaceId;
	    this.emitter = new main_core_events.EventEmitter();
	  }
	}

	class CopyLink extends ContextItem {
	  create() {
	    return {
	      text: this.message,
	      onclick: (event, menuItem) => {
	        BX.clipboard.copy(location.origin + LinkManager.getSpaceLink(this.spaceId));
	        menuItem.getMenuWindow().close();
	        ui_notification.UI.Notification.Center.notify({
	          content: main_core.Loc.getMessage('SN_SPACES_LIST_SPACE_COPY_LINK_NOTIFY')
	        });
	      }
	    };
	  }
	}

	class Logout extends ContextItem {
	  create() {
	    return {
	      text: this.message,
	      onclick: (event, menuItem) => {
	        const messageBox = new ui_dialogs_messagebox.MessageBox({
	          message: main_core.Loc.getMessage('SN_SPACES_LIST_SPACE_COPY_LOGOUT_POPUP_TEXT'),
	          buttons: ui_dialogs_messagebox.MessageBoxButtons.OK_CANCEL,
	          okCaption: main_core.Loc.getMessage('SN_SPACES_LIST_SPACE_COPY_LOGOUT_POPUP_CONFIRM_BTN'),
	          onOk: () => {
	            socialnetwork_controller.Controller.leaveGroup(this.spaceId).then(() => {
	              menuItem.getMenuWindow().close();
	              messageBox.close();
	              this.emit('click');
	            }).catch(() => {
	              messageBox.getOkButton().setDisabled(false);
	            });
	          }
	        });
	        messageBox.show();
	      }
	    };
	  }
	}

	var _switch = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("switch");
	var _flush = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("flush");
	class Pin extends ContextItem {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _flush, {
	      value: _flush2
	    });
	    Object.defineProperty(this, _switch, {
	      value: _switch2
	    });
	  }
	  create() {
	    return {
	      text: this.message,
	      onclick: (event, menuItem) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _switch)[_switch]().then(result => {
	          menuItem.getMenuWindow().close();
	          const resultMessage = result.data.message;
	          const resultMode = result.data.mode;
	          setTimeout(() => {
	            menuItem.setText(resultMessage);
	          }, 800);
	          babelHelpers.classPrivateFieldLooseBase(this, _flush)[_flush](resultMode);
	        });
	      }
	    };
	  }
	}
	function _switch2() {
	  return main_core.ajax.runAction('socialnetwork.api.livefeed.spaces.switcher.pin', {
	    data: {
	      switcher: {
	        type: Pin.ID,
	        spaceId: this.spaceId
	      },
	      space: this.spaceId
	    }
	  });
	}
	function _flush2(resultMode) {
	  this.emit(EventTypes.pinChanged, {
	    spaceId: this.spaceId,
	    isPinned: resultMode === 'Y'
	  });
	}
	Pin.ID = 'pinner';

	var _switch$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("switch");
	var _flush$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("flush");
	class Follow extends ContextItem {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _flush$1, {
	      value: _flush2$1
	    });
	    Object.defineProperty(this, _switch$1, {
	      value: _switch2$1
	    });
	  }
	  create() {
	    return {
	      text: this.message,
	      onclick: (event, menuItem) => {
	        babelHelpers.classPrivateFieldLooseBase(this, _switch$1)[_switch$1]().then(result => {
	          menuItem.getMenuWindow().close();
	          const resultMessage = result.data.message;
	          const resultMode = result.data.mode;
	          setTimeout(() => {
	            menuItem.setText(resultMessage);
	          }, 800);
	          babelHelpers.classPrivateFieldLooseBase(this, _flush$1)[_flush$1](resultMode);
	        });
	      }
	    };
	  }
	}
	function _switch2$1() {
	  return main_core.ajax.runAction('socialnetwork.api.livefeed.spaces.switcher.follow', {
	    data: {
	      switcher: {
	        type: Follow.ID,
	        spaceId: this.spaceId
	      },
	      space: this.spaceId
	    }
	  });
	}
	function _flush2$1(resultMode) {
	  this.emit('followChanged', {
	    spaceId: this.spaceId,
	    isFollowed: resultMode === 'Y'
	  });
	}
	Follow.ID = 'follow';

	class Open extends ContextItem {
	  create() {
	    return {
	      text: this.message,
	      onclick: (event, menuItem) => {
	        this.emitter.emit(EventTypes.openSpaceFromContextMenu, {
	          spaceId: this.spaceId
	        });
	        menuItem.getMenuWindow().close();
	      }
	    };
	  }
	  setPath(path) {
	    this.path = path;
	    return this;
	  }
	}

	var _cache = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cache");
	var _setOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setOptions");
	var _getOption = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getOption");
	var _init = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("init");
	class ContextMenu extends main_core_events.EventEmitter {
	  constructor(_options) {
	    super();
	    Object.defineProperty(this, _init, {
	      value: _init2
	    });
	    Object.defineProperty(this, _getOption, {
	      value: _getOption2
	    });
	    Object.defineProperty(this, _setOptions, {
	      value: _setOptions2
	    });
	    Object.defineProperty(this, _cache, {
	      writable: true,
	      value: new main_core.Cache.MemoryCache()
	    });
	    this.setEventNamespace('BX.Socialnetwork.Spaces.ContextMenu');
	    babelHelpers.classPrivateFieldLooseBase(this, _setOptions)[_setOptions](_options);
	    babelHelpers.classPrivateFieldLooseBase(this, _init)[_init]();
	  }
	  createMenu() {
	    const id = this.getMenuId();
	    this.menu = main_popup.MenuManager.create({
	      id,
	      closeByEsc: true,
	      bindElement: babelHelpers.classPrivateFieldLooseBase(this, _getOption)[_getOption]('bindElement'),
	      items: this.getItems()
	    });
	  }
	  getMenuId() {
	    return ContextMenu.ID + babelHelpers.classPrivateFieldLooseBase(this, _getOption)[_getOption]('spaceId');
	  }
	  toggle() {
	    this.collection.destroy();
	    this.collection.add(this);
	    this.createMenu();
	    this.menu.toggle();
	  }
	  getSpaceId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getOption)[_getOption]('spaceId');
	  }
	  isShown() {
	    var _this$menu$getPopupWi;
	    return (_this$menu$getPopupWi = this.menu.getPopupWindow()) == null ? void 0 : _this$menu$getPopupWi.isShown();
	  }
	  getItems() {
	    const items = [];
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _getOption)[_getOption]('isSelected')) {
	      const open = new Open({
	        spaceId: babelHelpers.classPrivateFieldLooseBase(this, _getOption)[_getOption]('spaceId'),
	        message: babelHelpers.classPrivateFieldLooseBase(this, _getOption)[_getOption]('openMessage')
	      });
	      items.push(open.setPath(babelHelpers.classPrivateFieldLooseBase(this, _getOption)[_getOption]('path')).create());
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getOption)[_getOption]('listFilter') === FilterModeTypes.my && babelHelpers.classPrivateFieldLooseBase(this, _getOption)[_getOption]('listMode') === Modes.recent) {
	      const pin = new Pin({
	        spaceId: babelHelpers.classPrivateFieldLooseBase(this, _getOption)[_getOption]('spaceId'),
	        message: babelHelpers.classPrivateFieldLooseBase(this, _getOption)[_getOption]('pinMessage')
	      });
	      items.push(pin.create());
	    }
	    const follow = new Follow({
	      spaceId: babelHelpers.classPrivateFieldLooseBase(this, _getOption)[_getOption]('spaceId'),
	      message: babelHelpers.classPrivateFieldLooseBase(this, _getOption)[_getOption]('followMessage')
	    });
	    items.push(follow.create());
	    const copyLink = new CopyLink({
	      spaceId: babelHelpers.classPrivateFieldLooseBase(this, _getOption)[_getOption]('spaceId'),
	      message: babelHelpers.classPrivateFieldLooseBase(this, _getOption)[_getOption]('copyLinkMessage')
	    });
	    items.push(copyLink.create());
	    const permissions = babelHelpers.classPrivateFieldLooseBase(this, _getOption)[_getOption]('permissions');
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getOption)[_getOption]('listFilter') === FilterModeTypes.my && babelHelpers.classPrivateFieldLooseBase(this, _getOption)[_getOption]('listMode') === Modes.recent && permissions.canLeave) {
	      const logout = new Logout({
	        spaceId: babelHelpers.classPrivateFieldLooseBase(this, _getOption)[_getOption]('spaceId'),
	        message: babelHelpers.classPrivateFieldLooseBase(this, _getOption)[_getOption]('logoutMessage')
	      });
	      logout.subscribe('click', () => {
	        if (RecentService.getInstance().getSelectedSpaceId() === babelHelpers.classPrivateFieldLooseBase(this, _getOption)[_getOption]('spaceId')) {
	          this.emit('openCommonSpace');
	        }
	      });
	      items.push(logout.create());
	    }
	    return items;
	  }
	}
	function _setOptions2(options) {
	  babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].set('options', options);
	}
	function _getOption2(option) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _cache)[_cache].get('options')[option];
	}
	function _init2() {
	  this.collection = ContextMenuCollection.getInstance();
	}
	ContextMenu.ID = 'space-context-menu-';

	class ContextMenuCollection {
	  static getInstance() {
	    if (ContextMenuCollection.instance === null) {
	      ContextMenuCollection.instance = new this();
	    }
	    return ContextMenuCollection.instance;
	  }
	  add(menu) {
	    ContextMenuCollection.items.push(menu);
	  }
	  destroy() {
	    ContextMenuCollection.items.forEach(item => {
	      const menu = main_popup.MenuManager.getMenuById(ContextMenu.ID + item.getSpaceId());
	      menu == null ? void 0 : menu.destroy();
	    });
	    ContextMenuCollection.items = ContextMenuCollection.items.filter(item => item.isShown());
	  }
	}
	ContextMenuCollection.items = [];
	ContextMenuCollection.instance = null;

	var _date = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("date");
	var _currentDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentDate");
	var _isToday = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isToday");
	var _isCurrentWeek = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isCurrentWeek");
	var _isCurrentYear = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isCurrentYear");
	class DateFormatter {
	  static formatDate(timestamp) {
	    return new DateFormatter(new Date(timestamp)).formatDate();
	  }
	  constructor(date) {
	    Object.defineProperty(this, _isCurrentYear, {
	      value: _isCurrentYear2
	    });
	    Object.defineProperty(this, _isCurrentWeek, {
	      value: _isCurrentWeek2
	    });
	    Object.defineProperty(this, _isToday, {
	      value: _isToday2
	    });
	    Object.defineProperty(this, _date, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _currentDate, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _date)[_date] = date;
	    babelHelpers.classPrivateFieldLooseBase(this, _currentDate)[_currentDate] = new Date();
	  }
	  formatDate() {
	    let format = '';
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isToday)[_isToday]()) {
	      format = main_date.DateTimeFormat.getFormat('SHORT_TIME_FORMAT');
	    } else if (babelHelpers.classPrivateFieldLooseBase(this, _isCurrentWeek)[_isCurrentWeek]()) {
	      format = 'D';
	    } else if (babelHelpers.classPrivateFieldLooseBase(this, _isCurrentYear)[_isCurrentYear]()) {
	      format = main_date.DateTimeFormat.getFormat('DAY_SHORT_MONTH_FORMAT');
	    } else {
	      format = main_date.DateTimeFormat.getFormat('MEDIUM_DATE_FORMAT');
	    }
	    return main_date.DateTimeFormat.format(format, babelHelpers.classPrivateFieldLooseBase(this, _date)[_date].getTime() / 1000);
	  }
	}
	function _isToday2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _date)[_date].toLocaleDateString() === babelHelpers.classPrivateFieldLooseBase(this, _currentDate)[_currentDate].toLocaleDateString();
	}
	function _isCurrentWeek2() {
	  const currentWeekNumber = parseInt(main_date.DateTimeFormat.format('W', babelHelpers.classPrivateFieldLooseBase(this, _currentDate)[_currentDate]), 10);
	  const weekNumber = parseInt(main_date.DateTimeFormat.format('W', babelHelpers.classPrivateFieldLooseBase(this, _date)[_date]), 10);
	  return babelHelpers.classPrivateFieldLooseBase(this, _isCurrentYear)[_isCurrentYear]() && weekNumber === currentWeekNumber;
	}
	function _isCurrentYear2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _date)[_date].getFullYear() === babelHelpers.classPrivateFieldLooseBase(this, _currentDate)[_currentDate].getFullYear();
	}

	// @vue/component

	const Avatar = {
	  props: {
	    avatar: {
	      type: Object,
	      default: () => {}
	    },
	    isSecret: {
	      type: Boolean,
	      default: true
	    },
	    isInvitation: {
	      type: Boolean,
	      default: true
	    }
	  },
	  data() {
	    return {
	      secretSpaceImage: '/bitrix/components/bitrix/socialnetwork.spaces.list/templates/.default/images/socialnetwork-spaces_icon_close-spase.svg'
	    };
	  },
	  computed: {
	    avatarModel() {
	      return this.avatar;
	    },
	    avatarClass() {
	      let result = '';
	      if (this.avatarModel.type === 'icon') {
	        if (this.avatarModel.id.length > 0) {
	          result = `sonet-common-workgroup-avatar --${this.avatarModel.id}`;
	        } else {
	          result = 'ui-icon-common-user-group ui-icon';
	        }
	      }
	      return result;
	    },
	    iconStyle() {
	      let result = '';
	      if (this.avatarModel.type === 'image') {
	        result = `background-image: url(${this.avatarModel.id});`;
	      }
	      return result;
	    },
	    iconClass() {
	      return this.avatarModel.type === 'image' ? 'sn-spaces__list-item_img' : '';
	    }
	  },
	  template: `
		<div class="sn-spaces__list-item_icon" :class="avatarClass">
			<i :style="iconStyle" :class="iconClass"/>
			<div v-if="isInvitation" class="sn-spaces__list-item_invitation-icon">
				<div class="ui-icon-set --mail" style="--ui-icon-set__icon-size: 18px;"></div>
			</div>
			<div class="sn-spaces__list-item_icon-close" v-if="isSecret"/>
		</div>
	`
	};

	// @vue/component
	const SpaceContent = {
	  components: {
	    Avatar
	  },
	  props: {
	    space: {
	      type: Object,
	      default: () => {}
	    },
	    mode: {
	      type: String,
	      required: true
	    },
	    isInvitation: {
	      type: Boolean,
	      default: false
	    },
	    showAvatar: {
	      type: Boolean,
	      default: true
	    }
	  },
	  data() {
	    return {
	      modes: Modes,
	      spaceUserRoles: SpaceUserRoles,
	      doShowSuccessButton: false
	    };
	  },
	  computed: {
	    spaceModel() {
	      return this.space;
	    },
	    selectedFilterModeType() {
	      return this.$store.state.main.selectedFilterModeType;
	    },
	    doShowCounter() {
	      return this.spaceModel.counter && this.spaceModel.counter > 0 && this.mode === this.modes.recent && !this.isApplicantButtonsShown;
	    },
	    doShowPin() {
	      return this.spaceModel.isPinned && this.mode === this.modes.recent && !this.isApplicantButtonsShown && !this.doShowCounter && this.selectedFilterModeType === FilterModeTypes.my;
	    },
	    isApplicantButtonsShown() {
	      return this.doShowJoinButton || this.doShowPendingButton || this.doShowSuccessButton;
	    },
	    doShowJoinButton() {
	      return this.spaceModel.userRole === this.spaceUserRoles.nonMember && this.mode === this.modes.recent;
	    },
	    doShowPendingButton() {
	      return this.spaceModel.userRole === this.spaceUserRoles.applicant && this.mode === this.modes.recent;
	    },
	    isFollowing() {
	      return this.spaceModel.userRole !== this.spaceUserRoles.member || this.spaceModel.follow;
	    },
	    spaceDescription() {
	      const doShowSpaceVisibilityType = this.isApplicantButtonsShown || !this.spaceModel.recentActivity.description || this.spaceModel.recentActivity.description.length === 0;
	      return doShowSpaceVisibilityType ? this.getVisibilityTypeName() : this.spaceModel.recentActivity.description;
	    },
	    isCommon() {
	      return this.spaceModel.id === 0;
	    }
	  },
	  created() {
	    this.$bitrix.eventEmitter.subscribe(`onSpaceUpdate_${this.spaceModel.id}`, this.onSpaceUpdate.bind(this));
	  },
	  beforeUnmount() {
	    this.$bitrix.eventEmitter.unsubscribe(`onSpaceUpdate_${this.spaceModel.id}`, this.onSpaceUpdate.bind(this));
	  },
	  methods: {
	    loc(message) {
	      return this.$bitrix.Loc.getMessage(message);
	    },
	    getVisibilityTypeName() {
	      var _SpaceViewModes$find;
	      if (this.isCommon) {
	        return '';
	      }
	      const spaceViewModeNameMessageId = (_SpaceViewModes$find = SpaceViewModes.find(spaceViewMode => {
	        return spaceViewMode.type === this.spaceModel.visibilityType;
	      })) == null ? void 0 : _SpaceViewModes$find.nameMessageId;
	      return main_core.Type.isStringFilled(spaceViewModeNameMessageId) ? this.loc(spaceViewModeNameMessageId) : '';
	    },
	    formatDate(timestamp) {
	      return DateFormatter.formatDate(timestamp);
	    },
	    isSecretSpace(visibilityType) {
	      return visibilityType === SpaceViewModeTypes.secret;
	    },
	    formatCounter(counter) {
	      if (counter > 99) {
	        return '99+';
	      }
	      if (counter === 0) {
	        return '';
	      }
	      return counter.toString();
	    },
	    counterClass(follow) {
	      return follow ? 'sn-spaces__list-item_counter' : 'sn-spaces__list-item_counter --mute';
	    },
	    showSuccessButton() {
	      this.doShowSuccessButton = true;
	      setTimeout(() => {
	        this.doShowSuccessButton = false;
	      }, 1000);
	    },
	    onSpaceUpdate(event) {
	      if (this.mode === this.modes.recent) {
	        const spaceData = event.data;
	        const helper = Helper.getInstance();
	        const space = helper.buildSpaces([spaceData]).pop();
	        const wasUserApplicant = this.spaceModel.userRole === this.spaceUserRoles.applicant;
	        const isUserMember = space.userRole === this.spaceUserRoles.member;
	        if (wasUserApplicant && isUserMember) {
	          this.showSuccessButton();
	        }
	      }
	    },
	    async onJoinButtonClick(event) {
	      event.stopPropagation();
	      await main_core.ajax.runAction('socialnetwork.api.userToGroup.join', {
	        data: {
	          params: {
	            groupId: this.spaceModel.id
	          }
	        }
	      }).then(response => {
	        const confirmationNeeded = response.data.confirmationNeeded;
	        let userRole = this.spaceUserRoles.member;
	        if (confirmationNeeded) {
	          userRole = this.spaceUserRoles.applicant;
	        } else {
	          this.showSuccessButton();
	        }
	        this.$store.dispatch('changeUserRole', {
	          spaceId: this.spaceModel.id,
	          userRole
	        });
	      }, error => {
	        console.log(error);
	      });
	    },
	    onPendingButtonClick(event) {
	      event.stopPropagation();
	    },
	    onAcceptedButtonClick(event) {
	      event.stopPropagation();
	    },
	    async acceptInvitationButtonClickHandler(event) {
	      event.stopPropagation();
	      await main_core.ajax.runAction('socialnetwork.api.userToGroup.acceptOutgoingRequest', {
	        data: {
	          groupId: this.spaceModel.id
	        }
	      }).then(response => {
	        const isSuccess = response.data;
	        if (isSuccess) {
	          this.$store.dispatch('changeUserRole', {
	            spaceId: this.spaceModel.id,
	            userRole: this.spaceUserRoles.member
	          });
	          this.$store.dispatch('deleteInvitationFromStore', {
	            spaceId: this.spaceModel.id
	          });
	        }
	      }, error => {
	        console.log(error);
	      });
	    },
	    async declineInvitationButtonClickHandler(event) {
	      event.stopPropagation();
	      await main_core.ajax.runAction('socialnetwork.api.userToGroup.rejectOutgoingRequest', {
	        data: {
	          groupId: this.spaceModel.id
	        }
	      }).then(response => {
	        const isSuccess = response.data;
	        if (isSuccess) {
	          this.$store.dispatch('deleteInvitationFromStore', {
	            spaceId: this.spaceModel.id
	          });
	          if (this.isSecretSpace(this.spaceModel.visibilityType)) {
	            this.$store.dispatch('deleteSpaceFromStore', {
	              spaceId: this.spaceModel.id
	            });
	          } else {
	            this.$store.dispatch('changeUserRole', {
	              spaceId: this.spaceModel.id,
	              userRole: this.spaceUserRoles.nonMember
	            });
	          }
	        }
	      }, error => {
	        console.log(error);
	      });
	    }
	  },
	  template: `
		<Avatar
			v-if="showAvatar"
			:avatar="spaceModel.avatar"
			:isSecret="isSecretSpace(spaceModel.visibilityType)"
			:isInvitation="isInvitation"
		/>
		<div class="sn-spaces__list-item_info">
			<div class="sn-spaces__list-item_title" :title="spaceModel.name">
				<div class="sn-spaces__list-item_name">{{spaceModel.name}}</div>
				<div
					v-if="!isFollowing"
					class="sn-spaces__list-item_mute ui-icon-set --sound-off"
					style="--ui-icon-set__icon-size: 18px;"
					data-id="spaces-list-element-mute-icon"
				></div>
			</div>
			<div class="sn-spaces__list-item_description" data-id="spaces-list-element-description">
				{{spaceDescription}}
			</div>
		</div>
		<div class="sn-spaces__list-item_details">
			<div class="sn-spaces__list-item_time" data-id="spaces-list-element-activity-date">
				{{formatDate(spaceModel.recentActivity.date.getTime())}}
			</div>
			<div class="sn-spaces__list-item_changes">
				<div
					v-if="doShowPin"
					class="ui-icon-set --pin-1"
					style='--ui-icon-set__icon-size: 18px;'
				/>
				<div
					v-if="doShowCounter"
					:class="counterClass(isFollowing)"
					data-id="spaces-list-element-counter"
				>
					{{formatCounter(spaceModel.counter)}}
				</div>
				<button
					v-if="doShowJoinButton"
					class="ui-btn ui-btn-xs ui-btn-success ui-btn-no-caps ui-btn-round sn-spaces__list-item_btn-event"
					@click="onJoinButtonClick"
					data-id="spaces-list-element-join-button"
				>
					{{loc('SOCIALNETWORK_SPACES_LIST_JOIN_SPACE_BUTTON')}}
				</button>
				<button
					v-if="doShowPendingButton"
					class="ui-btn ui-btn-xs ui-btn-primary ui-btn-no-caps ui-btn-round sn-spaces__list-item_btn-event"
					@click="onPendingButtonClick"
					data-id="spaces-list-element-pending-button"
				>
					<div class="ui-icon-set --mail-out" style='--ui-icon-set__icon-color: white;'></div>
				</button>
				<button
					v-if="doShowSuccessButton"
					class="ui-btn ui-btn-xs ui-btn-primary ui-btn-no-caps ui-btn-round sn-spaces__list-item_btn-event"
					@click="onAcceptedButtonClick"
					data-id="spaces-list-element-success-button"
				>
					<div class="ui-icon-set --check" style='--ui-icon-set__icon-color: white;'></div>
				</button>
			</div>
		</div>
		<div v-if="isInvitation" class="sn-spaces__list-item_btns">
			<button
				class="ui-btn ui-btn-sm ui-btn-success ui-btn-no-caps ui-btn-round"
				@click="acceptInvitationButtonClickHandler"
				data-id="spaces-list-element-accept-invitation-button"
			>
				{{loc('SOCIALNETWORK_SPACES_LIST_ACCEPT_INVITATION_BUTTON')}}
			</button>
			<button
				class="ui-btn ui-btn-sm ui-btn-light-border ui-btn-no-caps ui-btn-round"
				@click="declineInvitationButtonClickHandler"
				data-id="spaces-list-element-decline-invitation-button"
			>
				{{loc('SOCIALNETWORK_SPACES_LIST_DECLINE_INVITATION_BUTTON')}}
			</button>
		</div>
	`
	};

	// @vue/component
	const POPUP_ID$1 = 'sn-spaces__short';
	const PopupShortSpace = {
	  components: {
	    BasePopup,
	    SpaceContent
	  },
	  emits: ['closeSpacePopup', 'popupSpaceClick'],
	  props: {
	    bindElement: {
	      type: Object,
	      required: true
	    },
	    context: {
	      type: String,
	      required: true
	    },
	    options: {
	      type: Object,
	      required: true
	    },
	    space: {
	      type: Object,
	      default: () => {}
	    },
	    mode: {
	      type: String,
	      required: true
	    },
	    link: {
	      type: String,
	      required: true
	    },
	    isInvitation: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data() {
	    return {
	      modes: Modes
	    };
	  },
	  computed: {
	    POPUP_ID() {
	      return `${POPUP_ID$1}-${this.context}`;
	    },
	    config() {
	      return {
	        className: 'sn-spaces__list-popup',
	        width: 293,
	        height: this.heightPopup,
	        closeIcon: false,
	        closeByEsc: true,
	        overlay: false,
	        padding: 0,
	        animation: 'fading-slide',
	        offsetLeft: this.options.left,
	        offsetTop: -70,
	        bindOptions: {
	          position: 'bottom'
	        },
	        bindElement: this.bindElement
	      };
	    },
	    classModifiers() {
	      const classModifiers = [];
	      if (this.isInvitation) {
	        classModifiers.push('--invitation');
	      }
	      return classModifiers.join(' ');
	    },
	    spaceModel() {
	      return this.space;
	    },
	    heightPopup() {
	      return this.isInvitation ? 115 : 70;
	    }
	  },
	  methods: {
	    closePopupShortSpace() {
	      this.$emit('closeSpacePopup');
	    },
	    onSpaceClick() {
	      this.$emit('popupSpaceClick');
	    }
	  },
	  template: `
		<BasePopup
			:config="config"
			:id="POPUP_ID"
		>
			<div
				ref="popup-content"
				class="sn-spaces__popup-list_collapsed-mode"
				@click="onSpaceClick"
				@mouseleave="closePopupShortSpace"
			>
				<div 
					class="sn-spaces__popup-list-item"
					:class="classModifiers"
				>
					<SpaceContent 
						:space="space" 
						:mode="mode"
						:is-invitation="isInvitation"
						:showAvatar="false"
					/>
				</div>
			</div>
		</BasePopup>
	`
	};

	// @vue/component
	const Space = {
	  components: {
	    PopupShortSpace,
	    SpaceContent
	  },
	  props: {
	    space: {
	      type: Object,
	      default: () => {}
	    },
	    mode: {
	      type: String,
	      required: true
	    },
	    isInvitation: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data() {
	    return {
	      modes: Modes,
	      spaceUserRoles: SpaceUserRoles,
	      showModePopup: false
	    };
	  },
	  computed: {
	    popupShortSpaceOptions() {
	      return {
	        left: this.widthItem
	      };
	    },
	    selectedFilterModeType() {
	      return this.$store.state.main.selectedFilterModeType;
	    },
	    classModifiers() {
	      const isRecentMode = this.mode === this.modes.recent;
	      const classModifiers = [];
	      if (this.spaceModel.isPinned && this.selectedFilterModeType === FilterModeTypes.my && isRecentMode) {
	        classModifiers.push('--pinned');
	      }
	      if (this.spaceModel.isSelected && isRecentMode) {
	        classModifiers.push('--active');
	      }
	      if (this.isInvitation) {
	        classModifiers.push('--invitation');
	      }
	      return classModifiers.join(' ');
	    },
	    link() {
	      return LinkManager.getSpaceLink(this.spaceModel.id);
	    },
	    spaceModel() {
	      return this.space;
	    },
	    widthItem() {
	      return this.$refs.link.getBoundingClientRect().width;
	    },
	    isCommon() {
	      return this.spaceModel.id === 0;
	    }
	  },
	  created() {
	    main_core_events.EventEmitter.subscribe(EventTypes.openSpaceFromContextMenu, this.openSpaceFromContextMenu);
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe(EventTypes.openSpaceFromContextMenu, this.openSpaceFromContextMenu);
	  },
	  methods: {
	    loc(message) {
	      return this.$bitrix.Loc.getMessage(message);
	    },
	    async openSpaceFromContextMenu(event) {
	      const spaceId = event.data.spaceId;
	      if (this.spaceModel.id === spaceId) {
	        await this.onSpaceClick();
	      }
	    },
	    getPinMessage() {
	      return this.spaceModel.isPinned ? this.loc('SOCIALNETWORK_SPACES_LIST_SPACE_UNPIN') : this.loc('SOCIALNETWORK_SPACES_LIST_SPACE_PIN');
	    },
	    getFollowMessage() {
	      return this.spaceModel.follow ? this.loc('SOCIALNETWORK_SPACES_LIST_SPACE_UNFOLLOW') : this.loc('SOCIALNETWORK_SPACES_LIST_SPACE_FOLLOW');
	    },
	    getOpenMessage() {
	      return this.loc('SOCIALNETWORK_SPACES_LIST_SPACE_OPEN');
	    },
	    getCopyLinkMessage() {
	      return this.loc('SN_SPACES_LIST_SPACE_COPY_LINK');
	    },
	    getLogoutMessage() {
	      return this.loc('SN_SPACES_LIST_SPACE_LOGOUT');
	    },
	    async onSpaceClick() {
	      const modeBeforeClick = this.mode;
	      if (this.mode !== Modes.recent) {
	        this.$bitrix.eventEmitter.emit(EventTypes.changeMode, Modes.recent);
	      }
	      this.$store.dispatch('setSelectedSpace', this.spaceModel.id);
	      BX.Socialnetwork.Spaces.space.reloadPageContent(LinkManager.getSpaceLink(this.spaceModel.id));
	      if ([Modes.recentSearch, Modes.search].includes(modeBeforeClick)) {
	        await Client.addSpaceToRecentSearch(this.spaceModel.id);
	      }
	    },
	    async onSpaceContextMenuClick(event) {
	      event.preventDefault();
	      if (this.isCommon || this.spaceModel.userRole !== SpaceUserRoles.member) {
	        return;
	      }
	      const menu = new ContextMenu({
	        spaceId: this.spaceModel.id,
	        bindElement: event.currentTarget,
	        path: this.link,
	        isSelected: this.spaceModel.isSelected,
	        permissions: this.spaceModel.permissions,
	        listFilter: this.selectedFilterModeType,
	        listMode: this.mode,
	        pinMessage: this.getPinMessage(),
	        followMessage: this.getFollowMessage(),
	        openMessage: this.getOpenMessage(),
	        copyLinkMessage: this.getCopyLinkMessage(),
	        logoutMessage: this.getLogoutMessage()
	      });
	      menu.subscribe('openCommonSpace', () => {
	        socialnetwork_controller.Controller.openCommonSpace();
	      });
	      menu.toggle();
	    },
	    openPopup() {
	      if (this.$store.getters.spacesListState === SpacesListStates.collapsed) {
	        this.showModePopup = true;
	      }
	    },
	    closePopup() {
	      if (this.$store.getters.spacesListState === SpacesListStates.collapsed) {
	        var _this$$refs$popupIte;
	        const bindElement = this.$refs.link;
	        const popupContainer = (_this$$refs$popupIte = this.$refs['popup-item']) == null ? void 0 : _this$$refs$popupIte.$refs['popup-content'];
	        let hoverElement = null;
	        main_core.Event.bind(document, 'mouseover', event => {
	          hoverElement = event.target;
	        });
	        setTimeout(() => {
	          if (!popupContainer || !bindElement.contains(hoverElement) && !popupContainer.contains(hoverElement)) {
	            this.showModePopup = false;
	          }
	        }, 100);
	      }
	    }
	  },
	  template: `
		<a
			ref="link"
			class="sn-spaces__list-item"
			:class="classModifiers"
			data-id="spaces-list-element"
			@click="onSpaceClick"
			@contextmenu="onSpaceContextMenuClick"
			@mouseenter="openPopup"
			@mouseleave="closePopup"
		>
			<PopupShortSpace
				ref="popup-item"
				:options="popupShortSpaceOptions"
				:space="space"
				:mode="mode"
				:link="link"
				:is-invitation="isInvitation"
				context="popup-short-space"
				:bind-element="$refs['link'] || {}"
				v-if="showModePopup"
				@close="showModePopup = false"
				@closeSpacePopup="closePopup"
				@popupSpaceClick="onSpaceClick"
			/>
			<SpaceContent 
				:space="space" 
				:mode="mode"
				:is-invitation="isInvitation"
			/>
		</a>
	`
	};

	const KeyboardCodes = Object.freeze({
	  enter: 13
	});

	// @vue/component
	const SpaceAddForm = {
	  components: {
	    PopupMenu
	  },
	  data() {
	    return {
	      modes: Modes,
	      spaceViewModes: SpaceViewModes,
	      spaceViewModeTypes: SpaceViewModeTypes,
	      spaceData: {
	        name: '',
	        viewMode: SpaceViewModeTypes.open,
	        image: null
	      },
	      isFocusedOnNameInput: false,
	      showViewModePopup: false,
	      wasCreateGroupRequestSent: false,
	      doShowNameAlreadyExistsError: false,
	      avatarColor: ''
	    };
	  },
	  computed: {
	    ...ui_vue3_vuex.mapGetters({
	      avatarColors: 'avatarColors',
	      previousAvatarColor: 'previousAvatarColor'
	    }),
	    isDataValidated() {
	      return this.spaceData.name.length > 0 && !this.doShowNameAlreadyExistsError;
	    },
	    confirmButtonClass() {
	      return this.isDataValidated && !this.wasCreateGroupRequestSent ? '' : '--disabled';
	    },
	    popupMenuOptions() {
	      return this.spaceViewModes.map(spaceViewMode => ({
	        type: spaceViewMode.type,
	        name: this.loc(spaceViewMode.nameMessageId),
	        description: this.loc(spaceViewMode.descriptionMessageId)
	      }));
	    },
	    selectedViewModeOptionName() {
	      return this.loc(`SOCIALNETWORK_SPACES_LIST_SPACE_VIEW_MODE_${this.spaceData.viewMode.toUpperCase()}_TITLE`);
	    },
	    nameInputModifier() {
	      return this.doShowNameAlreadyExistsError ? 'sn-spaces__list-add-item_input-error' : '';
	    },
	    isNameInputReadOnly() {
	      return this.wasCreateGroupRequestSent;
	    }
	  },
	  mounted() {
	    this.$refs.spaceAddFormNameInput.focus();
	    main_core.Event.bind(document, 'click', this.handleAutoHide, true);
	    main_core.Event.bind(document, 'keydown', this.handleKeyDown);
	    this.$bitrix.eventEmitter.subscribe(EventTypes.showUpperSpaceAddForm, this.chooseRandomAvatarColor);
	    this.chooseRandomAvatarColor();
	  },
	  unmounted() {
	    main_core.Event.unbind(document, 'click', this.handleAutoHide, true);
	    main_core.Event.unbind(document, 'keydown', this.handleKeyDown);
	    this.$bitrix.eventEmitter.unsubscribe(EventTypes.showUpperSpaceAddForm, this.chooseRandomAvatarColor);
	  },
	  methods: {
	    loc(message) {
	      return this.$bitrix.Loc.getMessage(message);
	    },
	    openViewModePopup() {
	      this.showViewModePopup = true;
	    },
	    onChangeSelectedOption(newOption) {
	      this.spaceData.viewMode = newOption;
	    },
	    chooseSpaceImage() {
	      const avatarEditor = this.getAvatarEditor();
	      avatarEditor.show('file');
	    },
	    getAvatarEditor() {
	      if (!this.avatarEditor) {
	        this.avatarEditor = new ui_avatarEditor.Editor({
	          enableCamera: false
	        });
	        main_core.Dom.addClass(this.avatarEditor.popup.getPopupContainer(), 'sn-spaces__avatar-editor');
	        this.avatarEditor.subscribe('onApply', event => {
	          var _file$name;
	          const [file] = event.getCompatData();
	          (_file$name = file.name) != null ? _file$name : file.name = 'tmp.png';
	          this.spaceData.image = file;
	          this.$refs.groupImage.src = URL.createObjectURL(file);
	          main_core.Dom.style(this.$refs.groupImageContainer, 'background', 'none');
	          main_core.Dom.style(this.$refs.groupImage, 'background', 'none');
	        });
	      }
	      return this.avatarEditor;
	    },
	    onAddSpaceClickHandler() {
	      this.addSpace();
	    },
	    chooseRandomAvatarColor() {
	      if (this.spaceData.image !== null) {
	        return;
	      }
	      if (!main_core.Type.isArrayFilled(this.avatarColors)) {
	        return;
	      }
	      const colors = this.avatarColors.filter(color => color !== this.previousAvatarColor);
	      this.avatarColor = colors[Math.floor(Math.random() * colors.length)];
	      main_core.Dom.style(this.$refs.groupImageContainer, 'backgroundColor', `#${this.avatarColor}`);
	      this.$store.dispatch('setAvatarColor', this.avatarColor);
	    },
	    addSpace() {
	      this.spaceData.name = this.spaceData.name.trim();
	      if (this.spaceData.name.length === 0) {
	        return;
	      }
	      const formData = new FormData();
	      formData.append('groupName', this.spaceData.name);
	      formData.append('viewMode', this.spaceData.viewMode);
	      if (this.spaceData.image !== null) {
	        formData.append('groupImage', this.spaceData.image, this.spaceData.image.name);
	      }
	      formData.append('avatarColor', this.avatarColor);
	      this.wasCreateGroupRequestSent = true;
	      main_core.ajax.runAction('socialnetwork.api.workgroup.createGroup', {
	        data: formData
	      }).then(response => {
	        BX.Socialnetwork.Spaces.space.reloadPageContent(`${LinkManager.getSpaceLink(response.data.groupId)}?empty-state=enabled`);
	        this.$bitrix.eventEmitter.emit(EventTypes.hideSpaceAddForm);

	        // eslint-disable-next-line promise/catch-or-return
	        Client.loadSpaceData(response.data.groupId)
	        // eslint-disable-next-line promise/no-nesting
	        .then(data => {
	          this.$store.dispatch('addSpacesToView', {
	            mode: Modes.recentSearch,
	            spaces: [data.space]
	          });
	          this.$store.dispatch('setSelectedSpace', data.space.id);
	        });
	      }, errorResponse => {
	        errorResponse.errors.forEach(error => {
	          if (error.code === 'ERROR_GROUP_NAME_EXISTS') {
	            this.doShowNameAlreadyExistsError = true;
	          }
	        });
	        this.wasCreateGroupRequestSent = false;
	        console.log(errorResponse);
	      });
	    },
	    handleAutoHide(event) {
	      if (this.shouldHideForm(event)) {
	        this.$bitrix.eventEmitter.emit(EventTypes.hideSpaceAddForm);
	      }
	    },
	    handleKeyDown(event) {
	      if (this.isFocusedOnNameInput && event.keyCode === KeyboardCodes.enter && this.isDataValidated && !this.wasCreateGroupRequestSent) {
	        this.addSpace();
	      }
	    },
	    shouldHideForm(event) {
	      var _this$avatarEditor, _this$avatarEditor$po;
	      const notVisible = this.$refs.spaceAddForm.offsetHeight === 0;
	      if (notVisible) {
	        return false;
	      }
	      const clickOnSpace = event.target.closest('.sn-spaces__list-item') !== null;
	      const clickOnSelf = this.$refs.spaceAddForm.contains(event.target);
	      const avatarPopupShown = (_this$avatarEditor = this.avatarEditor) == null ? void 0 : (_this$avatarEditor$po = _this$avatarEditor.popup) == null ? void 0 : _this$avatarEditor$po.isShown();
	      const viewModePopupShown = this.showViewModePopup;
	      const anyPopupShown = avatarPopupShown || viewModePopupShown;
	      return !clickOnSelf && !clickOnSpace && !anyPopupShown && !this.formDataChanged();
	    },
	    formDataChanged() {
	      const isNameFilled = this.spaceData.name !== '';
	      const isViewModeChanged = this.spaceData.viewMode !== SpaceViewModeTypes.open;
	      const isImageChosen = this.$refs.groupImage.src !== '';
	      return isNameFilled || isViewModeChanged || isImageChosen;
	    }
	  },
	  template: `
		<div class="sn-spaces__list-item --add-active --error" ref="spaceAddForm" data-id="spaces-list-add-space-form">
			<div class="sn-spaces__list-item_add">
				<div class="sn-spaces__list-item_icon" ref="groupImageContainer">
					<img
						alt="" class="spaces-list-add-space-image"
						@click="chooseSpaceImage()"
						ref="groupImage"
						data-id="spaces-list-add-space-image"
					>
				</div>
				<div class="sn-spaces__list-item_info">
					<div class="ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-xs ui-ctl-no-padding ui-ctl-no-border">
						<input
							v-model="spaceData.name"
							type="text" class="ui-ctl-element"
							:placeholder="loc('SOCIALNETWORK_SPACES_LIST_ADD_SPACE_FORM_NAME_PLACEHOLDER')"
							ref="spaceAddFormNameInput"
							data-id="spaces-list-add-space-name-input"
							@focus="isFocusedOnNameInput=true"
							@blur="isFocusedOnNameInput=false"
							:class="nameInputModifier"
							@input="doShowNameAlreadyExistsError=false"
							:readonly="isNameInputReadOnly"
						>
					</div>
					<div @click="openViewModePopup" ref="privacySelector" class="sn-spaces__list-item_select-private">
						<div
							class="sn-spaces__list-item_select-private-text"
							data-id="spaces-list-add-space-view-mode"
						>{{selectedViewModeOptionName}}</div>
						<div class="ui-icon-set --chevron-down" style="--ui-icon-set__icon-size: 14px;"></div>
						<PopupMenu
							v-if="showViewModePopup"
							:options="popupMenuOptions"
							context="space-add-form"
							:bind-element="this.$refs.privacySelector || {}"
							:hint="loc('SOCIALNETWORK_SPACES_LIST_SPACE_VIEW_MODE_HINT')"
							:selectedOption="this.spaceData.viewMode"
							@close="showViewModePopup = false"
							@changeSelectedOption="onChangeSelectedOption"
						/>
					</div>
				</div>
				<div class="sn-spaces__list-item_details">
					<div
						class="ui-icon-set --circle-check sn-spaces__list-item_save-btn" :class="confirmButtonClass"
						@click="onAddSpaceClickHandler()"
						data-id="spaces-list-add-space-create-button"
					></div>
				</div>
			</div>
			<div v-show="doShowNameAlreadyExistsError" class="sn-spaces__list-item_error">
				{{loc('SOCIALNETWORK_SPACES_LIST_NAME_ALREADY_EXISTS_ERROR')}}
			</div>
		</div>
	`
	};

	// @vue/component
	const SpaceListAddButton = {
	  methods: {
	    loc(message) {
	      return this.$bitrix.Loc.getMessage(message);
	    }
	  },
	  template: `
		<div class="sn-spaces__list-item --add-btn">
			<div class="sn-spaces__list-item_icon">
			</div>
			<div class="sn-spaces__list-item_info">
				<div class="sn-spaces__list-item_title">
					{{loc('SOCIALNETWORK_SPACES_LIST_ADD_SPACE_ITEM_TITLE')}}
				</div>
				<div class="sn-spaces__list-item_description">
					{{loc('SOCIALNETWORK_SPACES_LIST_ADD_SPACE_ITEM_DESCRIPTION')}}
				</div>
			</div>
		</div>
	`
	};

	const Loader = {
	  data() {
	    return {
	      loader: null
	    };
	  },
	  props: {
	    config: {
	      type: Object,
	      required: false,
	      default() {
	        return {};
	      }
	    }
	  },
	  mounted() {
	    this.getLoaderInstance().show();
	  },
	  beforeUnmount() {
	    if (!this.instance) {
	      return;
	    }
	    this.destroyLoader();
	  },
	  methods: {
	    getLoaderInstance() {
	      if (!this.instance) {
	        this.instance = new main_loader.Loader(this.getLoaderConfig());
	      }
	      return this.instance;
	    },
	    getDefaultConfig() {
	      return {
	        target: this.$refs.root,
	        size: 110,
	        color: '#2fc6f6',
	        offset: {
	          left: '0px',
	          top: '0px'
	        },
	        mode: 'absolute'
	      };
	    },
	    getLoaderConfig() {
	      const defaultConfig = this.getDefaultConfig();
	      return {
	        ...defaultConfig,
	        ...this.config
	      };
	    },
	    destroyLoader() {
	      this.instance.destroy();
	      this.instance = null;
	    }
	  },
	  template: `
		<div ref="root" style="position: relative">
		</div>
	`
	};

	// @vue/component

	const PAGINATION_OFFSET = 20;
	const SpaceList = {
	  components: {
	    Space,
	    Loader,
	    SpaceListAddButton,
	    SpaceAddForm
	  },
	  emits: ['isSpaceAddFormShown'],
	  data() {
	    return {
	      isScrollLoading: false,
	      isLoading: false,
	      modes: Modes,
	      doShowLowerSpaceAddForm: false,
	      doShowUpperSpaceAddForm: false,
	      skeletonItemsAmount: 25
	    };
	  },
	  props: {
	    mode: {
	      type: String,
	      required: true
	    },
	    spaces: {
	      type: Array,
	      required: true,
	      default: []
	    },
	    spaceInvitations: {
	      type: Array,
	      required: false,
	      default: []
	    },
	    canCreateGroup: Boolean,
	    spacesCountForLoad: {
	      type: Number,
	      required: true
	    },
	    serviceInstance: {
	      type: Object,
	      required: true
	    },
	    subtitle: {
	      type: String,
	      required: false,
	      default: ''
	    },
	    isShown: {
	      type: Boolean,
	      required: true
	    }
	  },
	  created() {
	    this.$bitrix.eventEmitter.subscribe(EventTypes.tryToLoadSpacesIfHasNoScrollbar, this.tryToLoadSpacesIfHasNoScrollbarHandler);
	    this.$bitrix.eventEmitter.subscribe(EventTypes.showLoader, this.showLoaderHandler);
	    this.$bitrix.eventEmitter.subscribe(EventTypes.hideLoader, this.hideLoaderHandler);
	    this.$bitrix.eventEmitter.subscribe(EventTypes.showUpperSpaceAddForm, this.showUpperSpaceAddFormHandler);
	    this.$bitrix.eventEmitter.subscribe(EventTypes.hideSpaceAddForm, this.hideSpaceAddFormHandler);
	  },
	  beforeUnmount() {
	    this.$bitrix.eventEmitter.unsubscribe(EventTypes.tryToLoadSpacesIfHasNoScrollbar, this.tryToLoadSpacesIfHasNoScrollbarHandler);
	    this.$bitrix.eventEmitter.unsubscribe(EventTypes.showLoader, this.showLoaderHandler);
	    this.$bitrix.eventEmitter.unsubscribe(EventTypes.hideLoader, this.hideLoaderHandler);
	    this.$bitrix.eventEmitter.unsubscribe(EventTypes.showUpperSpaceAddForm, this.showUpperSpaceAddFormHandler);
	    this.$bitrix.eventEmitter.unsubscribe(EventTypes.hideSpaceAddForm, this.hideSpaceAddFormHandler);
	  },
	  computed: {
	    service() {
	      return this.serviceInstance;
	    },
	    hasScrollbar() {
	      return this.$refs.list.scrollHeight > this.$refs.list.clientHeight;
	    },
	    loadingClass() {
	      return this.isLoading ? '--loading' : '';
	    },
	    filterMode() {
	      return this.$store.state.main.selectedFilterModeType;
	    },
	    doShowSubtitle() {
	      return this.subtitle.length > 0;
	    },
	    doShowSpaceListAddButton() {
	      return this.canCreateGroup && this.mode === this.modes.recent && this.spaces.length <= 5 && !this.isSpaceAddFormShown;
	    },
	    doShowInvitations() {
	      return this.mode === this.modes.recent && this.spaceInvitations.length > 0;
	    },
	    isSpaceAddFormShown() {
	      return this.doShowLowerSpaceAddForm || this.doShowUpperSpaceAddForm;
	    },
	    listState() {
	      return this.$store.getters.spacesListState;
	    }
	  },
	  watch: {
	    filterMode() {
	      this.scrollToTop();
	      this.isLoading = false;
	    },
	    isShown() {
	      if (this.isShown === true) {
	        const isSpaceListScrolled = this.$refs.list.scrollTop > 0;
	        this.$bitrix.eventEmitter.emit(EventTypes.spaceListShown, {
	          isSpaceListScrolled,
	          mode: this.mode
	        });
	      }
	    },
	    doShowUpperSpaceAddForm() {
	      if (this.doShowUpperSpaceAddForm === true) {
	        this.doShowLowerSpaceAddForm = false;
	      }
	    },
	    isSpaceAddFormShown() {
	      this.$emit('isSpaceAddFormShown', this.isSpaceAddFormShown);
	    }
	  },
	  methods: {
	    tryToLoadSpacesIfHasNoScrollbarHandler(event) {
	      const mode = event.data;
	      if (this.isProperMode(mode) && !this.hasScrollbar && this.service.canLoadSpaces()) {
	        this.loadSpaces();
	      }
	    },
	    showLoaderHandler(event) {
	      const mode = event.data;
	      if (this.isProperMode(mode)) {
	        this.isLoading = true;
	      }
	    },
	    hideLoaderHandler(event) {
	      const mode = event.data;
	      if (this.isProperMode(mode)) {
	        this.isLoading = false;
	      }
	    },
	    showUpperSpaceAddFormHandler() {
	      if (this.mode === this.modes.recent) {
	        this.doShowUpperSpaceAddForm = true;
	      }
	    },
	    showLowerSpaceAddForm() {
	      this.doShowLowerSpaceAddForm = true;
	    },
	    hideSpaceAddFormHandler() {
	      this.doShowLowerSpaceAddForm = false;
	      this.doShowUpperSpaceAddForm = false;
	      if (this.listState === SpacesListStates.expanded) {
	        this.$bitrix.eventEmitter.emit(EventTypes.changeSpaceListState, SpacesListStates.collapsed);
	      }
	    },
	    isProperMode(mode) {
	      return this.mode === mode;
	    },
	    scrollToTop() {
	      this.$refs.list.scrollTop = 0;
	    },
	    onScroll(event) {
	      const target = event.target;
	      const isSpaceListScrolled = target.scrollTop > 0;
	      this.$bitrix.eventEmitter.emit(EventTypes.spaceListScroll, {
	        isSpaceListScrolled,
	        mode: this.mode
	      });
	      const listRemainingSpace = target.scrollHeight - target.offsetHeight;
	      const listScroll = target.scrollTop;
	      const isScrolledToBottom = listScroll > listRemainingSpace - PAGINATION_OFFSET;
	      if (!this.isScrollLoading && this.service.canLoadSpaces() && isScrolledToBottom) {
	        this.loadSpaces();
	      }
	      ContextMenuCollection.getInstance().destroy();
	    },
	    loadSpaces() {
	      this.isScrollLoading = true;
	      this.service.loadSpaces({
	        loadedSpacesCount: this.spacesCountForLoad,
	        filterMode: this.filterMode
	      }).then(result => {
	        this.$store.dispatch('addSpacesToView', {
	          mode: this.mode,
	          spaces: result.spaces
	        });
	        this.isScrollLoading = false;
	      }).catch(() => {
	        setTimeout(() => {
	          this.isScrollLoading = false;
	        }, 5000);
	      });
	    }
	  },
	  template: `
		<span>
			<Loader v-if="isLoading" :config="{offset: {left: '0px', top: '40vh'}}"/>
			<div
				@scroll="onScroll"
				class="sn-spaces__list-content"
				:class="loadingClass"
				ref="list"
				data-id="spaces-list-content"
			>
				<div v-show="doShowSubtitle" class="sn-spaces__list-subtitle">
					{{subtitle}}
				</div>
				<SpaceAddForm v-if="doShowUpperSpaceAddForm"/>
				<div class="sn-spaces__list-item_invitation" v-if="doShowInvitations">
					<Space
						v-for="spaceInvitation in spaceInvitations"
						:key="spaceInvitation.id"
						:space="spaceInvitation"
						:mode="mode"
						:isInvitation="true"
					/>
				</div>
				<Space 
					v-for="space in spaces"
					:key="space.id"
					:space="space"
					:mode="mode"
				/>
				<SpaceListAddButton
					v-if="doShowSpaceListAddButton"
					@click="showLowerSpaceAddForm"
					data-id="spaces-list-add-space-button"
				/>
				<SpaceAddForm v-if="doShowLowerSpaceAddForm"/>
				<span v-show="isScrollLoading">
					<div v-for="index in skeletonItemsAmount" :key="index" class="sn-spaces__list-skeleton-item"></div>
				</span>
			</div>
		</span>
	`
	};

	class RecentSearchService {
	  constructor() {
	    this.hasMoreSpacesToLoad = false;
	  }
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  async loadSpaces() {
	    return Client.loadRecentSearchSpaces();
	  }
	  canLoadSpaces() {
	    return false;
	  }
	}
	RecentSearchService.instance = null;

	// @vue/component

	const MAX_QUANTITY_SHOW_BTN_TOGGLE_BLOCK = 3;
	const CollapsedModeToggleBlock = {
	  name: 'ToggleBlock',
	  data() {
	    return {
	      dataSpacesContent: document.getElementById('sn-spaces__content'),
	      counterImpressionsBtnShowToggleBlock: 0,
	      showBtnShowToggleBlock: false
	    };
	  },
	  created() {
	    this.$bitrix.eventEmitter.subscribe(EventTypes.showBtnToggleBlock, this.showBtnToggleBlockHandler);
	  },
	  beforeUnmount() {
	    this.$bitrix.eventEmitter.unsubscribe(EventTypes.showBtnToggleBlock, this.showBtnToggleBlockHandler);
	  },
	  computed: {
	    isShowHintBtn() {
	      return !(this.counterImpressionsBtnShowToggleBlock > MAX_QUANTITY_SHOW_BTN_TOGGLE_BLOCK);
	    },
	    btnClassName() {
	      return this.showBtnShowToggleBlock ? '' : '--hide';
	    }
	  },
	  mounted() {
	    if (localStorage.counterImpressionsBtnShowToggleBlock) {
	      this.counterImpressionsBtnShowToggleBlock = localStorage.counterImpressionsBtnShowToggleBlock;
	    }
	  },
	  methods: {
	    showBtnToggleBlockHandler() {
	      if (this.isShowHintBtn && !this.showBtnShowToggleBlock) {
	        this.showBtnShowToggleBlock = true;
	      }
	    },
	    hideBtnToggleBlockHandler() {
	      this.showBtnShowToggleBlock = false;
	    },
	    hideHintBtn() {
	      localStorage.counterImpressionsBtnShowToggleBlock = ++this.counterImpressionsBtnShowToggleBlock;
	      this.showBtnShowToggleBlock = false;
	    },
	    toggleList() {
	      this.showBtnShowToggleBlock = false;
	      if (this.$store.getters.spacesListState === SpacesListStates.collapsed) {
	        this.$bitrix.eventEmitter.emit(EventTypes.changeSpaceListState, SpacesListStates.default);
	        this.saveState(SpacesListStates.default);
	        main_core.Dom.removeClass(this.dataSpacesContent, '--list-collapsed-mode');
	      } else {
	        this.$bitrix.eventEmitter.emit(EventTypes.changeSpaceListState, SpacesListStates.collapsed);
	        this.saveState(SpacesListStates.collapsed);
	        main_core.Dom.addClass(this.dataSpacesContent, '--list-collapsed-mode');
	      }
	    },
	    saveState(state) {
	      main_core.ajax.runAction('socialnetwork.api.space.saveListSate', {
	        data: {
	          spacesListState: state
	        }
	      });
	    }
	  },
	  template: `
		<div class="sn-spaces__toggle-block">
			<div class="sn-spaces__toggle-image"></div>
			<div 
				class="sn-spaces__toggle-wrapper"
				@click="toggleList"
			>
				<div 
					ref="toggle-btn"
					class="sn-spaces__toggle-btn" 
					id="sn-spaces__toggle-btn"
					data-id="sn-spaces__toggle-btn"
				>
					<div class="ui-icon-set --chevron-left" style="--ui-icon-set__icon-size: 15px;"></div>
				</div>
			</div>
			<div
				class="sn-spaces__btn-show-toggle-block"
				:class="btnClassName"
				data-id="sn-spaces__btn-hint_show-toggle-block"
				v-if="isShowHintBtn"
				@click="hideHintBtn"
				@mouseleave="hideBtnToggleBlockHandler"
			>
				<div class="ui-icon-set --chevron-left" style="--ui-icon-set__icon-size: 15px;"></div>
			</div>
		</div>
	`
	};

	// @vue/component
	const BaseComponent = {
	  components: {
	    RecentHeader,
	    SpaceList,
	    SearchHeader,
	    CollapsedModeToggleBlock
	  },
	  data() {
	    return {
	      mode: 'recent',
	      modes: Modes,
	      listNode: document.getElementById('sn-spaces-list'),
	      isSpaceAddFormShownInRecentList: false
	    };
	  },
	  computed: {
	    ...ui_vue3_vuex.mapGetters({
	      recentSpaces: 'recentSpaces',
	      recentSpacesCountForLoad: 'recentSpacesCountForLoad',
	      recentSearchSpaces: 'recentSearchSpaces',
	      recentSearchSpacesCountForLoad: 'recentSearchSpacesCountForLoad',
	      searchSpaces: 'searchSpaces',
	      searchSpacesCountForLoad: 'searchSpacesCountForLoad',
	      spacesLoadedByCurrentSearchQueryCount: 'spacesLoadedByCurrentSearchQueryCount',
	      canCreateGroup: 'canCreateGroup',
	      spaceInvitations: 'spaceInvitations'
	    }),
	    doExpandCollapsedList() {
	      const isCollapsedState = this.$store.getters.spacesListState === SpacesListStates.collapsed;
	      const isSearchMode = [this.modes.search, this.modes.recentSearch].includes(this.mode);
	      return isCollapsedState && (isSearchMode || this.isSpaceAddFormShownInRecentList);
	    },
	    doCollapseExpandedList() {
	      const isExpandedState = this.$store.getters.spacesListState === SpacesListStates.expanded;
	      const isRecentMode = this.modes.recent === this.mode;
	      return isExpandedState && isRecentMode && !this.isSpaceAddFormShownInRecentList;
	    }
	  },
	  watch: {
	    doExpandCollapsedList() {
	      if (this.doExpandCollapsedList) {
	        this.changeSpaceListState(SpacesListStates.expanded);
	      }
	    },
	    doCollapseExpandedList() {
	      if (this.doCollapseExpandedList) {
	        this.changeSpaceListState(SpacesListStates.collapsed);
	      }
	    }
	  },
	  created() {
	    this.$bitrix.eventEmitter.subscribe(EventTypes.changeSpaceListState, this.changeSpaceListStateHandler);
	    this.$bitrix.eventEmitter.subscribe(EventTypes.changeMode, this.changeModeHandler);
	  },
	  beforeUnmount() {
	    this.$bitrix.eventEmitter.unsubscribe(EventTypes.changeSpaceListState, this.changeSpaceListStateHandler);
	    this.$bitrix.eventEmitter.unsubscribe(EventTypes.changeMode, this.changeModeHandler);
	  },
	  methods: {
	    loc(message) {
	      return this.$bitrix.Loc.getMessage(message);
	    },
	    changeModeHandler(event) {
	      const newMode = event.data;
	      this.setMode(newMode);
	    },
	    setMode(mode) {
	      this.mode = mode;
	    },
	    getRecentService() {
	      return RecentService.getInstance();
	    },
	    getRecentSearchService() {
	      return RecentSearchService.getInstance();
	    },
	    getSearchService() {
	      return SearchService.getInstance();
	    },
	    changeSpaceListStateHandler(event) {
	      const state = event.data;
	      this.changeSpaceListState(state);
	    },
	    changeSpaceListState(state) {
	      if (state === SpacesListStates.expanded && !main_core.Dom.hasClass(this.listNode, '--fixed')) {
	        main_core.Dom.addClass(this.listNode, '--fixed');
	      } else if (main_core.Dom.hasClass(this.listNode, '--fixed')) {
	        main_core.Dom.removeClass(this.listNode, '--fixed');
	      }
	      this.$store.dispatch('setSpacesListState', state);
	    },
	    isSpaceAddFormShownHandler(isSpaceAddFormShown) {
	      this.isSpaceAddFormShownInRecentList = isSpaceAddFormShown;
	    }
	  },
	  template: `
		<div class="sn-spaces__list-wrapper">
			<RecentHeader
				v-if="mode === modes.recent"
				:canCreateGroup="canCreateGroup"
				@changeMode="setMode"
			/>
			<SearchHeader
				v-if="mode === modes.search || mode === modes.recentSearch"
				@changeMode="setMode"
			/>
			<SpaceList
				v-show="mode === modes.recent"
				@isSpaceAddFormShown="isSpaceAddFormShownHandler"
				:isShown="mode === modes.recent"
				:mode="modes.recent"
				:spaces="recentSpaces"
				:spaceInvitations="spaceInvitations"
				:canCreateGroup="canCreateGroup"
				:spacesCountForLoad="recentSpacesCountForLoad"
				:serviceInstance="getRecentService()"
			/>
			<SpaceList
				v-show="mode === modes.recentSearch"
				:isShown="mode === modes.recentSearch"
				:mode="modes.recentSearch"
				:spaces="recentSearchSpaces"
				:canCreateGroup="canCreateGroup"
				:spacesCountForLoad="recentSearchSpacesCountForLoad"
				:serviceInstance="getRecentSearchService()"
				:subtitle="loc('SOCIALNETWORK_SPACES_LIST_RECENT_SEARCH_LIST_TITLE')"
			/>
			<SpaceList
				v-show="mode === modes.search"
				:isShown="mode === modes.search"
				:mode="modes.search"
				:spaces="searchSpaces"
				:canCreateGroup="canCreateGroup"
				:spacesCountForLoad="searchSpacesCountForLoad"
				:serviceInstance="getSearchService()"
				:subtitle="loc('SOCIALNETWORK_SPACES_LIST_SEARCH_LIST_TITLE')"
			/>
		</div>
		<CollapsedModeToggleBlock />
	`
	};

	var _pinChanged = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pinChanged");
	var _updateCounters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateCounters");
	var _onChangeSpace = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onChangeSpace");
	var _onChangeUserRole = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onChangeUserRole");
	var _onChangeSubscription = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onChangeSubscription");
	var _onRecentActivityUpdate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onRecentActivityUpdate");
	var _onRecentActivityDelete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onRecentActivityDelete");
	var _onRecentActivityRemoveFromSpace = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onRecentActivityRemoveFromSpace");
	class PullRequests extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    Object.defineProperty(this, _onRecentActivityRemoveFromSpace, {
	      value: _onRecentActivityRemoveFromSpace2
	    });
	    Object.defineProperty(this, _onRecentActivityDelete, {
	      value: _onRecentActivityDelete2
	    });
	    Object.defineProperty(this, _onRecentActivityUpdate, {
	      value: _onRecentActivityUpdate2
	    });
	    Object.defineProperty(this, _onChangeSubscription, {
	      value: _onChangeSubscription2
	    });
	    Object.defineProperty(this, _onChangeUserRole, {
	      value: _onChangeUserRole2
	    });
	    Object.defineProperty(this, _onChangeSpace, {
	      value: _onChangeSpace2
	    });
	    Object.defineProperty(this, _updateCounters, {
	      value: _updateCounters2
	    });
	    Object.defineProperty(this, _pinChanged, {
	      value: _pinChanged2
	    });
	    this.setEventNamespace('BX.Socialnetwork.Spaces.List.PullRequests');
	  }
	  getModuleId() {
	    return 'socialnetwork';
	  }
	  getMap() {
	    return {
	      workgroup_pin_changed: babelHelpers.classPrivateFieldLooseBase(this, _pinChanged)[_pinChanged].bind(this),
	      user_spaces_counter: babelHelpers.classPrivateFieldLooseBase(this, _updateCounters)[_updateCounters].bind(this),
	      workgroup_update: babelHelpers.classPrivateFieldLooseBase(this, _onChangeSpace)[_onChangeSpace].bind(this),
	      workgroup_subscribe_changed: babelHelpers.classPrivateFieldLooseBase(this, _onChangeSubscription)[_onChangeSubscription].bind(this),
	      space_user_role_change: babelHelpers.classPrivateFieldLooseBase(this, _onChangeUserRole)[_onChangeUserRole].bind(this),
	      recent_activity_update: babelHelpers.classPrivateFieldLooseBase(this, _onRecentActivityUpdate)[_onRecentActivityUpdate].bind(this),
	      recent_activity_delete: babelHelpers.classPrivateFieldLooseBase(this, _onRecentActivityDelete)[_onRecentActivityDelete].bind(this),
	      recent_activity_remove_from_space: babelHelpers.classPrivateFieldLooseBase(this, _onRecentActivityRemoveFromSpace)[_onRecentActivityRemoveFromSpace].bind(this)
	    };
	  }
	}
	function _pinChanged2(data) {
	  this.emit(EventTypes.pinChanged, {
	    spaceId: data.GROUP_ID,
	    isPinned: data.ACTION === 'pin'
	  });
	}
	function _updateCounters2(data) {
	  this.emit(EventTypes.updateCounters, data);
	}
	function _onChangeSpace2(data) {
	  const params = data.params;
	  this.emit(EventTypes.changeSpace, {
	    spaceId: params.GROUP_ID
	  });
	}
	function _onChangeUserRole2(data) {
	  this.emit(EventTypes.changeUserRole, {
	    spaceId: data.GROUP_ID,
	    userId: data.USER_ID
	  });
	}
	function _onChangeSubscription2(data) {
	  this.emit(EventTypes.changeSubscription, {
	    spaceId: data.GROUP_ID,
	    userId: data.USER_ID
	  });
	}
	function _onRecentActivityUpdate2(data) {
	  this.emit(EventTypes.recentActivityUpdate, {
	    recentActivities: data
	  });
	}
	function _onRecentActivityDelete2(data) {
	  this.emit(EventTypes.recentActivityDelete, {
	    typeId: data.typeId,
	    entityId: data.entityId
	  });
	}
	function _onRecentActivityRemoveFromSpace2(data) {
	  this.emit(EventTypes.recentActivityRemoveFromSpace, {
	    spaceIds: data.spaceIdsToReload
	  });
	}

	var _initialOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initialOptions");
	var _target = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("target");
	var _application = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("application");
	var _initLinkManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initLinkManager");
	var _initServices = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initServices");
	var _createApplication = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createApplication");
	class List {
	  constructor(options) {
	    Object.defineProperty(this, _createApplication, {
	      value: _createApplication2
	    });
	    Object.defineProperty(this, _initServices, {
	      value: _initServices2
	    });
	    Object.defineProperty(this, _initLinkManager, {
	      value: _initLinkManager2
	    });
	    Object.defineProperty(this, _initialOptions, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _target, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _application, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _initialOptions)[_initialOptions] = options;
	    if (options.doShowCollapseMenuAhaMoment) {
	      new LeftMenuAhaMoment().showAhaMoment();
	    }
	  }
	  create(target) {
	    babelHelpers.classPrivateFieldLooseBase(this, _target)[_target] = target;
	    babelHelpers.classPrivateFieldLooseBase(this, _initLinkManager)[_initLinkManager]();
	    babelHelpers.classPrivateFieldLooseBase(this, _initServices)[_initServices]();
	    babelHelpers.classPrivateFieldLooseBase(this, _createApplication)[_createApplication]();
	  }
	}
	function _initLinkManager2() {
	  LinkManager.groupPath = babelHelpers.classPrivateFieldLooseBase(this, _initialOptions)[_initialOptions].pathToGroupSpace;
	  LinkManager.commonSpacePath = babelHelpers.classPrivateFieldLooseBase(this, _initialOptions)[_initialOptions].pathToUserSpace;
	}
	function _initServices2() {
	  RecentService.getInstance().setSelectedSpaceId(parseInt(babelHelpers.classPrivateFieldLooseBase(this, _initialOptions)[_initialOptions].selectedSpaceId, 10));
	}
	function _createApplication2() {
	  const recentSpaceIds = babelHelpers.classPrivateFieldLooseBase(this, _initialOptions)[_initialOptions].recentSpaceIds;
	  const invitationSpaceIds = babelHelpers.classPrivateFieldLooseBase(this, _initialOptions)[_initialOptions].invitationSpaceIds;
	  const invitations = babelHelpers.classPrivateFieldLooseBase(this, _initialOptions)[_initialOptions].invitations;
	  const spaces = babelHelpers.classPrivateFieldLooseBase(this, _initialOptions)[_initialOptions].spaces;
	  const avatarColors = babelHelpers.classPrivateFieldLooseBase(this, _initialOptions)[_initialOptions].avatarColors;
	  const selectedFilterModeType = babelHelpers.classPrivateFieldLooseBase(this, _initialOptions)[_initialOptions].filterMode;
	  const spacesListState = babelHelpers.classPrivateFieldLooseBase(this, _initialOptions)[_initialOptions].spacesListMode;
	  const canCreateGroup = babelHelpers.classPrivateFieldLooseBase(this, _initialOptions)[_initialOptions].canCreateGroup;
	  const currentUserId = babelHelpers.classPrivateFieldLooseBase(this, _initialOptions)[_initialOptions].currentUserId;
	  babelHelpers.classPrivateFieldLooseBase(this, _application)[_application] = ui_vue3.BitrixVue.createApp({
	    name: 'SpacesList',
	    props: {
	      initialSpaces: Array,
	      selectedFilterModeType: String,
	      spacesListState: String,
	      canCreateGroup: Boolean,
	      recentSpaceIds: Array,
	      invitations: Array,
	      invitationSpaceIds: Array
	    },
	    components: {
	      BaseComponent
	    },
	    methods: {
	      castArrayValuesToInt(array) {
	        return array.map(value => parseInt(value, 10));
	      },
	      subscribeToPull() {
	        const pullRequests = new PullRequests();
	        pullRequests.subscribe(EventTypes.pinChanged, this.pinChangedHandler);
	        pullRequests.subscribe(EventTypes.updateCounters, this.updateCountersHandler);
	        pullRequests.subscribe(EventTypes.changeSpace, this.updateSpaceData);
	        pullRequests.subscribe(EventTypes.changeUserRole, this.updateSpaceUserData);
	        pullRequests.subscribe(EventTypes.changeSubscription, this.updateSpaceUserData);
	        pullRequests.subscribe(EventTypes.recentActivityUpdate, this.recentActivityUpdate);
	        pullRequests.subscribe(EventTypes.recentActivityDelete, this.recentActivityDelete);
	        pullRequests.subscribe(EventTypes.recentActivityRemoveFromSpace, this.recentActivityRemoveFromSpace);
	        pull_client.PULL.subscribe(pullRequests);
	      },
	      pinChangedHandler(event) {
	        this.pinSpace(event.getData().spaceId, event.getData().isPinned);
	      },
	      pinSpace(spaceId, isPinned) {
	        this.$store.dispatch('pinSpace', {
	          spaceId,
	          isPinned
	        });
	      },
	      updateCountersHandler(event) {
	        if (event.data.userId && parseInt(event.data.userId, 10) === currentUserId) {
	          this.$store.dispatch('updateCounters', event.data);
	        }
	      },
	      async recentActivityUpdate(event) {
	        const recentActivities = event.data.recentActivities;
	        const spacesToLoad = [];
	        recentActivities.forEach(recentActivityData => {
	          const space = this.$store.state.main.spaces.get(recentActivityData.spaceId);
	          if (space) {
	            this.$store.dispatch('updateSpaceRecentActivityData', recentActivityData);
	          } else {
	            spacesToLoad.push(recentActivityData.spaceId);
	          }
	        });
	        if (spacesToLoad.length > 0) {
	          await this.loadSpaces(spacesToLoad);
	        }
	      },
	      async recentActivityDelete(event) {
	        const deletedActivityTypeId = event.data.typeId;
	        const deletedActivityEntityId = event.data.entityId;
	        const spaceModels = [...this.$store.getters.recentSpaces.values()];
	        const spacesToLoad = [];
	        spaceModels.forEach(space => {
	          if (this.wasRecentActivityDeleted(space, deletedActivityTypeId, deletedActivityEntityId)) {
	            spacesToLoad.push(space.id);
	          }
	        });
	        if (spacesToLoad.length > 0) {
	          this.loadSpaces(spacesToLoad);
	        }
	      },
	      wasRecentActivityDeleted(space, deletedType, deletedEntityId) {
	        const recentActivity = space.recentActivity;
	        if (SpaceCommonToCommentActivityTypes[deletedType]) {
	          const commentType = SpaceCommonToCommentActivityTypes[deletedType];
	          return recentActivity.secondaryEntityId === deletedEntityId && commentType === recentActivity.typeId || recentActivity.entityId === deletedEntityId && deletedType === recentActivity.typeId;
	        }
	        return recentActivity.entityId === deletedEntityId && deletedType === recentActivity.typeId;
	      },
	      async recentActivityRemoveFromSpace(event) {
	        const spaceIds = event.data.spaceIds;
	        const spacesIdsToLoad = [];
	        spaceIds.forEach(spaceId => {
	          const space = this.$store.state.main.spaces.get(spaceId);
	          if (space) {
	            spacesIdsToLoad.push(spaceId);
	          }
	        });
	        this.loadSpaces(spacesIdsToLoad);
	      },
	      async loadSpace(spaceId) {
	        const requestData = await Client.loadSpaceData(spaceId);
	        this.$store.dispatch('updateSpaceData', requestData);
	      },
	      async loadSpaces(spaceIds) {
	        const requestData = await Client.loadSpacesData(spaceIds);
	        requestData.forEach(spaceData => {
	          this.$store.dispatch('updateSpaceData', {
	            space: spaceData,
	            checkInvitation: false
	          });
	        });
	      },
	      async updateSpaceData(event) {
	        if (event.data.spaceId >= 0) {
	          await this.loadSpace(event.data.spaceId);
	        }
	      },
	      async updateSpaceUserData(event) {
	        if (event.data.userId && parseInt(event.data.userId, 10) === currentUserId) {
	          const requestData = await Client.loadSpaceData(event.data.spaceId);
	          if (requestData.space) {
	            this.$bitrix.eventEmitter.emit(`onSpaceUpdate_${requestData.space.id}`, requestData.space);
	          }
	          this.$store.dispatch('updateSpaceData', requestData);
	        }
	      }
	    },
	    beforeCreate() {
	      this.$bitrix.Application.set(this);
	    },
	    created() {
	      this.$store.dispatch('setSpaces', this.initialSpaces);
	      this.$store.dispatch('setRecentSpaceIds', this.castArrayValuesToInt(this.recentSpaceIds));
	      this.$store.dispatch('setInvitationSpaceIds', this.castArrayValuesToInt(this.invitationSpaceIds));
	      this.$store.dispatch('setInvitations', this.invitations);
	      this.$store.dispatch('setAvatarColors', avatarColors);
	      this.$store.dispatch('setSelectedFilterModeType', this.selectedFilterModeType);
	      this.$store.dispatch('setSpacesListState', this.spacesListState);
	      this.$store.dispatch('setCanCreateGroup', this.canCreateGroup);
	      this.subscribeToPull();
	    },
	    mounted() {
	      this.$bitrix.eventEmitter.emit(EventTypes.showLoader, Modes.recentSearch);
	      RecentSearchService.getInstance().loadSpaces().then(result => {
	        this.$store.dispatch('addSpacesToView', {
	          mode: Modes.recentSearch,
	          spaces: result
	        });
	        this.$bitrix.eventEmitter.emit(EventTypes.hideLoader, Modes.recentSearch);
	      }).catch(() => {});
	    },
	    template: `
					<BaseComponent/>
				`
	  }, {
	    initialSpaces: spaces,
	    selectedFilterModeType,
	    spacesListState,
	    canCreateGroup,
	    recentSpaceIds,
	    invitationSpaceIds,
	    invitations
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _application)[_application].use(Store);
	  babelHelpers.classPrivateFieldLooseBase(this, _application)[_application].mount(babelHelpers.classPrivateFieldLooseBase(this, _target)[_target]);
	}

	exports.List = List;

}((this.BX.Socialnetwork.Spaces = this.BX.Socialnetwork.Spaces || {}),BX.Vue3,BX,BX.UI.Dialogs,BX.Main,BX.Main,BX.Socialnetwork,BX.UI.AvatarEditor,BX,BX.Vue3.Vuex,BX,BX.Event,BX));
//# sourceMappingURL=script.js.map
