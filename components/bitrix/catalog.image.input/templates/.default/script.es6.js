import {ajax, Reflection, Runtime, Text, Type} from 'main.core';
import {type BaseEvent, EventEmitter} from 'main.core.events';

class ImageInput
{
	onUploaderIsInitedHandler = this.handleOnUploaderIsInited.bind(this);

	values = new Map();
	newValues = new Map();

	static imageInputInstances = new Map();

	static PROCESS_STATUS = 'PROCESS';
	static WAIT_STATUS = 'WAIT';

	static getById(id: string): ?ImageInput
	{
		return ImageInput.imageInputInstances.get(id) || null;
	}

	constructor(id, options = {})
	{
		this.id = id;
		this.wrapper = BX(id);
		this.productId = options.productId;
		this.skuId = options.skuId;
		this.iblockId = options.iblockId;
		this.saveable = options.saveable;
		this.inputId = options.inputId;
		this.ajaxStatus = ImageInput.WAIT_STATUS;
		if (options.hideAddButton === true)
		{
			const addButton = this.wrapper.querySelector('[data-role="image-add-button"]');
			if (Type.isDomNode(addButton))
			{
				addButton.style.display = 'none';
			}
		}

		if (Type.isObject(options.values))
		{
			for (const key in options.values)
			{
				if (!options.values.hasOwnProperty(key))
				{
					continue;
				}

				this.values.set(key, options.values[key]);
			}
		}

		if (this.isSaveable())
		{
			EventEmitter.subscribe('onUploaderIsInited', this.onUploaderIsInitedHandler);
		}

		ImageInput.imageInputInstances.set(this.id, this);
	}

	isSaveable()
	{
		return (this.saveable === true);
	}

	handleOnUploaderIsInited(event)
	{
		const [id, uploader] = event.getCompatData();
		if (Type.isStringFilled(this.inputId) && this.inputId === id)
		{
			this.uploaderFieldMap = new Map();
			EventEmitter.subscribe(uploader, 'onFileIsDeleted', this.onFileDelete.bind(this));
			EventEmitter.subscribe(uploader, 'onFileIsUploaded', this.onFileUpload.bind(this));
			EventEmitter.subscribe(uploader, 'onDone', this.onDone.bind(this));
			EventEmitter.subscribe(uploader, 'onQueueIsChanged', this.onQueueIsChanged.bind(this));
		}
	}

	unsubscribeEvents()
	{
		if (this.isSaveable())
		{
			EventEmitter.unsubscribe('onUploaderIsInited', this.onUploaderIsInitedHandler);
		}
	}

	unsubscribeImageInputEvents()
	{
		if (Reflection.getClass('BX.UI.ImageInput'))
		{
			const imageInput = BX.UI.ImageInput.getById(this.inputId);
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

	onFileDelete(event: BaseEvent)
	{
		const [, , , file] = event.getCompatData();
		const inputName = file.input_name;

		if (Type.isNil(inputName))
		{
			return null;
		}

		this.values.delete(inputName);
		if (this.isSaveable())
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
			&& Type.isNil(this.uploaderFieldMap.get(itemId))
		)
		{
			this.uploaderFieldMap.set(itemId, image['input_name']);
		}
	}

	onDone()
	{
		if (this.newValues.size > 0 && this.isSaveable())
		{
			this.save();
		}

		this.newValues.clear();
	}

	onFileUpload(event: BaseEvent)
	{
		const [itemId, , params] = event.getCompatData();

		if (
			!this.isSaveable()
			|| !Type.isObject(params)
			|| !('file' in params)
			|| !('files' in params.file)
			|| !('default' in params.file.files)
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
		const fileFieldName = this.uploaderFieldMap.get(itemId) || itemId;
		this.values.set(fileFieldName, photoItem);
		this.newValues.set(fileFieldName, photoItem);
	}

	save()
	{
		if (this.submitFileTimeOut)
		{
			clearTimeout(this.submitFileTimeOut);
		}

		const requestId = Text.getRandom(20);
		this.refreshImageSelectorId = requestId;
		this.submitFileTimeOut = setTimeout(() => {
			const values = {};
			this.values.forEach((file, id) => {
				values[id] = file;
			});

			ajax.runAction(
				'catalog.productSelector.saveMorePhoto',
				{
					json: {
						productId: this.productId,
						variationId: this.skuId,
						iblockId: this.iblockId,
						imageValues: values,
					}
				}
			).then((response) => {
				if (!this.refreshImageSelectorId === requestId)
				{
					return;
				}

				this.values.clear();
				if (Type.isObject(response.data?.values))
				{
					for (const key in response.data.values)
					{
						if (!response.data.values.hasOwnProperty(key))
						{
							continue;
						}

						this.values.set(key, response.data.values[key]);
					}
				}

				Runtime.html(this.wrapper, response.data.input);
				EventEmitter.emit( 'Catalog.ImageInput::save', [
					this.id,
					this.inputId,
					response,
				]);
			});
		}, 500);
	}
}

Reflection.namespace('BX.Catalog').ImageInput = ImageInput;