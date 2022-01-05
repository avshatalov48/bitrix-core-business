import { Tag, Type, Loc, Dom, Event, Text, Runtime } from 'main.core';
import { RoomsManager } from 'calendar.roomsmanager';
import { Util } from 'calendar.util';

export class Location
{
	static locationList = [];
	static meetingRoomList = [];
	static currentRoomCapacity = 0;
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
		this.inlineEditModeEnabled = params.inlineEditModeEnabled;
		this.meetingRooms = params.iblockMeetingRoomList || [];
		Location.setMeetingRoomList(params.iblockMeetingRoomList);
		Location.setLocationList(params.locationList);
		if (!this.disabled)
		{
			this.default = this.setDefaultRoom(params.locationList) || '';
		}
		this.create();
		this.setViewMode(params.viewMode === true)
	}

	create()
	{
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
				<div class="calendar-field-place-link">${this.DOM.inlineEditLink = Tag.render`
					<span class="calendar-text-link">${Loc.getMessage('EC_REMIND1_ADD')}</span>`}
				</div>`);
			this.DOM.inputWrap.style.display = 'none';
			Event.bind(this.DOM.inlineEditLinkWrap, 'click', this.displayInlineEditControls.bind(this));
		}

		if (this.disabled)
		{
			Dom.addClass(this.DOM.wrapNode, 'locked');
			this.DOM.inputWrap.appendChild(Dom.create('DIV', {
				props: {className: 'calendar-lock-icon'},
				events: {
					click: () => {
						top.BX.UI.InfoHelper.show('limit_office_calendar_location');
					}
				}
			}))
		}

		this.DOM.input = this.DOM.inputWrap.appendChild(Dom.create('INPUT', {
			attrs: {
				name: this.params.inputName || '',
				placeholder: Loc.getMessage('EC_LOCATION_PLACEHOLDER'),
				type: 'text',
				autocomplete: this.disabled ? 'on' : 'off',
			},
			props: {
				className: 'calendar-field calendar-field-select'
			},
			style: {
				paddingRight: 25 + "px",
			}
		}));
	}

	setValues()
	{
		let
			menuItemList = [],
			selectedIndex = false,
			meetingRooms = Location.getMeetingRoomList(),
			locationList = Location.getLocationList();

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

				if (this.value.type === 'mr'
					&& parseInt(this.value.value) === room.ID)
				{
					selectedIndex = menuItemList.length - 1;
				}
			}, this);

			if (menuItemList.length > 0)
			{
				menuItemList.push({delimiter: true});
			}
		}

		if (Type.isArray(locationList))
		{
			if (locationList.length)
			{
				locationList.forEach(function(room)
				{
					room.ID = parseInt(room.ID);
					room.LOCATION_ID = parseInt(room.LOCATION_ID);
					menuItemList.push({
						ID: room.ID,
						LOCATION_ID: room.LOCATION_ID,
						label: room.NAME,
						capacity: parseInt(room.CAPACITY) || 0,
						color: room.COLOR,
						labelRaw: room.NAME,
						labelCapacity: this.getCapacityMessage(room.CAPACITY),
						value: room.ID,
						type: 'calendar'
					});

					if (this.value.type === 'calendar'
						&& parseInt(this.value.value) === parseInt(room.ID))
					{
						selectedIndex = menuItemList.length - 1;
					}
				}, this);

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

		if (this.value)
		{
			this.DOM.input.value = this.value.str || '';
			if (this.value.type &&
				(this.value.str === this.getTextLocation(this.value) ||
					this.getTextLocation(this.value) === Loc.getMessage('EC_LOCATION_EMPTY')))
			{
				this.DOM.input.value = Loc.getMessage('EC_LOCATION_CHOOSE');
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

		if (this.selectContol)
		{
			this.selectContol.destroy();
		}

		this.selectContol = new BX.Calendar.Controls.SelectInput({
			input: this.DOM.input,
			values: menuItemList,
			valueIndex: selectedIndex,
			zIndex: this.zIndex,
			disabled: this.disabled,
			minWidth: 300,
			onChangeCallback: BX.delegate(function()
			{
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
			}, this)
		});
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
			this.DOM.inputWrap.appendChild(this.DOM.alertIconLocation)
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
			this.DOM.inputWrap.removeChild(this.DOM.alertIconLocation);
		}
	}

	getCapacityMessage(capacity)
	{
		let suffix;
		if ((capacity % 100 > 10) && (capacity % 100 < 20))
		{
			suffix = 5;
		}
		else
		{
			suffix = capacity % 10;
		}
		return Loc.getMessage("EC_LOCATION_CAPACITY_" + suffix, {'#NUM#': capacity})
	}

	editMeetingRooms()
	{
		let params = {};
		if (this.params.getControlContentCallback)
		{
			params.wrap = this.params.getControlContentCallback();
		}

		if (!params.wrap)
		{
			params.wrap = this.showEditMeetingRooms();
		}

		this.buildLocationEditControl(params);
	}

	loadRoomSlider()
	{
		this.getRoomsManager()
			.then(this.getRoomsManagerData()
		);
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
							isConfigureList: true
						}
					);
				}
				this.roomsInterface.show();
			}.bind(this));
	}

	showEditMeetingRooms()
	{
		if (this.editDialog)
		{
			this.editDialog.destroy();
		}

		this.editDialogContent = Dom.create('DIV', {props: {className: 'bxec-location-wrap'}});
		this.editDialog = new BX.PopupWindow(this.id + '_popup', null,
			{
				overlay: {opacity: 10},
				autoHide: true,
				closeByEsc : true,
				zIndex: this.zIndex,
				offsetLeft: 0,
				offsetTop: 0,
				draggable: true,
				bindOnResize: false,
				titleBar: Loc.getMessage('EC_MEETING_ROOM_LIST_TITLE'),
				closeIcon: { right : "12px", top : "10px"},
				className: 'bxc-popup-window',
				buttons: [
					new BX.PopupWindowButton({
						text: Loc.getMessage('EC_SEC_SLIDER_SAVE'),
						events: {click : BX.delegate(function()
							{
								this.saveValues();
								if (this.editDialog)
								{
									this.editDialog.close();
								}
							}, this)
						}}),

					new BX.PopupWindowButtonLink({
						text: Loc.getMessage('EC_SEC_SLIDER_CANCEL'),
						className: "popup-window-button-link-cancel",
						events: {click : BX.delegate(function()
							{
								if (this.editDialog)
								{
									this.editDialog.close();
								}
							}, this)}
					})
				],
				content: this.editDialogContent,
				events: {}
			});

		this.editDialog.show();
		return this.editDialogContent;
	}

	buildLocationEditControl(params)
	{
		let i;

		this.locationEditControlShown = true;
		this.editDialogWrap = params.wrap;

		// Display meeting room list
		this.locationRoomList = [];
		this.addNewButtonField = false;
		if (Type.isArray(Location.locationList))
		{
			Location.locationList.forEach(function(room)
			{
				if (room.NAME !== '' && room.ID)
				{
					this.locationRoomList.push({
						id: parseInt(room.ID),
						name: room.NAME,
						color: room.COLOR,
						location_id: parseInt(room.LOCATION_ID),

					});
				}
			}, this);
		}

		if (!this.locationRoomList.length)
		{
			this.locationRoomList.push({
				id: 0,
				name: ''
			});
		}

		for (i = 0; i < this.locationRoomList.length; i++)
		{
			this.addRoomField(this.locationRoomList[i], params.wrap);
		}

		// Display add button
		this.addNewButtonField = {
			outerWrap: params.wrap.appendChild(Dom.create('DIV', {props: {className: 'calendar-field-container calendar-field-container-container-text'}}))
		};
		this.addNewButtonField.innerWrap = this.addNewButtonField.outerWrap.appendChild(Dom.create('DIV', {props: {className: 'calendar-field-block'}}));

		this.addNewButtonField.innerCont = this.addNewButtonField.innerWrap.appendChild(Dom.create('DIV', {
			props: {className: 'calendar-text'},
			html: '<span class="calendar-text-link">' + Loc.getMessage('EC_MEETING_ROOM_ADD') + '</span>',
			events: {
				click: BX.delegate(function ()
				{
					let lastItem = this.locationRoomList[this.locationRoomList.length - 1];
					if (lastItem.id || lastItem.deleted || BX.util.trim(lastItem.field.input.value))
					{
						this.locationRoomList.push(this.addRoomField({id: 0}, params.wrap));
					}
				}, this)
			}
		}));
		params.wrap.appendChild(this.addNewButtonField.outerWrap);
	}

	addRoomField(room)
	{
		room.field = {
			outerWrap: this.editDialogWrap.appendChild(Dom.create('DIV', {props: {className: 'calendar-field-container calendar-field-container-string'}}))
		};
		room.field.innerWrap = room.field.outerWrap.appendChild(Dom.create('DIV', {props: {className: 'calendar-field-block'}}));

		room.field.innerWrap.style.paddingRight = '40px';
		room.field.input = room.field.innerWrap.appendChild(Dom.create('INPUT', {
			props: {className: 'calendar-field calendar-field-string'},
			attrs: {
				value: room.name || '',
				placeholder: Loc.getMessage('EC_MEETING_ROOM_PLACEHOLDER'),
				type: 'text'
			},
			events: {
				keyup: BX.delegate(function(e)
				{
					if (parseInt(e.keyCode) === 13)
					{
						this.editRoom(room);
					}
				}, this)
			}
		}));
		room.field.delRoomEntry = room.field.innerWrap.appendChild(Dom.create('SPAN', {
			props: {className: 'calendar-remove-filed'},
			events: {
				click: BX.delegate(function()
				{
					Location.deleteField(room);
				}, this)
			}
		}));

		if (this.addNewButtonField)
		{
			this.editDialogWrap.appendChild(this.addNewButtonField.outerWrap);
		}

		if (!room.id)
		{
			room.field.input.focus();
		}

		return room;
	}

	editRoom(room)
	{
		if (!this.locationEditControlShown)
			return;

		room.field.input.value = BX.util.trim(room.field.input.value);
		if (!room.id)
		{
			if (room.field.input.value && BX.util.trim(room.field.input.value) !== BX.util.trim(room.name))
			{
				room.name = room.field.input.value;
				this.locationRoomList.push(this.addRoomField({id: 0}));
			}
		}
		else
		{
			if (BX.util.trim(room.field.input.value) !== (room.name))
			{
				room.name = room.field.input.value;
				room.changed = true;
			}
		}
	}

	static deleteField(room)
	{
		BX.remove(room.field.outerWrap, true);
		room.deleted = true;
		room.changed = true;
	}

	saveValues()
	{
		let i, locationList = [];
		for (i = 0; i < this.locationRoomList.length; i++)
		{
			if (this.locationRoomList[i].field && this.locationRoomList[i].field.input)
			{
				if (this.locationRoomList[i].name !== this.locationRoomList[i].field.input.value && this.locationRoomList[i].id)
				{
					this.locationRoomList[i].changed = true;
				}

				this.locationRoomList[i].name = this.locationRoomList[i].field.input.value;
			}

			if ((!this.locationRoomList[i].deleted && this.locationRoomList[i].name) || this.locationRoomList[i].id)
			{
				locationList.push({
					id: this.locationRoomList[i].id || 0,
					name: this.locationRoomList[i].name || '',
					changed: (this.locationRoomList[i].changed || !this.locationRoomList[i].id) ? 'Y' : 'N',
					deleted: (this.locationRoomList[i].deleted || !this.locationRoomList[i].name) ? 'Y' : 'N'
				});
			}
		}

		BX.ajax.runAction('calendar.api.calendarajax.saveLocationList', {
			data: {
				locationList: locationList
			}
		})
			.then(
				// Success
				(response) => {
					Location.setLocationList(response.data.locationList);
					this.setValues();
				},
				// Failure
				(response) => {
					//this.calendar.displayError(response.errors);
				}
			);
		this.locationEditControlShown = false;
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

	setValue(value)
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

		this.setValues();

		if (this.inlineEditModeEnabled)
		{
			let textLocation = this.getTextLocation(this.value);
			this.DOM.inlineEditLink.innerHTML = Text.encode(textLocation || Loc.getMessage('EC_REMIND1_ADD'));
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

	static getCurrentCapacity()
	{
		return Location.currentRoomCapacity || 0;
	}

	static setCurrentCapacity(capacity)
	{
		Location.currentRoomCapacity = capacity;
	}

	static getMeetingRoomList()
	{
		return Location.meetingRoomList;
	}

	displayInlineEditControls()
	{
		this.DOM.inlineEditLinkWrap.style.display = 'none';
		this.DOM.inputWrap.style.display = '';
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
								locationContext: this //for updating list of locations in event creation menu
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
}
