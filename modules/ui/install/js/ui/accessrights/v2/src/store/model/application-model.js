import { BuilderModel, type GetterTree, type MutationTree } from 'ui.vue3.vuex';

export type ApplicationState = {
	options: Readonly<Options>,
	guid: string,
	isProgress: boolean,
}

export type Options = {
	component: string,
	actionSave: string,
	mode: string,
	bodyType?: 'json' | 'data',
	additionalSaveParams: Object,
	isSaveOnlyChangedRights: boolean,
	maxVisibleUserGroups: ?number,
	searchContainerSelector: ?string,
	additionalMembersParams: AdditionalMembersParams,
	userSortConfigName: string,
	isSaveAccessRightsList: boolean,
	moduleId: string,
}

export type AdditionalMembersParams = {
	addUserGroupsProviderTab?: boolean,
	addProjectsProviderTab?: boolean,
	addStructureTeamsProviderTab?: boolean,
	addStructureRolesProviderTab?: boolean,
	useStructureDepartmentsProviderTab?: boolean,
};

export const ACTION_SAVE = 'save';
export const MODE = 'ajax';
export const BODY_TYPE = 'data';

export class ApplicationModel extends BuilderModel
{
	#guid: string;
	#options: Readonly<Options>;

	getName(): string
	{
		return 'application';
	}

	setOptions(options: Options): ApplicationModel
	{
		this.#options = options;

		return this;
	}

	setGuid(guid: string): ApplicationModel
	{
		this.#guid = guid;

		return this;
	}

	getState(): ApplicationState
	{
		return {
			options: this.#options,
			guid: this.#guid,
			isProgress: false,
		};
	}

	getGetters(): GetterTree<ApplicationState>
	{
		return {
			isMaxVisibleUserGroupsSet: (state): boolean => {
				return state.options.maxVisibleUserGroups > 0;
			},
			isModified: (state, getters, rootState, rootGetters): boolean => {
				return rootGetters['userGroups/isModified'] || rootGetters['accessRights/isModified'];
			},
			guid: (state): string => {
				return state.guid;
			},
			additionalMembersParams: (state): AdditionalMembersParams => {
				return state.options.additionalMembersParams;
			},
		};
	}

	getMutations(): MutationTree<ApplicationState>
	{
		return {
			setProgress: (state, isProgress: boolean): void => {
				// eslint-disable-next-line no-param-reassign
				state.isProgress = Boolean(isProgress);
			},
		};
	}
}
