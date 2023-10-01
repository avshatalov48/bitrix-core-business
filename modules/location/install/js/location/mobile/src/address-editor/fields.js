import Field from './field';
import Keyboard from '../mixins/keyboard';
import { LocationHelper } from '../location-helper';
import { Dom } from 'main.core';

export default
{
	mixins: [Keyboard],
	components: {
		'field': Field,
	},
	props: {
		address: {
			type: Object,
			required: false,
		},
		addressFormat: {
			type: Object,
			required: true,
		},
		isEditable: {
			type: Boolean,
			required: false,
			default: true,
		},
	},
	data: () => {
		return {
			fields: [],
		};
	},
	created()
	{
		this.fields = LocationHelper.getAddressFieldsValues(this.address, this.addressFormat);
		this.subscribeToKeyboardEvents();
	},
	methods: {
		saveValues(): void
		{
			this.$emit(
				'address-changed',
				LocationHelper.applyFieldsToAddress(
					this.address,
					this.addressFormat,
					this.fields
				)
			);
		},
		onNewSearchClicked(): void
		{
			this.$emit('new-search-clicked');
		},
		onBackToMapClicked(): void
		{
			this.$emit('back-to-map-clicked');
		},
		onFieldInput(event): void
		{
			const field = this.fields.find((field) => field.type === event.type);
			if (field)
			{
				field.value = event.value;
			}
		},
		onDone(): void
		{
			this.$emit('done', LocationHelper.applyFieldsToAddress(this.address, this.addressFormat, this.fields));
		},
		hasSource(): boolean
		{
			const source = LocationHelper.makeSource();

			return !!source;
		},
	},
	computed: {
		shouldShowNewSearchButton(): boolean
		{
			return !this.isKeyboardShown && this.hasSource();
		},
	},
	watch: {
		isKeyboardShown(newValue): void
		{
			const mobileWrapper = document.querySelector('.mobile-address-container');

			if (newValue)
			{
				setTimeout(() => {
					Dom.style(mobileWrapper, 'height', `calc(100% + ${this.$refs['save-values'].offsetHeight}px)`);
					this.adjustWindowHeight();
				},0)
			}
			else
			{
				setTimeout(() => {
					Dom.style(mobileWrapper, 'height', '');
					document.body.style.height = '';
				},0)
			}
		}
	},
	template: `
		<div class="mobile-address-fields-fields-inner-container">
			<div class="mobile-address-fields-fields-container">
				<div class="mobile-address-fields-item-wrap">
					<field						
						v-for="field in fields"
						:name="field.name"
						:type="field.type"
						:value="field.value"
						:isEditable="isEditable"
						@input="onFieldInput"
					>
					</field>
				</div>				  
				<div
					v-if="isEditable"
					v-show="shouldShowNewSearchButton"
					@click="onNewSearchClicked"
					class="mobile-address-fields-search-container"
				>
					<div
						data-test-id="newSearchButton"
						class="mobile-address-fields-search"
					>
						<div class="mobile-address-fields-search-icon-search"></div>
						{{ $Bitrix.Loc.getMessage('LOCATION_MOBILE_APP_NEW_SEARCH') }}
					</div>
				</div>
				<div
					v-if="!isEditable"
					@click="onBackToMapClicked"
					class="mobile-address-fields-search-container"
				>
					<div
						data-test-id="backToMapButton"
						class="mobile-address-fields-search"
					>
						{{ $Bitrix.Loc.getMessage('LOCATION_MOBILE_APP_BACK_TO_MAP') }}
					</div>
				</div>
			</div>
			<div
				v-if="isEditable"
				v-show="!isKeyboardShown"
				@click="onDone"
				class="mobile-address-fields-use-this-address-container"
			>
				<div class="mobile-address-fields-use-this-address">
					{{ $Bitrix.Loc.getMessage('LOCATION_MOBILE_APP_DONE') }}
				</div>
			</div>
		</div>
		<div
			v-show="isKeyboardShown"
			@click="saveValues"
			class="mobile-address-fields-save-values"
			ref="save-values"
		>
			{{ $Bitrix.Loc.getMessage('LOCATION_MOBILE_APP_SAVE_VALUES') }}
		</div>
	`
};
