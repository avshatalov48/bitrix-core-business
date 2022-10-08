import {Tag} from 'main.core';
import {Address, Format, AddressStringConverter, FormatTemplateType} from 'location.core';

export default class AddressString
{
	#address;
	#element;
	#stringElement;
	#addressFormat;

	constructor(props)
	{
		if (!(props.addressFormat instanceof Format))
		{
			throw new Error('addressFormat must be instance of Format');
		}

		this.#addressFormat = props.addressFormat;
	}

	set address(address: ?Address): void
	{
		this.#address = address;

		if (!this.#stringElement)
		{
			return;
		}

		this.#stringElement.innerHTML = this.#convertAddressToString(address);

		if (!address && !this.isHidden())
		{
			this.hide();
		}
		else if (address && this.isHidden())
		{
			this.show();
		}
	}

	#convertAddressToString(address: ?Address): string
	{
		let result = '';

		if (address)
		{
			result = AddressStringConverter.convertAddressToStringTemplate(
				address,
				this.#addressFormat.getTemplate(FormatTemplateType.DEFAULT),
				AddressStringConverter.CONTENT_TYPE_HTML,
				', ',
				this.#addressFormat
			);
		}

		return result;
	}
	
	render(props): Element
	{
		this.#address = props.address;
		const addresStr = this.#convertAddressToString(this.#address);
		this.#stringElement = Tag.render`<div class="location-map-address-text">${addresStr}</div>`;

		this.#element = Tag.render`
			<div class="location-map-address-container">
				<div class="location-map-address-icon"></div>
				${this.#stringElement}
			</div>`;

		if (addresStr === '')
		{
			this.hide();
		}

		return this.#element;
	}

	show()
	{
		if (this.#element)
		{
			this.#element.style.display = 'block';
		}
	}

	hide()
	{
		if (this.#element)
		{
			this.#element.style.display = 'none';
		}
	}

	isHidden()
	{
		return !this.#element || this.#element.style.display === 'none';
	}
}