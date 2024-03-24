import { EventEmitter } from 'main.core.events';
import { EventTypes } from '../const/event';

export class PullRequests extends EventEmitter
{
	constructor()
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.List.PullRequests');
	}

	getModuleId(): string
	{
		return 'socialnetwork';
	}

	getMap(): Object
	{
		return {
			workgroup_pin_changed: this.#pinChanged.bind(this),
			user_spaces_counter: this.#updateCounters.bind(this),
			workgroup_update: this.#onChangeSpace.bind(this),
			space_user_role_change: this.#onChangeUserRole.bind(this),
			workgroup_subscribe_changed: this.#onChangeSubscription.bind(this),
			recent_activity_update: this.#onRecentActivityUpdate.bind(this),
			recent_activity_delete: this.#onRecentActivityDelete.bind(this),
		};
	}

	#pinChanged(data): void
	{
		this.emit(EventTypes.pinChanged, {
			spaceId: data.GROUP_ID,
			isPinned: data.ACTION === 'pin',
		});
	}

	#updateCounters(data): void
	{
		this.emit(EventTypes.updateCounters, data);
	}

	#onChangeSpace(data): void
	{
		const params = data.params;
		this.emit(EventTypes.changeSpace, {
			spaceId: params.GROUP_ID,
		});
	}

	#onChangeUserRole(data): void
	{
		this.emit(EventTypes.changeUserRole, {
			spaceId: data.GROUP_ID,
			userId: data.USER_ID,
		});
	}

	#onChangeSubscription(data): void
	{
		this.emit(EventTypes.changeSubscription, {
			spaceId: data.GROUP_ID,
			userId: data.USER_ID,
		});
	}

	#onRecentActivityUpdate(data): void
	{
		this.emit(EventTypes.recentActivityUpdate, {
			recentActivityData: data.recentActivityData,
		});
	}

	#onRecentActivityDelete(data): void
	{
		this.emit(EventTypes.recentActivityDelete, {
			typeId: data.typeId,
			entityId: data.entityId,
		});
	}
}
