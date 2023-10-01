import { EventEmitter } from 'main.core.events';
import {
	Location,
	LocationType,
} from 'location.core';

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

	render(props: Object): Promise
	{
		throw new Error('Must be implemented');
	}

	set location(location: Location): void
	{
		throw new Error('Must be implemented');
	}

	panTo(latitude: string, longitude: string): void
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

	static getZoomByLocation(location: ?Location): number
	{
		const defaultZoom = 18;
		if (!location)
		{
			return defaultZoom;
		}

		const locationType = location.type;
		if (locationType <= 0)
		{
			return defaultZoom;
		}

		if (locationType < LocationType.COUNTRY)
		{
			return 1;
		}
		else if (locationType === LocationType.COUNTRY)
		{
			return 4;
		}
		else if (locationType <= LocationType.ADM_LEVEL_1)
		{
			return 6;
		}
		else if (locationType <= LocationType.LOCALITY)
		{
			return 11;
		}
		else if (locationType <= LocationType.STREET)
		{
			return 16;
		}

		return defaultZoom;
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
