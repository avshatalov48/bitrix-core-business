import {Vue} from 'ui.vue';
import {ControlMode, AddressStringConverter} from 'location.core';
import {Factory, State} from 'location.widget';
import {ClosableDirective} from './closabledirective';
import './css/address.css';

export const AddressControlConstructor = Vue.extend({
	directives: {
		closable: ClosableDirective
	},
	props: {
		name: {type: String, required: true},
		initValue: {required: false},
		onChangeCallback: {type: Function, required: false}
	},
	data()
	{
		return {
			id: null,
			isEditMode: null,
			value: null,
			addressWidget: null,
		}
	},
	methods: {
		startOver()
		{
			this.addressWidget.address = null;
			this.changeValue(null);
			this.closeMap();
		},
		changeValue(newValue)
		{
			this.$emit('change', newValue);
			this.value = newValue;

			if (this.onChangeCallback)
			{
				setTimeout(this.onChangeCallback, 0);
			}
		},
		buildAddress(value)
		{
			try
			{
				return new BX.Location.Core.Address(JSON.parse(value));
			}
			catch(e)
			{
				return null;
			}
		},
		getMap()
		{
			if (!this.addressWidget)
			{
				return null;
			}

			for( let feature of this.addressWidget.features)
			{
				if(feature instanceof BX.Location.Widget.MapFeature)
				{
					return feature;
				}
			}

			return null;
		},
		showMap()
		{
			let map = this.getMap();

			if (map)
			{
				map.showMap();
			}
		},
		closeMap()
		{
			let map = this.getMap();

			if (map)
			{
				map.closeMap();
			}

			this.isEditMode = false;
		},
		onInputControlClicked()
		{
			if (this.value)
			{
				this.showMap();
			}
			else
			{
				this.closeMap();
			}
		}
	},
	computed: {
		wrapperClass()
		{
			return {
				'ui-ctl': true,
				'ui-ctl-w100': true,
				'ui-ctl-after-icon': true,
			}
		},
		addressFormatted()
		{
			if(!this.value || !this.addressWidget)
			{
				return '';
			}
			let address = this.buildAddress(this.value);

			if (!address)
			{
				return '';
			}

			return address.toString(
				this.addressWidget.addressFormat,
				AddressStringConverter.STRATEGY_TYPE_FIELD_SORT
			);
		},
	},
	mounted()
	{
		if (this.initValue)
		{
			this.value = this.initValue;
		}

		let factory = new BX.Location.Widget.Factory;

		this.addressWidget = factory.createAddressWidget({
			address: this.initValue ? this.buildAddress(this.initValue) : null,
			mapBehavior: 'manual',
			popupBindOptions: {
				position: 'right'
			},
			mode: ControlMode.edit,
			useFeatures:
				{
					fields: false,
					map: true,
					autocomplete: true
				}
		});

		this.addressWidget.subscribeOnAddressChangedEvent((event) => {
			let data = event.getData();

			this.isEditMode = true;

			let address = data.address;

			if (!address.latitude || !address.longitude)
			{
				this.changeValue(null);
				this.closeMap();
			}
			else
			{
				this.changeValue(address.toJson());
				this.showMap();
			}
		});

		this.addressWidget.subscribeOnStateChangedEvent((event) => {
			let data = event.getData();

			if (data.state === State.DATA_INPUTTING)
			{
				this.changeValue(null);
				this.closeMap();
			}
		});

		/**
		 * Render widget
		 */
		this.addressWidget.render({
			inputNode: this.$refs['input-node'],
			mapBindElement: this.$refs['input-node'],
			controlWrapper: this.$refs['control-wrapper'],
		});
	},

	template: `
		<div
			v-closable="{
			  exclude: ['input-node'],
			  handler: 'closeMap'
			}"
			class="ui-ctl-w100"
		>
			<div :class="wrapperClass" ref="control-wrapper">
				<div
					@click="startOver"
					class="ui-ctl-after ui-ctl-icon-btn ui-ctl-icon-clear"
				></div>
				<input
					@click="onInputControlClicked"
					ref="input-node"
					type="text"
					class="ui-ctl-element ui-ctl-textbox"
					v-html="addressFormatted"
				/>
				<input v-model="value" type="hidden" :name="name" />
			</div>				
		</div>
	`
});
