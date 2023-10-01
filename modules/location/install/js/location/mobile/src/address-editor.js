import { ajax } from 'main.core';
import MapMode from './address-editor/map';
import AutocompleteMode from './address-editor/autocomplete';
import FieldsMode from './address-editor/fields';

import { LocationHelper } from './location-helper';

import 'ui.design-tokens';
import './css/address-editor.css';

const ModeList = {
	map: 'map',
	autocomplete: 'autocomplete',
	fields: 'fields',
};

export default
{
	props: {
		initialAddress: {
			type: Object,
			required: false,
		},
		addressFormat: {
			type: Object,
			required: false,
			default: JSON.parse(
				BX.message('LOCATION_WIDGET_DEFAULT_FORMAT')
			),
		},
		deviceGeoPosition: {
			type: Object,
			required: false,
		},
		recentAddresses: {
			type: Array,
			required: false,
			default: [],
		},
		isEditable: {
			type: Boolean,
			required: false,
			default: true,
		},
		uid: {
			type: String,
			required: false,
		},
	},
	components: {
		'map-mode': MapMode,
		'autocomplete-mode': AutocompleteMode,
		'fields-mode': FieldsMode,
	},
	data: () => {
		return {
			address: null,
			mode: null,
		};
	},
	created()
	{
		if (this.initialAddress)
		{
			this.setAddress(this.initialAddress);
		}

		this.initializeMode();
	},
	computed: {
		isModeMap(): boolean
		{
			return this.mode === ModeList.map;
		},
		isModeAutocomplete(): boolean
		{
			return this.mode === ModeList.autocomplete;
		},
		isModeFields(): boolean
		{
			return this.mode === ModeList.fields;
		},
	},
	methods: {
		initializeMode(): void
		{
			const source = LocationHelper.makeSource();

			if (source)
			{
				if (this.isEditable)
				{
					this.setMode(ModeList.autocomplete);
				}
				else
				{
					if (LocationHelper.isAddressValidForMap(this.address))
					{
						this.setMode(ModeList.map);
					}
					else
					{
						this.setMode(ModeList.fields);
					}
				}
			}
			else
			{
				this.setMode(ModeList.fields);
			}
		},
		setAddress(address): void
		{
			this.address = address;
		},
		setMode(mode): void
		{
			this.mode = mode;
		},
		emitAddressUsed(): void
		{
			if (!this.isEditable)
			{
				return;
			}

			const emitAddressValue = this.getEmitAddressValue();
			if (emitAddressValue.value !== null)
			{
				this.saveRecentAddress();
			}

			const params = {
				address: emitAddressValue,
			};
			if (this.uid)
			{
				params.uid = this.uid;
			}

			BXMobileApp.Events.postToComponent('Location::MobileAddressEditor::AddressSelected', params);
		},
		getEmitAddressValue(): Object
		{
			if (this.address === null)
			{
				return {
					value: null,
					text: '',
					coords: [],
				};
			}

			const address = LocationHelper.makeAddressFromObject(this.address);

			if (this.initialAddress)
			{
				address.id = this.initialAddress.id || 0;
			}

			return {
				value: address.toJson(),
				text: LocationHelper.getTextAddressForDefault(this.address, this.addressFormat),
				coords: (address.latitude !== '' && address.longitude !== '')
					? [address.latitude, address.longitude]
					: [],
			};
		},
		saveRecentAddress()
		{
			ajax.runAction('location.api.recentaddress.save', {
				data: {
					address: this.address,
				}
			});
		},
		onMapSearchClicked(): void
		{
			if (!this.isEditable)
			{
				return;
			}
			this.setMode(ModeList.autocomplete);
		},
		onMapAddressChanged(address): void
		{
			this.setAddress(address);
		},
		onMapDone(): void
		{
			this.emitAddressUsed();
		},
		onMapAddressClicked(): void
		{
			if (this.isEditable)
			{
				return;
			}

			this.setMode(ModeList.fields);
		},
		onAutocompleteAddressPicked(address): void
		{
			this.setAddress(address);
			this.setMode(ModeList.map);
		},
		onAutocompleteAddressChanged(address): void
		{
			this.setAddress(address);
		},
		onAutocompleteMapClicked(): void
		{
			this.setMode(ModeList.map);
		},
		onAutocompleteAddressNotFoundClicked(): void
		{
			this.setMode(ModeList.fields);
		},
		onAutocompleteClearAddress(): void
		{
			this.setAddress(null);
		},
		onFieldsAddressChanged(address): void
		{
			this.setAddress(address);
		},
		onFieldsNewSearchClicked(): void
		{
			this.setMode(ModeList.autocomplete);
		},
		onFieldsDone(address): void
		{
			this.setAddress(address);
			this.emitAddressUsed();
		},
		onFieldsBackToMapClicked(): void
		{
			this.setMode(ModeList.map);
		},
	},
	template: `
		<div class="mobile-address-container mobile-address--scope">
			<map-mode
				v-if="isModeMap"
				:address="address"
				:addressFormat="addressFormat"
				:deviceGeoPosition="deviceGeoPosition"
				:isEditable="isEditable"
				@search-clicked="onMapSearchClicked"
				@address-changed="onMapAddressChanged"
				@done-clicked="onMapDone"
				@address-clicked="onMapAddressClicked"
				ref="mobile-container"
			>
			</map-mode>
			<autocomplete-mode
				v-if="isModeAutocomplete"
				:address="address"
				:addressFormat="addressFormat"
				:deviceGeoPosition="deviceGeoPosition"
				:recentAddresses="recentAddresses"
				:isEditable="isEditable"
				@address-picked="onAutocompleteAddressPicked"
				@address-changed="onAutocompleteAddressChanged"
				@map-clicked="onAutocompleteMapClicked"
				@address-not-found-clicked="onAutocompleteAddressNotFoundClicked"
				@clear-address="onAutocompleteClearAddress"
			>
			</autocomplete-mode>
			<fields-mode
				v-if="isModeFields"
				:address="address"
			   	:addressFormat="addressFormat"
				:isEditable="isEditable"
				@address-changed="onFieldsAddressChanged"
				@new-search-clicked="onFieldsNewSearchClicked"
			   	@done="onFieldsDone"
				@back-to-map-clicked="onFieldsBackToMapClicked"
			>
			</fields-mode>
		</div>
	`
};
