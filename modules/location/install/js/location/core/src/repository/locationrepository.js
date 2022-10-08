import BaseRepository from './baserepository';
import LocationJsonConverter from '../entity/location/locationjsonconverter';
import Location from '../entity/location';
import Address from '../entity/address';
import LocationObjectConverter from '../entity/location/locationobjectconverter';

export default class LocationRepository extends BaseRepository
{
	constructor(props = {})
	{
		props.path = props.path || 'location.api.location';
		super(props);
	}

	findParents(location: Location): Promise
	{
		if(!(location instanceof Location))
		{
			throw new TypeError('location must be type of Location');
		}

		return this.actionRunner.run(
			'findParents',
			{
				location: LocationObjectConverter.convertLocationToObject(location)
			})
			.then(this.processResponse.bind(this))
			.then(this.#convertCollection.bind(this));
	}

	findByExternalId(externalId: string, sourceCode: string, languageId: string): Promise
	{
		if(!externalId || !sourceCode || !languageId)
		{
			throw new Error('externalId and sourceCode and languageId must be defined');
		}

		return this.actionRunner.run(
			'findByExternalId',
			{
				externalId: externalId,
				sourceCode: sourceCode,
				languageId: languageId
			})
			.then(this.processResponse.bind(this))
			.then(this.#convertLocation.bind(this));
	}

	findById(locationId: number, languageId: string)
	{
		if(!locationId || !languageId)
		{
			throw new Error('locationId and languageId must be defined');
		}

		return this.actionRunner.run(
			'findById',
			{
				id: locationId,
				languageId: languageId
			})
			.then(this.processResponse.bind(this))
			.then(this.#convertLocation.bind(this));
	}

	#convertCollection(collectionJsonData: Array): Array<Location>
	{
		if(!Array.isArray(collectionJsonData))
		{
			throw new Error('Can\'t convert location collection data');
		}

		const result = [];

		collectionJsonData.forEach((location) => {
			result.push(
				this.#convertLocation(location)
			);
		});

		return result;
	}

	#convertLocation(locationData)
	{
		if(!locationData)
		{
			return null;
		}

		if(typeof locationData !== 'object')
		{
			throw new Error('Can\'t convert location data');
		}

		return LocationJsonConverter.convertJsonToLocation(locationData);
	}
}