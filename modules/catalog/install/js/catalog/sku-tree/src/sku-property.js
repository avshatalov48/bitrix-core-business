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
		this.hideUnselected = options.hideUnselected;
	}

	getId()
	{
		return this.property.ID;
	}

	getSelectedSkuId()
	{
		return this.parent.getSelectedSkuId();
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

		let iconNode = '';
		if (propertyValue.PICT && propertyValue.PICT.SRC)
		{
			const style = "background-image: url('" + propertyValue.PICT.SRC + "');";
			iconNode = Tag.render`<span class="ui-ctl-label-img" style="${style}"></span>`;
		}
		else if (nameNode)
		{
			nameNode.style.paddingLeft = '0';
		}
		else
		{
			nameNode = Tag.render`<span class="ui-ctl-label-text">-</span>`;
		}

		const titleItem =
			this.parent.isShortView && Type.isStringFilled(this.property.NAME)
				? Text.encode(this.property.NAME)
				: propertyName
		;

		return Tag.render`
			<label 	class="ui-ctl ui-ctl-radio-selector"
					onclick="${this.skuSelectHandler}"
					title="${titleItem}"
					data-property-id="${this.getId()}"
					data-property-value="${propertyValue.ID}">
				<input type="radio"
					disabled="${!this.parent.isSelectable()}"
					name="property-${this.getSelectedSkuId()}-${this.getId()}-${uniqueId}"
					class="ui-ctl-element">
				<span class="ui-ctl-inner">
					${iconNode}
					${nameNode}
				</span>
			</label>
		`;
	}

	renderTextSku(propertyValue, uniqueId)
	{
		const propertyName = Type.isStringFilled(propertyValue.NAME) ? Text.encode(propertyValue.NAME) : '-';
		const titleItem =
			this.parent.isShortView && Type.isStringFilled(this.property.NAME)
				? Text.encode(this.property.NAME)
				: propertyName
		;

		return Tag.render`
			<label 	class="ui-ctl ui-ctl-radio-selector"
					onclick="${this.skuSelectHandler}"
					title="${titleItem}"
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

		const title = !this.parent.isShortView
			? Tag.render`<div class="product-item-detail-info-container-title">${Text.encode(this.property.NAME)}</div>`
			: ''
		;

		return Tag.render`
			<div class="product-item-detail-info-container">
				${title}
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
					Dom.addClass(skuList, 'product-item-scu-list--pick-color');
					node = this.renderPictureSku(propertyValue, uniqueId);
				}
				else
				{
					Dom.addClass(skuList, 'product-item-scu-list--pick-size');
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

			if (
				(this.hideUnselected && selectedSkuProperty !== id)
				|| !activeSkuProperties.includes(item.propertyValueId)
			)
			{
				Dom.style(item.node, {display: 'none'});
			}
			else
			{
				Dom.style(item.node, {display: null});
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

		this.parent.getSelectedSku().then((selectedSkuData) => {
			EventEmitter.emit('SkuProperty::onChange', [selectedSkuData, this.property]);
			if (this.parent)
			{
				this.parent.emit('SkuProperty::onChange', [selectedSkuData, this.property]);
			}
		});

		this.parent.toggleSkuProperties();
	}
}
