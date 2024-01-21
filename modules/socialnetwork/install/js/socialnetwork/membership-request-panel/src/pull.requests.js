import { EventEmitter } from 'main.core.events';

export class PullRequests extends EventEmitter
{
	#groupId: number;

	constructor(groupId: number)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.MRP.PullRequests');

		this.#groupId = groupId;
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
