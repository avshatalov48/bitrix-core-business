import { EventEmitter } from 'main.core.events';

export class PullRequests extends EventEmitter
{
	#groupId: number;

	constructor(groupId)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.Group.Settings.PullRequests');

		this.#groupId = parseInt(groupId, 10);
	}

	getModuleId(): string
	{
		return 'socialnetwork';
	}

	getMap(): Object
	{
		return {
			workgroup_update: this.#update.bind(this),
			workgroup_user_add: this.#update.bind(this),
			workgroup_user_delete: this.#update.bind(this),
			workgroup_user_update: this.#update.bind(this),
		};
	}

	#update(data): void
	{
		if (parseInt(data.params.GROUP_ID, 10) === this.#groupId)
		{
			this.emit('update');
		}
	}
}
