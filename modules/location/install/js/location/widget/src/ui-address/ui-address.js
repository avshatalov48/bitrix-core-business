import {Type, Tag, Dom} from 'main.core';
import {Address, ControlMode, ErrorPublisher} from 'location.core';
import {BaseEvent} from 'main.core.events';
import './css/ui-address.css';
import {State as WidgetState} from 'location.widget';
import Factory from '../factory';
import Switch from './switch';
import Icon from './icon';

/**
 * Address field widget for the ui.entity-editor
 */
export default class UIAddress extends BX.UI.EntityEditorField
{
	constructor(props)
	{
		super(props);

		this._input = null;
		this._inputIcon = null;
		this._hiddenInput = null;
		this._innerWrapper = null;
		this._addressWidget = null;
		this._addressFieldsContainer = null;
	}

	static create(id, settings)
	{
		const self = new UIAddress();
		self.initialize(id, settings);
		return self;
	}

	initialize(id, settings)
	{
		super.initialize(id, settings);

		const value = this.getValue();
		let address = null;

		if(Type.isStringFilled(value))
		{
			try
			{
				address = new Address(JSON.parse(value));
			}
			catch (e)
			{
				BX.debug('Cant parse address value');
				return;
			}
		}


		const widgetFactory = new Factory();

		this._addressWidget = widgetFactory.createAddressWidget({
			address: address,
			mode: this._mode === BX.UI.EntityEditorMode.edit ? ControlMode.edit : ControlMode.view,
			popupBindOptions: {
				position: 'right'
			}
		});

		this._addressWidget.subscribeOnStateChangedEvent(this.#onAddressWidgetChangedState.bind(this));
		this._addressWidget.subscribeOnAddressChangedEvent(this.#onAddressChanged.bind(this));

		this._fieldsSwitch = new Switch({
			state: Switch.STATE_OFF,
			titles: [
				BX.message('LOCATION_WIDGET_AUI_MORE'),
				BX.message('LOCATION_WIDGET_AUI_BRIEFLY')
			]
		});
		this._fieldsSwitch.subscribeOnToggleEventSubscribe(this.#onFieldsSwitchToggle.bind(this));
	}

	#onIconClick()
	{
		if(this._input.value !== '')
		{
			this._input.value = '';
			this._addressWidget.address = null;
			this._inputIcon.type = Icon.TYPE_SEARCH;
		}

		if(this.hasError())
		{
			this.clearError();
		}
	}

	#onFieldsSwitchToggle(event)
	{
		const data = event.getData();
		const state = data.state;

		if(state === Switch.STATE_OFF)
		{
			this.#hideFields();
		}
		else
		{
			this.#showFields();
		}

		this._addressWidget.resetView();
	}

	focus()
	{
		if(!this._input)
		{
			return;
		}

		BX.focus(this._input);
		BX.UI.EditorTextHelper.getCurrent().setPositionAtEnd(this._input);
	}

	#hideFields()
	{
		if(this._addressFieldsContainer)
		{
			this._addressFieldsContainer.classList.remove('visible');
		}
	}

	#showFields()
	{
		if(this._addressFieldsContainer)
		{
			this._addressFieldsContainer.classList.add('visible');
		}
	}

	#onAddressWidgetChangedState(event)
	{
		const data = event.getData();
		const state = data.state;
		let iconType;

		if(data.state === WidgetState.DATA_LOADING)
		{
			iconType = Icon.TYPE_LOADER;
		}
		else
		{
			if(data.state === WidgetState.DATA_INPUTTING)
			{
				this.markAsChanged();
			}

			iconType = UIAddress.#chooseInputIconTypeByAddress(this.#getAddress());
		}


		this._inputIcon.type = iconType;
	}

	getModeSwitchType(mode)
	{
		let result = BX.UI.EntityEditorModeSwitchType.common;

		if(mode === BX.UI.EntityEditorMode.edit)
		{
			// eslint-disable-next-line no-bitwise
			result |= BX.UI.EntityEditorModeSwitchType.button | BX.UI.EntityEditorModeSwitchType.content;
		}

		return result;
	}

	doSetMode(mode)
	{
		this._addressWidget.mode = mode === BX.UI.EntityEditorMode.edit ? ControlMode.edit : ControlMode.view;
		this._fieldsSwitch.state = Switch.STATE_OFF;
	}

	getContentWrapper()
	{
		return this._innerWrapper;
	}

	#onAddressChanged(event: BaseEvent): void
	{
		const data = event.getData();
		const address = data.address;

		if(this._hiddenInput)
		{
			this._hiddenInput.value = address ? address.toJson() : '';
			this.markAsChanged();
		}

		if(this._inputIcon)
		{
			this._inputIcon.type = UIAddress.#chooseInputIconTypeByAddress(address);
		}
	}

	save()
	{
		if(!this.isEditable())
		{
			return;
		}

		const address = this.#getAddress();

		this._model.setField(
			this.getName(),
			address ? address.toJson() : ''
		);

		this._addressWidget.resetView();
	}

	showError(error, anchor)
	{
		super.showError.apply(this, [error, anchor]);

		if(this._input)
		{
			BX.addClass(this._inputContainer, 'ui-ctl-danger');
		}
	}

	clearError()
	{
		super.clearError.apply(this);

		if(this._input)
		{
			BX.removeClass(this._inputContainer, 'ui-ctl-danger');
		}
	}

	doClearLayout(options)
	{
		this._input = null;
		this._innerWrapper = null;
		this._inputContainer = null;
		this._addressFieldsContainer = null;
		this._inputIcon = null;
		this._hiddenInput = null;
		Dom.clean(this._innerWrapper);
	}

	validate(result)
	{
		if(!(this._mode === BX.UI.EntityEditorMode.edit && this._input))
		{
			throw Error('BX.Location.UIAddress. Invalid validation context');
		}

		this.clearError();

		if(this.hasValidators())
		{
			return this.executeValidators(result);
		}

		const isValid = !this.isRequired() || BX.util.trim(this._input.value) !== '';

		if(!isValid)
		{
			result.addError(BX.UI.EntityValidationError.create({field: this}));
			this.showRequiredFieldError(this._input);
		}
		return isValid;
	}

	getRuntimeValue()
	{
		return (this._mode === BX.UI.EntityEditorMode.edit
			? this.#getAddress() : null
		);
	}

	static #chooseInputIconTypeByAddress(address: ?Address): string
	{
		return address ? Icon.TYPE_CLEAR : Icon.TYPE_SEARCH;
	};

	#convertAddressToString(address: ?Address): string
	{
		if(!address)
		{
			return '';
		}

		return address.toString(this._addressWidget.addressFormat);
	}

	#getAddress(): Address
	{
		return this._addressWidget.address;
	}

	layout(options): void
	{
		if(this._hasLayout)
		{
			return;
		}

		this.ensureWrapperCreated({classNames: ['ui-entity-card-content-block-field-phone']});
		this.adjustWrapper();

		const title = this.getTitle();

		if(this.isDragEnabled())
		{
			this._wrapper.appendChild(this.createDragButton());
		}

		const addressWidgetParams = {};

		if(this._mode === BX.UI.EntityEditorMode.edit)
		{
			this._wrapper.appendChild(this.createTitleNode(title));
			this._input = Tag.render`<input class="ui-ctl-element ui-ctl-textbox" value="" type="text" autocomplete="off" name="${`${this.getName()}_STRING`}">`;
			this._hiddenInput = Tag.render`<input value='${this.getValue()}' type="hidden" name="${this.getName()}">`;
			this._inputIcon = new Icon();
			this._inputIcon.subscribeOnClickEvent(this.#onIconClick.bind(this));
			const inputIconNode = this._inputIcon.render({
				type: UIAddress.#chooseInputIconTypeByAddress(this.#getAddress())
			});

			this._inputContainer = Tag.render`<div class="ui-ctl ui-ctl-w100 ui-ctl-after-icon">${inputIconNode}${this._input}${this._hiddenInput}</div>`;
			this._titleWrapper.appendChild(Tag.render`${this._fieldsSwitch.render(this._mode)}`);

			this._innerWrapper = Tag.render`						    
				<div class="location-search-control-block">					
					${this._inputContainer}
				</div>`;

			addressWidgetParams.inputNode = this._input;
			addressWidgetParams.mapBindElement = inputIconNode;

			this._addressFieldsContainer = Tag.render`<div class="location-fields-control-block"></div>`;

			if(this._fieldsSwitch.state === Switch.STATE_ON)
			{
				this._addressFieldsContainer.classList.add('visible');
			}

			addressWidgetParams.fieldsContainer = this._addressFieldsContainer;
			this._innerWrapper.appendChild(this._addressFieldsContainer);
		}
		else// if(this._mode === BX.UI.EntityEditorMode.view)
		{
			this._wrapper.appendChild(this.createTitleNode(title));
			let addressStringNode;

			if(this.hasContentToDisplay())
			{
				const addressString = this.#convertAddressToString(this.#getAddress());
				addressStringNode = Tag.render`<span class="ui-link ui-link-dark ui-link-dotted">${addressString}</span>`;

				this._innerWrapper = Tag.render`
					<div class="location-search-control-block">
						<div class="ui-entity-editor-content-block-text">
							${addressStringNode}														
						</div>
					</div>`;

				addressWidgetParams.mapBindElement = addressStringNode;
			}
			else
			{
				this._innerWrapper = Tag.render`<div class="location-search-control-block">
					${BX.message('UI_ENTITY_EDITOR_FIELD_EMPTY')}
				</div>`;

				addressWidgetParams.mapBindElement = this._innerWrapper;
			}
		}

		addressWidgetParams.controlWrapper = this._innerWrapper;
		this._addressWidget.render(addressWidgetParams);
		this._wrapper.appendChild(this._innerWrapper);

		this._addressWidget.subscribeOnErrorEvent(this.errorListener.bind(this));

		if(this.isContextMenuEnabled())
		{
			this._wrapper.appendChild(this.createContextMenuButton());
		}

		if(this.isDragEnabled())
		{
			this.initializeDragDropAbilities();
		}

		this.registerLayout(options);
		this._hasLayout = true;
	}

	errorListener(event: BaseEvent)
	{
		const data = event.getData();
		const errors = data.errors;

		if(this._inputIcon)
		{
			this._inputIcon.type = Icon.TYPE_CLEAR;
		}

		if(!Type.isArray(errors))
		{
			return;
		}

		// todo: this.showError supports only one error
		errors.forEach((error) => {
			let message;

			if(error.message)
			{
				message = error.message;
			}
			else
			{
				message = BX.message('LOCATION_WIDGET_AUI_UNKNOWN_ERROR');
			}

			if(error.code)
			{
				message += ` [${error.code}]`;
			}

			this.showError(message);
		});
	}

	processModelChange(params)
	{
		if(BX.prop.get(params, 'originator', null) === this)
		{
			return;
		}

		if(!BX.prop.getBoolean(params, 'forAll', false)
			&& BX.prop.getString(params, 'name', '') !== this.getName()
		)
		{
			return;
		}

		this.refreshLayout();
	}

	static registerField(): void
	{
		if(typeof BX.UI.EntityEditorControlFactory !== 'undefined')
		{
			BX.UI.EntityEditorControlFactory.registerFactoryMethod('address', UIAddress.registerFieldMethod);
		}
		else
		{
			BX.addCustomEvent('BX.UI.EntityEditorControlFactory:onInitialize', (params, eventArgs) =>
			{
				eventArgs.methods.address = UIAddress.registerFieldMethod;
			});
		}
	}

	static registerFieldMethod(type, controlId, settings): ?UIAddress
	{
		let result = null;

		if(type === 'address')
		{
			result = UIAddress.create(controlId, settings);
		}

		return result;
	}
}
