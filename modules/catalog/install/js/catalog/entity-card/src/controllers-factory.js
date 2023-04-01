import IblockSectionController from './iblock-section/controller';
import {type BaseEvent, EventEmitter} from 'main.core.events'
import VariationGridController from './variation-grid/controller';
import VariationLinkController from './variation-link/controller';
import GoogleMapController from './google-map/controller';
import EmployeeController from './employee/controller';
import UserController from './user/controller';
import IblockElementController from './iblock-element/controller';
import BindingToCrmElementController from './binding-to-crm-element/controller';
import FieldConfiguratorController from './field-configurator/controller';
import ProductServiceGridController from "./product-service-grid/controller"

export default class ControllersFactory
{
	constructor()
	{
		EventEmitter.subscribe('BX.UI.EntityEditorControllerFactory:onInitialize', (event: BaseEvent) => {
			const [, eventArgs] = event.getCompatData();
			eventArgs.methods['entityCard'] = this.factory.bind(this);
		});
	}

	factory(type, controlId, settings)
	{
		if (type === 'field_configurator')
		{
			return new FieldConfiguratorController(controlId, settings);
		}

		if (type === 'iblock_section')
		{
			return new IblockSectionController(controlId, settings);
		}

		if (type === 'iblock_element')
		{
			return new IblockElementController(controlId, settings);
		}

		if (type === 'variation_grid')
		{
			return new VariationGridController(controlId, settings);
		}

		if (type === 'variation_link')
		{
			return new VariationLinkController(controlId, settings);
		}

		if (type === 'google_map')
		{
			return new GoogleMapController(controlId, settings);
		}

		if (type === 'employee')
		{
			return new EmployeeController(controlId, settings);
		}

		if (type === 'user')
		{
			return new UserController(controlId, settings);
		}

		if (type === 'binding_to_crm_element')
		{
			return new BindingToCrmElementController(controlId, settings);
		}

		if (type === 'service_grid')
		{
			return new ProductServiceGridController(controlId, settings);
		}

		return null;
	}
}