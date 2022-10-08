import {Type} from 'main.core';
import Address from './address';
import LocationJsonConverter from './location/locationjsonconverter';
import LocationFieldCollection from './location/locationfieldcollection';

export default class Location
{
	#id;
	#code;
	#externalId;
	#sourceCode;
	#type;
	#name;
	#languageId;
	#latitude;
	#longitude;
	#address;
	#fieldCollection;

	constructor(props = {})
	{
		this.#id = parseInt(props.id) || 0;
		this.#code = props.code || '';
		this.#externalId = props.externalId || '';
		this.#sourceCode = props.sourceCode || '';
		this.#type = parseInt(props.type) || 0;
		this.#name = props.name || '';
		this.#languageId = props.languageId || '';
		this.#latitude = props.latitude || '';
		this.#longitude = props.longitude || '';
		this.#fieldCollection = new LocationFieldCollection();

		if(Type.isObject(props.fieldCollection))
		{
			for(const [type, value] of Object.entries(props.fieldCollection))
			{
				this.setFieldValue(type, value);
			}
		}

		this.#address = null;

		if(props.address)
		{
			if(props.address instanceof Address)
			{
				this.#address = props.address;
			}
			else if(typeof props.address === 'object')
			{
				this.#address = new Address(props.address);
			}
			else
			{
				BX.debug('Wrong typeof props.address');
			}
		}
	}

	get id(): number
	{
		return this.#id;
	}

	get code(): string
	{
		return this.#code;
	}

	get externalId(): string
	{
		return this.#externalId;
	}

	get sourceCode(): string
	{
		return this.#sourceCode;
	}

	get type(): number
	{
		return this.#type;
	}

	get name(): string
	{
		return this.#name;
	}

	get languageId(): string
	{
		return this.#languageId;
	}

	set id(value: number): void
	{
		this.#id = value;
	}

	set code(code: string): void
	{
		this.#code = code;
	}

	set externalId(value: string): void
	{
		this.#externalId = value;
	}

	set sourceCode(value: string): void
	{
		this.#sourceCode = value;
	}

	set type(value: number): void
	{
		this.#type = value;
	}

	set name(value: string): void
	{
		this.#name = value;
	}

	set languageId(value: string): void
	{
		this.#languageId = value;
	}

	get latitude(): string
	{
		return this.#latitude;
	}

	set latitude(latitude: string): void
	{
		this.#latitude = latitude;
	}

	get longitude(): string
	{
		return this.#longitude;
	}

	set longitude(longitude: string): void
	{
		this.#longitude = longitude;
	}

	set address(address: Address): void
	{
		this.#address = address;
	}

	get address(): ?Address
	{
		return this.#address;
	}

	toJson(): string
	{
		return LocationJsonConverter.convertLocationToJson(this);
	}

	toAddress(): ?Address
	{
		let result = null;

		if(this.address)
		{
			const addressObj = JSON.parse(this.address.toJson());
			addressObj.location = JSON.parse(this.toJson());
			result = new Address(addressObj);
		}

		return result;
	}

	get fieldCollection(): LocationFieldCollection
	{
		return this.#fieldCollection;
	}

	setFieldValue(type: number, value: string): void
	{
		this.#fieldCollection.setFieldValue(type, value);
	}

	getFieldValue(type: number): ?string
	{
		return this.#fieldCollection.getFieldValue(type);
	}

	isFieldExists(type: number): boolean
	{
		return this.#fieldCollection.isFieldExists(type);
	}

	hasExternalRelation(): boolean
	{
		return (this.#externalId && this.#sourceCode);
	}
}