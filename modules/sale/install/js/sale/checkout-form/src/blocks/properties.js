import {BaseBlock} from './base-block';
import {Dom, Tag, Text, Type, Loc} from 'main.core';

export class Properties extends BaseBlock
{
	layout()
	{
		this.getWrapper().appendChild(
			this.getMode() === BaseBlock.VIEW_MODE ? this.getViewLayout() : this.getEditLayout()
		);
	}

	getPropertiesShort(): string
	{
		const propertyValues = this.getForm().getField('properties');
		const properties = [];

		for (let propertyId in propertyValues)
		{
			if (propertyValues.hasOwnProperty(propertyId) && Type.isStringFilled(propertyValues[propertyId]))
			{
				properties.push(propertyValues[propertyId]);
			}
		}

		return properties.join(', ');
	}

	getViewLayout(): HTMLElement
	{
		const orderNumber = this.getForm().getSchemeField('accountNumber');
		const propertiesInfo = this.getPropertiesShort();

		return Tag.render`
			<div style="border-bottom: 1px solid #cecece;">
				<tr class="checkout-summary-item">
					<td colspan="2">
						<div class="checkout-item-personal-order-info">
							<div class="checkout-item-personal-order-payment">
								<strong>${Loc.getMessage('SALE_BLOCKS_PROPERTIES_ORDER_TITLE').replace('#ORDER_NUMBER#', orderNumber)}</strong>
								<div>${propertiesInfo}</div>
							</div>
							<div class="checkout-item-personal-order-shipping">
								<strong>${Loc.getMessage('SALE_BLOCKS_PROPERTIES_SHIPPING_METHOD')}</strong>
								<div>${Loc.getMessage('SALE_BLOCKS_PROPERTIES_SHIPPING_METHOD_DESCRIPTION')}</div>
							</div>
						</div>
					</td>
				</tr>
			</div>
		`;
	}

	getEditLayout(): HTMLElement
	{
		return Tag.render`
			<div class="checkout-form-container">
				<div class="checkout-form-header">
					<div class="checkout-form-title">${Loc.getMessage('SALE_BLOCKS_PROPERTIES_TITLE')}</div>
				</div>
				<div class="checkout-form-block">
					<form>
						<div class="form-group">
							${this.getProperties()}
						</div>
					</form>
				</div>
			</div>
		`;
	}

	getProperties(): HTMLElement[]
	{
		const properties = [];

		this.getForm().getSchemeField('properties', []).forEach((item) => {
			if (item.type === 'STRING')
			{
				const value = this.getForm().getField('properties', {})[item.id] || '';
				const type = item.isPhone === 'Y' ? 'tel' : 'text';
				const propertyNode = Tag.render`
					<input 
						type="${type}"
						class="form-control form-control-lg"
						placeholder="${Text.encode(item.name)}"
						value="${Text.encode(value)}"
						data-property-id="${item.id}"
						onchange="${this.onChangeHandler.bind(this)}"
						onfocusout="${this.onFocusOutHandler.bind(this)}">
			`;

				BX.addCustomEvent("BX.Sale.Checkout.Property.Error:onSave_" + item.id, ()=>{
					Dom.addClass(propertyNode, 'border-danger');
					Dom.removeClass(propertyNode, 'border-success');
				});

				properties.push(propertyNode);
			}
		});

		return properties;
	}

	onChangeHandler(event: Event)
	{
		const input = event.target;

		if (!Type.isDomNode(input))
		{
			return;
		}

		const propertyId = input.getAttribute('data-property-id');
		const properties = this.getForm().getField('properties');

		properties[propertyId] = input.value;
		this.getForm().setFieldNoDemand('properties', properties);
	}

	onFocusOutHandler(event: Event)
	{
		const input = event.target;
		if (!Type.isDomNode(input))
		{
			return;
		}

		if (Type.isStringFilled(input.value))
		{
			Dom.addClass(input, 'border-success');
			Dom.removeClass(input, 'border-danger');
		}
		else
		{
			Dom.addClass(input, 'border-danger');
			Dom.removeClass(input, 'border-success');
		}
	}
}