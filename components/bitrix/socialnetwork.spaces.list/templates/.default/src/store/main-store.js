import { Helper } from './helper';
import { FilterModeTypes } from '../const/filter-mode';

import type { SpaceModel } from '../model/space-model';
import type { InvitationModel } from '../model/invitation-model';

export const MainStore = {
	state(): Object
	{
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
			canCreateGroup: false,
		};
	},
	actions:
		{
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
				const addedSpaceIds = spaces.map((space) => space.id);
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
					if (BX.getClass('BX.Intranet.LeftMenu'))
					{
						data.spaces.forEach((space) => {
							if (space.id === 0)
							{
								const leftMenuCounters = {
									spaces: total,
									sonet_total: space.metrics.countersLiveFeedTotal,
								}
								BX.Intranet.LeftMenu.updateCounters(leftMenuCounters, false);
							}
						});
					}
				});

				data.spaces.forEach((space) => {
					store.commit('updateCounter', {
						userId,
						spaceId: space.id,
						counter: space.total,
						tasksTotal: space.metrics.countersTasksTotal,
						calendarTotal: space.metrics.countersCalendarTotal,
						workGroupTotal: space.metrics.countersWorkGroupRequestTotal,
						lifeFeedTotal: space.metrics.countersLiveFeedTotal,
					});
				});
			},
			updateSpaceData: (store, data) => {
				if (data.space && data.isInvitation)
				{
					store.commit('addInvitations', Helper.getInstance().buildInvitations([data.invitation]));
				}
				else
				{
					store.commit('deleteInvitationBySpaceId', data.spaceId);
				}

				if (data.space)
				{
					const helper = Helper.getInstance();
					const space: SpaceModel = helper.buildSpaces([data.space]).pop();
					const lastRecentSpace: SpaceModel = store.getters.recentSpaces[store.getters.recentSpaces.length - 1];

					store.commit('addSpaces', [space]);

					const doDateActivityFits = lastRecentSpace.dateActivity < space.dateActivity;
					const doUserRoleFitsFilterMode = helper.doSpaceUserRoleFitsFilterMode(
						space.userRole,
						store.state.selectedFilterModeType,
					);

					if (doDateActivityFits && doUserRoleFitsFilterMode)
					{
						store.commit('addRecentListSpaceId', space.id);
					}
					else if (!doUserRoleFitsFilterMode)
					{
						store.commit('removeRecentListSpaceId', space.id);
					}
				}
				else
				{
					store.commit('deleteSpaceById', data.spaceId);
				}
			},
		},
	mutations:
		{
			setSpaces: (state, spaces) => {
				state.spaces.clear();
				spaces.forEach((space) => state.spaces.set(space.id, space));
			},
			addSpaces: (state, spaces) => {
				spaces.forEach((space) => state.spaces.set(space.id, space));
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
				invitations.forEach((invitation) => state.invitations.set(invitation.spaceId, invitation));
			},
			setInvitationSpaceIds: (state, spaceIds) => {
				// eslint-disable-next-line no-param-reassign
				state.invitationSpaceIds = new Set(spaceIds);
			},
			addInvitations: (state, invitations) => {
				invitations.forEach((invitation) => {
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
			setSpacesListState: (state, spacesListState) => {
				// eslint-disable-next-line no-param-reassign
				state.spacesListState = spacesListState;
			},
			pinSpace: (state, data) => {
				const space: SpaceModel = state.spaces.get(data.spaceId);
				space.isPinned = data.isPinned;
				state.spaces.set(space.id, space);
			},
			changeUserRole: (state, data) => {
				const space: SpaceModel = state.spaces.get(data.spaceId);
				space.userRole = data.userRole;
			},
			deleteSpaceById: (state, spaceId: number) => {
				state.spaces.delete(spaceId);
				state.recentListSpaceIds.delete(spaceId);
				state.searchResultFromServerSpaceIds.delete(spaceId);
				state.searchResultFromLoadedSpaceIds.delete(spaceId);
				state.recentSearchListSpaceIds.delete(spaceId);
			},
			deleteInvitationBySpaceId: (state, spaceId: number) => {
				state.invitations.delete(spaceId);
				state.invitationSpaceIds.delete(spaceId);
			},
			updateCounter: (state, data) => {
				const userId = data.userId;
				const spaceId = data.spaceId;
				const counter = data.counter;
				const tasksTotal = data.tasksTotal;
				const calendarTotal = data.calendarTotal;
				const workGroupTotal = data.workGroupTotal;
				const discussionsTotal = data.lifeFeedTotal;

				const space: SpaceModel = state.spaces.get(spaceId) ?? {};
				space.counter = counter;

				BX.ready(() => {
					const menu = (spaceId == 0)
						? BX.Main.interfaceButtonsManager.getById(`spaces_user_menu_${userId}`)
						: BX.Main.interfaceButtonsManager.getById(`spaces_group_menu_${spaceId}`);

					if (menu)
					{
						const btn = `spaces_top_menu_${userId}_${spaceId}`;
						const tasksBtn = `${btn}_tasks`;
						const calendarBtn = `${btn}_calendar`;
						const discussionBtn = `${btn}_discussions`;

						menu.updateCounter(tasksBtn, tasksTotal);
						menu.updateCounter(calendarBtn, calendarTotal);
						menu.updateCounter(discussionBtn, discussionsTotal);
					}
				});
			},
		},
	getters:
		{
			spaces: (state) => {
				return [...state.spaces.values()];
			},
			invitations: (state) => {
				return [...state.invitations.values()];
			},
			spaceInvitations: (state, getters) => {
				const spacesMap = state.spaces;
				const invitations = getters.invitations;

				return invitations.map((invitation: InvitationModel) => {
					const space: SpaceModel = spacesMap.get(invitation.spaceId);
					const spaceInvitationFields = {
						dateActivity: invitation.invitationDate,
						lastActivityDescription: invitation.message,
						counter: 1,
					};

					return { ...space, ...spaceInvitationFields };
				}).sort((a: SpaceModel, b: SpaceModel) => {
					return b.dateActivity - a.dateActivity;
				});
			},
			canCreateGroup: (state) => {
				return state.canCreateGroup;
			},
			spacesListState: (state) => {
				return state.spacesListState;
			},
			recentSpacesUnordered: (state, getters) => {
				const spaces = getters.spaces;
				const unsortedRecentSpaces = spaces.filter((space: SpaceModel) => {
					return state.recentListSpaceIds.has(space.id) && !state.invitationSpaceIds.has(space.id);
				});

				return unsortedRecentSpaces.sort((a: SpaceModel, b: SpaceModel) => {
					return b.dateActivity - a.dateActivity;
				});
			},
			pinnedSpacesFromRecent: (state, getters): Array => {
				return getters.recentSpacesUnordered.filter((space: SpaceModel) => space.isPinned);
			},
			commonSpaceFromRecent: (state, getters): SpaceModel | undefined => {
				return getters.recentSpacesUnordered.find((space: SpaceModel) => space.id === 0);
			},
			selectedSpaceFromRecent: (state, getters): SpaceModel | undefined => {
				return getters.recentSpacesUnordered.find((space: SpaceModel) => space.isSelected && !space.isPinned);
			},
			otherSpacesFromRecent: (state, getters): Array => {
				return getters.recentSpacesUnordered.filter((space: SpaceModel) => {
					return space.id !== getters.commonSpaceFromRecent.id && !space.isSelected && !space.isPinned;
				});
			},
			recentSpaces: (state, getters): Array => {
				const result = [];
				if (state.selectedFilterModeType === FilterModeTypes.my)
				{
					result.push(...getters.pinnedSpacesFromRecent);
				}

				if (getters.commonSpaceFromRecent && state.selectedFilterModeType !== FilterModeTypes.other)
				{
					result.push(getters.commonSpaceFromRecent);
				}

				if (getters.selectedSpaceFromRecent && getters.selectedSpaceFromRecent.id !== getters.commonSpaceFromRecent.id)
				{
					result.push(getters.selectedSpaceFromRecent);
				}

				result.push(...getters.otherSpacesFromRecent);

				return result;
			},
			searchSpaces: (state, getters) => {
				const spaces = getters.spaces;
				const searchResultIds = new Set([
					...state.searchResultFromLoadedSpaceIds,
					...state.searchResultFromServerSpaceIds,
				]);

				return spaces.filter((space: SpaceModel) => searchResultIds.has(space.id));
			},
			spacesLoadedByCurrentSearchQueryCount: (state) => {
				return state.searchResultFromServerSpaceIds.size;
			},
			recentSearchSpaces: (state, getters) => {
				const unsortedRecentSearchSpaces = getters.spaces.filter((space: SpaceModel) => {
					return state.recentSearchListSpaceIds.has(space.id);
				});

				return unsortedRecentSearchSpaces.sort((a: SpaceModel, b: SpaceModel) => {
					return b.lastSearchDate - a.lastSearchDate;
				});
			},
			recentSpacesCountForLoad: (state, getters) => {
				// Do this subtraction because of selected space and common space.
				// They are selected bypassing the sorting
				return getters.recentSpaces.length - 2;
			},
			recentSearchSpacesCountForLoad: (state, getters) => {
				return getters.recentSearchSpaces.length;
			},
			searchSpacesCountForLoad: (state, getters) => {
				return getters.spacesLoadedByCurrentSearchQueryCount;
			},
		},
};
