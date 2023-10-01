import {
	Format,
	Location,
	Address,
	LocationType,
	Point,
	FormatTemplateType,
	AddressType,
	ControlMode,
	BaseSource,
	LocationRepository,
	AddressStringConverter,
} from 'location.core';

import { Factory } from 'location.source';

export class LocationHelper
{
	static makeObjectFromLocation(location: Location): Object
	{
		return JSON.parse(location.toJson());
	}

	static makeAddressFromObject(address: Object): Address
	{
		return new Address(address);
	}

	static makeObjectFromAddress(address: Address): Object
	{
		return JSON.parse(address.toJson());
	}

	static makeFormatFromObject(format: Object): Format
	{
		return new Format(format);
	}

	static makeSource(): ?BaseSource
	{
		return Factory.create(
			BX.message('LOCATION_MOBILE_SOURCE_CODE'),
			BX.message('LOCATION_MOBILE_LANGUAGE_ID'),
			BX.message('LOCATION_MOBILE_SOURCE_LANGUAGE_ID'),
			BX.message('LOCATION_MOBILE_SOURCE_PARAMS')
		);
	}

	static makeRepository(): LocationRepository
	{
		return new LocationRepository();
	}

	static getTextAddress(address: ?Object, addressFormat: Object, template: string): string
	{
		if (address === null)
		{
			return '';
		}

		const locAddressFormat = LocationHelper.makeFormatFromObject(addressFormat);

		return AddressStringConverter.convertAddressToStringTemplate(
			LocationHelper.makeAddressFromObject(address),
			locAddressFormat.getTemplate(template),
			'text',
			', ',
			locAddressFormat
		);
	}

	static getTextAddressForAutocomplete(address: ?Object, addressFormat: Object): string
	{
		return LocationHelper.getTextAddress(address, addressFormat, FormatTemplateType.AUTOCOMPLETE);
	}

	static getTextAddressForDefault(address: ?Object, addressFormat: Object): string
	{
		return LocationHelper.getTextAddress(address, addressFormat, FormatTemplateType.DEFAULT);
	}

	static getTextAddressForMap(address: ?Object, addressFormat: Object): string
	{
		let result = LocationHelper.getTextAddressForAutocomplete(address, addressFormat);
		if (result.trim() === '')
		{
			result = LocationHelper.getTextAddressForDefault(address, addressFormat);
		}

		return result;
	}

	static makeMapRenderProps(
		address: ?Object,
		deviceGeoPosition: ?Object,
		isEditable: boolean,
		mapContainer: HTMLElement
	): Object
	{
		const result = {
			zoomControl: false,
			mode: isEditable ? ControlMode.edit : ControlMode.view,
			mapContainer,
		};

		if (!LocationHelper.isAddressValidForMap(address))
		{
			if (deviceGeoPosition)
			{
				result.location = {
					latitude: deviceGeoPosition.latitude,
					longitude: deviceGeoPosition.longitude,
					type: LocationType.BUILDING,
				};
				result.searchOnRender = !address;
			}
			else
			{
				const defaultLocationPoint = JSON.parse(BX.message('LOCATION_MOBILE_DEFAULT_LOCATION_POINT'));
				if (defaultLocationPoint)
				{
					result.location = {
						latitude: defaultLocationPoint.latitude,
						longitude: defaultLocationPoint.longitude,
						type: LocationType.BUILDING,
					};
					result.searchOnRender = !address;
				}
			}
		}
		else
		{
			result.location = {
				latitude: address.latitude,
				longitude: address.longitude,
				type: Math.max(
					...Object.keys(address.fieldCollection).map(Number)
				),
			};
		}

		return result;
	}

	static makeAutocompleteParams(address: ?Object, deviceGeoPosition: ?Object): Object
	{
		const result = {};

		const biasPoint = LocationHelper.getAutocompleteBiasPoint(address, deviceGeoPosition)
		if (biasPoint)
		{
			result.biasPoint = biasPoint;
		}

		return result;
	}

	static getAutocompleteBiasPoint(address: ?Object, deviceGeoPosition: ?Object): ?Point
	{
		if (
			address !== null
			&& address.latitude !== ''
			&& address.longitude !== ''
		)
		{
			return new Point(
				address.latitude,
				address.longitude
			);
		}

		if (deviceGeoPosition)
		{
			return new Point(
				deviceGeoPosition.latitude,
				deviceGeoPosition.longitude
			);
		}

		return null;
	}

	static search(query, address: ?Object, deviceGeoPosition: ?Object): Promise
	{
		return new Promise((resolve, reject) => {
			const source = LocationHelper.makeSource();

			source.autocompleteService.autocomplete(
				query,
				LocationHelper.makeAutocompleteParams(address, deviceGeoPosition),
			)
				.then(
					(searchResults) => {
						resolve(searchResults.map(LocationHelper.makeObjectFromLocation));
					},
					() => {
						reject();
					}
				);
		});
	}

	static makeAddressFromText(line2Value: string, addressFormat: Object): Object
	{
		const locAddressFormat = LocationHelper.makeFormatFromObject(addressFormat);
		const locAddress = LocationHelper.makeAddressFromObject({
			languageId: locAddressFormat.languageId,
		});
		locAddress.fieldCollection.setFieldValue(
			AddressType.ADDRESS_LINE_2,
			line2Value
		);

		return LocationHelper.makeObjectFromAddress(locAddress);
	}

	static findAddressByLocation(location: Object): Promise
	{
		return new Promise((resolve, reject) => {

			const repository = LocationHelper.makeRepository();

			repository.findByExternalId(
				location.externalId,
				location.sourceCode,
				location.languageId,
			).then(
				(foundLocation) => {
					resolve(
						foundLocation
							? LocationHelper.makeObjectFromAddress(foundLocation.address)
							: null,
					);
				},
				() => {
					reject();
				}
			);
		});
	}

	static getAddressFieldsValues(address: Object, addressFormat: Object): Array<Object>
	{
		const locAddress = address ? LocationHelper.makeAddressFromObject(address) : null;
		const locAddressFormat = LocationHelper.makeFormatFromObject(addressFormat);

		const result = [];

		for (const type in locAddressFormat.fieldCollection.fields)
		{
			const field = locAddressFormat.fieldCollection.fields[type];

			result.push({
				name: field.name,
				type: field.type,
				value: locAddress ? locAddress.fieldCollection.getFieldValue(type) : '',
			});
		}

		return result;
	}

	static applyFieldsToAddress(address: ?Object, addressFormat: Object, fields: Array<Object>): ?Object
	{
		const locAddressFormat = LocationHelper.makeFormatFromObject(addressFormat);
		const locAddress = LocationHelper.makeAddressFromObject(
			address || { languageId: locAddressFormat.languageId }
		);

		let allFieldsAreEmpty = true;
		for (const field of fields)
		{
			const currentValue = locAddress.fieldCollection.getFieldValue(field.type);

			if (currentValue !== field.value)
			{
				locAddress.fieldCollection.setFieldValue(
					field.type,
					field.value
				);
			}

			if (field.value !== null && field.value !== '')
			{
				allFieldsAreEmpty = false;
			}
		}

		if (allFieldsAreEmpty)
		{
			return null;
		}

		return LocationHelper.makeObjectFromAddress(locAddress);
	}

	static applyDetailsToAddress(address: Object, details: string): Object
	{
		const locAddress = LocationHelper.makeAddressFromObject(address);

		locAddress.fieldCollection.setFieldValue(
			AddressType.ADDRESS_LINE_2,
			details
		);

		return LocationHelper.makeObjectFromAddress(locAddress);
	}

	static getAddressDetails(address: Object): string
	{
		const locAddress = LocationHelper.makeAddressFromObject(address);

		return locAddress.fieldCollection.getFieldValue(AddressType.ADDRESS_LINE_2) || '';
	}

	static getLocationTypeClarification(location: Object): string
	{
		return location.fieldCollection[LocationType.TMP_TYPE_CLARIFICATION] || '';
	}

	static isAddressValidForMap(address: ?Object): boolean
	{
		return (
			address
			&& address.latitude !== ''
			&& address.longitude !== ''
			&& address.latitude !== '0'
			&& address.longitude !== '0'
		);
	}

	static getLine2FieldName(addressFormat: Object): string
	{
		const locAddressFormat = LocationHelper.makeFormatFromObject(addressFormat);

		const field = locAddressFormat.getField(AddressType.ADDRESS_LINE_2);
		if (!field)
		{
			return '';
		}

		return field.name;
	}
}
