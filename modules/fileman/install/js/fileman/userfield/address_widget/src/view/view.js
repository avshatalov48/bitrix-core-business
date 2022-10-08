import 'ui.design-tokens';
import {Address as AddressWidget, Factory} from "location.widget";
import {Dom, Tag} from "main.core";
import {Address as AddressEntity, AddressStringConverter, ControlMode, Format} from "location.core";
import {BaseView} from './baseview';
import './css/style.css';

export class View extends BaseView
{
	#addresses: AddressEntity[] = [];

	#widgets: AddressWidget[] = [];

	constructor(params: Object)
	{
		super(params);
		this.#addresses = params.addresses;
	}

	destroyWidgets()
	{
		this.#widgets.forEach((widget) => {
			widget.destroy();
		});
	}

	layout(): Element
	{
		const layout = Tag.render`<div></div>`;

		this.#addresses.forEach((address) => {
			Dom.append(this.getLayoutForAddress(address), layout);
		});

		Dom.append(layout, this.getWrapper());

		return this.getWrapper();
	}

	getLayoutForAddress(address: AddressEntity)
	{
		const factory = new Factory();
		const widget = factory.createAddressWidget({
			address: address,
			mode: ControlMode.view,
			popupOptions: {
				offsetLeft: 14,
			},
			popupBindOptions: {
				forceBindPosition: true,
				position: 'right',
			},
			mapBehavior: 'auto',
			useFeatures: {
				fields: false,
				map: true,
				autocomplete: false,
			}
		});

		this.#widgets.push(widget);

		const addressLayout = Tag.render`
			<span class="fields address field-item view">
				<span class="ui-link ui-link-dark ui-link-dotted">${this.getFormattedAddress(address)}</span>
			</span>
		`;

		widget.render({
			mapBindElement: addressLayout,
			controlWrapper: addressLayout,
		});

		return addressLayout;
	}

	getFormattedAddress(address: AddressEntity): string
	{
		const format = new Format(JSON.parse(BX.message('LOCATION_WIDGET_DEFAULT_FORMAT')));
		return address.toString(format, AddressStringConverter.STRATEGY_TYPE_TEMPLATE_COMMA) ?? '';
	}
}
