import {Address as AddressWidget, AutocompleteFeature, Factory, State} from "location.widget";
import {Address as AddressEntity, AddressStringConverter, AddressType, ControlMode, Format} from "location.core";
import {Dom, Event, Tag, Loc, Text} from "main.core";
import type {EditEntryProps} from "./editentryprops";
import {EventEmitter} from "main.core.events";

export class EditEntry extends EventEmitter
{
	#widget: AddressWidget;
	#nodes: { name: string, node: Element } = {};
	#wrapper: Element;
	#address: AddressEntity = null;
	#fieldName: string;
	#fieldFormName: string;
	#showMap: boolean = true;
	#enableRemoveButton: boolean = false;
	#isCompactMode: boolean = false;
	#initialAddressId: ?number = null;
	#areDetailsShown: boolean = false;
	#isLoading: boolean = false;
	#isDropdownLoading: boolean = false;
	#isDestroyed: boolean = false;

	static onRemoveInputButtonClickedEvent = 'onRemoveInputButtonClicked';

	constructor(props: EditEntryProps)
	{
		super();
		this.setEventNamespace('Fileman.EditEntry');
		this.#wrapper = props.wrapper;
		this.#fieldName = props.fieldName;
		this.#fieldFormName = props.fieldFormName;
		this.#enableRemoveButton = props.enableRemoveButton;
		this.#initialAddressId = props.initialAddressId;
		this.#showMap = props.showMap;

		if (props.address)
		{
			this.#address = props.address;
		}
		if (props.isCompactMode)
		{
			this.#isCompactMode = props.isCompactMode;
		}
	}

	layout(): Element
	{
		const factory = new Factory();
		this.#widget = factory.createAddressWidget({
			address: this.#address,
			mode: ControlMode.edit,
			popupOptions: {
				offsetLeft: 14,
			},
			popupBindOptions: {
				forceBindPosition: true,
				position: 'right',
			},
			mapBehavior: 'auto',
			useFeatures: {
				fields: true,
				map: this.#showMap,
				autocomplete: true
			}
		});

		this.#nodes.userInput = Tag.render`<input type="text" class="ui-ctl-element" />`;

		this.#nodes.fieldsContainer = Tag.render`<div class="location-fields-control-block"></div>`;
		this.#nodes.detailsToggle = Tag.render`<span class="ui-link ui-link-secondary address-control-mode-switch">${Loc.getMessage('ADDRESS_USERFIELD_DETAILS')}</span>`;
		Event.bind(this.#nodes.detailsToggle, 'click', this.onDetailsToggleClick.bind(this));

		let inputValue = this.getInitialAddressFieldValue();
		this.#nodes.fieldValueInput = Tag.render`<input type="hidden" name="${this.#fieldFormName}" value="${inputValue}" />`;

		this.#nodes.inputIcon = Tag.render`<button type="button" class="ui-ctl-after ui-ctl-icon-clear"></button>`;
		Event.bind(this.#nodes.inputIcon, 'click', this.onInputIconClick.bind(this));

		this.#widget.subscribeOnAddressChangedEvent(this.onAddressChanged.bind(this));
		this.#widget.subscribeOnStateChangedEvent(this.onWidgetStateChangedEvent.bind(this));
		this.#widget.subscribeOnFeatureEvent(this.onFeatureEvent.bind(this));

		this.#nodes.entryWrapper = Tag.render`
			<div class="edit-entry-input-wrapper">
				<div class="fields address field-item edit ui-ctl ui-ctl-after-icon ${this.getUserInputSizeClass()}">
					${this.#nodes.userInput}
					${this.#nodes.fieldsContainer}
					${this.#nodes.inputIcon}
				</div>
			</div>
		`;

		// a workaround for bizproc conditionals; their conditionals popup seems to use the topmost <input>'s value
		const hiddenFormattedInputValue = this.#address ? this.getRawValueForHiddenFormattedInput(this.#address) : '';
		this.#nodes.hiddenFormattedAddressInput = Tag.render`<input type="hidden" name="${this.#fieldName}_formatted" value="${hiddenFormattedInputValue}" />`;

		// a flag used to identify values set manually by the user
		const manualEditFlagNode = Tag.render`<input type="hidden" name="${this.#fieldName}_manual_edit" value="Y">`;

		this.#nodes.layout = Tag.render`
			<div class="edit-entry-layout-wrapper ${this.getLayoutSizeClass()}">
				<div class="address-control-mode-switch-wrapper">
					${this.#nodes.detailsToggle}
				</div>
				${this.#nodes.hiddenFormattedAddressInput}
				${this.#nodes.entryWrapper}
				${manualEditFlagNode}
			</div>
		`;

		if (this.#enableRemoveButton)
		{
			Dom.append(this.getRemoveInputButton(this.#nodes.layout), this.#nodes.entryWrapper);
		}

		Dom.append(this.#nodes.fieldValueInput, this.#nodes.layout);

		this.#widget.render({
			inputNode: this.#nodes.userInput,
			mapBindElement: this.#wrapper,
			controlWrapper: this.#nodes.layout,
			fieldsContainer: this.#nodes.fieldsContainer,
		});

		return this.#nodes.layout;
	}

	getUserInputSizeClass(): string
	{
		return this.#isCompactMode ? 'ui-ctl-wd' : 'ui-ctl-w100';
	}

	getLayoutSizeClass(): string
	{
		return this.#isCompactMode ? 'compact' : '';
	}

	getRemoveInputButton(layout: Element)
	{
		const removeInputButton = Tag.render`
			<span class="uf-address-search-input-remove"></span>
		`;

		Event.bind(removeInputButton, 'click', (event) => {
			this.emit(EditEntry.onRemoveInputButtonClickedEvent);
		});

		return removeInputButton;
	}

	destroy()
	{
		if (!this.#nodes.layout)
		{
			return;
		}

		if (this.#widget)
		{
			this.#widget.destroy();
		}

		if (this.#address && this.#address.id > 0)
		{
			Dom.clean(this.#nodes.layout);
			const input = Tag.render`<input type="hidden" name="${this.#fieldFormName}" value="${this.#address.id}_del" />`;
			Dom.append(input, this.#nodes.layout);
			this.emitFieldChangedEvent();
		}
		else
		{
			Dom.remove(this.#nodes.layout);
		}

		this.#isDestroyed = true;
	}

	isDestroyed(): boolean
	{
		return this.#isDestroyed;
	}

	onAddressChanged(event)
	{
		const initialAddressId = parseInt(this.#initialAddressId);
		/** @type {AddressEntity} */
		const address = event.data.address;
		if (!address)
		{
			return;
		}

		// when we clear the input, the address' id becomes 0, and because of it a new address is created upon
		// saving. we can set the address's id to the old id to edit the old address instead
		if (initialAddressId && parseInt(address.id) !== initialAddressId)
		{
			address.id = initialAddressId;
		}

		this.#nodes.fieldValueInput.value = this.getChangedAddressFieldValue(address);

		this.#nodes.hiddenFormattedAddressInput.value = this.getRawValueForHiddenFormattedInput(address);

		this.emitFieldChangedEvent();
	}

	onWidgetStateChangedEvent(event)
	{
		const state = event.data.state;
		this.#isLoading = (state === State.DATA_LOADING);

		this.refreshInputIcon();
	}

	onFeatureEvent(event)
	{
		if (event.data.feature instanceof AutocompleteFeature)
		{
			this.#isDropdownLoading = (event.data.eventCode === AutocompleteFeature.searchStartedEvent);

			this.refreshInputIcon();
		}
	}

	isInputLoading(): boolean
	{
		return this.#isLoading || this.#isDropdownLoading;
	}

	refreshInputIcon()
	{
		if (!this.#nodes.inputIcon)
		{
			return;
		}

		if (this.isInputLoading())
		{
			Dom.removeClass(this.#nodes.inputIcon, 'ui-ctl-icon-clear');
			Dom.addClass(this.#nodes.inputIcon, 'ui-ctl-icon-loader');
		}
		else
		{
			Dom.removeClass(this.#nodes.inputIcon, 'ui-ctl-icon-loader');
			Dom.addClass(this.#nodes.inputIcon, 'ui-ctl-icon-clear');
		}
	}

	getInitialAddressFieldValue(): string
	{
		let inputValue =  '';

		if (this.#address?.id == 0)
		{
			if (this.#address.location)
			{
				// JSON has probably been passed as the component's value; we need to create a new address
				inputValue = Text.encode(this.#address.toJson());
			}
			else
			{
				// for compatibility with the format used before the switch to location module's addresses
				inputValue = `${this.#address.getFieldValue(AddressType.ADDRESS_LINE_2)}|${this.#address.latitude};${this.#address.longitude}`;
			}
		}
		else if (this.#address?.id > 0)
		{
			inputValue = `${this.getFormattedAddress(this.#address)}|${this.#address.latitude};${this.#address.longitude}|${this.#address.id}`;
		}

		return inputValue;
	}

	getChangedAddressFieldValue(address: AddressEntity): string
	{
		return address.toJson();
	}

	getFormattedAddress(address: AddressEntity): string
	{
		const format = new Format(JSON.parse(BX.message('LOCATION_WIDGET_DEFAULT_FORMAT')));
		return address.toString(format, AddressStringConverter.STRATEGY_TYPE_TEMPLATE_COMMA) ?? '';
	}

	getRawValueForHiddenFormattedInput(address: AddressEntity): string
	{
		return `${this.getFormattedAddress(address)}|${address.latitude};${address.longitude}`;
	}

	onInputIconClick()
	{
		if (this.isInputLoading())
		{
			return;
		}

		this.#nodes.userInput.focus();

		this.#widget.resetView();
		this.#widget.address = null;

		if (this.#address && this.#address.id > 0)
		{
			this.#nodes.fieldValueInput.value = this.#address.id + '_del';
		}
		else
		{
			this.#nodes.fieldValueInput.value = '';
		}

		this.emitFieldChangedEvent();
	}

	onDetailsToggleClick()
	{
		if (!this.#nodes.fieldsContainer || !this.#nodes.detailsToggle)
		{
			return;
		}

		const fieldsContainer = this.#nodes.fieldsContainer;
		const detailsToggle = this.#nodes.detailsToggle;
		if (this.#areDetailsShown && Dom.hasClass(fieldsContainer, 'visible'))
		{
			Dom.removeClass(fieldsContainer, 'visible');
			detailsToggle.innerText = Loc.getMessage('ADDRESS_USERFIELD_DETAILS');
		}
		else
		{
			Dom.addClass(fieldsContainer, 'visible');
			detailsToggle.innerText = Loc.getMessage('ADDRESS_USERFIELD_NO_DETAILS');
		}

		this.#areDetailsShown = !this.#areDetailsShown;
	}

	emitFieldChangedEvent()
	{
		BX.onCustomEvent(window, 'onUIEntityEditorUserFieldExternalChanged', [this.#fieldName]);
		BX.onCustomEvent(window, 'onCrmEntityEditorUserFieldExternalChanged', [this.#fieldName]);
	}
}
