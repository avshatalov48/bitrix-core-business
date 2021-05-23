import {Dom, Tag, Text, Type} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {SkuTree} from 'catalog.sku-tree';

export default class SkuProperty
{
	parent: ?SkuTree;

	skuSelectHandler = this.handleSkuSelect.bind(this);

	constructor(options)
	{
		this.parent = options.parent || null;
		if (!this.parent)
		{
			throw new Error('Parent is not defined.');
		}

		this.property = options.property || {};
		this.offers = options.offers || [];
		this.existingValues = options.existingValues || [];
		this.nodeDescriptions = [];
	}

	getId()
	{
		return this.property.ID;
	}

	getSelectedSkuId()
	{
		return this.parent.getSelectedSku().ID;
	}

	hasSkuValues()
	{
		return this.property.VALUES.length;
	}

	renderPictureSku(propertyValue, uniqueId)
	{
		const propertyName = Type.isStringFilled(propertyValue.NAME) ? Text.encode(propertyValue.NAME) : '';

		let nameNode = '';
		if (Type.isStringFilled(propertyName))
		{
			nameNode = Tag.render`<span class="ui-ctl-label-text">${propertyName}</span>`;
		}

		let style;
		if (propertyValue.PICT && propertyValue.PICT.SRC)
		{
			style = "background-image: url('" + propertyValue.PICT.SRC + "');";
		}
		else if (nameNode)
		{
			style = "display: none;";
			nameNode.style.paddingLeft = '0';
		}
		else
		{
			style = "background: #fff url(data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2215%22%20height%3D%2215%22%3E%3Cpath%20fill%3D%22%23A8ADB4%22%20fill-rule%3D%22evenodd%22%20d%3D%22M1.765%200h11.47C14.21%200%2015%20.79%2015%201.765v11.47C15%2014.21%2014.21%2015%2013.235%2015H1.765C.79%2015%200%2014.21%200%2013.235V1.765C0%20.79.79%200%201.765%200zm0%2013.235h11.47v-.882l-3.058-3.53-1.53%201.765-3.824-4.412-3.058%203.53v3.53zm9.264-7.94a1.324%201.324%200%20100-2.648%201.324%201.324%200%20000%202.647z%22%20opacity%3D%22.761%22/%3E%3C/svg%3E) no-repeat center;";
		}

		return Tag.render`
			<label 	class="ui-ctl ui-ctl-radio-selector"
					onclick="${this.skuSelectHandler}"
					title="${propertyName}"
					data-property-id="${this.getId()}"
					data-property-value="${propertyValue.ID}">
				<input type="radio"
					disabled="${!this.parent.isSelectable()}"
					name="property-${this.getSelectedSkuId()}-${this.getId()}-${uniqueId}"
					class="ui-ctl-element">
				<span class="ui-ctl-inner">
					<span class="ui-ctl-label-img" style="${style}"></span>
					${nameNode}
				</span>
			</label>
		`;
	}

	renderTextSku(propertyValue, uniqueId)
	{
		const propertyName = Type.isStringFilled(propertyValue.NAME) ? Text.encode(propertyValue.NAME) : '-';

		return Tag.render`
			<label 	class="ui-ctl ui-ctl-radio-selector"
					onclick="${this.skuSelectHandler}"
					title="${propertyName}"
					data-property-id="${this.getId()}"
					data-property-value="${propertyValue.ID}">
				<input type="radio"
					disabled="${!this.parent.isSelectable()}"
					name="property-${this.getSelectedSkuId()}-${this.getId()}-${uniqueId}"
					class="ui-ctl-element">
				<span class="ui-ctl-inner">
					<span class="ui-ctl-label-text">${propertyName}</span>
				</span>
			</label>
		`;
	}

	layout()
	{
		if (!this.hasSkuValues())
		{
			return;
		}

		this.skuList = this.renderProperties();
		this.toggleSkuPropertyValues();

		return Tag.render`
			<div class="product-item-detail-info-container">
				<div class="product-item-detail-info-container-title">${Text.encode(this.property.NAME)}</div>
				<div class="product-item-scu-container">
					${this.skuList}
				</div>
			</div>
		`;
	}

	renderProperties()
	{
		const skuList = Tag.render`<div class="product-item-scu-list ui-ctl-spacing-right"></div>`;

		this.property.VALUES.forEach((propertyValue) => {
			let propertyValueId = propertyValue.ID;
			let node;
			let uniqueId = Text.getRandom();

			if (!propertyValueId || this.existingValues.includes(propertyValueId))
			{
				if (this.property.SHOW_MODE === 'PICT')
				{
					node = this.renderPictureSku(propertyValue, uniqueId);
				}
				else
				{
					node = this.renderTextSku(propertyValue, uniqueId);
				}

				this.nodeDescriptions.push({propertyValueId, node});
				skuList.appendChild(node);
			}
		});

		return skuList;
	}

	toggleSkuPropertyValues()
	{
		const selectedSkuProperty = this.parent.getSelectedSkuProperty(this.getId());
		const activeSkuProperties = this.parent.getActiveSkuProperties(this.getId());

		this.nodeDescriptions.forEach((item) => {
			let id = Text.toNumber(item.propertyValueId);
			let input = item.node.querySelector('input[type="radio"]');

			if (selectedSkuProperty === id)
			{
				input.checked = true;
				Dom.addClass(item.node, 'selected');
			}
			else
			{
				input.checked = false;
				Dom.removeClass(item.node, 'selected');
			}

			if (activeSkuProperties.includes(id))
			{
				Dom.style(item.node, {display: null});
			}
			else
			{
				Dom.style(item.node, {display: 'none'});
			}
		});
	}

	handleSkuSelect(event)
	{
		event.stopPropagation();

		const selectedSkuProperty = event.target.closest('[data-property-id]');
		if (!this.parent.isSelectable() || Dom.hasClass(selectedSkuProperty, 'selected'))
		{
			return;
		}

		const propertyId = Text.toNumber(selectedSkuProperty.getAttribute('data-property-id'));
		const propertyValue = Text.toNumber(selectedSkuProperty.getAttribute('data-property-value'));

		this.parent.setSelectedProperty(propertyId, propertyValue);
		this.parent.toggleSkuProperties();

		EventEmitter.emit('SkuProperty::onChange', [this.parent.getSelectedSku(), this.property]);
		if (this.parent)
		{
			this.parent.emit('SkuProperty::onChange', [this.parent.getSelectedSku(), this.property]);
		}
	}
}