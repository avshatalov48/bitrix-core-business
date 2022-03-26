import {Runtime, Tag, Text, Type} from 'main.core';
import './component.css';
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

		if (!Type.isStringFilled(this.selector.getModel()?.getImageCollection().getEditInput()))
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

	setView(html): void
	{
		this.selector.getModel()?.getImageCollection().setPreview(html);
	}

	setInputHtml(html): void
	{
		this.selector.getModel()?.getImageCollection().setEditInput(html);
	}

	restoreDefaultInputHtml(): void
	{
		const defaultInput = `
			<div class='ui-image-input-container ui-image-input-img--disabled'>
				<div class='adm-fileinput-wrapper '>
					<div class='adm-fileinput-area mode-pict adm-fileinput-drag-area'></div>
				</div>
			</div>
`		;

		this.selector.getModel()?.getImageCollection().setEditInput(defaultInput);
	}

	isViewMode(): boolean
	{
		return this.selector && this.selector.isViewMode();
	}

	isEnabledLiveSaving(): boolean
	{
		return this.enableSaving;
	}

	layout(): HTMLElement
	{
		const imageContainer = Tag.render`<div></div>`;
		const html =
			this.isViewMode()
				? this.selector.getModel()?.getImageCollection()?.getPreview()
				: this.selector.getModel()?.getImageCollection()?.getEditInput()
		;

		Runtime.html(imageContainer, html);

		return imageContainer;
	}
}