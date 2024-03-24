import { BaseEvent, EventEmitter } from 'main.core.events';

type Params = {
	window: Window;
}

export class Disk extends EventEmitter
{
	#window: Window;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.Disk');

		this.#window = params.window;

		this.#window.BX.Event.EventEmitter.subscribe(
			'Disk.TileItem.Item:onItemDblClick',
			this.#changeNavigation.bind(this),
		);
		this.#window.BX.Event.EventEmitter.subscribe(
			'Disk.TileItem.Item:onItemEnter',
			this.#changeNavigation.bind(this),
		);
		this.#window.BX.Event.EventEmitter.subscribe(
			'Disk.Breadcrumbs:onClickBreadcrumb',
			this.#changeBreadcrumbsNavigation.bind(this),
		);
	}

	#changeNavigation(baseEvent: BaseEvent)
	{
		const [item] = baseEvent.getCompatData();
		if (item.isFolder)
		{
			this.emit('changePage', item.item.titleLink.href);
		}
	}

	#changeBreadcrumbsNavigation(baseEvent: BaseEvent)
	{
		const [breadcrumbLink] = baseEvent.getCompatData();

		this.emit('changePage', breadcrumbLink.href);
	}
}
