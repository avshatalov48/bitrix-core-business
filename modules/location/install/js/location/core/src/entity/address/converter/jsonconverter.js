import Address from '../../address';
import AddressFieldCollection from '../addressfieldcollection';
import AddressLinkCollection from '../addresslinkcollection';

export default class JsonConverter
{
	/**
	 * @param {Object} jsonData
	 * @returns {Address}
	 */
	static convertJsonToAddress(jsonData: Object): Address
	{
		return new Address(jsonData);
	}

	/**
	 * @param {Address} address
	 * @returns {{languageId: string, location: ({"'...'"}|null), id: number, fieldCollection: {"'...'"}}} Json data
	 */
	static convertAddressToJson(address: Address): Object
	{
		const obj = {
			id: address.id,
			languageId: address.languageId,
			latitude: address.latitude,
			longitude: address.longitude,
			fieldCollection: JsonConverter.#objectifyFieldCollection(address.fieldCollection),
			links: JsonConverter.#objectifyLinks(address.links),
			location: null
		};

		if (address.location)
		{
			obj.location = JSON.parse(address.location.toJson());
		}

		return JSON.stringify(obj);
	}

	/**
	 * @param {AddressFieldCollection} fieldCollection
	 * @returns {Object}
	 */
	static #objectifyFieldCollection(fieldCollection: AddressFieldCollection): Object
	{
		const result = {};

		Object.values(fieldCollection.fields).forEach((field) => {
			result[field.type] = field.value;
		});

		return result;
	}

	static #objectifyLinks(links: AddressLinkCollection): Array<{entityId: string, entityType: string}>
	{
		return links.map((link) => {
			return {
				entityId: link.entityId,
				entityType: link.entityType
			};
		});
	}
}