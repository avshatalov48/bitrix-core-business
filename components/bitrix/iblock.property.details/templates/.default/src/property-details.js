import { Runtime, ajax, Loc, Type } from 'main.core';
import { MessageBox } from 'ui.dialogs.messagebox';
import { Buttons } from './buttons';
import { Errors } from './errors';
import { Progress } from './progress';
import { Sliders } from './sliders';

export class PropertyDetails
{
	propertyId: Number;
	iblockId: Number;
	container: HTMLElement;
	slidersOptions: Object;
	signedParameters: String;
	detailPageUrlTemplate: String;

	errors: Errors;
	buttons: Buttons;
	progress: Progress;

	constructor(options)
	{
		this.iblockId = options.iblockId;
		this.propertyId = options.propertyId;
		this.slidersOptions = options.sliders;
		this.signedParameters = options.signedParameters;
		this.detailPageUrlTemplate = options.detailPageUrlTemplate || '';

		this.container = document.querySelector(options.containerSelector);

		this.errors = new Errors(this.container);
		this.progress = new Progress(this.container);
		this.buttons = new Buttons(this.container, {
			onSave: this.handlerSaveButtonClick.bind(this),
			onRemove: this.handlerRemoveButtonClick.bind(this),
		});

		this.initEvents();
		this.adjustVisibilityLeftMenu();
		this.stylizationSettingsControls();

		BX.UI.Hint.init(this.container);

		const deferredSliderName = Sliders.getDeferredSlider();
		if (deferredSliderName)
		{
			this.openSlider(deferredSliderName);
		}
	}

	#runAction(action, data): Promise
	{
		return ajax.runComponentAction('bitrix:iblock.property.details', action, {
			mode: 'class',
			signedParameters: this.signedParameters,
			data,
		});
	}

	initEvents(): void
	{
		this.getPropertyTypeInput().addEventListener('change', this.handlePropertyTypeChange.bind(this));
	}

	getTabs(): NodeList
	{
		return this.container.querySelectorAll('.iblock-property-details-tab');
	}

	getAdditionalTab(): HTMLElement
	{
		return Array.prototype.find.call(
			this.getTabs(),
			(node: HTMLElement) => node.dataset.tab === 'additional',
		);
	}

	getPropertyTypeInput(): HTMLInputElement
	{
		return this.container.querySelector('[name="PROPERTY_TYPE"]');
	}

	openTab(tabName): void
	{
		const activeClassName = 'iblock-property-details-tab_current';

		this.getTabs().forEach((tab: HTMLElement) => {
			if (tab.dataset.tab === tabName)
			{
				tab.classList.add(activeClassName);
			}
			else if (tab.classList.contains(activeClassName))
			{
				tab.classList.remove(activeClassName);
			}
		});
	}

	openSlider(sliderName): void
	{
		const sliderOptions = this.slidersOptions[sliderName];
		if (!sliderOptions)
		{
			throw new Error(`Cannot find config for slider '${sliderName}'`);
		}

		if (this.isNewProperty() && sliderOptions.newPropertyConfirmMessage)
		{
			MessageBox.confirm(
				sliderOptions.newPropertyConfirmMessage,
				() => {
					Sliders.setDeferredSlider(sliderName);

					this.handlerSaveButtonClick();

					return true;
				},
				Loc.getMessage('IBLOCK_PROPERTY_DETAILS_POPUP_OPEN_SLIDER_CONFIRM_SAVE_BUTTON'),
			);
		}
		else
		{
			top.BX.SidePanel.Instance.open(sliderOptions.url, sliderOptions);
		}
	}

	handlePropertyTypeChange(e)
	{
		if (this.progress.isProgress)
		{
			return;
		}

		this.progress.start();
		this.errors.hide();

		this
			.#runAction('getSettings', {
				propertyFullType: this.getPropertyTypeInput().value,
			})
			.then((response) => {
				const showedFields = response.data.info?.showedFields;
				if (Type.isArray(showedFields))
				{
					this.adjustVisibilityCommonFields(showedFields);
				}

				let html = '';
				if (response.data.html && response.data.html.length > 0)
				{
					html = response.data.html;
				}

				this.progress.stop();

				Runtime.html(this.getAdditionalTab(), html).then(() => {
					this.adjustVisibilityLeftMenu();
					this.stylizationSettingsControls();
				});
			})
			.catch((response) => {
				this.progress.stop();

				this.errors.show(
					response.errors,
				);
			})
		;
	}

	adjustVisibilityCommonFields(fields: Array): void
	{
		const commonTab = this.container.querySelector('[data-tab="common"]');
		if (!commonTab)
		{
			return;
		}

		commonTab.querySelectorAll('input, select, textarea').forEach((input) => {
			if (!input.name || input.name === 'PROPERTY_TYPE')
			{
				return;
			}

			const inputContainer = input.closest('.iblock-property-details-input');
			if (fields.includes(input.name))
			{
				input.disabled = false;
				if (inputContainer)
				{
					inputContainer.style.display = null;
				}
			}
			else
			{
				input.disabled = true;
				if (inputContainer)
				{
					inputContainer.style.display = 'none';
				}
			}
		});
	}

	adjustVisibilityLeftMenu(): void
	{
		const propertyType = this.container.querySelector('[name="PROPERTY_TYPE"]')?.value;

		const listMenuItem = document.querySelector('#iblock-property-details-sidepanel-menu [data-slider="list-values"]');
		if (propertyType === 'L')
		{
			listMenuItem.style.display = 'flex';
		}
		else
		{
			listMenuItem.style.display = 'none';
		}

		const directoryMenuItem = document.querySelector('#iblock-property-details-sidepanel-menu [data-slider="directory-items"]');
		if (propertyType === 'S:directory')
		{
			directoryMenuItem.style.display = 'flex';
		}
		else
		{
			directoryMenuItem.style.display = 'none';
		}
	}

	stylizationSettingsControls(): void
	{
		const buttonInputTypes = new Set([
			'button',
			'submit',
			'reset',
		]);
		const flagInputTypes = new Set([
			'checkbox',
			'radio',
		]);

		const isOnlyChild = function(control) {
			let childs = control.parentNode.childNodes;

			childs = Array.prototype.filter.call(childs, (item) => {
				if (item instanceof Text)
				{
					return item.nodeValue.trim() !== '';
				}

				return true;
			});

			return childs.length === 1;
		};

		const prepareControl = function(control) {
			// skip `ui.forms` controls
			if (control.classList.contains('ui-ctl-element'))
			{
				return;
			}

			switch (control.nodeName)
			{
				case 'INPUT': {
					const type = control.type || 'text';
					if (buttonInputTypes.has(type))
					{}
					else if (flagInputTypes.has(type))
					{
					// pass
					}
					else if (type === 'hidden')
					{
					// pass
					}
					else
					{
						control.classList.add('ui-ctl-element');
						if (isOnlyChild(control))
						{
							control.classList.add('ui-ctl-w100');
						}
						else
						{
							control.classList.add('ui-ctl-inline');
						}
					}

					break;
				}

				case 'SELECT': {
					control.classList.add('ui-ctl-element');
					if (!isOnlyChild(control))
					{
						control.classList.add('ui-ctl-inline');
					}

					break;
				}

				case 'TEXTAREA': {
					control.classList.add('ui-ctl-element');
					control.classList.add('ui-ctl-textarea');

					break;
				}
			// No default
			}
		};

		const settingsContainer = this.getAdditionalTab().querySelector('.iblock-property-details-settings-table');
		if (settingsContainer)
		{
			settingsContainer
				.querySelectorAll('input, select, textarea')
				.forEach(prepareControl)
			;
		}

		const defaultValueControl = this.getAdditionalTab().querySelector('[name="DEFAULT_VALUE"]');
		if (defaultValueControl)
		{
			defaultValueControl
				.closest('.iblock-property-details-input')
				.querySelectorAll('input, select, textarea')
				.forEach(prepareControl)
			;
		}
	}

	getFields(): FormData
	{
		const result = new FormData();

		let m;
		const regex = /^(.+?)(\[.+)$/;
		const formData = new FormData(
			this.container.querySelector('form'),
		);
		for (const pair of formData.entries())
		{
			let name = pair[0];
			if (m = regex.exec(name))
			{
				name = `fields[${m[1]}]${m[2]}`;
			}
			else
			{
				name = `fields[${name}]`;
			}

			result.append(name, pair[1]);
		}

		return result;
	}

	#getSlider()
	{
		return top.BX.SidePanel.Instance.getTopSlider();
	}

	isNewProperty(): Boolean
	{
		return parseInt(this.propertyId) === 0;
	}

	handlerSaveButtonClick(): Promise
	{
		this.progress.start();
		this.errors.hide();

		const data = this.getFields();
		data.append('propertyId', this.propertyId);
		data.append('iblockId', this.iblockId);
		data.append('sessid', BX.bitrix_sessid());

		return this
			.#runAction('save', data)
			.then((response) => {
				this.progress.stop();

				if (response.errors.length > 0)
				{
					this.errors.show(
						response.errors,
					);

					return false;
				}

				top.BX.Event.EventEmitter.emit('IblockPropertyDetails:saved', [
					response.data,
				]);
				this.#getSlider().close();

				return true;
			})
			.catch((response) => {
				this.progress.stop();

				this.errors.show(
					response.errors,
				);

				return false;
			})
		;
	}

	handlerRemoveButtonClick(): Promise
	{
		this.progress.start();
		this.errors.hide();

		return this
			.#runAction('delete', {
				id: this.propertyId,
			})
			.then((response) => {
				this.progress.stop();

				this.#getSlider().close();

				return true;
			})
			.catch((response) => {
				this.progress.stop();

				this.errors.show(
					response.errors,
				);

				return false;
			})
		;
	}
}
