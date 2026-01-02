import { Loc, Runtime, Text, Type } from 'main.core';
import { type ActionTree, BuilderModel, type GetterTree, type MutationTree, type Store } from 'ui.vue3.vuex';
import { ServiceLocator } from '../../service/service-locator';
import type { AccessRightItem, AccessRightSection } from './access-rights-model';

export type UserGroupsState = {
	collection: UserGroupsCollection,
	deleted: Set<string>,
	selectedMember: SelectedMember,
	sortConfig: Record<string, Record<string, number>>,
}

export type UserGroupsStore = Store<UserGroupsState>;

export type UserGroupsCollection = Map<string, UserGroup>;

// aka Role
export type UserGroup = {
	id: string,
	isNew: boolean,
	isModified: boolean, // whether group metadata is modified - title, members
	title: string,
	accessRights: Map<string, AccessRightValue>,
	members: MemberCollection, // access code => member
};

export type AccessRightValue = {
	id: string,
	values: Set<string>,
	isModified: boolean,
};

export type MemberCollection = Map<string, Member>; // access code => member

// user/group/department/set of users
export type Member = {
	id: string,
	type: ?string, // see main/install/components/bitrix/main.ui.selector/templates/.default/script.js
	name: ?string,
	avatar: ?string,
};

type SetAccessRightValuesPayload = SetAccessRightValuesForShownPayload & {
	userGroupId: string,
};

type SetAccessRightValuesForShownPayload = {
	sectionCode: string,
	valueId: string,
	values: Set<string>,
};

export type UserGroupsOption = {
	selectedMember: SelectedMember,
	sortConfig: Record<string, Record<string, number>>,
};

export type SelectedMember = {
	id: string,
	member: ?Member,
	accessCodes: string[],
}

export const NEW_USER_GROUP_ID_PREFIX = 'new~~~';

export const SELECTED_ALL_USER_ID = 'all-users';

export class UserGroupsModel extends BuilderModel
{
	#initialUserGroups: UserGroupsCollection = new Map();
	#sortConfig: Record<string, Record<string, number>> = {};
	#selectedMember: SelectedMember;

	getName(): string
	{
		return 'userGroups';
	}

	setInitialUserGroups(groups: UserGroupsCollection): UserGroupsModel
	{
		this.#initialUserGroups = groups;

		return this;
	}

	setSortConfig(sortConfig: Record<string, Record<string, number>>): UserGroupsModel
	{
		this.#sortConfig = sortConfig;

		return this;
	}

	setSelectedMember(selectedMember: SelectedMember): UserGroupsModel
	{
		this.#selectedMember = selectedMember;

		return this;
	}

	getState(): UserGroupsState
	{
		return {
			collection: Runtime.clone(this.#initialUserGroups),
			deleted: new Set(),
			selectedMember: this.#selectedMember,
			sortConfig: this.#sortConfig,
		};
	}

	getElementState(params = {}): UserGroup
	{
		return {
			id: `${NEW_USER_GROUP_ID_PREFIX}${Text.getRandom()}`,
			isNew: true,
			isModified: true,
			title: Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLE_NAME'),
			accessRights: new Map(),
			members: new Map(),
		};
	}

	#getUserGroupsCollectionBySelectedMember(state: UserGroupsState): UserGroupsCollection
	{
		const result = new Map();
		const selectedMemberId = state.selectedMember?.id ?? SELECTED_ALL_USER_ID;
		const accessCodes = state.selectedMember?.accessCodes ? [...(state.selectedMember.accessCodes)] : [];

		for (const [userGroupId, userGroup] of state.collection)
		{
			if (selectedMemberId === SELECTED_ALL_USER_ID || accessCodes.some((code) => userGroup.members.has(code)))
			{
				result.set(userGroupId, userGroup);
			}
		}

		return result;
	}

	getGetters(): GetterTree<UserGroupsState>
	{
		return {
			shown: (state, getters, rootState): UserGroupsCollection => {
				const selectedMemberId = state.selectedMember?.id ?? SELECTED_ALL_USER_ID;
				const collection = this.#getUserGroupsCollectionBySelectedMember(state);

				if (!state.sortConfig || !state.sortConfig[selectedMemberId])
				{
					if (rootState.application.options.maxVisibleUserGroups > 0)
					{
						return new Map([...collection].slice(0, rootState.application.options.maxVisibleUserGroups));
					}

					return collection;
				}

				const sortedGroups = [...collection]
					.filter(([userGroupId]) => state.sortConfig[selectedMemberId][userGroupId] >= 0)
					.sort(
						([idA], [idB]) => (state.sortConfig[selectedMemberId][idA] ?? Infinity)
							- (state.sortConfig[selectedMemberId][idB] ?? Infinity),
					);

				if (rootState.application.options.maxVisibleUserGroups > 0)
				{
					return new Map(sortedGroups.slice(0, rootState.application.options.maxVisibleUserGroups));
				}

				return new Map(sortedGroups);
			},
			userGroupsBySelectedMember: (state): UserGroupsCollection => {
				return this.#getUserGroupsCollectionBySelectedMember(state);
			},
			getEmptyAccessRightValue: (state, getters, rootState, rootGetters) => (userGroupId: string, sectionCode: string, valueId: string): AccessRightValue => {
				const values = rootGetters['accessRights/getEmptyValue'](sectionCode, valueId);

				return {
					id: valueId,
					values,
					isModified: state.collection.get(userGroupId).isNew,
				};
			},
			defaultAccessRightValues: (state, getters, rootState): Map<string, AccessRightValue> => {
				const result = new Map();

				for (const section of rootState.accessRights.collection.values())
				{
					for (const [rightId, right] of section.rights)
					{
						if (Type.isNil(right.defaultValue))
						{
							continue;
						}

						result.set(rightId, {
							id: rightId,
							values: right.defaultValue,
							isModified: true,
						});
					}
				}

				return result;
			},
			isModified: (state): boolean => {
				if (state.deleted.size > 0)
				{
					return true;
				}

				for (const userGroup of state.collection.values())
				{
					if (userGroup.isNew || userGroup.isModified)
					{
						return true;
					}

					for (const value of userGroup.accessRights.values())
					{
						if (value.isModified)
						{
							return true;
						}
					}
				}

				return false;
			},
			isMaxVisibleUserGroupsReached: (state, getters, rootState, rootGetters): boolean => {
				if (!rootGetters['application/isMaxVisibleUserGroupsSet'])
				{
					return false;
				}

				return getters.shown.size >= rootState.application.options.maxVisibleUserGroups;
			},
		};
	}

	getActions(): ActionTree<UserGroupsState>
	{
		return {
			setAccessRightValues: (store, payload): void => {
				this.#setAccessRightValuesAction(store, payload);
			},
			setAccessRightValuesForShown: (store, payload): void => {
				this.#setAccessRightValuesForShownAction(store, payload);
			},
			setMinAccessRightValues: (store, payload): void => {
				this.#setMinAccessRightValuesAction(store, payload);
			},
			setMaxAccessRightValues: (store, payload): void => {
				this.#setMaxAccessRightValuesAction(store, payload);
			},
			setMinAccessRightValuesInSection: (store, payload): void => {
				this.#setMinAccessRightValuesInSectionAction(store, payload);
			},
			setMaxAccessRightValuesInSection: (store, payload): void => {
				this.#setMaxAccessRightValuesInSectionAction(store, payload);
			},
			setMinAccessRightValuesForRight: (store, payload): void => {
				this.#setMinAccessRightValuesForRight(store, payload);
			},
			setMaxAccessRightValuesForRight: (store, payload): void => {
				this.#setMaxAccessRightValuesForRight(store, payload);
			},
			setRoleTitle: (store, payload): void => {
				this.#setRoleTitleAction(store, payload);
			},
			addMember: (store, payload): void => {
				this.#addMemberAction(store, payload);
			},
			removeMember: (store, payload): void => {
				this.#removeMemberAction(store, payload);
			},
			updateMembersForUserGroup: (store, payload): void => {
				this.#updateMembersForUserGroupAction(store, payload);
			},
			copyUserGroup: (store, payload): void => {
				this.#copyUserGroupAction(store, payload);
			},
			copySectionValues: (store, payload): void => {
				this.#copySectionValuesAction(store, payload);
			},
			addUserGroup: (store): void => {
				this.#addUserGroupAction(store);
			},
			removeUserGroup: (store, payload): void => {
				this.#removeUserGroupAction(store, payload);
			},
			updateUserGroupSort: (store, payload): void => {
				this.#updateUserGroupSortAction(store, payload);
			},
			updateSortConfigForSelectedMember: (store, payload): void => {
				this.#updateSortConfigForSelectedMemberAction(store, payload);
			},
			updateSortConfig: (store, payload): void => {
				this.#updateSortConfigAction(store, payload);
			},
			deleteRight: (store, payload): void => {
				this.#deleteRightAction(store, payload);
			},
			selectMember: (store, payload): void => {
				this.#selectMemberAction(store, payload);
			},
		};
	}

	#setAccessRightValuesAction(store: UserGroupsStore, payload: SetAccessRightValuesPayload): void
	{
		if (!Type.isSet(payload.values))
		{
			console.warn('ui.accessrights.v2: Attempt to set not-Set values', payload);

			return;
		}

		if (!this.#isUserGroupExists(store, payload.userGroupId))
		{
			console.warn('ui.accessrights.v2: Attempt to set value to a user group that dont exists', payload);

			return;
		}

		if (!this.#isValueExistsInStructure(store, payload.sectionCode, payload.valueId))
		{
			console.warn('ui.accessrights.v2: Attempt to set value to a right that dont exists in structure', payload);

			return;
		}

		store.commit('setAccessRightValues', {
			userGroupId: payload.userGroupId,
			valueId: payload.valueId,
			values: payload.values,
			isModified: this.#isValueModified(
				payload.userGroupId,
				payload.valueId,
				payload.values,
				store.rootGetters['accessRights/getEmptyValue'](payload.sectionCode, payload.valueId),
			),
		});
	}

	#setAccessRightValuesForShownAction(store: UserGroupsStore, payload: SetAccessRightValuesForShownPayload): void
	{
		for (const userGroupId of store.getters.shown.keys())
		{
			void store.dispatch('setAccessRightValues', {
				...payload,
				userGroupId,
			});
		}
	}

	#setMinAccessRightValuesAction(store: UserGroupsStore, { userGroupId }): void
	{
		for (const sectionCode: string of store.rootState.accessRights.collection.keys())
		{
			void store.dispatch('setMinAccessRightValuesInSection', { userGroupId, sectionCode });
		}

		void store.dispatch('accessRights/expandAllSections', null, { root: true });
	}

	#setMinAccessRightValuesInSectionAction(store: UserGroupsStore, { userGroupId, sectionCode }): void
	{
		const section: ?AccessRightSection = store.rootState.accessRights.collection.get(sectionCode);
		if (!section)
		{
			console.warn('ui.accessrights.v2: attempt to set min values in section that dont exists', { sectionCode });

			return;
		}

		for (const item of section.rights.values())
		{
			const valueToSet = this.#getMinValueForColumnAction(
				item,
				store.rootGetters['accessRights/getEmptyValue'](section.sectionCode, item.id),
			);
			if (Type.isNil(valueToSet))
			{
				continue;
			}

			void store.dispatch('setAccessRightValues', {
				userGroupId,
				sectionCode: section.sectionCode,
				valueId: item.id,
				values: valueToSet,
			});
		}
	}

	#setMinAccessRightValuesForRight(store: UserGroupsStore, { sectionCode, rightId }): void
	{
		const right: ?AccessRightItem = store.rootState.accessRights.collection.get(sectionCode)?.rights.get(rightId);
		if (!right)
		{
			console.warn(
				'ui.accessrights.v2: attempt to set min values for right that dont exists',
				{ sectionCode, rightId },
			);

			return;
		}

		const valueToSet = this.#getMinValue(right);
		if (Type.isNil(valueToSet))
		{
			console.warn(
				'ui.accessrights.v2: attempt to set min values for right that dont have min value set',
				{ sectionCode, rightId },
			);

			return;
		}

		void store.dispatch('setAccessRightValuesForShown', {
			sectionCode,
			valueId: rightId,
			values: valueToSet,
		});
	}

	#getMinValueForColumnAction(item: AccessRightItem, emptyValue: Set<string>): ?Set<string>
	{
		const setEmpty = Type.isBoolean(item.setEmptyOnSetMinMaxValueInColumn) && item.setEmptyOnSetMinMaxValueInColumn;
		if (setEmpty)
		{
			return emptyValue;
		}

		return this.#getMinValue(item);
	}

	#getMinValue(item: AccessRightItem): ?Set<string>
	{
		return ServiceLocator.getValueTypeByRight(item)?.getMinValue(item);
	}

	#setMaxAccessRightValuesAction(store: UserGroupsStore, { userGroupId }): void
	{
		for (const sectionCode: string of store.rootState.accessRights.collection.keys())
		{
			void store.dispatch('setMaxAccessRightValuesInSection', { userGroupId, sectionCode });
		}

		void store.dispatch('accessRights/expandAllSections', null, { root: true });
	}

	#setMaxAccessRightValuesInSectionAction(store: UserGroupsStore, { userGroupId, sectionCode }): void
	{
		const section: ?AccessRightSection = store.rootState.accessRights.collection.get(sectionCode);
		if (!section)
		{
			console.warn('ui.accessrights.v2: attempt to set max values in section that dont exists', { sectionCode });

			return;
		}

		for (const item of section.rights.values())
		{
			const valueToSet = this.#getMaxValueForColumnAction(
				item,
				store.rootGetters['accessRights/getEmptyValue'](section.sectionCode, item.id),
			);
			if (Type.isNil(valueToSet))
			{
				continue;
			}

			void store.dispatch('setAccessRightValues', {
				userGroupId,
				sectionCode: section.sectionCode,
				valueId: item.id,
				values: valueToSet,
			});
		}
	}

	#setMaxAccessRightValuesForRight(store: UserGroupsStore, { sectionCode, rightId }): void
	{
		const right: ?AccessRightItem = store.rootState.accessRights.collection.get(sectionCode)?.rights.get(rightId);
		if (!right)
		{
			console.warn(
				'ui.accessrights.v2: attempt to set max values for right that dont exists',
				{ sectionCode, rightId },
			);

			return;
		}

		const valueToSet = this.#getMaxValue(right);
		if (Type.isNil(valueToSet))
		{
			console.warn(
				'ui.accessrights.v2: attempt to set max values for right that dont have max value set',
				{ sectionCode, rightId },
			);

			return;
		}

		void store.dispatch('setAccessRightValuesForShown', {
			sectionCode,
			valueId: rightId,
			values: valueToSet,
		});
	}

	#getMaxValueForColumnAction(item: AccessRightItem, emptyValue: Set<string>): ?Set<string>
	{
		const setEmpty = Type.isBoolean(item.setEmptyOnSetMinMaxValueInColumn) && item.setEmptyOnSetMinMaxValueInColumn;
		if (setEmpty)
		{
			return emptyValue;
		}

		return this.#getMaxValue(item);
	}

	#getMaxValue(item: AccessRightItem): ?Set<string>
	{
		return ServiceLocator.getValueTypeByRight(item)?.getMaxValue(item);
	}

	#copySectionValuesAction(
		store: UserGroupsStore,
		payload: { srcUserGroupId: string, dstUserGroupId: string, sectionCode: string },
	): void
	{
		const src = this.#getUserGroup(store.state, payload.srcUserGroupId);
		if (!src)
		{
			console.warn('ui.accessrights.v2: Attempt to copy values from user group that dont exists', payload);

			return;
		}

		const section: ?AccessRightSection = store.rootState.accessRights.collection.get(payload.sectionCode);
		if (!section)
		{
			console.warn('ui.accessrights.v2: Attempt to copy values for section that dont exists', payload);

			return;
		}

		for (const rightId of section.rights.keys())
		{
			const value = src.accessRights.get(rightId);
			if (value)
			{
				void store.dispatch('setAccessRightValues', {
					userGroupId: payload.dstUserGroupId,
					sectionCode: section.sectionCode,
					valueId: value.id,
					values: value.values,
				});
			}
			else
			{
				const emptyValue = store.rootGetters['accessRights/getEmptyValue'](section.sectionCode, rightId);

				void store.dispatch('setAccessRightValues', {
					userGroupId: payload.dstUserGroupId,
					sectionCode: section.sectionCode,
					valueId: rightId,
					values: emptyValue,
				});
			}
		}
	}

	#setRoleTitleAction(store: UserGroupsStore, payload: {userGroupId: string, title: string}): void
	{
		if (!Type.isString(payload.title))
		{
			console.warn('ui.accessrights.v2: Attempt to set role title with something other than string', payload);

			return;
		}

		if (!this.#isUserGroupExists(store, payload.userGroupId))
		{
			console.warn('ui.accessrights.v2: Attempt to update user group that dont exists', payload);

			return;
		}

		store.commit('setRoleTitle', payload);
	}

	#addMemberAction(store: UserGroupsStore, payload: {userGroupId: string, accessCode: string, member: Member }): void
	{
		if (!this.#isUserGroupExists(store, payload.userGroupId))
		{
			console.warn('ui.accessrights.v2: Attempt to add member to a user group that dont exists', payload);

			return;
		}

		if (
			!Type.isStringFilled(payload.accessCode)
			|| !this.#isMemberValid(payload)
		)
		{
			console.warn('ui.accessrights.v2: Attempt to add member with invalid payload', payload);

			return;
		}

		store.commit('addMember', payload);
	}

	#isMemberValid(payload: {member: Member}): boolean
	{
		return Type.isStringFilled(payload.member.id)
			&& Type.isStringFilled(payload.member.type)
			&& Type.isStringFilled(payload.member.name)
			&& (Type.isNil(payload.member.avatar) || Type.isStringFilled(payload.member.avatar));
	}

	#removeMemberAction(store: UserGroupsStore, payload: {userGroupId: string, accessCode: string }): void
	{
		if (!this.#isUserGroupExists(store, payload.userGroupId))
		{
			console.warn('ui.accessrights.v2: Attempt to remove member from a user group that dont exists', payload);

			return;
		}

		if (!Type.isStringFilled(payload.accessCode))
		{
			console.warn('ui.accessrights.v2: Attempt to remove member with invalid payload', payload);

			return;
		}

		store.commit('removeMember', payload);
	}

	#updateMembersForUserGroupAction(store: UserGroupsStore, payload: {userGroupId: string, members: array<Member> }): void
	{
		if (!this.#isUserGroupExists(store, payload.userGroupId))
		{
			console.warn('ui.accessrights.v2: Attempt to remove member from a user group that dont exists', payload);

			return;
		}

		const memberCollection = new Map();
		payload.members.forEach((member) => {
			if (
				!Type.isStringFilled(member.id)
				|| !Type.isStringFilled(member.type)
				|| !Type.isStringFilled(member.name)
				|| !(Type.isNil(member.avatar) || Type.isStringFilled(member.avatar))
			)
			{
				console.warn('ui.accessrights.v2: Attempt to add member with invalid payload', member);
			}
			else
			{
				memberCollection.set(member.id, member);
			}
		});

		store.commit('updateMembersForUserGroup', {
			userGroupId: payload.userGroupId,
			memberCollection,
		});
	}

	#copyUserGroupAction(store: UserGroupsStore, { userGroupId }): void
	{
		const sourceGroup = this.#getUserGroup(store.state, userGroupId);

		if (!sourceGroup)
		{
			console.warn('ui.accessrights.v2: Attempt to copy user group that dont exists', { userGroupId });

			return;
		}

		const emptyGroup = this.getElementState();

		const copy: UserGroup = {
			...Runtime.clone(sourceGroup),
			id: emptyGroup.id,
			title: Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_COPIED_ROLE_NAME', {
				'#ORIGINAL#': sourceGroup.title,
			}),
			isNew: true,
			isModified: true,
		};

		for (const value of copy.accessRights.values())
		{
			// is a new group all values are modified
			value.isModified = true;
		}

		const updatedSortConfig = this.#updateSortConfig(
			store.state.sortConfig,
			copy.id,
		);

		this.#updateSortConfigAction(store, { sortConfig: updatedSortConfig });

		store.commit('addUserGroup', {
			userGroup: copy,
		});
	}

	#addUserGroupAction(store: UserGroupsStore): void
	{
		const newGroup = {
			...this.getElementState(),
			accessRights: Runtime.clone(store.getters.defaultAccessRightValues),
			members: new Map(),
		};

		if (store.state.selectedMember && store.state.selectedMember.member)
		{
			newGroup.members.set(store.state.selectedMember.member.id, store.state.selectedMember.member);
		}

		const updatedSortConfig = this.#updateSortConfig(
			store.state.sortConfig,
			newGroup.id,
			store.state.selectedMember?.id,
		);

		this.#updateSortConfigAction(store, { sortConfig: updatedSortConfig });
		store.commit('addUserGroup', { userGroup: newGroup });
	}

	#removeUserGroupAction(store: UserGroupsStore, { userGroupId }): void
	{
		const userGroup = this.#getUserGroup(store.state, userGroupId);
		if (!userGroup)
		{
			console.warn('ui.accessrights.v2: Attempt to remove user group that dont exists', { userGroupId });

			return;
		}

		store.commit('removeUserGroup', { userGroupId });
		if (!userGroup.isNew)
		{
			store.commit('markUserGroupForDeletion', { userGroupId });
		}
	}

	#updateUserGroupSortAction(store: UserGroupsStore, { userGroupId, sort }): void
	{
		if (!this.#isUserGroupExists(store, userGroupId))
		{
			console.warn('ui.accessrights.v2: Attempt to show user group that dont exists', { userGroupId });

			return;
		}

		store.commit('updateUserGroupSort', { userGroupId, sort });
	}

	#updateSortConfigForSelectedMemberAction(store: UserGroupsStore, { sortConfigForSelectedMember }): void
	{
		if (!this.#isValidSortConfigForSelectedMember(sortConfigForSelectedMember))
		{
			console.warn('ui.accessrights.v2: Invalid sort configuration provided', sortConfigForSelectedMember);

			return;
		}

		store.commit('updateSortConfigForSelectedMember', { sortConfigForSelectedMember });
	}

	#updateSortConfigAction(store: UserGroupsStore, { sortConfig }): void
	{
		if (!this.#isValidSortConfig(sortConfig))
		{
			console.warn('ui.accessrights.v2: Invalid sort configuration provided', sortConfig);

			return;
		}

		store.commit('updateSortConfig', { sortConfig });
	}

	#selectMemberAction(store: UserGroupsStore, payload: { member: SelectedMember }): void
	{
		if (!this.#isValidSelectedMember(payload.member))
		{
			console.warn('ui.accessrights.v2: Invalid selected member provided', payload.member);

			return;
		}

		store.commit('selectMember', Runtime.clone(payload));
	}

	#hideUserGroupAction(store: UserGroupsState, { userGroupId }): void
	{
		if (!this.#isUserGroupExists(store, userGroupId))
		{
			console.warn('ui.accessrights.v2: Attempt to shrink user group that dont exists', { userGroupId });

			return;
		}

		store.commit('hideUserGroup', { userGroupId });
	}

	#updateSortConfig(
		currentSorting: Record<string, Record<string, number>>,
		groupId: string,
		selectedMemberId?: string,
	): Record<string, Record<string, number>> {
		const userIdsToUpdate = new Set([SELECTED_ALL_USER_ID]);
		if (selectedMemberId)
		{
			userIdsToUpdate.add(selectedMemberId);
		}

		const newSortConfig = { ...currentSorting };

		for (const userId of userIdsToUpdate)
		{
			if (!newSortConfig[userId])
			{
				continue;
			}

			const currentValues = Object.values(newSortConfig[userId]);
			const maxValue = currentValues.length > 0 ? Math.max(...currentValues) : 0;

			newSortConfig[userId] = {
				...newSortConfig[userId],
				[groupId]: maxValue + 1,
			};
		}

		return newSortConfig;
	}

	#isUserGroupExists(store, userGroupId: string): boolean
	{
		const group = this.#getUserGroup(store.state, userGroupId);

		return Boolean(group);
	}

	#isValidSortConfigForSelectedMember(config: Record<string, number>): boolean {
		return Object.values(config).every(
			(value) => Type.isNumber(value),
		);
	}

	#isValidSortConfig(config: Record<string, Record<string, number>>): boolean {
		return Object.values(config).every(
			(userConfig) => this.#isValidSortConfigForSelectedMember(userConfig),
		);
	}

	#isValidSelectedMember(selectedMember: SelectedMember): boolean {
		if (selectedMember.id === SELECTED_ALL_USER_ID)
		{
			return true;
		}

		return (
			Type.isString(selectedMember.id)
			&& Type.isObject(selectedMember.member)
			&& Type.isArray(selectedMember.accessCodes)
		);
	}

	#getUserGroup(state: UserGroupsState, userGroupId: string): ?UserGroup
	{
		return state.collection.get(userGroupId);
	}

	#isValueExistsInStructure(store, sectionCode: string, valueId: string): boolean
	{
		const section: ?AccessRightSection = store.rootState.accessRights.collection.get(sectionCode);

		return section?.rights.has(valueId);
	}

	#deleteRightAction(store: UserGroupsState, { rightId }): void
	{
		store.commit('deleteRight', { rightId });
	}

	getMutations(): MutationTree<UserGroupsState>
	{
		return {
			setAccessRightValues: (state, { userGroupId, valueId, values, isModified }) => {
				const userGroup = this.#getUserGroup(state, userGroupId);

				const accessRightValue = userGroup.accessRights.get(valueId);

				if (!accessRightValue)
				{
					userGroup.accessRights.set(
						valueId,
						{
							id: valueId,
							values,
							isModified,
						},
					);

					return;
				}

				accessRightValue.values = values;
				accessRightValue.isModified = isModified;
			},
			setRoleTitle: (state, { userGroupId, title }) => {
				const userGroup = this.#getUserGroup(state, userGroupId);
				userGroup.title = title;
				userGroup.isModified = this.#isUserGroupModified(userGroup);
			},
			addMember: (state, { userGroupId, accessCode, member }) => {
				const userGroup = this.#getUserGroup(state, userGroupId);
				userGroup.members.set(accessCode, member);
				userGroup.isModified = this.#isUserGroupModified(userGroup);
			},
			removeMember: (state, { userGroupId, accessCode }) => {
				const userGroup = this.#getUserGroup(state, userGroupId);
				userGroup.members.delete(accessCode);
				userGroup.isModified = this.#isUserGroupModified(userGroup);
			},
			updateMembersForUserGroup: (state, { userGroupId, memberCollection }) => {
				const userGroup = this.#getUserGroup(state, userGroupId);
				userGroup.members = memberCollection;
				userGroup.isModified = this.#isUserGroupModified(userGroup);
			},
			addUserGroup: (state, { userGroup }) => {
				state.collection.set(userGroup.id, userGroup);
			},
			removeUserGroup: (state, { userGroupId }) => {
				state.collection.delete(userGroupId);
			},
			markUserGroupForDeletion: (state, { userGroupId }) => {
				state.deleted.add(userGroupId);
			},
			updateSortConfigForSelectedMember: (state, { sortConfigForSelectedMember }) => {
				const selectedMemberId = state.selectedMember?.id ?? SELECTED_ALL_USER_ID;
				// eslint-disable-next-line no-param-reassign
				state.sortConfig[selectedMemberId] = sortConfigForSelectedMember;
			},
			updateSortConfig: (state, { sortConfig }) => {
				// eslint-disable-next-line no-param-reassign
				state.sortConfig = sortConfig;
			},
			deleteRight: (state: UserGroupsState, { rightId }) => {
				for (const role: UserGroup of state.collection.values())
				{
					if (role.accessRights.get(rightId))
					{
						role.accessRights.delete(rightId);
					}
				}
			},
			selectMember: (state, { member }) => {
				// eslint-disable-next-line no-param-reassign
				state.selectedMember = member;
			},
		};
	}

	#isValueModified(userGroupId: string, valueId: string, values: Set<string>, emptyValue: Set<string>): boolean
	{
		const initialGroup = this.#initialUserGroups.get(userGroupId);
		if (!initialGroup)
		{
			// its a newly created group, all values are modified

			return true;
		}

		const initialValues = initialGroup.accessRights.get(valueId)?.values ?? emptyValue;

		// use native Sets instead of Vue-wrapped proxy-sets, they throw an error on `symmetricDifference`
		return !this.#isSetsEqual(new Set(initialValues), new Set(values));
	}

	#isSetsEqual(a: Set, b: Set): boolean
	{
		if (Type.isFunction(a.symmetricDifference))
		{
			// native way to compare sets for modern browsers
			return a.symmetricDifference(b).size === 0;
		}

		// polyfill

		if (a.size !== b.size)
		{
			return false;
		}

		for (const value of a)
		{
			if (!b.has(value))
			{
				return false;
			}
		}

		for (const value of b)
		{
			if (!a.has(value))
			{
				return false;
			}
		}

		return true;
	}

	#isUserGroupModified(userGroup: UserGroup): boolean
	{
		if (userGroup.isNew)
		{
			return true;
		}

		const initialGroup = this.#initialUserGroups.get(userGroup.id);
		if (!initialGroup)
		{
			throw new Error('ui.accessrights.v2: initial user group not found');
		}

		if (userGroup.title !== initialGroup.title)
		{
			return true;
		}

		const initialAccessCodes = new Set(initialGroup.members.keys());
		const currentAccessCodes = new Set(userGroup.members.keys());

		return !this.#isSetsEqual(initialAccessCodes, currentAccessCodes);
	}
}
