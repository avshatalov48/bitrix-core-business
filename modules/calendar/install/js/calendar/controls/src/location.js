import { Tag, Type, Loc, Dom, Event, Text, Runtime } from 'main.core';
import { RoomsManager, RoomsSection } from 'calendar.roomsmanager';
import { CategoryManager } from 'calendar.categorymanager';
import {EventEmitter} from 'main.core.events';
import { Util } from 'calendar.util';
import { SelectInput } from 'calendar.controls';

export class Location
{
	static locationList = [];
	static meetingRoomList = [];
	static currentRoomCapacity = 0;
	static accessibility = [];
	static DAY_LENGTH = 86400000;
	static instances = [];
	datesRange = [];
	viewMode = false;

	constructor(params)
	{
		this.params = params;
		this.id = params.id || 'location-' + Math.round(Math.random() * 1000000);
		this.zIndex = params.zIndex || 3100;

		this.DOM = {
			wrapNode: params.wrap
		};
		this.roomsManager = params.roomsManager || null;
		this.locationAccess = params.locationAccess || false;
		this.disabled = !params.richLocationEnabled;
		this.value = {type: '', text: '', value: ''};
		this.isLoading = false;
		this.inlineEditModeEnabled = params.inlineEditModeEnabled;
		this.meetingRooms = params.iblockMeetingRoomList || [];
		Location.setMeetingRoomList(params.iblockMeetingRoomList);
		Location.setLocationList(params.locationList);
		if (!this.disabled)
		{
			this.default = this.setDefaultRoom(params.locationList) || '';
		}
		this.create();
		this.setViewMode(params.viewMode === true);
		this.processValue();
		this.setCategoryManager();
		this.setValuesDebounced = BX.debounce(this.setValues.bind(this), 100);
		this.updateAccessibilityDebounce = Runtime.debounce(this.updateAccessibility.bind(this), 100);
		Location.instances.push(this);
	}

	create()
	{
		this.DOM.wrapNode.style.display = 'flex';
		this.DOM.inputWrap = this.DOM.wrapNode.appendChild(Tag.render`
			<div class="calendar-field-block"></div>
		`)

		this.DOM.alertIconLocation = Tag.render`
			<div class="ui-alert-icon-danger calendar-location-alert-icon" data-hint-no-icon="Y" data-hint="${Loc.getMessage('EC_LOCATION_OVERFLOW')}">
			<i></i>
			</div>
		`;
		if (this.inlineEditModeEnabled)
		{
			this.DOM.inlineEditLinkWrap = this.DOM.wrapNode.appendChild(Tag.render`
				<div class="calendar-field-place-link calendar-location-readonly">${this.DOM.inlineEditLink = Tag.render`
					<span class="calendar-text-link">${Loc.getMessage('EC_REMIND1_ADD')}</span>`}
				</div>`);

			this.DOM.inputWrap.style.display = 'none';

			Event.bind(
				this.DOM.inlineEditLinkWrap, 'click', () => {
					this.displayInlineEditControls();
					this.selectContol.showPopup();
				}
			);
		}

		this.DOM.inputWrapInner = this.DOM.inputWrap.appendChild(Tag.render`
				<div class="calendar-event-location-input-wrap-inner">
				</div>`
		);

		this.DOM.input = this.DOM.inputWrapInner.appendChild(Dom.create('INPUT', {
			attrs: {
				name: this.params.inputName || '',
				placeholder: this.disabled
					? Loc.getMessage('EC_LOCATION_PLACEHOLDER_LOCKED')
					: Loc.getMessage('EC_LOCATION_PLACEHOLDER')
				,
				type: 'text',
				autocomplete: this.disabled ? 'on' : 'off',
			},
			props: {
				className: 'calendar-field calendar-field-select'
			},
			style: {
				paddingRight: 25 + 'px',
				minWidth: 300 + 'px',
				maxWidth: 300 + 'px',
			}
		}));

		if (this.disabled)
		{
			Dom.addClass(this.DOM.wrapNode, 'locked');

			this.DOM.lockIcon = Tag.render`
				<div class="calendar-lock-icon"></div>
			`;
			Event.bind(this.DOM.lockIcon, 'click', () => {
				top.BX.UI.InfoHelper.show('limit_office_calendar_location');
			})

			Dom.append(this.DOM.lockIcon, this.DOM.inputWrapInner);
		}
	}

	setValues()
	{
		this.addLocationRemoveButton();

		if (!this.categoryManagerFromDB)
		{
			this.setValuesDebounced();
			return;
		}

		this.prohibitClick();

		let
			menuItemList = [],
			selectedIndex = false,
			meetingRooms = Location.getMeetingRoomList(),
			locationList = Location.getLocationList();

		const roomList = this.createRoomList(locationList);

		this.categoriesWithRooms = this?.categoryManagerFromDB?.getCategoriesWithRooms(roomList);

		if (Type.isArray(meetingRooms))
		{
			meetingRooms.forEach(function(room)
			{
				room.ID = parseInt(room.ID);
				menuItemList.push({
					ID: room.ID,
					label: room.NAME,
					labelRaw: room.NAME,
					value: room.ID,
					capacity: 0,
					type: 'mr'
				});

				if (
					this.value.type === 'mr'
					&& parseInt(this.value.value) === room.ID
				)
				{
					selectedIndex = menuItemList.length - 1;
				}
			}, this);

			if (menuItemList.length > 0)
			{
				menuItemList.push({delimiter: true});
			}
		}

		const pushRoomToItemList = (room) => {
			room.id = parseInt(room.id);
			room.location_id = parseInt(room.location_id);
			const isSelected = parseInt(this.value.value) === parseInt(room.id);
			menuItemList.push({
				ID: room.id,
				LOCATION_ID: room.location_id,
				label: room.name,
				capacity: parseInt(room.capacity) || 0,
				color: room.color,
				reserved: room.reserved || false,
				labelRaw: room.name,
				labelCapacity: this.getCapacityMessage(room.capacity),
				value: room.id,
				type: 'calendar',
				selected: isSelected,
			});

			if (this.value.type === 'calendar' && isSelected)
			{
				selectedIndex = menuItemList.length - 1;
			}
		};

		if (Type.isObject(this.categoriesWithRooms))
		{
			if (this.categoriesWithRooms.categories.length || this.categoriesWithRooms.default.length)
			{
				this.categoriesWithRooms.categories.forEach((category) => {
					if (category.rooms.length)
					{
						menuItemList.push({text: category.name, delimiter: true});
						category.rooms.forEach((room) => pushRoomToItemList(room), this);
					}
				});

				if (this.categoriesWithRooms.default.length)
				{
					menuItemList.push({
						text: "\0",
						className: 'calendar-popup-window-delimiter-default-category',
						delimiter: true,
					});
					this.categoriesWithRooms.default.forEach((room) => pushRoomToItemList(room), this);
				}

				if (this.locationAccess)
				{
					this.loadRoomSlider();
					menuItemList.push({delimiter: true});
					menuItemList.push({
						label: Loc.getMessage('EC_LOCATION_MEETING_ROOM_SET'),
						callback: this.openRoomsSlider.bind(this)
					});
				}
			}
			else
			{
				if (this.locationAccess)
				{
					this.loadRoomSlider();
					menuItemList.push({
						label: Loc.getMessage('EC_ADD_LOCATION'),
						callback: this.openRoomsSlider.bind(this)
					});
				}
			}
		}

		let disabledControl = this.disabled;
		if (!menuItemList.length)
		{
			disabledControl = true;
		}

		this.processValue();

		this.menuItemList = menuItemList;
		const selectControlCreated = this.selectContol;
		this.selectContol ??= new SelectInput({
			input: this.DOM.input,
			values: menuItemList,
			valueIndex: selectedIndex,
			zIndex: this.zIndex,
			disabled: disabledControl,
			minWidth: 300,
			onChangeCallback: () => {
				const menuItemList = this.menuItemList;

				EventEmitter.emit('Calendar.LocationControl.onValueChange');
				let i, value = this.DOM.input.value;
				this.value = {text: value};
				for (i = 0; i < menuItemList.length; i++)
				{
					if (menuItemList[i].labelRaw === value)
					{
						this.value.type = menuItemList[i].type;
						this.value.value = menuItemList[i].value;
						Location.setCurrentCapacity(menuItemList[i].capacity)
						break;
					}
				}
				if (Type.isFunction(this.params.onChangeCallback))
				{
					this.params.onChangeCallback();
				}
				if (this.value.text === '')
				{
					this.removeLocationRemoveButton();
				}
				this.addLocationRemoveButton();

				menuItemList.forEach((location) => {
					location.selected = (location.value === this.value.value);
				});
				this.selectContol.setValueList(menuItemList);

				this.allowClick();
			},
			onPopupShowCallback: () => {
				if (this.getShouldCheckLocationAccessibility())
				{
					this.checkLocationAccessibility(this.accessibilityParams);
				}
			},
		});

		this.selectContol.setValueList(menuItemList);
		this.selectContol.setValue({
			valueIndex: selectedIndex,
		});
		this.selectContol.setDisabled(disabledControl);

		if (!selectControlCreated)
		{
			this.setLoading(this.isLoading);
		}

		this.allowClick();
	}

	processValue()
	{
		if (this.value)
		{
			this.DOM.input.value = this.value.str || '';
			if (
				this.value.type
				&& (
					this.value.str === this.getTextLocation(this.value)
					|| this.getTextLocation(this.value) === Loc.getMessage('EC_LOCATION_EMPTY')
				)
			)
			{
				this.DOM.input.value = '';
				this.value = '';
			}
			for (const locationListElement of Location.locationList)
			{
				if (parseInt(locationListElement.ID) === this.value.room_id)
				{
					Location.setCurrentCapacity(parseInt(locationListElement.CAPACITY));
					break;
				}
			}
		}
	}

	setValuesDebounce()
	{
		this.setValuesDebounced();
	}

	removeValue()
	{
		this.setValue(false, false);
		this.selectContol.onChangeCallback();
		this.removeLocationRemoveButton();
	}

	removeLocationRemoveButton()
	{
		if (this.DOM.inputWrap.contains(this.DOM.removeLocationButton))
		{
			this.DOM.inputWrap.removeChild(this.DOM.removeLocationButton);
		}
		else if (this.DOM.wrapNode.contains(this.DOM.removeLocationButton))
		{
			this.DOM.wrapNode.removeChild(this.DOM.removeLocationButton);
		}

		this.DOM.removeLocationButton = null;
		if (Type.isDomNode(this.DOM.inlineEditLink))
		{
			this.displayInlineEditControls();
		}
	}

	addLocationRemoveButton()
	{
		let wrap = this.DOM.inputWrap;
		if(this.DOM?.inlineEditLinkWrap?.style.display === '')
		{
			wrap = this.DOM.wrapNode;
		}

		if(
			(this.value.value || this.value.str || this.value.text)
			&& !this.viewMode
			&& !this.DOM.removeLocationButton
			&& this.value.text !== ''
		)
		{
			this.DOM.removeLocationButton = wrap.appendChild(Tag.render`
				<span class="calendar-location-clear-btn-wrap calendar-location-readonly">
					<span class="calendar-location-clear-btn-text">${Loc.getMessage('EC_LOCATION_CLEAR_INPUT')}</span>
				</span>`
			);
			Event.bind(this.DOM.removeLocationButton, 'click', this.removeValue.bind(this));
		}
	}

	isShown()
	{
		return this.selectContol?.shown ?? false;
	}

	setViewMode(viewMode)
	{
		this.viewMode = viewMode;
		if (this.viewMode)
		{
			Dom.addClass(this.DOM.wrapNode, 'calendar-location-readonly')
		}
		else
		{
			Dom.removeClass(this.DOM.wrapNode, 'calendar-location-readonly')
		}
	}

	addCapacityAlert()
	{
		if (!Dom.hasClass(this.DOM.input, 'calendar-field-location-select-border'))
		{
			Dom.addClass(this.DOM.input, 'calendar-field-location-select-border');
		}
		if (Type.isDomNode(this.DOM.alertIconLocation))
		{
			Util.initHintNode(this.DOM.alertIconLocation);
		}
		setTimeout(() => {
			this.DOM.inputWrapInner.after(this.DOM.alertIconLocation)
		}, 200);
	}

	removeCapacityAlert()
	{
		if (Dom.hasClass(this.DOM.input, 'calendar-field-location-select-border'))
		{
			Dom.removeClass(this.DOM.input, 'calendar-field-location-select-border');
		}
		if (this.DOM.alertIconLocation.parentNode === this.DOM.inputWrap)
		{
			Dom.remove(this.DOM.alertIconLocation);
		}
	}

	getCapacityMessage(capacity)
	{
		let suffix;
		if (
			(capacity % 100 > 10)
			&& (capacity % 100 < 20)
		)
		{
			suffix = 5;
		}
		else
		{
			suffix = capacity % 10;
		}
		return Loc.getMessage('EC_LOCATION_CAPACITY_' + suffix, {'#NUM#': capacity})
	}

	getShouldCheckLocationAccessibility(): boolean
	{
		return this.shouldCheckLocationAccessibility;
	}

	setShouldCheckLocationAccessibility(shouldCheck: boolean): void
	{
		this.shouldCheckLocationAccessibility = shouldCheck;
	}

	checkLocationAccessibility(params)
	{
		this.accessibilityParams = params;
		this.setLoading(true);
		this.updateAccessibilityDebounce();
	}

	updateAccessibility()
	{
		const params = this.accessibilityParams;
		this.getLocationAccessibility(params.from, params.to).then(() => {
			const timezone = (params.timezone && params.timezone !== '')
				? params.timezone
				: Util.getUserSettings().timezoneName
			;
			const timezoneOffset = Util.getTimeZoneOffset(timezone) * 60 * 1000;
			const fromTs = new Date(params.from.getTime() + timezoneOffset).getTime();
			let toTs = new Date(params.to.getTime() + timezoneOffset).getTime();
			if (params.fullDay)
			{
				toTs += Location.DAY_LENGTH;
			}

			for (const index in Location.locationList)
			{
				Location.locationList[index].reserved = false;
				let roomId = Location.locationList[index].ID;
				for (const date of this.datesRange)
				{
					if (
						Type.isUndefined(Location.accessibility[date])
						|| !Type.isArrayFilled(Location.accessibility[date][roomId])
					)
					{
						continue;
					}

					for (const event of Location.accessibility[date][roomId])
					{
						if (parseInt(event.PARENT_ID) === parseInt(params.currentEventId))
						{
							continue;
						}

						let eventTimezoneOffset = 0;
						if (event.DT_SKIP_TIME === 'N')
						{
							eventTimezoneOffset = Util.getTimeZoneOffset(event.TZ_FROM) * 60 * 1000;
						}

						const eventTsFrom = new Date(Util.parseDate(event.DATE_FROM).getTime() + eventTimezoneOffset).getTime();
						let eventTsTo = new Date(Util.parseDate(event.DATE_TO).getTime() + eventTimezoneOffset).getTime();
						if (event.DT_SKIP_TIME === 'Y')
						{
							eventTsTo += Location.DAY_LENGTH;
						}

						if (eventTsFrom < toTs && eventTsTo > fromTs)
						{
							Location.locationList[index].reserved = true;
							break;
						}
					}
					if (Location.locationList[index].reserved)
					{
						break;
					}
				}
			}

			this.setValues();
			this.setLoading(false);
		});
	}

	setLoading(isLoading)
	{
		this.isLoading = isLoading;
		this.selectContol?.setLoading(isLoading);
	}

	getLocationAccessibility(from, to)
	{
		return new Promise((resolve) => {
			this.datesRange = Location.getDatesRange(from, to);
			let isCheckedAccessibility = true;

			for (let date of this.datesRange)
			{
				if (Type.isUndefined(Location.accessibility[date]))
				{
					isCheckedAccessibility = false;
					break;
				}
			}

			if (!isCheckedAccessibility)
			{
				BX.ajax.runAction('calendar.api.locationajax.getLocationAccessibility', {
					data: {
						datesRange: this.datesRange,
						locationList: Location.locationList,
					}
				}).then(
					(response) => {
						for (let date of this.datesRange)
						{
							Location.accessibility[date] = response.data[date];
						}
						resolve(Location.accessibility, this.datesRange);
					},
					(response) => {
						resolve(response.errors);
					}
				);
			}
			else
			{
				resolve(Location.accessibility, this.datesRange);
			}
		});
	}

	static handlePull(params)
	{
		const entry = params.fields;
		if (!entry.DATE_FROM || !entry.DATE_TO)
		{
			return;
		}

		let dateFrom = Util.parseDate(entry.DATE_FROM);
		let dateTo = Util.parseDate(entry.DATE_TO);
		let datesRange = Location.getDatesRange(dateFrom, dateTo);

		const excludedDates = entry.EXDATE?.split(';');
		if (Type.isArrayFilled(excludedDates))
		{
			datesRange.push(excludedDates.pop());
		}

		for (let date of datesRange)
		{
			if (Location.accessibility[date])
			{
				delete Location.accessibility[date];
			}
		}

		Location.instances.forEach((instance) => {
			if (instance.isShown())
			{
				instance.checkLocationAccessibility(instance.accessibilityParams);
			}
			else
			{
				instance.setShouldCheckLocationAccessibility(true);
			}
		});
	}

	loadRoomSlider()
	{
		this.setRoomsManager();
	}

	openRoomsSlider()
	{
		this.getRoomsInterface()
			.then(function(RoomsInterface) {
				if (!this.roomsInterface)
				{
					this.roomsInterface = new RoomsInterface(
						{
							calendarContext: null,
							readonly: false,
							roomsManager: this.roomsManagerFromDB,
							categoryManager: this.categoryManagerFromDB,
							isConfigureList: true
						}
					);
				}
				this.roomsInterface.show();
			}.bind(this));
	}

	getTextValue(value)
	{
		if (!value)
		{
			value = this.value;
		}

		let res = value.str || value.text || '';
		if (value && value.type === 'mr')
		{
			res = 'ECMR_' + value.value + (value.mrevid ? '_' + value.mrevid : '');

		}
		else if (value && value.type === 'calendar')
		{
			res = 'calendar_' + value.value + (value.room_event_id ? '_' + value.room_event_id : '');
		}
		return res;
	}

	getValue()
	{
		return this.value;
	}

	setValue(value, debounced = true)
	{
		if (Type.isPlainObject(value))
		{
			this.value.text = value.text || '';
			this.value.type = value.type || '';
			this.value.value = value.value || '';
		}
		else
		{
			this.value = Location.parseStringValue(value);
		}

		if (debounced)
		{
			this.setValuesDebounce();
		}
		else
		{
			this.setValues();
		}

		if (this.inlineEditModeEnabled)
		{
			let textLocation = this.getTextLocation(this.value);
			this.DOM.inlineEditLink.innerHTML = Text.encode(textLocation || Loc.getMessage('EC_REMIND1_ADD'));
			if(textLocation)
			{
				this.addLocationRemoveButton();
			}
		}
	}

	// parseLocation
	static parseStringValue(str)
	{
		if (!Type.isString(str))
		{
			str = '';
		}

		let
			res = {
				type : false,
				value : false,
				str : str
			};

		if (str.substr(0, 5) === 'ECMR_')
		{
			res.type = 'mr';
			let value = str.split('_');
			if (value.length >= 2)
			{
				if (!isNaN(parseInt(value[1])) && parseInt(value[1]) > 0)
				{
					res.value = res.mrid = parseInt(value[1]);
				}

				if (!isNaN(parseInt(value[2])) && parseInt(value[2]) > 0)
				{
					res.mrevid = parseInt(value[2]);
				}
			}
		}
		else if (str.substr(0, 9) === 'calendar_')
		{
			res.type = 'calendar';
			let value = str.split('_');
			if (value.length >= 2)
			{
				if (!isNaN(parseInt(value[1])) && parseInt(value[1]) > 0)
				{
					res.value = res.room_id = parseInt(value[1]);
				}
				if (!isNaN(parseInt(value[2])) && parseInt(value[2]) > 0)
				{
					res.room_event_id = parseInt(value[2]);
				}
			}
		}

		return res;
	}

	getTextLocation(location)
	{
		let
			value = Type.isPlainObject(location) ? location : Location.parseStringValue(location),
			i, str = value.str;

		if (Type.isArray(this.meetingRooms) && value.type === 'mr')
		{
			str = Loc.getMessage('EC_LOCATION_EMPTY');
			for (i = 0; i < this.meetingRooms.length; i++)
			{
				if (parseInt(value.value) === parseInt(this.meetingRooms[i].ID))
				{
					str = this.meetingRooms[i].NAME;
					break;
				}
			}
		}

		if (Type.isArray(Location.locationList) && value.type === 'calendar')
		{
			str = Loc.getMessage('EC_LOCATION_EMPTY');
			for (i = 0; i < Location.locationList.length; i++)
			{
				if (parseInt(value.value) === parseInt(Location.locationList[i].ID))
				{
					str = Location.locationList[i].NAME;
					break;
				}
			}
		}

		return str;
	}

	static setLocationList(locationList)
	{
		if (Type.isArray(locationList))
		{
			Location.locationList = locationList;
			this.sortLocationList();
		}
	}

	static sortLocationList()
	{
		Location.locationList.sort((a,b) => {
			if (a.NAME.toLowerCase() > b.NAME.toLowerCase())
			{
				return 1;
			}
			if (a.NAME.toLowerCase() < b.NAME.toLowerCase())
			{
				return -1;
			}
			return 0;
		})
	}

	static getLocationList()
	{
		return Location.locationList;
	}

	static setMeetingRoomList(meetingRoomList)
	{
		if (Type.isArray(meetingRoomList))
		{
			Location.meetingRoomList = meetingRoomList;
		}
	}

	static getMeetingRoomList()
	{
		return Location.meetingRoomList;
	}

	static setLocationAccessibility(accessibility)
	{
		Location.accessibility = accessibility;
	}

	static getLocationAccessibility()
	{
		return Location.accessibility;
	}

	static setCurrentCapacity(capacity)
	{
		Location.currentRoomCapacity = capacity;
	}

	static getCurrentCapacity()
	{
		return Location.currentRoomCapacity || 0;
	}

	displayInlineEditControls()
	{
		this.DOM.inlineEditLinkWrap.style.display = 'none';
		this.DOM.inputWrap.style.display = '';
		this.addLocationRemoveButton();
	}

	setDefaultRoom(locationList)
	{
		if (this.roomsManager && !RoomsManager.isEmpty(locationList))
		{
			this.activeRooms = this.roomsManager.getRoomsInfo().active;
			if (!RoomsManager.isEmpty(this.activeRooms))
			{
				const activeRoomId = this.activeRooms[0];
				for (const locationListElement of locationList)
				{
					if (parseInt(locationListElement.ID) === activeRoomId)
					{
						Location.setCurrentCapacity(parseInt(locationListElement.CAPACITY));
						return 'calendar_' + activeRoomId;
					}
				}
			}
			else
			{
				Location.setCurrentCapacity(parseInt(locationList[0].CAPACITY));
				return 'calendar_' + locationList[0].ID;
			}
		}
		else
		{
			return '';
		}
	}

	getRoomsInterface()
	{
		return new Promise((resolve) => {
			const bx = BX.Calendar.Util.getBX();
			const extensionName = 'calendar.rooms';
			bx.Runtime.loadExtension(extensionName)
				.then(() =>
					{
						if (bx.Calendar.Rooms.RoomsInterface)
						{
							resolve(bx.Calendar.Rooms.RoomsInterface);
						}
						else
						{
							console.error('Extension ' + extensionName + ' not found');
							resolve(bx.Calendar.Rooms.RoomsInterface);
						}
					}
				);
		});
	}

	getRoomsManager()
	{
		return new Promise((resolve) => {
			const bx = BX.Calendar.Util.getBX();
			const extensionName = 'calendar.roomsmanager';
			bx.Runtime.loadExtension(extensionName)
				.then(() =>
					{
						if (bx.Calendar.RoomsManager)
						{
							resolve(bx.Calendar.RoomsManager);
						}
						else
						{
							console.error('Extension ' + extensionName + ' not found');
							resolve(bx.Calendar.RoomsManager);
						}
					}
				);
		});
	}

	getRoomsManagerData()
	{
		return new Promise((resolve) => {
			BX.ajax.runAction('calendar.api.locationajax.getRoomsManagerData')
				.then((response) => {

						this.roomsManagerFromDB = new RoomsManager(
							{
								sections: response.data.sections,
								rooms: response.data.rooms
							},
							{
								locationAccess: response.data.config.locationAccess,
								hiddenSections: response.data.config.hiddenSections,
								type: response.data.config.type,
								ownerId: response.data.config.ownerId,
								userId: response.data.config.userId,
								new_section_access: response.data.config.defaultSectionAccess,
								sectionAccessTasks: response.data.config.sectionAccessTasks,
								showTasks: response.data.config.showTasks,
								locationContext: this, //for updating list of locations in event creation menu
								accessNames: response.data.config.accessNames,
							}
						)
						resolve(response.data);
					},
					// Failure
					(response) => {
						console.error('Extension not found');
						resolve(response.data);
					}
				);
		});
	}

	createRoomList(locationList)
	{
		return locationList.map((location) => {
			return new RoomsSection(location);
		});
	}

	setRoomsManager()
	{
		if (!this.roomsManagerFromDB)
		{
			this.getRoomsManager()
				.then(
					this.getRoomsManagerData()
				);
		}
	}

	getCategoryManager()
	{
		return new Promise((resolve) => {
			const bx = BX.Calendar.Util.getBX();
			const extensionName = 'calendar.categorymanager';
			bx.Runtime.loadExtension(extensionName)
				.then(() =>
					{
						if (bx.Calendar.CategoryManager)
						{
							resolve(bx.Calendar.CategoryManager);
						}
						else
						{
							console.error('Extension ' + extensionName + ' not found');
							resolve(bx.Calendar.CategoryManager);
						}
					}
				);
		});
	}

	getCategoryManagerData()
	{
		return new Promise((resolve) => {
			BX.ajax.runAction('calendar.api.locationajax.getCategoryManagerData')
				.then((response) => {
						this.categoryManagerFromDB = new CategoryManager(
							{
								categories: response.data.categories,
							},
							{
								perm: response.data.permissions,
								locationContext: this //for updating list of locations in event creation menu
							}
						);
						resolve(response.data);
					},
					// Failure
					(response) => {
						console.error('Extension not found');
						resolve(response.data);
					}
				);
		});
	}

	setCategoryManager()
	{
		if (!this.categoryManagerFromDB)
		{
			this.getCategoryManager()
				.then(
					this.getCategoryManagerData()
				);
		}
	}

	prohibitClick()
	{
		if (
			this.DOM.inlineEditLinkWrap
			&& !Dom.hasClass(this.DOM.inlineEditLinkWrap, 'calendar-location-readonly')
		)
		{
			Dom.addClass(this.DOM.inlineEditLinkWrap, 'calendar-location-readonly');
		}
		if (
			this.DOM.removeLocationButton
			&& !Dom.hasClass(this.DOM.removeLocationButton, 'calendar-location-readonly')
		)
		{
			Dom.addClass(this.DOM.removeLocationButton, 'calendar-location-readonly');
		}
	}

	allowClick()
	{
		if (
			this.DOM.inlineEditLinkWrap
			&& Dom.hasClass(this.DOM.inlineEditLinkWrap, 'calendar-location-readonly')
		)
		{
			Dom.removeClass(this.DOM.inlineEditLinkWrap, 'calendar-location-readonly');
		}
		if (
			this.DOM.removeLocationButton
			&& Dom.hasClass(this.DOM.removeLocationButton, 'calendar-location-readonly')
		)
		{
			Dom.removeClass(this.DOM.removeLocationButton, 'calendar-location-readonly');
		}
	}

	static getDateInFormat(date)
	{
		return ('0' + date.getDate()).slice(-2) + '.'
			+ ('0' + (date.getMonth() + 1)).slice(-2) + '.'
			+ date.getFullYear()
	}

	static getDatesRange(from, to)
	{
		const fromDate = new Date(from.getTime() - Util.getDayLength());
		const toDate = new Date(to.getTime() + Util.getDayLength());
		let startDate = fromDate.setHours(0, 0, 0, 0);
		let finishDate = toDate.setHours(0, 0, 0, 0);
		let result = [];
		while (startDate <= finishDate)
		{
			result.push(Location.getDateInFormat(new Date(startDate)));
			startDate += Location.DAY_LENGTH;
		}

		return result;
	}
}