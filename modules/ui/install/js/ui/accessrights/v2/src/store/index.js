import { Builder, type Store } from 'ui.vue3.vuex';
import type { AccessRightsCollection } from './model/access-rights-model';
import { AccessRightsModel } from './model/access-rights-model';
import { ApplicationModel, type Options } from './model/application-model';
import type { UserGroupsCollection } from './model/user-groups-model';
import { UserGroupsModel } from './model/user-groups-model';

export function createStore(
	options: Readonly<Options>,
	userGroups: UserGroupsCollection,
	accessRights: AccessRightsCollection,
	appGuid: string | number,
): {
	store: Store,
	resetState: () => Promise<void>,
	userGroupsModel: UserGroupsModel,
}
{
	const userGroupsModel = UserGroupsModel.create()
		.setInitialUserGroups(userGroups)
	;

	const { store } = Builder
		.init()
		.addModel(
			ApplicationModel.create()
				.setOptions(options)
				.setGuid(appGuid)
			,
		)
		.addModel(
			AccessRightsModel.create()
				.setInitialAccessRights(accessRights)
			,
		)
		.addModel(userGroupsModel)
		.syncBuild()
	;

	return {
		store,
		resetState: () => userGroupsModel.clearState(),
		userGroupsModel,
	};
}
