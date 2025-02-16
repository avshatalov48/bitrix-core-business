import { Runtime, Type } from 'main.core';
import { type ActionTree, BuilderModel, GetterTree, type MutationTree } from 'ui.vue3.vuex';
import { ServiceLocator } from '../../service/service-locator';
import { compileAliasKey } from '../../utils';

export type AccessRightsState = {
	collection: AccessRightsCollection,
	searchQuery: string,
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
};

export type AccessRightSectionIcon = {
	type: string, // icon name from 'ui.icon-set'
	bgColor: string, // hex or --ui-color-palette-* variable name
};

export type AccessRightItem = {
	id: string,
	type: string,
	title: string,
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
		};
	}

	getElementState(params = {}): AccessRightSection
	{
		throw new Error('Cant create AccessRightSection. You are doing something wrong');
	}

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

	getMutations(): MutationTree<AccessRightsState>
	{
		return {
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
