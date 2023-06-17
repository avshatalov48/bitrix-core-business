import {Store} from 'ui.vue3.vuex';

import {Core} from 'im.v2.application.core';

import type {UserInviteParams} from '../types/user';

export class UserPullHandler
{
	#store: Store;

	constructor()
	{
		this.#store = Core.getStore();
	}

	handleUserInvite(params: UserInviteParams)
	{
		if (params.invited)
		{
			return;
		}

		this.#store.dispatch('users/update', {
			id: params.userId,
			fields: params.user
		});
	}
}