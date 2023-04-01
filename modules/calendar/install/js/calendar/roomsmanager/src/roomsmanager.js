import {Type, Loc, Event, Runtime } from 'main.core';
import { SectionManager } from 'calendar.sectionmanager';
import { Util } from 'calendar.util';
import { RoomsSection } from './roomssection';
import { EventEmitter } from 'main.core.events';
export { RoomsSection };

export class RoomsManager extends SectionManager
{
	constructor(data, config)
	{
		super(data, config);
		this.locationAccess = config.locationAccess || false;
		this.locationContext = config.locationContext || null;
		this.setRooms(data.rooms);
		this.setConfig(config);
		this.sortRooms();
		this.setSections(data.sections);
		this.sortSections();
		this.reloadRoomsFromDatabaseDebounce = Runtime.debounce(this.reloadRoomsFromDatabase, SectionManager.RELOAD_DELAY, this);

		if (Object.keys(Util.accessNames).length === 0)
		{
			BX.Calendar.Util.setAccessNames(config.accessNames);
		}
		EventEmitter.subscribeOnce('BX.Calendar.Rooms:delete', this.deleteRoomHandler.bind(this));
	}

	sortRooms()
	{
		this.roomsIndex = {};
		this.rooms = this.rooms.sort((a, b) => {
			if (a.name.toLowerCase() > b.name.toLowerCase())
			{
				return 1;
			}
			if (a.name.toLowerCase() < b.name.toLowerCase())
			{
				return -1;
			}
			return 0;
		});

		this.rooms.forEach((room, i) => {
			this.roomsIndex[room.getId()] = i;
		});
	}

	setRooms(params = [])
	{
		this.rooms = [];
		this.roomsIndex = {};
		params.forEach((roomData) => {
			let room = new RoomsSection(roomData);
			this.rooms.push(room);
			this.roomsIndex[room.getId()] = this.rooms.length - 1;
		});
	}

	getRooms()
	{
		return this.rooms;
	}

	getRoom(id)
	{
		return this.rooms[this.roomsIndex[id]];
	}

	createRoom(params)
	{
		return new Promise(resolve => {

			params.name = this.checkName(params.name);
			params.capacity = this.checkCapacity(params.capacity);
			params.necessity = (params.necessity && params.capacity !== 0) ? 'Y' : 'N';

			BX.ajax.runAction('calendar.api.locationajax.createRoom', {
					data: {
						name: params.name,
						capacity: params.capacity,
						necessity: params.necessity,
						ownerId: this.ownerId,
						color: params.color,
						access: params.access || null,
						categoryId: params.categoryId,
					}
				})
				.then(
					(response) => {
						const roomList = response.data.rooms || [];
						const sectionList = response.data.sections || [];
						this.setRooms(roomList);
						this.sortRooms();
						this.setSections(sectionList);
						this.sortSections();

						Util.getBX().Event.EventEmitter.emit(
							'BX.Calendar.Rooms:create',
							new Event.BaseEvent(
								{
									data: { roomsList: roomList }
								}
							)
						);
						this.setLocationSelector(roomList);
						resolve(response.data);
					},
					(response) => {
						BX.Calendar.Util.displayError(response.errors);
						resolve(response.data);
					}
				);

		});
	}

	updateRoom(params)
	{
		return new Promise(resolve => {
			params.name = this.checkName(params.name);
			params.capacity = this.checkCapacity(params.capacity);
			params.necessity = (params.necessity && params.capacity !== 0) ? 'Y' : 'N';

			BX.ajax.runAction('calendar.api.locationajax.updateRoom', {
					data: {
						id: params.id,
						location_id: params.location_id,
						name: params.name,
						capacity: params.capacity,
						necessity: params.necessity,
						color: params.color,
						access: params.access || null,
						categoryId: params.categoryId,
					}
				})
				.then(
					(response) => {
						const roomList = response.data.rooms || [];
						const sectionList = response.data.sections || [];
						this.setRooms(roomList);
						this.sortRooms();
						this.setSections(sectionList);
						this.sortSections();
						this.unsetHiddenRoom(params.id)

						Util.getBX().Event.EventEmitter.emit(
							'BX.Calendar.Rooms:update',
							new Event.BaseEvent(
								{
									data: { roomsList: roomList }
								}
							)
						);
						this.setLocationSelector(roomList);
						resolve(response.data);
					},
					(response) => {
						BX.Calendar.Util.displayError(response.errors);
						resolve(response.data);
					}
				);
		});
	}

	deleteRoom(id, location_id)
	{
		const EventAlias = Util.getBX().Event;
		EventAlias.EventEmitter.emit(
			'BX.Calendar.Section:delete',
			new EventAlias.BaseEvent({data: {sectionId: id}})
		);
		return new Promise(resolve => {
			BX.ajax.runAction('calendar.api.locationajax.deleteRoom', {
					data: {
						id: id,
						location_id: location_id
					}
				})
				.then(
					(response) => {
						const roomList = response.data.rooms || [];
						const sectionList = response.data.sections || [];
						if (!roomList.length)
						{
							BX.reload();
						}
						this.setRooms(roomList);
						this.sortRooms();
						this.setSections(sectionList);
						this.sortSections();

						Util.getBX().Event.EventEmitter.emit(
							'BX.Calendar.Rooms:delete',
							new Event.BaseEvent(
								{
									data: {
										id: id
									}
								}
							)
						);
						this.setLocationSelector(roomList);
						resolve(response.data);
					},
					(response) => {
						BX.Calendar.Util.displayError(response.errors);
						resolve(response.data);
					}
				);
		});
	}

	checkName(name)
	{
		if (typeof name === 'string')
		{
			name = name.trim();
			if (RoomsManager.isEmpty(name))
			{
				name = Loc.getMessage('EC_SEC_SLIDER_NEW_ROOM');
			}
		}
		else
		{
			name = Loc.getMessage('EC_SEC_SLIDER_NEW_ROOM');
		}
		return name;
	}

	checkCapacity(capacity)
	{
		if (RoomsManager.isEmpty(capacity) || capacity <= 0 || capacity >= 10000)
		{
			return 0;
		}
		return capacity;
	}

	getRoomsInfo()
	{
		const allActive = [];
		const superposed = [];
		const active = [];
		const hidden = [];

		this.rooms.forEach((room) => {
			if (room.isShown() && this.calendarType === 'location' && room.type === 'location')
			{
				if (room.isSuperposed())
				{
					superposed.push(room.id);
				}
				else
				{
					active.push(room.id);
				}
				allActive.push(room.id);
			}
			else
			{
				hidden.push(room.id);
			}
		});

		return { superposed, active, hidden, allActive };
	}

	getRoomName(id)
	{
		if (RoomsManager.isEmpty(id))
		{
			return null;
		}
		const room = this.getRoom(id);
		return room.name;
	}

	unsetHiddenRoom(id)
	{
		if (id)
		{
			const room = this.getRoom(id)
			if (room.calendarContext && !room.isShown())
			{
				room.show();
			}
		}
	}

	handlePullRoomChanges(params)
	{
		if (params.command === 'delete_room')
		{
			const roomId = parseInt(params.ID, 10);
			if (this.roomsIndex[roomId])
			{
				this.deleteRoomHandler(roomId);
				Util.getBX().Event.EventEmitter.emit(
					'BX.Calendar.Rooms:pull-delete',
					new Event.BaseEvent(
						{
							data: { roomId: roomId }
						}
					)
				);
			}
			else
			{
				this.reloadRoomsFromDatabaseDebounce();
			}
		}
		else if (params.command === 'create_room')
		{
			this.reloadRoomsFromDatabase().then(this.reloadDataDebounce());
			Util.getBX().Event.EventEmitter.emit('BX.Calendar.Rooms:pull-create');
			Util.getBX().Event.EventEmitter.emit('BX.Calendar:doRefresh');
		}
		else if (params.command === 'update_room')
		{
			this.reloadRoomsFromDatabase().then(this.reloadDataDebounce());
			Util.getBX().Event.EventEmitter.emit('BX.Calendar.Rooms:pull-update');
			Util.getBX().Event.EventEmitter.emit('BX.Calendar:doRefresh');
		}
		else
		{
			this.reloadRoomsFromDatabase().then(this.reloadDataDebounce());
		}
	}

	deleteRoomHandler(id)
	{
		if (this.roomsIndex[id] !== undefined)
		{
			this.rooms.splice(this.roomsIndex[id], 1);
			for (let i = 0; i < this.rooms.length; i++)
			{
				this.roomsIndex[this.rooms[i].id] = i;
			}
		}
		if (this.sectionIndex[id] !== undefined)
		{
			this.sections.splice(this.sectionIndex[id], 1);
			for (let i = 0; i < this.sections.length; i++)
			{
				this.sectionIndex[this.sections[i].id] = i;
			}
		}
	}

	reloadRoomsFromDatabase()
	{
		return new Promise(resolve => {
			BX.ajax.runAction('calendar.api.locationajax.getRoomsList')
				.then((response) => {
						this.setRooms(response.data.rooms || []);
						this.sortRooms();
						BX.Calendar.Controls.Location.setLocationList(response.data.rooms);
						resolve(response.data);
					},
					// Failure
					(response) => {
						resolve(response.data);
					}
				);
		});
	}

	getLocationAccess()
	{
		return this.locationAccess;
	}

	setLocationSelector(roomList)
	{
		BX.Calendar.Controls.Location.setLocationList(roomList);
		if (this.locationContext !== null)
		{
			this.locationContext.setValues();
		}
	}

	static isEmpty(param)
	{
		if (Type.isArray(param))
		{
			return !param.length;
		}
		return param === null || param === undefined || param === '' || param === [] || param === {};
	}
}