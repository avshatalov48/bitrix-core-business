import { Runtime, Type } from 'main.core';
import { type ActionTree, BuilderModel, GetterTree, type MutationTree } from 'ui.vue3.vuex';
import { ServiceLocator } from '../../service/service-locator';
import { compileAliasKey } from '../../utils';
import { AccessRightsInternalizer } from './transformation/internalizer/access-rights-internalizer';

export type AccessRightsState = {
	collection: AccessRightsCollection,
	searchQuery: string,
	deleted: Set<string>,
}

export type AccessRightsCollection = Map<string, AccessRightSection>;

export type AccessRightSection = {
	sectionTitle: string,
	sectionSubTitle: ?string,
	sectionCode: string, // not sent to backend, frontend uses it only for indexing
	sectionHint: ?string,
	sectionIcon?: AccessRightSectionIcon,
	rights: Map<string, AccessRightItem>,
	isExpanded: boolean,
	isShown: boolean,
	action: ?AccessRightSectionAction,
};

export type AccessRightSectionIcon = {
	type: string, // icon name from 'ui.icon-set'
	bgColor: string, // hex or --ui-color-palette-* variable name
};

export type AccessRightSectionAction = {
	buttonText: string,
};

export type AccessRightItem = {
	id: string,
	type: string,
	title: string,
	subtitle: ?string,
	hint: ?string, // hint for row title in the title column
	group: ?string, // id of parent item id
	groupHead: boolean,
	isGroupExpanded: ?boolean, // only for group head and grouped items
	isShown: boolean,
	minValue?: Set<string>,
	maxValue?: Set<string>,
	defaultValue?: Set<string>,
	emptyValue?: Set<string>,
	nothingSelectedValue?: Set<string>,
	setEmptyOnSetMinMaxValueInColumn?: boolean,

	variables: VariableCollection, // options to choose from in variable-like controls

	// only for multivariable
	allSelectedCode: ?string,
	selectedVariablesAliases: Map<string, string>,
	selectedVariablesAliasesSeparator: ?string,
	enableSearch?: boolean,
	showAvatars?: boolean,
	compactView?: boolean,
	hintTitle: ?string, // title for 'already selected values' hint in multivariable and dependent-variables selector
	dependentVariablesPopupHint: ?string, // hint in dependent-variables popup under all switchers

	// clickable right with icon
	iconClass: ?string,
	isClickable: boolean,
	isDeletable: boolean,
	isNew: boolean,
	isModified: boolean,
};

export type VariableCollection = Map<string, Variable>;

export type Variable = {
	id: string,
	title: string,
	// used only in multivariable selector
	entityId: ?string,
	supertitle: ?string,
	avatar: ?string,
	avatarOptions: ?Object,
	// used only in dependent-variables
	conflictsWith?: Set<string>,
	requires?: Set<string>,
	secondary: ?boolean, // switcher color and size for dependent-variables
	hint: ?string,
}

export class AccessRightsModel extends BuilderModel
{
	#initialRights: AccessRightsCollection = new Map();

	getName(): string
	{
		return 'accessRights';
	}

	setInitialAccessRights(rights: AccessRightsCollection): AccessRightsModel
	{
		this.#initialRights = rights;

		return this;
	}

	getState(): AccessRightsState
	{
		return {
			collection: Runtime.clone(this.#initialRights),
			searchQuery: '',
			deleted: new Set(),
		};
	}

	getElementState(params = {}): AccessRightSection
	{
		throw new Error('Cant create AccessRightSection. You are doing something wrong');
	}

	// eslint-disable-next-line max-lines-per-function
	getGetters(): GetterTree<AccessRightsState>
	{
		return {
			shown: (state): AccessRightsCollection => {
				const result = new Map();

				for (const [sectionCode, section] of state.collection)
				{
					if (section.isShown)
					{
						result.set(sectionCode, section);
					}
				}

				return result;
			},
			isMinValueSetForAny: (state, getters): boolean => {
				for (const section of state.collection.values())
				{
					for (const item of section.rights.values())
					{
						const isSet = getters.isMinValueSet(section.sectionCode, item.id);
						if (isSet)
						{
							return true;
						}
					}
				}

				return false;
			},
			isMinValueSet: (state) => (sectionCode: string, rightId: string): boolean => {
				const item = state.collection.get(sectionCode)?.rights.get(rightId);
				if (!item)
				{
					console.warn(
						'ui.accessrights.v2: attempt to check if min value set for unknown right',
						{ sectionCode, rightId },
					);

					return false;
				}

				return !Type.isNil(item.minValue);
			},
			isMaxValueSetForAny: (state, getters): boolean => {
				for (const section of state.collection.values())
				{
					for (const item of section.rights.values())
					{
						const isSet = getters.isMaxValueSet(section.sectionCode, item.id);
						if (isSet)
						{
							return true;
						}
					}
				}

				return false;
			},
			isMaxValueSet: (state) => (sectionCode: string, rightId: string): boolean => {
				const item = state.collection.get(sectionCode)?.rights.get(rightId);
				if (!item)
				{
					console.warn(
						'ui.accessrights.v2: attempt to check if max value set for unknown right',
						{ sectionCode, rightId },
					);

					return false;
				}

				return !Type.isNil(item.maxValue);
			},
			getEmptyValue: (state) => (sectionCode: string, valueId: string): Set<string> => {
				const item = state.collection.get(sectionCode)?.rights.get(valueId);
				if (!item)
				{
					return new Set();
				}

				return ServiceLocator.getValueTypeByRight(item)?.getEmptyValue(item) ?? new Set();
			},
			getNothingSelectedValue: (state, getters) => (sectionCode: string, valueId: string): Set<string> => {
				const item = state.collection.get(sectionCode)?.rights.get(valueId);

				return item?.nothingSelectedValue ?? getters.getEmptyValue(sectionCode, valueId);
			},
			getSelectedVariablesAlias: (state) => (sectionCode: string, valueId: string, values: Set<string>): ?string => {
				const item = state.collection.get(sectionCode)?.rights.get(valueId);
				if (!item)
				{
					return null;
				}

				const key = compileAliasKey(values, item.selectedVariablesAliasesSeparator);

				return item.selectedVariablesAliases.get(key);
			},
			isModified: (state): boolean => {
				if (state.deleted.size > 0)
				{
					return true;
				}

				for (const section: AccessRightSection of state.collection.values())
				{
					for (const rightItem: AccessRightItem of section.rights.values())
					{
						if (rightItem.isNew || rightItem.isModified)
						{
							return true;
						}
					}
				}

				return false;
			},
		};
	}

	getActions(): ActionTree<AccessRightsState>
	{
		return {
			toggleSection: (store, { sectionCode }): void => {
				if (!store.state.collection.has(sectionCode))
				{
					console.warn('ui.accessrights.v2: Attempt to toggle section that dont exists', { sectionCode });

					return;
				}

				store.commit('toggleSection', { sectionCode });
			},
			expandAllSections: (store): void => {
				for (const sectionCode of store.state.collection.keys())
				{
					store.commit('expandSection', { sectionCode });
				}
			},
			collapseAllSections: (store): void => {
				for (const sectionCode of store.state.collection.keys())
				{
					store.commit('collapseSection', { sectionCode });
				}
			},
			toggleGroup: (store, { sectionCode, groupId }): void => {
				const item = store.state.collection.get(sectionCode)?.rights.get(groupId);
				if (!item)
				{
					console.warn('ui.accessrights.v2: Attempt to toggle group that dont exists', { groupId });

					return;
				}

				if (!item.groupHead)
				{
					console.warn('ui.accessrights.v2: Attempt to toggle group that is not group head', { groupId });

					return;
				}

				store.commit('toggleGroup', { sectionCode, groupId });
			},
			search: (store, payload): void => {
				this.#searchAction(store, payload);
			},
			addRight: (store, { sectionCode, right }): void => {
				if (!store.state.collection.has(sectionCode))
				{
					console.warn('ui.accessrights.v2: Adding right to section that doesn`t exists', { sectionCode });

					return;
				}

				const section: AccessRightSection = store.state.collection.get(sectionCode);
				if (section)
				{
					const internalRight: AccessRightItem = (new AccessRightsInternalizer()).internalizeExternalItem(right);
					store.commit('expandSection', { sectionCode });
					store.commit('addRight', { sectionCode, right: internalRight });
				}
			},
			updateRightTitle: (store, { sectionCode, rightId, rightTitle }): void => {
				if (!store.state.collection.has(sectionCode))
				{
					console.warn('ui.accessrights.v2: Updating right in section that doesn`t exists', { sectionCode });

					return;
				}

				const section: AccessRightSection = store.state.collection.get(sectionCode);
				if (!section.rights.has(rightId))
				{
					console.warn('ui.accessrights.v2: Updating right that doesn`t exists', { rightId });

					return;
				}

				store.commit('expandSection', { sectionCode });
				store.commit('setRightTitle', { sectionCode, rightId, title: rightTitle });
			},
			updateRightSubtitle: (store, { sectionCode, rightId, rightSubtitle }): void => {
				if (!store.state.collection.has(sectionCode))
				{
					console.warn('ui.accessrights.v2: Updating right in section that doesn`t exists', { sectionCode });

					return;
				}
				const section: AccessRightSection = store.state.collection.get(sectionCode);
				if (!section.rights.has(rightId))
				{
					console.warn('ui.accessrights.v2: Updating right that doesn`t exists', { rightId });

					return;
				}

				store.commit('expandSection', { sectionCode });
				store.commit('setRightSubtitle', { sectionCode, rightId, subtitle: rightSubtitle });
			},
			deleteRight: (store, { sectionCode, rightId }): void => {
				if (!store.state.collection.has(sectionCode))
				{
					console.warn('ui.accessrights.v2: Deleting right in section that doesn`t exists', { sectionCode });

					return;
				}
				const section: AccessRightSection = store.state.collection.get(sectionCode);
				if (!section.rights.has(rightId))
				{
					console.warn('ui.accessrights.v2: Deleting right that doesn`t exists', { rightId });

					return;
				}

				store.commit('expandSection', { sectionCode });
				store.commit('deleteRight', { sectionCode, rightId });
			},
			markRightAsModified: (store, { sectionCode, rightId, isModified }): void => {
				if (!store.state.collection.has(sectionCode))
				{
					console.warn('ui.accessrights.v2: Updating right in section that doesn`t exists', { sectionCode });

					return;
				}
				const section: AccessRightSection = store.state.collection.get(sectionCode);
				if (!section.rights.has(rightId))
				{
					console.warn('ui.accessrights.v2: Updating right that doesn`t exists', { rightId });

					return;
				}

				store.commit('expandSection', { sectionCode });
				store.commit('markRightAsModified', { sectionCode, rightId, isModified });
			},
		};
	}

	#searchAction(store, { query }): void
	{
		if (!Type.isString(query))
		{
			console.warn('ui.accessrights.v2: attempt to search with non-string search query');

			return;
		}

		store.commit('setSearchQuery', { query });
		if (query === '')
		{
			store.commit('showAll');

			return;
		}

		store.commit('hideAll');

		const lowerQuery = query.toLowerCase();

		for (const section: AccessRightSection of store.state.collection.values())
		{
			if (
				section.sectionTitle.toLowerCase().includes(lowerQuery)
				|| section.sectionSubTitle?.toLowerCase().includes(lowerQuery)
			)
			{
				store.commit('showSection', { sectionCode: section.sectionCode });
				continue;
			}

			for (const item: AccessRightItem of section.rights.values())
			{
				if (!item.title.toLowerCase().includes(lowerQuery))
				{
					continue;
				}

				if (item.groupHead)
				{
					store.commit('showGroup', { sectionCode: section.sectionCode, groupId: item.id });
				}
				else
				{
					store.commit('showItem', { sectionCode: section.sectionCode, itemId: item.id });
					if (item.group)
					{
						store.commit('expandGroup', { sectionCode: section.sectionCode, groupId: item.group });
					}
				}
			}
		}
	}

	// eslint-disable-next-line max-lines-per-function
	getMutations(): MutationTree<AccessRightsState>
	{
		return {
			addRight: (state, { sectionCode, right }): void => {
				const section = state.collection.get(sectionCode);
				section.rights.set(right.id, right);
			},
			setRightTitle: (state, { sectionCode, rightId, title }): void => {
				const section = state.collection.get(sectionCode);
				section.rights.get(rightId).title = title;
			},
			setRightSubtitle: (state, { sectionCode, rightId, subtitle }): void => {
				const section = state.collection.get(sectionCode);
				section.rights.get(rightId).subtitle = subtitle;
			},
			deleteRight: (state, { sectionCode, rightId }): void => {
				const section: AccessRightSection = state.collection.get(sectionCode);
				section.rights.delete(rightId);
				state.deleted.add(rightId);
			},
			markRightAsModified: (state, { sectionCode, rightId, isModified }): void => {
				const section = state.collection.get(sectionCode);
				section.rights.get(rightId).isModified = isModified;
			},
			toggleSection: (state, { sectionCode }): void => {
				const section = state.collection.get(sectionCode);

				section.isExpanded = !section.isExpanded;
			},
			expandSection: (state, { sectionCode }): void => {
				const section = state.collection.get(sectionCode);

				section.isExpanded = true;
			},
			collapseSection: (state, { sectionCode }): void => {
				const section = state.collection.get(sectionCode);

				section.isExpanded = false;
			},
			toggleGroup: (state, { sectionCode, groupId }): void => {
				const section = state.collection.get(sectionCode);

				for (const item of section.rights.values())
				{
					if (
						(item.id === groupId && item.groupHead)
						|| item.group === groupId
					)
					{
						item.isGroupExpanded = !item.isGroupExpanded;
					}
				}
			},
			expandGroup: (state, { sectionCode, groupId }): void => {
				const section = state.collection.get(sectionCode);

				section.isExpanded = true;

				for (const item of section.rights.values())
				{
					if (
						(item.id === groupId && item.groupHead)
						|| item.group === groupId
					)
					{
						item.isGroupExpanded = true;
					}
				}
			},
			showItem: (state, { sectionCode, itemId }): void => {
				const section = state.collection.get(sectionCode);
				section.isShown = true;

				const item = section.rights.get(itemId);
				item.isShown = true;
				if (item.group)
				{
					section.rights.get(item.group).isShown = true;
				}
			},
			showGroup: (state, { sectionCode, groupId }): void => {
				const section = state.collection.get(sectionCode);

				section.isShown = true;

				for (const item of section.rights.values())
				{
					if (
						(item.id === groupId && item.groupHead)
						|| item.group === groupId
					)
					{
						item.isShown = true;
					}
				}
			},
			showSection: (state, { sectionCode }): void => {
				const section = state.collection.get(sectionCode);
				section.isShown = true;
				for (const item of section.rights.values())
				{
					item.isShown = true;
				}
			},
			showAll: (state): void => {
				for (const section of state.collection.values())
				{
					section.isShown = true;
					for (const item of section.rights.values())
					{
						item.isShown = true;
					}
				}
			},
			hideAll: (state): void => {
				for (const section of state.collection.values())
				{
					section.isShown = false;
					for (const item of section.rights.values())
					{
						item.isShown = false;
					}
				}
			},
			setSearchQuery: (state, { query }): void => {
				// eslint-disable-next-line no-param-reassign
				state.searchQuery = String(query);
			},
		};
	}
}
