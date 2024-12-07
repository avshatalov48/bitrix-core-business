import { EventEmitter, BaseEvent } from 'main.core.events';

export class PullRequests extends EventEmitter
{
	constructor()
	{
		super();

		this.setEventNamespace('Calendar.OpenEvents.List.EventManager.PullRequests');
	}

	getModuleId(): string
	{
		return 'calendar';
	}

	getMap(): Object
	{
		return {
			OPEN_EVENT_CREATED: this.#create.bind(this),
			OPEN_EVENT_UPDATED: this.#update.bind(this),
			OPEN_EVENT_DELETED: this.#delete.bind(this),
		};
	}

	#create(event: BaseEvent): void
	{
		this.emit('create', event);
	}

	#update(event: BaseEvent): void
	{
		this.emit('update', event);
	}

	#delete(event: BaseEvent): void
	{
		this.emit('delete', event);
	}
}
