import {Reflection, Runtime, Tag, Text, Type} from 'main.core';
import './component.css';
import {BaseEvent, EventEmitter} from "main.core.events";
import {ProductSelector} from 'catalog.product-selector';

export class ProductImageInput
{
	onUploaderIsInitedHandler = this.handleOnUploaderIsInited.bind(this);

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
		if (this.isEnabledLiveSaving())
		{
			EventEmitter.subscribe('onUploaderIsInited', this.onUploaderIsInitedHandler);
		}
	}

	handleOnUploaderIsInited(event)
	{
		const [id, uploader] = event.getCompatData();
		if (!this.isViewMode() && Type.isStringFilled(this.id) && this.id === id)
		{
			this.uploaderFieldMap = {};
			EventEmitter.subscribe(uploader, 'onFileIsDeleted', this.onFileDelete.bind(this));
			EventEmitter.subscribe(uploader, 'onFileIsUploaded', this.onFileUpload.bind(this));
			EventEmitter.subscribe(uploader, 'onQueueIsChanged', this.onQueueIsChanged.bind(this));
		}
	}

	unsubscribeEvents()
	{
		if (this.isEnabledLiveSaving())
		{
			EventEmitter.unsubscribe('onUploaderIsInited', this.onUploaderIsInitedHandler);
		}
	}

	unsubscribeImageInputEvents()
	{
		if (Reflection.getClass('BX.UI.ImageInput'))
		{
			const imageInput = BX.UI.ImageInput.getById(this.getId());
			if (imageInput)
			{
				imageInput.unsubscribeEvents();
			}
		}
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

	onFileDelete(event: BaseEvent)
	{
		const [, , , file] = event.getCompatData();
		const fileId = file.fileId;

		if (this.isViewMode() || !this.selector)
		{
			return;
		}

		const deleteResult = this.selector.getModel().removeMorePhotoItem(fileId);
		if (deleteResult)
		{
			this.save();
		}
	}

	onQueueIsChanged(event: BaseEvent)
	{
		const [, type, itemId, uploaderItem] = event.getCompatData();
		const image = uploaderItem.file;

		if (
			type === 'add'
			&& 'input_name' in image
			&& Type.isNil(this.uploaderFieldMap[itemId])
		)
		{
			this.uploaderFieldMap[itemId] = image['input_name'];
		}
	}

	onFileUpload(event: BaseEvent)
	{
		const [itemId, , params] = event.getCompatData();

		if (!Type.isObject(params)
			|| !('file' in params)
			|| !('files' in params.file)
			|| !('default' in params.file.files)
			|| this.isViewMode()
			|| !this.selector
		)
		{
			return;
		}

		const currentUploadedFile = params['file']['files']['default'];
		const photoItem = {
			fileId: itemId,
			data: {
				name: currentUploadedFile.name,
				type: currentUploadedFile.type,
				tmp_name: currentUploadedFile.path,
				size: currentUploadedFile.size,
				error: null
			}
		};
		const fileFieldName = this.uploaderFieldMap[itemId] || itemId;
		this.selector.getModel().addMorePhotoItem(fileFieldName, photoItem);

		this.save(true);
	}

	save(rebuild)
	{
		if (this.selector)
		{
			this.selector.saveFiles(rebuild);
		}
	}
}