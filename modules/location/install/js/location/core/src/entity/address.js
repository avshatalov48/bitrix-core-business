import {Type} from 'main.core';
import AddressFieldCollection from './address/addressfieldcollection';
import AddressLinkCollection from './address/addresslinkcollection';
import AddressLink from './address/addresslink';
import StringConverter from './address/converter/stringconverter';
import Format from './format';
import JsonConverter from './address/converter/jsonconverter';
import Location from './location';

export default class Address
{
	#id;
	#languageId;
	#latitude;
	#longitude;

	#fieldCollection;
	#links;

	#location;

	/**
	 * @param {{...}} props
	 */
	constructor(props)
	{
		if(Type.isUndefined(props.languageId))
		{
			throw new TypeError('languageId must be defined');
		}

		this.#languageId = props.languageId;

		this.#id = props.id || 0;
		this.#latitude = props.latitude || '';
		this.#longitude = props.longitude || '';
		this.#fieldCollection = new AddressFieldCollection();

		if(Type.isObject(props.fieldCollection))
		{
			for(const [type, value] of Object.entries(props.fieldCollection))
			{
				this.setFieldValue(type, value);
			}
		}

		this.#links = new AddressLinkCollection();

		if(Type.isArray(props.links))
		{
			for(const link of props.links)
			{
				this.addLink(link.entityId, link.entityType);
			}
		}

		this.#location = null;

		if(props.location)
		{
			if(props.location instanceof Location)
			{
				this.#location = props.location;
			}
			else if(Type.isObject(props.location))
			{
				this.#location = new Location(props.location);
			}
			else
			{
				BX.debug('Wrong typeof props.location');
			}
		}
	}

	/**
	 * @returns {int}
	 */
	get id(): number
	{
		return this.#id;
	}

	/**
	 * @returns {Location}
	 */
	get location(): ?Location
	{
		return this.#location;
	}

	/**
	 * @returns {string}
	 */
	get languageId(): string
	{
		return this.#languageId;
	}

	/**
	 * @returns {AddressFieldCollection}
	 */
	get fieldCollection(): AddressFieldCollection
	{
		return this.#fieldCollection;
	}

	/**
	 * @param {int} id
	 */
	set id(id: number)
	{
		this.#id = id;
	}

	/**
	 * @param {Location} location
	 */
	set location(location: ?Location)
	{
		this.#location = location;
	}

	/**
	 * @returns {string}
	 */
	get latitude(): string
	{
		return this.#latitude;
	}

	/**
	 * @param {string} latitude
	 */
	set latitude(latitude: string): void
	{
		this.#latitude = latitude;
	}

	/**
	 * @returns {string}
	 */
	get longitude(): string
	{
		return this.#longitude;
	}

	/**
	 * @param {string} longitude
	 */
	set longitude(longitude: string): void
	{
		this.#longitude = longitude;
	}

	/**
	 * @returns {AddressLinkCollection}
	 */
	get links(): AddressLinkCollection
	{
		return this.#links.links;
	}

	/**
	 * @param {number} type
	 * @param {mixed} value
	 */
	setFieldValue(type: number, value: string): void
	{
		this.#fieldCollection.setFieldValue(type, value);
	}

	/**
	 * @param {number} type
	 * @returns {?string}
	 */
	getFieldValue(type: number): ?string
	{
		return this.#fieldCollection.getFieldValue(type);
	}

	/**
	 * Check if field exist
	 * @param type
	 * @returns {boolean}
	 */
	isFieldExists(type: number): boolean
	{
		return this.#fieldCollection.isFieldExists(type);
	}

	/**
	 * @return {string} JSON
	 */
	toJson(): string
	{
		return JsonConverter.convertAddressToJson(this);
	}

	/**
	 * @param {Format}format
	 * @param {?string}strategyType
	 * @param {?string}contentType
	 * @return {string}
	 */
	toString(format: Format, strategyType: ?string, contentType: ?string): string
	{
		if(!(format instanceof Format))
		{
			console.error('format must be instance of Format');
			return '';
		}

		const strategy = strategyType || StringConverter.STRATEGY_TYPE_TEMPLATE;
		const type = contentType || StringConverter.CONTENT_TYPE_HTML;
		return StringConverter.convertAddressToString(this, format, strategy, type);
	}

	/**
	 * @returns {?Location}
	 */
	toLocation(): ?Location
	{
		let result = null;

		if(this.location)
		{
			const locationObj = JSON.parse(this.location.toJson());
			locationObj.address = JSON.parse(this.toJson());
			result = new Location(locationObj);
		}

		return result;
	}

	/**
	 * @return {number}
	 */
	getType(): number
	{
		return this.#fieldCollection.getMaxFieldType();
	}

	/**
	 * @param {string} entityId
	 * @param {string} entityType
	 */
	addLink(entityId: number, entityType: string): void
	{
		this.#links.addLink(new AddressLink({
			entityId: entityId,
			entityType: entityType
		}));
	}

	clearLinks(): void
	{
		this.#links.clearLinks();
	}
}