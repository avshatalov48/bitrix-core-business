import {Format, FormatTemplateType, AddressStringConverter, Address} from 'location.core';

export default class AddressString
{
	// Input node element
	#input = null;
	// Address string value
	#value = '';
	// Address string as it was without custom inputs
	#pureAddressString = '';
	#addressFormat = null;

	constructor(input: HTMLInputElement, addressFormat: Format, address: ?Address)
	{
		if (!(input instanceof HTMLInputElement))
		{
			throw new TypeError('Wrong input type');
		}

		this.#input = input;

		if (!(addressFormat instanceof Format))
		{
			throw new TypeError('Wrong addressFormat type');
		}

		this.#addressFormat = addressFormat;

		if (address && !(address instanceof Address))
		{
			throw new TypeError('Wrong address type');
		}
		if (address)
		{
			this.setValueFromAddress(address);
		}
	}

	/**
	 *
	 * @param {string} value Address string value
	 * @param {boolean} isPureAddress Does it contain user input or not
	 */
	setValue(value: string, isPureAddress: boolean = false): void
	{
		this.#value = value;
		this.#input.value = value;

		if (isPureAddress)
		{
			this.#pureAddressString = value;
		}

		this.#actualizePureString();
	}

	actualize()
	{
		this.#value = this.#input.value;
		this.#actualizePureString();
	}

	#actualizePureString()
	{
		if (this.#isPureAddressStringModified())
		{
			this.#pureAddressString = '';
		}
	}

	isChanged()
	{
		return this.#value.trim() !== this.#input.value.trim();
	}

	get value(): string
	{
		return this.#value;
	}

	get customTail()
	{
		if (this.#pureAddressString === '')
		{
			return this.#value;
		}

		let result;

		if (!this.#isPureAddressStringModified())
		{
			result = this.#value.slice(this.#pureAddressString.length);
		}
		else
		{
			result = this.#value;
		}

		return result;
	}

	hasPureAddressString()
	{
		return this.#pureAddressString !== '';
	}

	// We suggest that user will input data after the address data
	#isPureAddressStringModified(): boolean
	{
		return this.#value === ''
			|| this.#pureAddressString === ''
			|| this.#value.indexOf(this.#pureAddressString) !== 0;
	}

	setValueFromAddress(address: ?Address): void
	{
		let value = '';

		if (address)
		{
			value = this.#convertAddressToString(address, FormatTemplateType.AUTOCOMPLETE);

			if (value.trim() === '')
			{
				value = this.#convertAddressToString(address, FormatTemplateType.DEFAULT);
			}
		}

		this.setValue(value, true);
	}

	#convertAddressToString(address: Address, templateType: string): string
	{
		if (!this.#addressFormat.isTemplateExists(templateType))
		{
			console.error(`Address format "${this.#addressFormat.code}" does not have a template "${templateType}"`);
			return '';
		}

		return AddressStringConverter.convertAddressToStringTemplate(
			address,
			this.#addressFormat.getTemplate(templateType),
			AddressStringConverter.CONTENT_TYPE_TEXT,
			', ',
			this.#addressFormat
		);
	}
}