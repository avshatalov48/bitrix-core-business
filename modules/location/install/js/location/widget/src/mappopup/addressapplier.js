import {Vue} from 'ui.vue';
import {Address, AddressStringConverter} from 'location.core';

export default Vue.extend({
	props: {
		address: {required: true},
		addressFormat: {required: true},
		isHidden: {required: true},
	},
	methods: {
		handleApplyClick()
		{
			this.$emit('apply', {address: this.address});
		},
		convertAddressToString(address: ?Address): string
		{
			if (!address)
			{
				return '';
			}

			return address.toString(this.addressFormat, AddressStringConverter.STRATEGY_TYPE_TEMPLATE_COMMA);
		}
	},
	computed: {
		addressString()
		{
			if (!this.address)
			{
				return '';
			}

			return this.address.toString(this.addressFormat, AddressStringConverter.STRATEGY_TYPE_TEMPLATE_COMMA, AddressStringConverter.CONTENT_TYPE_TEXT);
		},
		containerStyles()
		{
			return {
				display: this.isHidden ? 'none' : 'flex'
			};
		},
		containerClasses()
		{
			return this.isHidden ? {hidden: true} : {};
		},
		localize()
		{
			return Vue.getFilteredPhrases('LOCATION_WIDGET_');
		},
	},
	template: `
		<div
			:class="containerClasses"
			:style="containerStyles"
			class="location-map-address-changed"
		>
			<div class="location-map-address-changed-inner">
			<div class="location-map-address-changed-title">
				{{localize.LOCATION_WIDGET_AUI_ADDRESS_CHANGED_NEW_ADDRESS}}
			</div>
			<div class="location-map-address-changed-text">{{addressString}}</div>
			</div>
			<button @click="handleApplyClick" type="button" class="location-map-address-apply-btn">
				{{localize.LOCATION_WIDGET_AUI_ADDRESS_APPLY}}
			</button>
		</div>	
	`
});
