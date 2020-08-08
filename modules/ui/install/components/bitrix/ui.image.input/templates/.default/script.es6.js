import {Dom, Event, Reflection, Tag, Type} from 'main.core';
import {type BaseEvent, EventEmitter} from 'main.core.events';

class ImageInput
{
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
			if (uploader && Type.isDomNode(uploader.fileInput))
			{
				const wrapper = uploader.fileInput.closest('.adm-fileinput-wrapper');

				if (Type.isDomNode(wrapper))
				{
					const previews = wrapper.querySelectorAll('.adm-fileinput-item');

					if (previews.length)
					{
						Dom.addClass(wrapper, 'ui-image-input-wrapper');
					}
				}
			}

			requestAnimationFrame(() => {
				this.getLoaderContainer() && (this.getLoaderContainer().style.display = 'none');
				this.getContainer().style.display = '';
			});

			EventEmitter.subscribe(uploader, 'onFileIsCreated', this.onFileIsCreatedHandler.bind(this));
			EventEmitter.subscribe(uploader, 'onFileIsDeleted', this.onFileIsCreatedHandler.bind(this));
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

	onFileIsCreatedHandler(event: BaseEvent)
	{
		const [, , uploader] = event.getCompatData();

		if (uploader && Type.isDomNode(uploader.fileInput))
		{
			const wrapper = uploader.fileInput.closest('.adm-fileinput-wrapper');

			if (Type.isDomNode(wrapper))
			{
				setTimeout(() => {
						this.recalculateWrapper(wrapper);
					}, 100
				);
			}
		}
	}

	isMultipleInput()
	{
		return this.getInputInstance().uploadParams.maxCount !== 1;
	}

	buildShadowElement(wrapper)
	{
		if (!wrapper.querySelector('div.ui-image-item-shadow'))
		{
			const shadowElement = Tag.render`<div class="ui-image-item-shadow"></div>`;
			const bottomMargin = 4;
			const preview = wrapper.querySelector('.adm-fileinput-item-preview');
			const previewWrapper = wrapper.closest('.adm-fileinput-item-wrapper');

			const canvas = wrapper.querySelector('canvas');
			if (canvas)
			{
				shadowElement.style.height = canvas.offsetHeight + 'px';
				shadowElement.style.width = canvas.offsetWidth - bottomMargin + 'px';

				preview.style.height = canvas.offsetHeight + 'px';
				previewWrapper.style.height = canvas.offsetHeight + 'px';
			}

			Dom.prepend(shadowElement, wrapper);
		}
	}

	recalculateWrapper(wrapper)
	{
		const previews = wrapper.querySelectorAll('.adm-fileinput-item');
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