import { Helper } from './helper';
import { FilterModeTypes } from '../const/filter-mode';

import { RecentService } from '../api/load/recent-service';

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
			setSelectedSpace: (store, selectedSpaceId) => {
				const previousSelectedSpaceId = RecentService.getInstance().getSelectedSpaceId();
				RecentService.getInstance().setSelectedSpaceId(selectedSpaceId);

				store.commit('setSelectedSpace', {
					spaceId: previousSelectedSpaceId,
					selected: false,
				});
				store.commit('setSelectedSpace', {
					spaceId: selectedSpaceId,
					selected: true,
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
					if (BX.getClass('BX.Intranet.LeftMenu')) {
						data.spaces.forEach((space) => {
							if (space.id === 0)
							{
								const leftMenuCounters = {
									spaces: total,
									sonet_total: space.metrics.countersLiveFeedTotal,
								};
								BX.Intranet.LeftMenu.updateCounters(leftMenuCounters, false);
							}
						});
					}
				});

				// empty the existing space counters
				store.getters.spaces.forEach((space) => {
					store.commit('updateCounter', {
						userId,
						spaceId: space.id,
						counter: 0,
						tasksTotal: 0,
						calendarTotal: 0,
						workGroupTotal: 0,
						lifeFeedTotal: 0,
					});
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
				if (data.checkInvitation !== false)
				{
					if (data.space && data.isInvitation)
					{
						store.commit('addInvitations', Helper.getInstance().buildInvitations([data.invitation]));
					}
					else
					{
						store.commit('deleteInvitationBySpaceId', data.spaceId);
					}
				}

				if (data.space)
				{
					const helper = Helper.getInstance();
					const space: SpaceModel = helper.buildSpaces([data.space]).pop();
					const lastRecentSpace: SpaceModel = store.getters.recentSpaces[store.getters.recentSpaces.length - 1];
					store.commit('addSpaces', [space]);

					if (helper.doAddSpaceToRecentList(space, lastRecentSpace, store.state.selectedFilterModeType))
					{
						store.commit('addRecentListSpaceId', space.id);
					}
					else if (!helper.doSpaceUserRoleFitsFilterMode(space.userRole, store.state.selectedFilterModeType))
					{
						store.commit('removeRecentListSpaceId', space.id);
					}
				}
				else
				{
					store.commit('deleteSpaceById', data.spaceId);
				}
			},
			updateSpaceRecentActivityData: (store, recentActivityData) => {
				store.commit('updateSpaceRecentActivityData', recentActivityData);

				const space: SpaceModel = store.state.spaces.get(recentActivityData.spaceId);
				if (!space)
				{
					return;
				}
				const helper = Helper.getInstance();
				const lastRecentSpace: SpaceModel = store.getters.recentSpaces[store.getters.recentSpaces.length - 1];

				if (helper.doAddSpaceToRecentList(space, lastRecentSpace, store.state.selectedFilterModeType))
				{
					store.commit('addRecentListSpaceId', space.id);
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
			setSelectedSpace: (state, selectedState) => {
				const space: SpaceModel | undefined = state.spaces.get(selectedState.spaceId);
				if (space)
				{
					space.isSelected = selectedState.selected;
				}
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
				const spaceId = data.spaceId;
				const counter = data.counter;

				const space: SpaceModel = state.spaces.get(spaceId) ?? {};
				space.counter = counter;
			},
			updateSpaceRecentActivityData: (state, recentActivityData) => {
				const space: SpaceModel = state.spaces.get(recentActivityData.spaceId);
				if (!space)
				{
					return;
				}

				space.recentActivity = Helper.getInstance().buildRecentActivity(recentActivityData);
			},
		},
	getters:
	{
		spaces: (state): Array<SpaceModel> => {
			return [...state.spaces.values()];
		},
		invitations: (state): Array<InvitationModel> => {
			return [...state.invitations.values()];
		},
		spaceInvitations: (state, getters): Array<SpaceModel> => {
			const spacesMap = state.spaces;
			const invitations = getters.invitations;

			return invitations.map((invitation: InvitationModel) => {
				const space: SpaceModel = spacesMap.get(invitation.spaceId);
				const spaceInvitationFields = {
					recentActivity: {
						...space.recentActivity,
						description: invitation.message,
						date: invitation.invitationDate,
						timestamp: invitation.invitationDate.getTime(),
					},
					counter: 1,
				};

				return { ...space, ...spaceInvitationFields };
			}).sort((a: SpaceModel, b: SpaceModel) => {
				return b.recentActivity.date - a.recentActivity.date;
			});
		},
		canCreateGroup: (state) => {
			return state.canCreateGroup;
		},
		spacesListState: (state) => {
			return state.spacesListState;
		},
		recentSpacesUnordered: (state, getters): Array<SpaceModel> => {
			const spaces = getters.spaces;
			const unsortedRecentSpaces = spaces.filter((space: SpaceModel) => {
				return state.recentListSpaceIds.has(space.id) && !state.invitationSpaceIds.has(space.id);
			});

			return unsortedRecentSpaces.sort((a: SpaceModel, b: SpaceModel) => {
				return b.recentActivity.date - a.recentActivity.date;
			});
		},
		recentSpaces: (state, getters): Array<SpaceModel> => {
			let result = [];
			switch (state.selectedFilterModeType)
			{
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
		myRecentSpaces: (state, getters): Array<SpaceModel> => {
			return [
				...getters.pinnedSpacesFromRecent,
				getters.commonSpaceFromRecent,
				...getters.notPinnedSpacesWithoutCommonFromRecent,
			];
		},
		otherRecentSpaces: (state, getters): Array<SpaceModel> => {
			return [...getters.spacesWithoutCommonFromRecent];
		},
		allRecentSpaces: (state, getters): Array<SpaceModel> => {
			return [getters.commonSpaceFromRecent, ...getters.spacesWithoutCommonFromRecent];
		},
		commonSpaceFromRecent: (state, getters): SpaceModel | undefined => {
			return getters.recentSpacesUnordered.find((space: SpaceModel) => space.id === 0);
		},
		spacesWithoutCommonFromRecent: (state, getters): Array<SpaceModel> => {
			return getters.recentSpacesUnordered.filter((space: SpaceModel) => {
				return space.id !== getters.commonSpaceFromRecent.id;
			});
		},
		pinnedSpacesFromRecent: (state, getters): Array<SpaceModel> => {
			return getters.recentSpacesUnordered.filter((space: SpaceModel) => space.isPinned);
		},
		notPinnedSpacesWithoutCommonFromRecent: (state, getters): Array<SpaceModel> => {
			return getters.spacesWithoutCommonFromRecent.filter((space: SpaceModel) => !space.isPinned);
		},
		searchSpaces: (state, getters): Array<SpaceModel> => {
			const spaces = getters.spaces;
			const searchResultIds = new Set([
				...state.searchResultFromLoadedSpaceIds,
				...state.searchResultFromServerSpaceIds,
			]);

			return spaces.filter((space: SpaceModel) => searchResultIds.has(space.id));
		},
		spacesLoadedByCurrentSearchQueryCount: (state): number => {
			return state.searchResultFromServerSpaceIds.size;
		},
		recentSearchSpaces: (state, getters): Array<SpaceModel> => {
			const unsortedRecentSearchSpaces = getters.spaces.filter((space: SpaceModel) => {
				return state.recentSearchListSpaceIds.has(space.id);
			});

			return unsortedRecentSearchSpaces.sort((a: SpaceModel, b: SpaceModel) => {
				return b.lastSearchDate - a.lastSearchDate;
			});
		},
		recentSpacesCountForLoad: (state, getters): number => {
			// Do this subtraction because of common space.
			// It is selected bypassing the sorting
			return getters.recentSpaces.length - 1;
		},
		recentSearchSpacesCountForLoad: (state, getters): number => {
			return getters.recentSearchSpaces.length;
		},
		searchSpacesCountForLoad: (state, getters): number => {
			return getters.spacesLoadedByCurrentSearchQueryCount;
		},
	},
};
