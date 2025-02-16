import { type AjaxResponse, Text, Type } from 'main.core';
import { type AnalyticsOptions, sendData } from 'ui.analytics';
import type { Store } from 'ui.vue3.vuex';
import type { UserGroup } from '../store/model/user-groups-model';

export class AnalyticsManager
{
	#store: Store;
	#data: AnalyticsOptions;

	#isEnabled: boolean;
	#isCancelAlreadyRegistered: boolean = false;

	constructor(store: Store, analyticsData: AnalyticsOptions)
	{
		this.#store = store;
		this.#data = analyticsData;

		// check 2 out of 3 required fields
		// 'event' field is provided by AnalyticsManager
		this.#isEnabled = Object.hasOwn(this.#data, 'tool') && Object.hasOwn(this.#data, 'category');
	}

	onSaveAttempt(): void
	{
		if (!this.#isEnabled)
		{
			return;
		}

		const { createdRoles, editedRoles, deletedRoles } = this.#analyzeRoles();

		for (let i = 0; i < createdRoles; i++)
		{
			this.#registerRoleCreateEvent('attempt');
		}

		for (let i = 0; i < editedRoles; i++)
		{
			this.#registerRoleEditEvent('attempt');
		}

		for (let i = 0; i < deletedRoles; i++)
		{
			this.#registerRoleDeleteEvent('attempt');
		}
	}

	onSaveSuccess(): void
	{
		if (!this.#isEnabled)
		{
			return;
		}

		const { createdRoles, editedRoles, deletedRoles } = this.#analyzeRoles();

		for (let i = 0; i < createdRoles; i++)
		{
			this.#registerRoleCreateEvent('success');
		}

		for (let i = 0; i < editedRoles; i++)
		{
			this.#registerRoleEditEvent('success');
		}

		for (let i = 0; i < deletedRoles; i++)
		{
			this.#registerRoleDeleteEvent('success');
		}
	}

	onSaveError(response: AjaxResponse): void
	{
		if (!this.#isEnabled)
		{
			return;
		}

		const status = this.#getSaveErrorStatus(response);

		const { createdRoles, editedRoles, deletedRoles } = this.#analyzeRoles();

		for (let i = 0; i < createdRoles; i++)
		{
			this.#registerRoleCreateEvent(status);
		}

		for (let i = 0; i < editedRoles; i++)
		{
			this.#registerRoleEditEvent(status);
		}

		for (let i = 0; i < deletedRoles; i++)
		{
			this.#registerRoleDeleteEvent(status);
		}
	}

	onCancelChanges(): void
	{
		if (!this.#isEnabled)
		{
			return;
		}

		if (this.#isCancelAlreadyRegistered)
		{
			return;
		}

		sendData({
			...this.#data,
			event: 'settings_cancel',
		});

		this.#isCancelAlreadyRegistered = true;
	}

	onCloseWithoutSave(): void
	{
		if (!this.#isEnabled)
		{
			return;
		}

		sendData({
			...this.#data,
			event: 'settings_pop_cancel',
		});
	}

	#analyzeRoles(): {createdRoles: number, editedRoles: number, deletedRoles: number}
	{
		const result = {
			createdRoles: 0,
			editedRoles: 0,
			deletedRoles: this.#store.state.userGroups.deleted.size,
		};

		for (const userGroup: UserGroup of this.#store.state.userGroups.collection.values())
		{
			if (userGroup.isNew)
			{
				result.createdRoles++;
			}
			else if (this.#isUserGroupEdited(userGroup))
			{
				result.editedRoles++;
			}
		}

		return result;
	}

	#isUserGroupEdited(userGroup: UserGroup): boolean
	{
		if (userGroup.isModified)
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

		return false;
	}

	#getSaveErrorStatus(response: AjaxReponse): string
	{
		if (!Type.isArrayFilled(response?.errors))
		{
			return 'error';
		}

		for (const error of response.errors)
		{
			if (Type.isStringFilled(error?.code))
			{
				return `error_${Text.toCamelCase(error.code)}`;
			}
		}

		return 'error';
	}

	#registerRoleCreateEvent(status: string): void
	{
		const data = {
			...this.#data,
			event: 'role_create',
			status,
		};

		this.#appendRoleCountView(data);

		sendData(data);
	}

	#registerRoleEditEvent(status: string): void
	{
		const data = {
			...this.#data,
			event: 'role_edit',
			status,
		};

		this.#appendRoleCountView(data);

		sendData(data);
	}

	#registerRoleDeleteEvent(status: string): void
	{
		const data = {
			...this.#data,
			event: 'role_delete',
			status,
		};

		this.#appendRoleCountView(data);

		sendData(data);
	}

	#appendRoleCountView(data: AnalyticsOptions): void
	{
		this.#appendP(
			data,
			'roleCountView',
			this.#store.getters['userGroups/shown'].size,
		);
	}

	#appendP(data: AnalyticsOptions, name: string, value: any): void
	{
		for (const pName of ['p1', 'p2', 'p3', 'p4', 'p5'])
		{
			if (!Object.hasOwn(data, pName))
			{
				// eslint-disable-next-line no-param-reassign
				data[pName] = `${name}_${value}`;

				return;
			}
		}
	}
}
