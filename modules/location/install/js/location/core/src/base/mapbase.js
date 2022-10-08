import {EventEmitter} from 'main.core.events';
import {Location} from 'location.core';

/**
 * Base class for source maps
 */
export default class MapBase extends EventEmitter
{
	constructor()
	{
		super();
		this.setEventNamespace('BX.Location.Core.MapBase');
	}

	render(props: object): void
	{
		throw new Error('Must be implemented');
	}

	set location(location: Location): void
	{
		throw new Error('Must be implemented');
	}

	set mode(mode: string): void
	{
		throw new Error('Must be implemented');
	}

	set zoom(zoom: number): void
	{
		throw new Error('Must be implemented');
	}

	onLocationChangedEventSubscribe(listener: function): void
	{
		throw new Error('Must be implemented');
	}

	onMapShow()
	{

	}

	destroy()
	{

	}
}