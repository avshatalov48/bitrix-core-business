import { EventEmitter } from 'main.core.events';

export class PullRequests extends EventEmitter
{
	#entityId: number;
	#currentUserId: number;

	constructor(entityId: number, currentUserId: number)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.Menu.PullRequests');

		this.#entityId = parseInt(entityId, 10);
		this.#currentUserId = parseInt(currentUserId, 10);
	}

	getModuleId(): string
	{
		return 'socialnetwork';
	}

	getMap(): Object
	{
		return {
			workgroup_user_add: this.#update.bind(this),
			workgroup_user_delete: this.#update.bind(this),
			workgroup_user_update: this.#update.bind(this),
			workgroup_update: this.#update.bind(this),
			user_spaces_counter: this.#updateCounters.bind(this),
			space_feature_change: this.#updateMenuItem.bind(this),
		};
	}

	#update(data): void
	{
		if (parseInt(data.params.GROUP_ID, 10) === this.#entityId)
		{
			this.emit('update');
		}
	}

	#updateCounters(data): void
	{
		if (data.userId && parseInt(data.userId, 10) === this.#currentUserId)
		{
			// eslint-disable-next-line no-param-reassign
			data.space = data.spaces.find((space) => space.id === this.#entityId);

			if (!data.space)
			{
				// no counters for this space
				// eslint-disable-next-line no-param-reassign
				data.space = {
					id: this.#entityId,
					metrics: {
						countersTasksTotal: 0,
						countersCalendarTotal: 0,
						countersLiveFeedTotal: 0,
					},
				};
			}

			this.emit('updateCounters', data);
		}
	}

	#updateMenuItem(data): void
	{
		if (data.GROUP_ID === this.#entityId && data.USER_ID === this.#currentUserId)
		{
			this.emit('updateMenuItem', data);
		}
	}
}
