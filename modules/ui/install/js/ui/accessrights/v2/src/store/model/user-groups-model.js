import { Loc, Runtime, Text, Type } from 'main.core';
import { type ActionTree, BuilderModel, type GetterTree, type MutationTree, type Store } from 'ui.vue3.vuex';
import { ServiceLocator } from '../../service/service-locator';
import type { AccessRightItem, AccessRightSection } from './access-rights-model';

export type UserGroupsState = {
	collection: UserGroupsCollection,
	deleted: Set<string>,
}

export type UserGroupsStore = Store<UserGroupsState>;

export type UserGroupsCollection = Map<string, UserGroup>;

// aka Role
export type UserGroup = {
	id: string,
	isNew: boolean,
	isModified: boolean, // whether group metadata is modified - title, members
	isShown: boolean,
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
	type: string, // see main/install/components/bitrix/main.ui.selector/templates/.default/script.js
	id: string,
	name: string,
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

export const NEW_USER_GROUP_ID_PREFIX = 'new~~~';

export class UserGroupsModel extends BuilderModel
{
	#initialUserGroups: UserGroupsCollection = new Map();

	getName(): string
	{
		return 'userGroups';
	}

	setInitialUserGroups(groups: UserGroupsCollection): UserGroupsModel
	{
		this.#initialUserGroups = groups;

		return this;
	}

	getState(): UserGroupsState
	{
		return {
			collection: Runtime.clone(this.#initialUserGroups),
			deleted: new Set(),
		};
	}

	getElementState(params = {}): UserGroup
	{
		return {
			id: `${NEW_USER_GROUP_ID_PREFIX}${Text.getRandom()}`,
			isNew: true,
			isModified: true,
			isShown: true,
			title: Loc.getMessage('JS_UI_ACCESSRIGHTS_V2_ROLE_NAME'),
			accessRights: new Map(),
			members: new Map(),
		};
	}

	getGetters(): GetterTree<UserGroupsState>
	{
		return {
			shown: (state): UserGroupsCollection => {
				const result = new Map();

				for (const [userGroupId, userGroup] of state.collection)
				{
					if (userGroup.isShown)
					{
						result.set(userGroupId, userGroup);
					}
				}

				return result;
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
			showUserGroup: (store, payload): void => {
				this.#showUserGroupAction(store, payload);
			},
			hideUserGroup: (store, payload): void => {
				this.#hideUserGroupAction(store, payload);
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
			|| !Type.isStringFilled(payload.member.id)
			|| !Type.isStringFilled(payload.member.type)
			|| !Type.isStringFilled(payload.member.name)
			|| !(Type.isNil(payload.member.avatar) || Type.isStringFilled(payload.member.avatar))
		)
		{
			console.warn('ui.accessrights.v2: Attempt to add member with invalid payload', payload);

			return;
		}

		store.commit('addMember', payload);
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
			isShown: true,
		};

		for (const value of copy.accessRights.values())
		{
			// is a new group all values are modified
			value.isModified = true;
		}

		store.commit('addUserGroup', {
			userGroup: copy,
		});
	}

	#addUserGroupAction(store: UserGroupsStore): void
	{
		const newGroup = this.getElementState();
		newGroup.accessRights = Runtime.clone(store.getters.defaultAccessRightValues);

		store.commit('addUserGroup', {
			userGroup: newGroup,
		});
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

	#showUserGroupAction(store: UserGroupsState, { userGroupId }): void
	{
		if (!this.#isUserGroupExists(store, userGroupId))
		{
			console.warn('ui.accessrights.v2: Attempt to show user group that dont exists', { userGroupId });

			return;
		}

		store.commit('showUserGroup', { userGroupId });
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

	#isUserGroupExists(store, userGroupId: string): boolean
	{
		const group = this.#getUserGroup(store.state, userGroupId);

		return Boolean(group);
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
			addUserGroup: (state, { userGroup }) => {
				state.collection.set(userGroup.id, userGroup);
			},
			removeUserGroup: (state, { userGroupId }) => {
				state.collection.delete(userGroupId);
			},
			markUserGroupForDeletion: (state, { userGroupId }) => {
				state.deleted.add(userGroupId);
			},
			showUserGroup: (state, { userGroupId }) => {
				// eslint-disable-next-line no-param-reassign
				state.collection.get(userGroupId).isShown = true;
			},
			hideUserGroup: (state, { userGroupId }) => {
				// eslint-disable-next-line no-param-reassign
				state.collection.get(userGroupId).isShown = false;
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
