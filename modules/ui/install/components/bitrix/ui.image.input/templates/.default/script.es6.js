import {Dom, Event, Reflection, Tag, Type} from 'main.core';
import {type BaseEvent, EventEmitter} from 'main.core.events';
import {Loader} from 'main.loader';

class ImageInput
{
	container = null;
	loaderContainer = null;
	addButton = null;
	loader = null;
	timeout = null;
	uploading = false;

	constructor(params = {})
	{
		this.instanceId = params.instanceId;
		this.containerId = params.containerId;
		this.loaderContainerId = params.loaderContainerId;
		this.settings = params.settings || {};

		this.addImageHandler = this.addImage.bind(this);
		this.editImageHandler = this.editImage.bind(this);

		EventEmitter.subscribe('onUploaderIsInited', this.onUploaderIsInitedHandler.bind(this));
	}

	onUploaderIsInitedHandler(event: BaseEvent)
	{
		const [id, uploader] = event.getCompatData();

		if (this.instanceId === id)
		{
			if (this.getPreviews().length > 0)
			{
				Dom.addClass(this.getFileWrapper(), 'ui-image-input-wrapper');
			}

			requestAnimationFrame(() => {
				this.getLoaderContainer() && (this.getLoaderContainer().style.display = 'none');
				this.getContainer().style.display = '';
			});

			EventEmitter.subscribe(uploader, 'onFileIsDeleted', this.onFileIsDeletedHandler.bind(this));
			EventEmitter.subscribe(uploader, 'onStart', this.onUploadStartHandler.bind(this));
			EventEmitter.subscribe(uploader, 'onDone', this.onUploadDoneHandler.bind(this));
			EventEmitter.subscribe(uploader, 'onFileCanvasIsLoaded', this.onFileCanvasIsLoadedHandler.bind(this));
		}
	}

	getInputInstance()
	{
		return BX.UI.FileInput.getInstance(this.instanceId);
	}

	getFileInput()
	{
		return this.getInputInstance().agent.fileInput;
	}

	getContainer()
	{
		if (!this.container)
		{
			this.container = document.getElementById(this.containerId);

			if (!Type.isDomNode(this.container))
			{
				throw Error(`Can't find container with id ${this.containerId}`);
			}
		}

		return this.container;
	}

	getFileWrapper()
	{
		if (!this.fileWrapper)
		{
			this.fileWrapper = this.getContainer().querySelector('.adm-fileinput-wrapper');
		}

		return this.fileWrapper;
	}

	getLoaderContainer()
	{
		if (!this.loaderContainer)
		{
			this.loaderContainer = document.getElementById(this.loaderContainerId);
		}

		return this.loaderContainer;
	}

	getAddButton()
	{
		if (!this.addButton)
		{
			this.addButton = this.getContainer().querySelector('[data-role="image-add-button"]');
		}

		return this.addButton;
	}

	editImage(event)
	{
		if (event.target === this.getFileInput())
		{
			// api call .click() to fire file upload dialog
			if (event.detail === 0)
			{
				return;
			}
			// disable default file dialog open
			else
			{
				event.preventDefault();
			}
		}

		const inputInstance = this.getInputInstance();
		const items = inputInstance.agent.getItems().items;

		for (let id in items)
		{
			if (items.hasOwnProperty(id))
			{
				// hack to open editor (for unknown reasons the flag disappears)
				inputInstance.frameFlags.active = true;
				inputInstance.frameFiles(id);
				break;
			}
		}
	}

	addImage(event)
	{
		event.preventDefault();
		event.stopPropagation();
		this.getFileInput().click();
	}

	/**
	 * @returns {Loader}
	 */
	getLoader()
	{
		if (!this.loader)
		{
			this.loader = new Loader({
				target: this.getFileWrapper().querySelector('.adm-fileinput-drag-area')
			});
		}

		return this.loader;
	}
	
	showLoader()
	{
		this.getLoader().setOptions({
			size: Math.min(this.getContainer().offsetHeight, this.getContainer().offsetWidth)
		});
		this.getLoader().show();
	}
	
	hideLoader()
	{
		this.getLoader().hide();
	}

	onFileIsDeletedHandler()
	{
		this.timeout = clearTimeout(this.timeout);

		this.timeout = setTimeout(() => {
			this.hideLoader();
			this.recalculateWrapper();
		}, 100);
	}

	onUploadStartHandler(event: BaseEvent)
	{
		const [stream] = event.getCompatData();
		if (stream)
		{
			this.uploading = true;
		}

		clearTimeout(this.timeout);

		this.timeout = setTimeout(() => {
			this.showLoader();
			this.recalculateWrapper();
		}, 100);
	}

	onUploadDoneHandler(event: BaseEvent)
	{
		const [stream] = event.getCompatData();
		if (stream)
		{
			this.uploading = false;
			this.timeout = clearTimeout(this.timeout);

			requestAnimationFrame(() => {
				this.hideLoader();
				this.recalculateWrapper();
			});
		}
	}

	onFileCanvasIsLoadedHandler()
	{
		if (this.timeout && !this.uploading)
		{
			this.uploading = false;
			this.timeout = clearTimeout(this.timeout);

			requestAnimationFrame(() => {
				this.hideLoader();
				this.recalculateWrapper();
			});
		}
	}

	isMultipleInput()
	{
		return this.getInputInstance().uploadParams.maxCount !== 1;
	}

	buildShadowElement(wrapper)
	{
		let shadowElement = wrapper.querySelector('div.ui-image-item-shadow');
		if (!shadowElement)
		{
			shadowElement = Tag.render`<div class="ui-image-item-shadow"></div>`;
			Dom.prepend(shadowElement, wrapper);
		}

		const canvas = wrapper.querySelector('canvas');
		if (canvas)
		{
			const bottomMargin = 4;

			shadowElement.style.height = canvas.offsetHeight + 'px';
			shadowElement.style.width = canvas.offsetWidth - bottomMargin + 'px';

			wrapper.querySelector('.adm-fileinput-item-preview').style.height = canvas.offsetHeight + 'px';
			wrapper.closest('.adm-fileinput-item-wrapper').style.height = canvas.offsetHeight + 'px';
		}
	}

	getPreviews()
	{
		return this.getFileWrapper().querySelectorAll('.adm-fileinput-item');
	}

	recalculateWrapper()
	{
		const wrapper = this.getFileWrapper();
		const previews = this.getPreviews();
		const length = Math.min(previews.length, 3);

		if (length)
		{
			this.buildShadowElement(previews[0]);

			Dom.addClass(wrapper, 'ui-image-input-wrapper');
			this.getFileInput().style.display = 'none';

			Event.unbind(wrapper, 'click', this.editImageHandler);
			Event.bind(wrapper, 'click', this.editImageHandler);

			if (this.isMultipleInput())
			{
				this.getAddButton().style.display = '';

				Event.unbind(this.getAddButton(), 'click', this.addImageHandler);
				Event.bind(this.getAddButton(), 'click', this.addImageHandler);
			}
		}
		else
		{
			Dom.removeClass(wrapper, 'ui-image-input-wrapper');
			this.getFileInput().style.display = '';

			Event.unbind(wrapper, 'click', this.editImageHandler);

			if (this.isMultipleInput())
			{
				this.getAddButton().style.display = 'none';

				Event.unbind(this.getAddButton(), 'click', this.addImageHandler);
			}
		}

		switch (length)
		{
			case 3:
				Dom.addClass(wrapper, 'ui-image-input-wrapper-multiple');
				Dom.removeClass(wrapper, 'ui-image-input-wrapper-double');
				break;

			case 2:
				Dom.addClass(wrapper, 'ui-image-input-wrapper-double');
				Dom.removeClass(wrapper, 'ui-image-input-wrapper-multiple');
				break;

			default:
				Dom.removeClass(wrapper, 'ui-image-input-wrapper-double');
				Dom.removeClass(wrapper, 'ui-image-input-wrapper-multiple');
				break;
		}
	}
}

Reflection.namespace('BX.UI').ImageInput = ImageInput;