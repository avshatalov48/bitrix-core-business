import {Event, Loc, Tag} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import {
	LocationRepository,
	AutocompleteServiceBase,
	Format,
	Address,
	Location,
	ErrorPublisher,
	AddressType,
	Storage,
	Point
} from 'location.core';
import type {AutocompleteServiceParams} from 'location.core';
import Prompt from './prompt';
import State from '../state';
import AddressString from './addressstring';

/**
 * @mixes EventEmitter
 */
export default class Autocomplete extends EventEmitter
{
	static #onAddressChangedEvent = 'onAddressChanged';
	static #onStateChangedEvent = 'onStateChanged';
	static #onSearchStartedEvent = 'onSearchStarted';
	static #onSearchCompletedEvent = 'onSearchCompleted';
	static #onShowOnMapClickedEvent = 'onShowOnMapClicked';

	/** {Address} */
	#address;
	/** {AddressString|null} */
	#addressString = null;
	/** {String} */
	#languageId;
	/** {Format} */
	#addressFormat;
	/** {String} */
	#sourceCode;
	/** {LocationRepository} */
	#locationRepository;
	/** {Point} */
	#userLocationPoint;
	/** {Function} */
	#presetLocationsProvider;
	/** {Prompt} */
	#prompt;
	/** {AutocompleteServiceBase} */
	#autocompleteService;
	/** {number} */
	#timerId = null;
	/** {Element} */
	#inputNode;

	#searchPhrase = {
		requested: '',
		current: '',
		dropped: ''
	};

	#state;
	#wasCleared = false;
	#isDestroyed = false;

	#isAutocompleteRequestStarted = false;
	#isNextAutocompleteRequestWaiting = false;

	#onLocationSelectTimerId = null;

	constructor(props)
	{
		super(props);

		this.setEventNamespace('BX.Location.Widget.Autocomplete');

		if (!(props.addressFormat instanceof Format))
		{
			throw new Error('props.addressFormat must be type of Format');
		}

		this.#addressFormat = props.addressFormat;

		if (!(props.autocompleteService instanceof AutocompleteServiceBase))
		{
			throw new Error('props.autocompleteService must be type of AutocompleteServiceBase');
		}

		this.#autocompleteService = props.autocompleteService;

		if (!props.languageId)
		{
			throw new Error('props.languageId must be defined');
		}

		this.#languageId = props.languageId;
		this.#sourceCode = props.sourceCode;
		this.#address = props.address;
		this.#presetLocationsProvider = props.presetLocationsProvider;
		this.#locationRepository = props.locationRepository || new LocationRepository();
		this.#userLocationPoint = props.userLocationPoint;
		this.#setState(State.INITIAL);
	}

	render(props: {}): void
	{
		this.#inputNode = props.inputNode;
		this.#address = props.address;
		this.#addressString = new AddressString(this.#inputNode, this.#addressFormat, this.#address);
		this.#inputNode.addEventListener('keydown', this.#onInputKeyDown.bind(this));
		this.#inputNode.addEventListener('keyup', this.#onInputKeyUp.bind(this));
		this.#inputNode.addEventListener('focus', this.#onInputFocus.bind(this));
		this.#inputNode.addEventListener('focusout', this.#onInputFocusOut.bind(this));
		this.#inputNode.addEventListener('click', this.#onInputClick.bind(this));
		this.#inputNode.addEventListener('paste', this.#onInputPaste.bind(this));

		this.#prompt = new Prompt({
			inputNode: props.inputNode,
			menuNode: props.menuNode,
		});

		this.#prompt.subscribe(Prompt.onItemSelectedEvent, this.#onPromptItemSelected.bind(this));
		document.addEventListener('click', this.#onDocumentClick.bind(this));
	}

	// eslint-disable-next-line no-unused-vars
	#onInputClick(e: MouseEvent)
	{
		const value = this.#addressString.value;

		if (value.length === 0)
		{
			this.#showPresetLocations();
		}
	}

	#showPresetLocations()
	{
		const presetLocationList = this.#presetLocationsProvider();

		this.#prompt.setMenuItems(presetLocationList, '');

		let leftBottomMenuMessage;

		if (presetLocationList.length > 0)
		{
			leftBottomMenuMessage = Loc.getMessage('LOCATION_WIDGET_PICK_ADDRESS_OR_SHOW_ON_MAP');
		}
		else
		{
			leftBottomMenuMessage = Loc.getMessage('LOCATION_WIDGET_START_PRINTING_OR_SHOW_ON_MAP');
		}

		this.#showMenu(leftBottomMenuMessage, null);
	}

	#createRightBottomMenuNode(location: ?Location): Element
	{
		const element = Tag.render`
				<span class="location-map-popup-item--show-on-map">
					${Loc.getMessage('LOCATION_WIDGET_SHOW_ON_MAP')}
				</span>
		`;

		element.addEventListener('click', this.#getShowOnMapHandler(location));

		return element;
	}

	#createLeftBottomMenuNode(text: string): Element
	{
		return Tag.render`
				<span>
					<span class="menu-popup-item-icon"></span>
					<span class="menu-popup-item-text">${text}</span>
				</span>
		`;
	}

	#showMenu(leftBottomText: string, location: ?Location): void
	{
		/* Menu destroys popup after the closing, so we need to refresh it every time, we show it */
		this.#prompt.getMenu().setBottomRightItemNode(
			this.#createRightBottomMenuNode(location)
		);
		this.#prompt.getMenu().setBottomLeftItemNode(
			this.#createLeftBottomMenuNode(leftBottomText)
		);
		this.#prompt.getMenu().show();
	}

	#onInputFocusOut(e: Event)
	{
		if (this.#isDestroyed)
		{
			return;
		}

		if (
			this.#state === State.DATA_INPUTTING
			&& !(
				e.relatedTarget
				&& (e.relatedTarget.getAttribute('data-role') === 'location-widget-menu-item')
			)
		) {
			this.#setState(State.DATA_SUPPOSED);

			let isChanged = false;
			if (this.#addressString) {
				if (
					!this.#address
					|| !this.#addressString.hasPureAddressString()
				)
				{
					this.#address = this.#convertStringToAddress(
						this.#addressString.value
					);
					isChanged = true;
				}
				// this.#addressString === null until autocompete'll be rendered
				else if (this.#addressString.customTail !== '')
				{
					const currentValue = this.#address.getFieldValue(this.#addressFormat.fieldForUnRecognized);
					const newValue = currentValue
						? currentValue + this.#addressString.customTail
						: this.#addressString.customTail
					;
					this.#address.setFieldValue(
						this.#addressFormat.fieldForUnRecognized,
						newValue
					);
					isChanged = true;
				}
			}

			if (isChanged)
			{
				this.#addressString.setValueFromAddress(this.#address);
				this.#onAddressChangedEventEmit([], {storeAsLastAddress: false});
			}
		}

		// Let's prevent other onInputFocusOut handlers.
		e.stopImmediatePropagation();
	}

	#onInputFocus()
	{
		if (this.#isDestroyed)
		{
			return;
		}

		if (!this.#address)
		{
			const lastAddress = Storage.getInstance().lastAddress;

			if (
				lastAddress
				&& lastAddress.fieldCollection.isFieldExists(AddressType.LOCALITY)
				&& !this.#wasCleared
			)
			{
				const fieldCollection = {};

				fieldCollection[AddressType.LOCALITY] = lastAddress.fieldCollection.getFieldValue(
					AddressType.LOCALITY
				);
				if (lastAddress.fieldCollection.isFieldExists(AddressType.COUNTRY))
				{
					fieldCollection[AddressType.COUNTRY] = lastAddress.fieldCollection.getFieldValue(AddressType.COUNTRY);
				}
				if (lastAddress.fieldCollection.isFieldExists(AddressType.ADM_LEVEL_1))
				{
					fieldCollection[AddressType.ADM_LEVEL_1] = lastAddress.fieldCollection.getFieldValue(AddressType.ADM_LEVEL_1);
				}

				if (['RU', 'RU_2'].includes(this.#addressFormat.code))
				{
					fieldCollection[AddressType.ADDRESS_LINE_2] = ', ';
				}

				this.#address = new Address({
					languageId: lastAddress.languageId,
					fieldCollection: fieldCollection,
				});
				this.#addressString.setValueFromAddress(this.#address);
				this.#setState(State.DATA_SUPPOSED);
				this.#onAddressChangedEventEmit(
					[],
					{storeAsLastAddress: false}
				);

				setTimeout(() => {
					BX.setCaretPosition(this.#inputNode, this.#inputNode.value.length);
				}, 0);
			}
		}
		else
		{
			if (
				this.#address
				&& (!this.#address.location || !this.#address.location.hasExternalRelation())
				&& this.#addressString.value.length > 0
			)
			{
				this.showPrompt(this.#addressString.value);
			}
		}
	}

	#makeAutocompleteServiceParams(): AutocompleteServiceParams
	{
		const result: AutocompleteServiceParams = {};

		//result.biasPoint = this.#userLocationPoint;
		if (this.#address && this.#address.latitude && this.#address.longitude)
		{
			result.biasPoint = new Point(
				this.#address.latitude,
				this.#address.longitude
			);
		}

		return result;
	}

	/**
	 * @param address
	 */
	set address(address: ?Address): void
	{
		this.#address = address;

		if (this.#addressString) // already rendered
		{
			this.#addressString.setValueFromAddress(this.#address);
		}

		if (!address)
		{
			this.#wasCleared = true;
		}
	}

	/**
	 * @returns {Address}
	 */
	get address(): ?Address
	{
		return this.#address;
	}

	/**
	 * Close menu on mouse click outside
	 * @param {MouseEvent} event
	 */
	#onDocumentClick(event: MouseEvent)
	{
		if (this.#isDestroyed)
		{
			return;
		}

		if (event.target === this.#inputNode)
		{
			return;
		}

		if (this.#prompt.isShown())
		{
			this.#prompt.close();
		}
	}

	/**
	 * Subscribe on changed event
	 * @param {Function} listener
	 */
	onAddressChangedEventSubscribe(listener: Function): void
	{
		this.subscribe(Autocomplete.#onAddressChangedEvent, listener);
	}

	/**
	 * Subscribe on loading event
	 * @param {Function} listener
	 */
	onStateChangedEventSubscribe(listener: Function): void
	{
		this.subscribe(Autocomplete.#onStateChangedEvent, listener);
	}

	/**
	 * @param {Function} listener
	 */
	onSearchStartedEventSubscribe(listener: Function): void
	{
		this.subscribe(Autocomplete.#onSearchStartedEvent, listener);
	}

	/**
	 * @param {Function} listener
	 */
	onSearchCompletedEventSubscribe(listener: Function): void
	{
		this.subscribe(Autocomplete.#onSearchCompletedEvent, listener);
	}

	/**
	 * @param {Function} listener
	 */
	onShowOnMapClickedEventSubscribe(listener: Function): void
	{
		this.subscribe(Autocomplete.#onShowOnMapClickedEvent, listener);
	}

	/**
	 * Is called when autocompleteService returned location list
	 * @param {array} locationsList
	 * @param {object} params
	 */
	#onPromptsReceived(locationsList: array<Location>, params: Object): void
	{
		if (Array.isArray(locationsList) && locationsList.length > 0)
		{
			if (
				locationsList.length === 1
				&& this.#address
				&& this.#address.location
				&& this.#address.location.externalId
				&& this.#address.location.externalId === locationsList[0].externalId
			)
			{
				this.closePrompt();
				return;
			}

			this.#prompt.setMenuItems(locationsList, this.#searchPhrase.requested, this.address);
			this.#showMenu(Loc.getMessage('LOCATION_WIDGET_PICK_ADDRESS_OR_SHOW_ON_MAP'), locationsList[0]);
		}
		else
		{
			this.#prompt.getMenu().clearItems();

			this.#prompt.getMenu().addMenuItem(
				{
					id: 'notFound',
					html: `<span>${Loc.getMessage('LOCATION_WIDGET_PROMPT_ADDRESS_NOT_FOUND')}</span>`,
					// eslint-disable-next-line no-unused-vars
					onclick: (event, item) => {
						this.#prompt.close();
					}
				}
			);

			this.#showMenu(Loc.getMessage('LOCATION_WIDGET_CHECK_ADDRESS_OR_SHOW_ON_MAP'), null);
		}
	}

	#getShowOnMapHandler(location: ?Location)
	{
		return () => {
			if (location)
			{
				this.#fulfillSelection(location);
				return;
			}

			// Otherwise this click will close just opened map popup.
			setTimeout(() => {
				this.emit(Autocomplete.#onShowOnMapClickedEvent);
			}, 1);
		};
	}

	static #splitPhrase(phrase: string): Object
	{
		// eslint-disable-next-line no-param-reassign
		phrase = phrase.trim();

		if (phrase.length <= 0)
		{
			return ['', ''];
		}

		const tailPosition = phrase.lastIndexOf(' ');

		if (tailPosition <= 0)
		{
			return ['', ''];
		}

		return [phrase.slice(0, tailPosition), phrase.slice(tailPosition + 1)];
	}

	/**
	 * Is called when location from menu have chosen
	 * @param event
	 */
	#onPromptItemSelected(event: BaseEvent): void
	{
		if (event.data.location)
		{
			this.#fulfillSelection(event.data.location);
		}
	}

	get state(): string
	{
		return this.#state;
	}

	#setState(state: string)
	{
		this.#state = state;
		this.emit(Autocomplete.#onStateChangedEvent, {state: this.#state});
	}

	/**
	 * Fulfill selected location
	 * @param {Location} location
	 * @returns {Promise}
	 */
	#fulfillSelection(location: ?Location): void
	{
		let result;
		this.#setState(State.DATA_SELECTED);
		if (location)
		{
			if (location.hasExternalRelation() && this.#sourceCode === location.sourceCode)
			{
				result = this.#getLocationDetails(location)
					.then(
						(detailedLocation: ?Location) => {

							if (
								location.address
								&& location.address.getFieldValue(AddressType.ADDRESS_LINE_2)
							)
							{
								let addressLine2 = '';
								if (detailedLocation.address.getFieldValue(AddressType.ADDRESS_LINE_2))
								{
									addressLine2 = detailedLocation.address.getFieldValue(AddressType.ADDRESS_LINE_2);
									addressLine2 += ', ';
								}
								addressLine2 += location.address.getFieldValue(AddressType.ADDRESS_LINE_2);

								detailedLocation.address.setFieldValue(AddressType.ADDRESS_LINE_2, addressLine2);
							}

							this.#createOnLocationSelectTimer(detailedLocation, 0);
							return true;
						},
						(response) => ErrorPublisher.getInstance().notify(response.errors)
					);
			}
			else
			{
				result = new Promise((resolve) => {
					setTimeout(() => {
						this.#createOnLocationSelectTimer(location, 0);
						resolve();
					}, 0);
				});
			}
		}
		else
		{
			result = new Promise((resolve) => {
				setTimeout(() => {
					this.#createOnLocationSelectTimer(null, 0);
					resolve();
				}, 0);
			});
		}

		return result;
	}

	#onAddressChangedEventEmit(excludeSetAddressFeatures: Array = [], options: Object = {})
	{
		this.emit(
			Autocomplete.#onAddressChangedEvent,
			{
				address: this.#address,
				excludeSetAddressFeatures,
				options: options,
			}
		);
	}

	/**
	 * obtain location details
	 * @param {Location} location
	 * @returns {*}
	 */
	#getLocationDetails(location: Location): Promise
	{
		this.#setState(State.DATA_LOADING);

		return this.#locationRepository.findByExternalId(
			location.externalId,
			location.sourceCode,
			location.languageId
		)
			.then((detailedLocation: ?Location) => {
					this.#setState(State.DATA_LOADED);

					let result;
					/*
					 * Nominatim could return a bit different location without the coordinates.
					 * For example N752206814
					 */
					if (
						detailedLocation.latitude !== '0'
						&& detailedLocation.longitude !== '0'
						&& detailedLocation !== ''
					)
					{
						result = detailedLocation;
						result.name = location.name;
					}
					else
					{
						result = location;
					}

					return result;
				},
				(response) => {
					ErrorPublisher.getInstance().notify(response.errors);
				}
			);
	}

	#convertStringToAddress(addressString: string)
	{
		const result = new Address({
			languageId: this.#languageId
		});

		result.setFieldValue(this.#addressFormat.fieldForUnRecognized, addressString);
		return result;
	}

	/**
	 * Is called when location was selected and the location details were obtained
	 * @param {Location} location
	 */
	#onLocationSelect(location: ?Location): void
	{
		this.#address = location ? location.toAddress() : null;
		this.#addressString.setValueFromAddress(this.#address);
		this.#onAddressChangedEventEmit();
	}

	#onInputKeyDown(e: KeyboardEvent): void
	{
		if (
			!(
				this.#inputNode
				&& this.#inputNode.selectionStart === 0
				&& this.#inputNode.selectionEnd === this.#inputNode.value.length
			)
		)
		{
			return;
		}

		if (
			(
				e.code === 'Backspace'
				|| e.code === 'Delete'
				|| (e.code === 'KeyV' && ((e.ctrlKey || e.metaKey)))
				|| (e.code === 'KeyX' && ((e.ctrlKey || e.metaKey)))
				|| (e.code === 'Insert' && e.shiftKey)
			)
			|| (
				!(e.ctrlKey || e.metaKey)
				&& [...e.key].length === 1
			)
		)
		{
			this.address = null;
			this.#onAddressChangedEventEmit();
		}
	}

	#onInputKeyUp(e: KeyboardEvent): void
	{
		if (this.#isDestroyed)
		{
			return;
		}

		if (
			this.#state !== State.DATA_INPUTTING
			&& this.#addressString.isChanged()
		)
		{
			this.#setState(State.DATA_INPUTTING);
		}

		if (this.#prompt.isShown())
		{
			let location;
			const onLocationSelectTimeout = 700;

			switch (e.code)
			{
				case 'NumpadEnter':
				case 'Enter':
					if (this.#prompt.isItemChosen())
					{
						this.#fulfillSelection(this.#prompt.getChosenItem())
							.then(() => {
									this.#prompt.close();
								},
								(error) => BX.debug(error)
							);
					}
					return;

				case 'Tab':
				case 'Escape':
					this.#setState(State.DATA_SUPPOSED);
					this.#onAddressChangedEventEmit();
					this.#prompt.close();
					return;

				case 'ArrowUp':
					location = this.#prompt.choosePrevItem();

					if (location && location.address)
					{
						this.#createOnLocationSelectTimer(location, onLocationSelectTimeout);
					}

					return;

				case 'ArrowDown':
					location = this.#prompt.chooseNextItem();

					if (location && location.address)
					{
						this.#createOnLocationSelectTimer(location, onLocationSelectTimeout);
					}

					return;
			}
		}

		if (this.#addressString.isChanged())
		{
			this.#addressString.actualize();
			this.showPrompt(this.#addressString.value);
		}

		if (this.#addressString.value.length === 0)
		{
			this.#showPresetLocations();
		}
	}

	#onInputPaste(): void
	{
		setTimeout(() => {
			if (
				this.#state !== State.DATA_INPUTTING
				&& this.#addressString.isChanged()
			)
			{
				this.#setState(State.DATA_INPUTTING);
			}

			if (this.#addressString.isChanged())
			{
				this.#addressString.actualize();
				this.showPrompt(this.#addressString.value);
			}
		}, 0);
	}

	#createOnLocationSelectTimer(location: Location, timeout: Number): void
	{
		if (this.#onLocationSelectTimerId !== null)
		{
			clearTimeout(this.#onLocationSelectTimerId);
		}

		this.#onLocationSelectTimerId = setTimeout(() => {
				this.#onLocationSelect(location);
			},
			timeout
		);
	}

	/**
	 * @param {string} searchPhrase
	 */
	showPrompt(searchPhrase: string): void
	{
		this.#searchPhrase.requested = searchPhrase;
		this.#searchPhrase.current = searchPhrase;
		this.#searchPhrase.dropped = '';
		this.#showPromptInner(searchPhrase);
	}

	closePrompt(): void
	{
		if (this.#prompt)
		{
			this.#prompt.close();
		}
	}

	isPromptShown(): boolean
	{
		if (this.#prompt)
		{
			this.#prompt.isShown();
		}
	}

	#showPromptInner(searchPhrase: string): void
	{
		if (searchPhrase.length <= 3)
		{
			return;
		}

		if (this.#timerId !== null)
		{
			clearTimeout(this.#timerId);
		}

		this.#timerId = this.#createTimer(searchPhrase);
	}

	/**
	 * Wait for further user input for some time
	 * @param {string} searchPhrase
	 * @returns {number}
	 */
	#createTimer(searchPhrase: string): number
	{
		return setTimeout(() => {
				// to avoid multiple parallel requests, server responses are too slow.
				if (this.#isAutocompleteRequestStarted)
				{
					clearTimeout(this.#timerId);
					this.#timerId = this.#createTimer(searchPhrase);
					this.#isNextAutocompleteRequestWaiting = true;
					return;
				}
				this.#isNextAutocompleteRequestWaiting = false;

				this.emit(Autocomplete.#onSearchStartedEvent);
				this.#isAutocompleteRequestStarted = true;
				const params = this.#makeAutocompleteServiceParams();

				this.#autocompleteService.autocomplete(searchPhrase, params)
					.then(
						(locationsList: Array<Location>) => {
							this.#timerId = null;
							if (!this.#isNextAutocompleteRequestWaiting)
							{
								this.#onPromptsReceived(locationsList, params);
								this.emit(Autocomplete.#onSearchCompletedEvent);
							}
							this.#isAutocompleteRequestStarted = false;
						},
						(error) => {
							if (!this.#isNextAutocompleteRequestWaiting)
							{
								this.emit(Autocomplete.#onSearchCompletedEvent);
							}
							this.#isAutocompleteRequestStarted = false;
							BX.debug(error);
						}
					);
			},
			300
		);
	}

	destroy(): void
	{
		if (this.#isDestroyed)
		{
			return;
		}

		Event.unbindAll(this);

		if (this.#prompt)
		{
			this.#prompt.destroy();
			this.#prompt = null;
		}

		this.#timerId = null;

		if (this.#inputNode)
		{
			this.#inputNode.removeEventListener('keydown', this.#onInputKeyDown);
			this.#inputNode.removeEventListener('keyup', this.#onInputKeyUp);
			this.#inputNode.removeEventListener('focus', this.#onInputFocus);
			this.#inputNode.removeEventListener('focusout', this.#onInputFocusOut);
			this.#inputNode.removeEventListener('click', this.#onInputClick);
			this.#inputNode.removeEventListener('paste', this.#onInputPaste);
		}

		document.removeEventListener('click', this.#onDocumentClick);
		this.#isDestroyed = true;
	}
}
