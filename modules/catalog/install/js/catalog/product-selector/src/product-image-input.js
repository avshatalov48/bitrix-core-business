import {Reflection, Runtime, Tag, Text, Type} from 'main.core';
import './component.css';
import {BaseEvent, EventEmitter} from "main.core.events";
import {ProductSelector} from 'catalog.product-selector';

export class ProductImageInput
{
	constructor(id, options = {})
	{
		this.id = id || Text.getRandom();
		this.selector = options.selector || null;
		if (!(this.selector instanceof ProductSelector))
		{
			throw new Error('Product selector instance not found.');
		}

		this.config = options.config || {};
		this.setView(options.view);

		if (Type.isStringFilled(options.inputHtml))
		{
			this.setInputHtml(options.inputHtml);
		}
		else
		{
			this.restoreDefaultInputHtml();
		}

		this.enableSaving = options.enableSaving;

		this.uploaderFieldMap = {};
	}

	getId()
	{
		return this.id;
	}

	setId(id)
	{
		this.id = id;
	}

	setView(html)
	{
		this.view = Type.isStringFilled(html) ? html : '';
	}

	setInputHtml(html)
	{
		this.inputHtml = Type.isStringFilled(html) ? html : '';
	}

	restoreDefaultInputHtml()
	{
		this.inputHtml = `
			<div class='ui-image-input-container ui-image-input-img--disabled'>
				<div class='adm-fileinput-wrapper '>
					<div class='adm-fileinput-area mode-pict adm-fileinput-drag-area'></div>
				</div>
			</div>
`		;
	}

	isViewMode(): boolean
	{
		return this.selector && this.selector.isViewMode();
	}

	isEnabledLiveSaving(): boolean
	{
		return this.enableSaving;
	}

	layout()
	{
		const imageContainer = Tag.render`<div></div>`;

		Runtime.html(imageContainer, this.isViewMode() ? this.view : this.inputHtml);

		return imageContainer;
	}
}