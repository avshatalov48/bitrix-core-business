import Location from '../location';
import Address from '../address';
import LocationObjectConverter from './locationobjectconverter';

export default class LocationJsonConverter
{
	/**
	 * @param {{...}}jsonData
	 * @returns {Location}
	 */
	static convertJsonToLocation(jsonData)
	{
		const initData = {...jsonData};

		if(jsonData.address)
		{
			initData.address = new Address(jsonData.address);
		}

		return new Location(initData);
	}

	/**
	 * @param {Location} location
	 * @returns {{...}}
	 */
	static convertLocationToJson(location: Location)
	{
		if(!(location instanceof Location))
		{
			throw new TypeError('location must be type of location');
		}

		const obj = LocationObjectConverter.convertLocationToObject(location);
		return obj ? JSON.stringify(obj) : '';
	}
}