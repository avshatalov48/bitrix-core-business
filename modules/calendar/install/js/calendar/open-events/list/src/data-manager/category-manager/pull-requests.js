import { BaseEvent, EventEmitter } from 'main.core.events';

export class PullRequests extends EventEmitter
{
	constructor()
	{
		super();

		this.setEventNamespace('Calendar.OpenEvents.List.CategoryManager.PullRequests');
	}

	getModuleId(): string
	{
		return 'calendar';
	}

	getMap(): Object
	{
		return {
			EVENT_CATEGORY_CREATED: this.#create.bind(this),
			EVENT_CATEGORY_UPDATED: this.#update.bind(this),
			EVENT_CATEGORY_DELETED: this.#delete.bind(this),
			OPEN_EVENT_SCORER_UPDATED: this.#eventScorerUpdated.bind(this),
		};
	}

	#update(event: BaseEvent): void
	{
		this.emit('update', event);
	}

	#create(event: BaseEvent): void
	{
		this.emit('create', event);
	}

	#delete(event: BaseEvent): void
	{
		this.emit('delete', event);
	}

	#eventScorerUpdated(event: BaseEvent): void
	{
		this.emit('eventScorerUpdated', event);
	}
}
