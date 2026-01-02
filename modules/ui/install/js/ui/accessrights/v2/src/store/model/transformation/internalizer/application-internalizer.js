import { Type } from 'main.core';
import { ACTION_SAVE, BODY_TYPE, MODE, type Options } from '../../application-model';
import type { Transformer } from '../transformer';

export class ApplicationInternalizer implements Transformer<Options, Readonly<Options>>
{
	// noinspection OverlyComplexFunctionJS
	transform(externalSource: Options): Readonly<Options>
	{
		// freeze tells vue that we don't need reactivity on this state
		// and prevents accidental modification as well
		return this.#deepFreeze({
			component: String(externalSource.component),
			actionSave: Type.isStringFilled(externalSource.actionSave) ? externalSource.actionSave : ACTION_SAVE,
			mode: Type.isStringFilled(externalSource.mode) ? externalSource.mode : MODE,
			bodyType: Type.isStringFilled(externalSource.bodyType) ? externalSource.bodyType : BODY_TYPE,
			additionalSaveParams: Type.isPlainObject(externalSource.additionalSaveParams)
				? externalSource.additionalSaveParams
				: [],
			isSaveOnlyChangedRights: Type.isBoolean(externalSource.isSaveOnlyChangedRights)
				? externalSource.isSaveOnlyChangedRights
				: false,
			maxVisibleUserGroups: Type.isInteger(externalSource.maxVisibleUserGroups)
				? externalSource.maxVisibleUserGroups
				: null,
			searchContainerSelector: Type.isStringFilled(externalSource.searchContainerSelector)
				? externalSource.searchContainerSelector
				: null,
			additionalMembersParams: Type.isPlainObject(externalSource.additionalMembersParams)
				? {
					addUserGroupsProviderTab: Boolean(externalSource.additionalMembersParams?.addUserGroupsProviderTab ?? false),
					addProjectsProviderTab: Boolean(externalSource.additionalMembersParams?.addProjectsProviderTab ?? true),
					addStructureTeamsProviderTab: Boolean(
						externalSource.additionalMembersParams?.addStructureTeamsProviderTab ?? false,
					),
					useStructureDepartmentsProviderTab: Boolean(
						externalSource.additionalMembersParams?.useStructureDepartmentsProviderTab ?? false,
					),
					addStructureRolesProviderTab: Boolean(
						externalSource.additionalMembersParams?.addStructureRolesProviderTab ?? false,
					),
				}
				: {
					addUserGroupsProviderTab: false,
					addProjectsProviderTab: true,
					addStructureTeamsProviderTab: false,
					useStructureDepartmentsProviderTab: false,
					addStructureRolesProviderTab: false,
				},
			userSortConfigName: String(externalSource.userSortConfigName ?? externalSource.component),
			isSaveAccessRightsList: Type.isBoolean(externalSource.isSaveAccessRightsList)
				? externalSource.isSaveAccessRightsList
				: false,
			moduleId: Type.isString(externalSource.moduleId) ? externalSource.moduleId : '',
		});
	}

	#deepFreeze(target: Object): Readonly<Object>
	{
		if (Type.isObject(target))
		{
			Object.values(target).forEach((value) => {
				this.#deepFreeze(value);
			});

			return Object.freeze(target);
		}

		return target;
	}
}
