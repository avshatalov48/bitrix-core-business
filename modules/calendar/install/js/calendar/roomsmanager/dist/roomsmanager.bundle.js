this.BX = this.BX || {};
(function (exports,main_core,calendar_util,calendar_sectionmanager,main_core_events) {
	'use strict';

	var RoomsSection = /*#__PURE__*/function (_CalendarSection) {
	  babelHelpers.inherits(RoomsSection, _CalendarSection);

	  function RoomsSection(data) {
	    var _this;

	    babelHelpers.classCallCheck(this, RoomsSection);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(RoomsSection).call(this, data));

	    _this.updateData(data);

	    _this.calendarContext = calendar_util.Util.getCalendarContext(); // this.roomsManager = this.calendarContext.roomsManager;

	    return _this;
	  }

	  babelHelpers.createClass(RoomsSection, [{
	    key: "updateData",
	    value: function updateData(data) {
	      this.data = data || {};
	      this.type = data.CAL_TYPE || '';
	      this.necessity = data.NECESSITY || 'N';
	      this.capacity = parseInt(data.CAPACITY) || 0;
	      this.ownerId = parseInt(data.OWNER_ID) || 0;
	      this.id = parseInt(data.ID);
	      this.location_id = parseInt(data.LOCATION_ID);
	      this.color = this.data.COLOR;
	      this.name = this.data.NAME;
	    }
	  }, {
	    key: "belongsToView",
	    value: function belongsToView() {
	      // const calendarContext = Util.getCalendarContext();
	      // return this.type === calendarContext.getCalendarType()
	      // 	&& this.ownerId === calendarContext.getOwnerId();
	      return true;
	    }
	  }]);
	  return RoomsSection;
	}(calendar_sectionmanager.CalendarSection);

	var RoomsManager = /*#__PURE__*/function (_SectionManager) {
	  babelHelpers.inherits(RoomsManager, _SectionManager);

	  function RoomsManager(data, config) {
	    var _this;

	    babelHelpers.classCallCheck(this, RoomsManager);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(RoomsManager).call(this, data, config));
	    _this.locationAccess = config.locationAccess || false;
	    _this.locationContext = config.locationContext || null;

	    _this.setRooms(data.rooms);

	    _this.setConfig(config);

	    _this.sortRooms();

	    _this.setSections(data.sections);

	    _this.sortSections();

	    main_core_events.EventEmitter.subscribeOnce('BX.Calendar.Rooms:delete', _this.deleteRoomHandler.bind(babelHelpers.assertThisInitialized(_this)));
	    return _this;
	  }

	  babelHelpers.createClass(RoomsManager, [{
	    key: "sortRooms",
	    value: function sortRooms() {
	      var _this2 = this;

	      this.roomsIndex = {};
	      this.rooms = this.rooms.sort(function (a, b) {
	        if (a.name.toLowerCase() > b.name.toLowerCase()) {
	          return 1;
	        }

	        if (a.name.toLowerCase() < b.name.toLowerCase()) {
	          return -1;
	        }

	        return 0;
	      });
	      this.rooms.forEach(function (room, i) {
	        _this2.roomsIndex[room.getId()] = i;
	      });
	    }
	  }, {
	    key: "setRooms",
	    value: function setRooms() {
	      var _this3 = this;

	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
	      this.rooms = [];
	      this.roomsIndex = {};
	      params.forEach(function (roomData) {
	        var room = new RoomsSection(roomData);

	        _this3.rooms.push(room);

	        _this3.roomsIndex[room.getId()] = _this3.rooms.length - 1;
	      });
	    }
	  }, {
	    key: "getRooms",
	    value: function getRooms() {
	      return this.rooms;
	    }
	  }, {
	    key: "getRoom",
	    value: function getRoom(id) {
	      return this.rooms[this.roomsIndex[id]];
	    }
	  }, {
	    key: "createRoom",
	    value: function createRoom(params) {
	      var _this4 = this;

	      return new Promise(function (resolve) {
	        params.name = _this4.checkName(params.name);
	        params.capacity = _this4.checkCapacity(params.capacity);
	        params.necessity = params.necessity && params.capacity !== 0 ? 'Y' : 'N';
	        BX.ajax.runAction('calendar.api.locationajax.createRoom', {
	          data: {
	            name: params.name,
	            capacity: params.capacity,
	            necessity: params.necessity,
	            ownerId: _this4.ownerId,
	            color: params.color,
	            access: params.access || null
	          }
	        }).then(function (response) {
	          var roomList = response.data.rooms || [];
	          var sectionList = response.data.sections || [];

	          _this4.setRooms(roomList);

	          _this4.sortRooms();

	          _this4.setSections(sectionList);

	          _this4.sortSections();

	          calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar.Rooms:create', new main_core.Event.BaseEvent({
	            data: {
	              roomsList: roomList
	            }
	          }));

	          _this4.setLocationSelector(roomList);

	          resolve(response.data);
	        }, function (response) {
	          BX.Calendar.Util.displayError(response.errors);
	          resolve(response.data);
	        });
	      });
	    }
	  }, {
	    key: "updateRoom",
	    value: function updateRoom(params) {
	      var _this5 = this;

	      return new Promise(function (resolve) {
	        params.name = _this5.checkName(params.name);
	        params.capacity = _this5.checkCapacity(params.capacity);
	        params.necessity = params.necessity && params.capacity !== 0 ? 'Y' : 'N';
	        BX.ajax.runAction('calendar.api.locationajax.updateRoom', {
	          data: {
	            id: params.id,
	            location_id: params.location_id,
	            name: params.name,
	            capacity: params.capacity,
	            necessity: params.necessity,
	            color: params.color,
	            access: params.access || null
	          }
	        }).then(function (response) {
	          var roomList = response.data.rooms || [];
	          var sectionList = response.data.sections || [];

	          _this5.setRooms(roomList);

	          _this5.sortRooms();

	          _this5.setSections(sectionList);

	          _this5.sortSections();

	          _this5.unsetHiddenRoom(params.id);

	          calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar.Rooms:update', new main_core.Event.BaseEvent({
	            data: {
	              roomsList: roomList
	            }
	          }));

	          _this5.setLocationSelector(roomList);

	          resolve(response.data);
	        }, function (response) {
	          BX.Calendar.Util.displayError(response.errors);
	          resolve(response.data);
	        });
	      });
	    }
	  }, {
	    key: "deleteRoom",
	    value: function deleteRoom(id, location_id) {
	      var _this6 = this;

	      if (confirm(BX.message('EC_ROOM_DELETE_CONFIRM'))) {
	        var EventAlias = calendar_util.Util.getBX().Event;
	        EventAlias.EventEmitter.emit('BX.Calendar.Section:delete', new EventAlias.BaseEvent({
	          data: {
	            sectionId: id
	          }
	        }));
	        return new Promise(function (resolve) {
	          BX.ajax.runAction('calendar.api.locationajax.deleteRoom', {
	            data: {
	              id: id,
	              location_id: location_id
	            }
	          }).then(function (response) {
	            var roomList = response.data.rooms || [];
	            var sectionList = response.data.sections || [];

	            if (!roomList.length) {
	              BX.reload();
	            }

	            _this6.setRooms(roomList);

	            _this6.sortRooms();

	            _this6.setSections(sectionList);

	            _this6.sortSections();

	            calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar.Rooms:delete', new main_core.Event.BaseEvent({
	              data: {
	                id: id
	              }
	            }));

	            _this6.setLocationSelector(roomList);

	            resolve(response.data);
	          }, function (response) {
	            BX.Calendar.Util.displayError(response.errors);
	            resolve(response.data);
	          });
	        });
	      }
	    }
	  }, {
	    key: "checkName",
	    value: function checkName(name) {
	      if (typeof name === 'string') {
	        name = name.trim();

	        if (RoomsManager.isEmpty(name)) {
	          name = main_core.Loc.getMessage('EC_SEC_SLIDER_NEW_ROOM');
	        }
	      } else {
	        name = main_core.Loc.getMessage('EC_SEC_SLIDER_NEW_ROOM');
	      }

	      return name;
	    }
	  }, {
	    key: "checkCapacity",
	    value: function checkCapacity(capacity) {
	      if (RoomsManager.isEmpty(capacity) || capacity <= 0 || capacity >= 10000) {
	        return 0;
	      }

	      return capacity;
	    }
	  }, {
	    key: "getRoomsInfo",
	    value: function getRoomsInfo() {
	      var _this7 = this;

	      var allActive = [];
	      var superposed = [];
	      var active = [];
	      var hidden = [];
	      this.rooms.forEach(function (room) {
	        if (room.isShown() && _this7.calendarType === 'location' && room.type === 'location') {
	          if (room.isSuperposed()) {
	            superposed.push(room.id);
	          } else {
	            active.push(room.id);
	          }

	          allActive.push(room.id);
	        } else {
	          hidden.push(room.id);
	        }
	      });
	      return {
	        superposed: superposed,
	        active: active,
	        hidden: hidden,
	        allActive: allActive
	      };
	    }
	  }, {
	    key: "getRoomName",
	    value: function getRoomName(id) {
	      if (RoomsManager.isEmpty(id)) {
	        return null;
	      }

	      var room = this.getRoom(id);
	      return room.name;
	    }
	  }, {
	    key: "unsetHiddenRoom",
	    value: function unsetHiddenRoom(id) {
	      if (id) {
	        this.room = this.getRoom(id);

	        if (!this.room.isShown()) {
	          this.room.show();
	        }

	        return null;
	      }
	    }
	  }, {
	    key: "handlePullRoomChanges",
	    value: function handlePullRoomChanges(params) {
	      if (params.command === 'delete_room') {
	        var roomId = parseInt(params.fields.ID, 10);

	        if (this.roomsIndex[roomId]) {
	          this.deleteRoomHandler(roomId);
	          calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar.Rooms:pull-delete', new main_core.Event.BaseEvent({
	            data: {
	              roomId: roomId
	            }
	          }));
	        } else {
	          this.reloadRoomData();
	        }
	      } else if (params.command === 'create_room') {
	        this.reloadRoomData().then(this.reloadData().then(function () {
	          calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar.Rooms:pull-create');
	        }));
	        calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar:doRefresh');
	      } else if (params.command === 'update_room') {
	        this.reloadRoomData().then(this.reloadData().then(function () {
	          calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar.Rooms:pull-update');
	        }));
	        calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar:doRefresh');
	      } else {
	        this.reloadRoomData().then(this.reloadData);
	      }
	    }
	  }, {
	    key: "deleteRoomHandler",
	    value: function deleteRoomHandler(id) {
	      if (this.roomsIndex[id] !== undefined) {
	        this.rooms.splice(this.roomsIndex[id], 1);

	        for (var i = 0; i < this.rooms.length; i++) {
	          this.roomsIndex[this.rooms[i].id] = i;
	        }
	      }

	      if (this.sectionIndex[id] !== undefined) {
	        this.sections.splice(this.sectionIndex[id], 1);

	        for (var _i = 0; _i < this.sections.length; _i++) {
	          this.sectionIndex[this.sections[_i].id] = _i;
	        }
	      }
	    }
	  }, {
	    key: "reloadRoomData",
	    value: function reloadRoomData() {
	      var _this8 = this;

	      return new Promise(function (resolve) {
	        BX.ajax.runAction('calendar.api.locationajax.getRoomsList').then(function (response) {
	          _this8.setRooms(response.data.rooms || []);

	          _this8.sortRooms();

	          BX.Calendar.Controls.Location.setLocationList(response.data.rooms);
	          resolve(response.data);
	        }, // Failure
	        function (response) {
	          resolve(response.data);
	        });
	      });
	    }
	  }, {
	    key: "getLocationAccess",
	    value: function getLocationAccess() {
	      return this.locationAccess;
	    }
	  }, {
	    key: "setLocationSelector",
	    value: function setLocationSelector(roomList) {
	      BX.Calendar.Controls.Location.setLocationList(roomList);

	      if (this.locationContext !== null) {
	        this.locationContext.setValues();
	      }
	    }
	  }], [{
	    key: "isEmpty",
	    value: function isEmpty(param) {
	      if (main_core.Type.isArray(param)) {
	        return !param.length;
	      }

	      return param === null || param === undefined || param === '' || param === [] || param === {};
	    }
	  }]);
	  return RoomsManager;
	}(calendar_sectionmanager.SectionManager);

	exports.RoomsSection = RoomsSection;
	exports.RoomsManager = RoomsManager;

}((this.BX.Calendar = this.BX.Calendar || {}),BX,BX.Calendar,BX.Calendar,BX.Event));
//# sourceMappingURL=roomsmanager.bundle.js.map
