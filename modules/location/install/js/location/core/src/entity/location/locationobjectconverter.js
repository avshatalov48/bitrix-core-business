import Location from '../location';
import LocationFieldCollection from './locationfieldcollection';

export default class LocationObjectConverter
{
	static convertLocationToObject(location: Location): Object
	{
		if(!(location instanceof Location))
		{
			throw new TypeError('location must be type of location');
		}

		const obj = {
			id: location.id,
			code: location.code,
			externalId: location.externalId,
			sourceCode: location.sourceCode,
			type: location.type,
			name: location.name,
			languageId: location.languageId,
			latitude: location.latitude,
			longitude: location.longitude,
			fieldCollection: LocationObjectConverter.#objectifyFieldCollection(location.fieldCollection),
			address: null
		};

		if(location.address)
		{
			obj.address = JSON.parse(location.address.toJson());
		}

		return obj;
	}

	static #objectifyFieldCollection(fieldCollection: LocationObjectConverter): Object
	{
		let result = {};

		Object.values(fieldCollection.fields).forEach((field) => {
			result[field.type] = field.value;
		});

		return result;
	}
}