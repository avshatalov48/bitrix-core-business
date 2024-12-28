import { BuilderModel, type GetterTree, type MutationTree } from 'ui.vue3.vuex';

export type ApplicationState = {
	options: Readonly<Options>,
	guid: string,
	isSaving: boolean,
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
}

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
			isSaving: false,
		};
	}

	getGetters(): GetterTree<ApplicationState>
	{
		return {
			isMaxVisibleUserGroupsSet: (state): boolean => {
				return state.options.maxVisibleUserGroups > 0;
			},
		};
	}

	getMutations(): MutationTree<ApplicationState>
	{
		return {
			setSaving: (state, isSaving: boolean): void => {
				// eslint-disable-next-line no-param-reassign
				state.isSaving = Boolean(isSaving);
			},
		};
	}
}
