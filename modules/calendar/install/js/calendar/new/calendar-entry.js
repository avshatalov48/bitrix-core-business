;(function(window) {

	function EntryController(calendar)
	{
		this.calendar = calendar;
		this.pulledEntriesIndex = {};
		this.entriesRaw = [];
		this.userIndex = {};
		this.loadedEntriesIndex = {};
		this.externalEntryIndex = {};
		this.movedEntries = [];
		this.sentRequests = [];

		this.REQUEST_GET_LIST = 'getList';
		this.REQUEST_MOVE_EVENT = 'moveEvent';
	}

	EntryController.prototype = {

		isAwaitingAnyResponses: function()
		{
			return this.sentRequests.length > 0;
		},

		getList: function (params)
		{
			return new Promise(async (resolve) => {
				if (this.calendar.isExternalMode())
				{
					const entries = await this.getExternalLoadedList(params);
					resolve(entries);
				}
				else if (this.doesDateRangeContainUnloadedEvents(params.startDate, params.finishDate))
				{
					const entries = await this.getLoadedList(params);
					resolve(entries);
				}
				else
				{
					const entries = this.getCachedList(params);
					resolve(entries);
				}
			});
		},

		getExternalLoadedList: async function(params)
		{
			let entries;
			this.sentRequests.push(this.REQUEST_GET_LIST);
			await this.loadExternalEntries(params).then(() => {
					this.sentRequests.pop();
					entries = this.getEntriesFromEntriesRaw(params.viewRange);
				});

			return entries;
		},

		getLoadedList: async function(params)
		{
			let entries;
			this.sentRequests.push(this.REQUEST_GET_LIST);
			await BX.Calendar.EntryManager.doDelayedActions()
				.then(() => this.loadEntries(params))
				.then((responseData) => {
					this.sentRequests.pop();
					if (responseData.newYearFrom !== undefined && responseData.newMonthFrom !== undefined)
					{
						const previousStart = params.viewRange.start;
						params.viewRange.start = new Date(responseData.newYearFrom, responseData.newMonthFrom - 1, previousStart.getDate());
					}
					if (responseData.newYearTo !== undefined && responseData.newMonthTo !== undefined)
					{
						const previousEnd = params.viewRange.end;
						params.viewRange.end = new Date(responseData.newYearTo, responseData.newMonthTo - 1, previousEnd.getDate());
					}
					entries = this.getEntriesFromEntriesRaw(params.viewRange);
				});

			return entries;
		},

		getCachedList: function(params)
		{
			return this.getEntriesFromEntriesRaw(params.viewRange);
		},

		canDo: function(entry, action)
		{
			if (typeof entry !== 'object' && action === 'add_event')
			{
				if(this.calendar.util.type === 'location')
				{
					return true;
				}
				return !this.calendar.util.readOnlyMode();
			}

			if ((action === 'edit' || action === 'delete') && !this.calendar.util.readOnlyMode())
			{
				if ((entry.isMeeting() && entry.id !== entry.parentId)
				|| entry.isResourcebooking())
				{
					return false;
				}

				var section = this.calendar.sectionManager.getSection(entry.sectionId);
				return section && section.canDo && section.canDo('edit');
			}
			return false;
		},

		getUsableDateTime: function(timestamp, roundMin)
		{
			if (typeof timestamp == 'object' && timestamp.getTime)
				timestamp = timestamp.getTime();

			var r = (roundMin || 10) * 60 * 1000;
			timestamp = Math.ceil(timestamp / r) * r;
			return new Date(timestamp);
		},

		getTimeForNewEntry: function(date)
		{
			date = this.getUsableDateTime(date);

			return {
				from : date,
				to : new Date(date.getTime() + 3600000)
			};
		},

		getDefaultEntryName: function()
		{
			return this.calendar.newEntryName || BX.message('EC_DEFAULT_ENTRY_NAME');
		},

		moveEventToNewDate: function(entry, dateFrom, dateTo, params = {})
		{
			entry = this.setDateRangeToEntry(entry, dateFrom, dateTo);
			this.addMovedEntry(entry);

			if (this.calendar.isExternalMode())
			{
				this.calendar.triggerEvent('entryOnDragEnd', {
					entry: entry,
					dateFrom: entry.from,
					dateTo: entry.to
				});
				return new Promise((resolve) => {
					resolve(false);
				});
			}

			if (entry.isMeeting()
				&& params.sendInvitesAgain === undefined
				&& entry.getAttendees().find(item => item.STATUS === 'N')
			)
			{
				return new Promise((resolve) => {
					BX.Calendar.EntryManager.showReInviteUsersDialog({
						callback: (result) => {
							this.moveEventToNewDate(entry, dateFrom, dateTo, {
								sendInvitesAgain: result.sendInvitesAgain
							}).then((isEntrySavedSuccessfully) => {
								resolve(isEntrySavedSuccessfully)
							});
						}
					});
				});
			}

			this.sentRequests.push(this.REQUEST_MOVE_EVENT);
			return new Promise((resolve) => {
				BX.ajax.runAction('calendar.api.calendarentryajax.moveEvent', {
					data: {
						id: entry.id,
						current_date_from: entry.data.DATE_FROM,
						date_from: entry.isFullDay() ? this.calendar.util.formatDate(entry.from) : this.calendar.util.formatDateTime(entry.from),
						date_to: entry.isFullDay() ? this.calendar.util.formatDate(entry.to) : this.calendar.util.formatDateTime(entry.to),
						skip_time: entry.isFullDay() ? 'Y' : 'N',
						attendees: this.getEntryAttendeesIds(entry),
						location: entry.location || '',
						recursive: entry.isRecursive() ? 'Y' : 'N',
						is_meeting: entry.isMeeting() ? 'Y' : 'N',
						section: entry.sectionId,
						timezone: this.calendar.util.getUserOption('timezoneName'), //timezone
						set_timezone: 'Y',
						sendInvitesAgain: params.sendInvitesAgain ? 'Y' : 'N',
						requestUid: BX.Calendar.Util.registerRequestId()
					}
				}).then((response) => {
					this.sentRequests.pop();

					let isEntrySavedSuccessfully = true;
					if (entry.isMeeting() && response.data.busy_warning)
					{
						alert(BX.message('EC_BUSY_ALERT'));
						isEntrySavedSuccessfully = false;
					}

					if (response.data.location_busy_warning)
					{
						alert(BX.message('EC_LOCATION_RESERVE_ERROR'));
						isEntrySavedSuccessfully = false;
					}

					if (!isEntrySavedSuccessfully)
					{
						this.removeMovedEntry(entry);
					}

					this.calendar.reload();
					resolve(isEntrySavedSuccessfully);
				});
			});
		},

		setDateRangeToEntry: function(entry, dateFrom, dateTo)
		{
			entry.from.setFullYear(dateFrom.getFullYear(), dateFrom.getMonth(), dateFrom.getDate());
			if (entry.fullDay)
			{
				entry.from.setHours(dateFrom.getHours(), dateFrom.getMinutes(), 0, 0);
			}

			if (dateTo && BX.type.isDate(dateTo))
			{
				entry.to.setFullYear(dateTo.getFullYear(), dateTo.getMonth(), dateTo.getDate());
				if (entry.fullDay)
				{
					entry.to.setHours(dateTo.getHours(), dateTo.getMinutes(), 0, 0);
				}
			}
			else
			{
				entry.to = new Date(entry.from.getTime() + (entry.data.DT_LENGTH - (entry.fullDay ? 1 : 0)) * 1000);
			}
			return entry;
		},

		getEntryAttendeesIds: function(entry)
		{
			const attendees = [];
			if (entry.isMeeting())
			{
				entry.data['ATTENDEE_LIST'].forEach((user) => {
					attendees.push(user['id']);
				});
			}
			return attendees;
		},

		viewEntry: function(params)
		{
			this.calendar.getView().showViewSlider(params);
		},

		editEntry: function(params)
		{
			this.calendar.getView().showEditSlider(params);
		},

		doesDateRangeContainUnloadedEvents: function(dateStart, dateEnd)
		{
			if (this.calendar.isExternalMode())
			{
				return this.externalEntryIndex[this.getChunkIdByDate(dateStart)]
					&& this.externalEntryIndex[this.getChunkIdByDate(dateEnd)];
			}

			const activeSections = this.getSections().allActive;
			for (const sectionId of activeSections)
			{
				if (!this.pulledEntriesIndex[sectionId]
					|| !this.pulledEntriesIndex[sectionId][this.getChunkIdByDate(dateStart)]
					|| !this.pulledEntriesIndex[sectionId][this.getChunkIdByDate(dateEnd)]
				)
				{
					return true;
				}
			}

			return false;
		},

		fillChunkIndex: function(startDate, finishDate, params)
		{
			params = BX.Type.isObjectLike(params) ? params : {};

			if (!this.loadedStartDate)
				this.loadedStartDate = startDate;
			else if (startDate.getTime() < this.loadedStartDate.getTime())
				this.loadedStartDate = startDate;

			if (!this.loadedFinishDate)
				this.loadedFinishDate = finishDate;
			else if (finishDate.getTime() > this.loadedFinishDate.getTime())
				this.loadedFinishDate = finishDate;

			var date = new Date();
			var iter = 0;
			date.setFullYear(startDate.getFullYear(), startDate.getMonth(), 1);
			var lastChunkId = this.getChunkIdByDate(finishDate);
			var chunkId = this.getChunkIdByDate(date);
			var value = params.value === undefined ? true : params.value;

			if (this.calendar.isExternalMode())
			{
				this.externalEntryIndex[chunkId] = value;
				this.externalEntryIndex[lastChunkId] = value;
				while (chunkId !== lastChunkId && iter < 100)
				{
					this.externalEntryIndex[chunkId] = value;
					date.setMonth(date.getMonth() + 1);
					chunkId = this.getChunkIdByDate(date);
					iter++;
				}
			}
			else
			{
				if(this.calendar.util.type === 'location')
				{
					params.sections = params.sections || this.calendar.roomsManager.getRoomsInfo().allActive;
				}
				else
				{
					params.sections = params.sections || this.calendar.sectionManager.getSectionsInfo().allActive;
				}
				params.index = params.index || this.pulledEntriesIndex;

				var index = params.index;
				params.sections.forEach(function(sectinId)
				{
					if (!index[sectinId])
					{
						index[sectinId] = {};
					}

					index[sectinId][chunkId] = value;
					index[sectinId][lastChunkId] = value;
				});

				while (chunkId !== lastChunkId && iter < 100)
				{
					params.sections.forEach(function(sectinId)
					{
						index[sectinId][chunkId] = value;
					});

					date.setMonth(date.getMonth() + 1);
					chunkId = this.getChunkIdByDate(date);
					iter++;
				}
			}
		},

		getChunkIdByDate: function(date)
		{
			return date.getFullYear() + '-' + (date.getMonth() + 1);
		},

		getLoadedEntiesLimits: function()
		{
			return {start: this.loadedStartDate, end: this.loadedFinishDate};
		},

		loadEntries: function(params)
		{
			return new Promise((resolve) => {
				const sections = this.getSections();
				BX.ajax.runAction('calendar.api.calendarentryajax.loadEntries', {
					data: {
						ownerId: this.calendar.util.ownerId,
						type: this.calendar.util.type,
						month_from: params.startDate ? (params.startDate.getMonth() + 1) : '',
						year_from: params.startDate ? params.startDate.getFullYear() : '',
						month_to: params.finishDate ? params.finishDate.getMonth() + 1 : '',
						year_to: params.finishDate ? params.finishDate.getFullYear() : '',
						active_sect: sections.active,
						sup_sect: sections.superposed,
						direction: params.direction ?? '',
					}
				}).then((response) => {
					this.appendToEntriesRaw(response.data.entries);
					this.updateUserIndex(response.data.userIndex);

					if (response.data.newYearFrom !== undefined && response.data.newMonthFrom !== undefined)
					{
						const previousStart = params.startDate;
						params.startDate = new Date(response.data.newYearFrom, response.data.newMonthFrom - 1, previousStart.getDate());
					}

					if (response.data.newYearTo !== undefined && response.data.newMonthTo !== undefined)
					{
						const previousFinish = params.finishDate;
						params.finishDate = new Date(response.data.newYearTo, response.data.newMonthTo - 1, previousFinish.getDate());
					}

					this.fillChunkIndex(params.startDate, params.finishDate, {
						sections: sections.allActive
					});

					BX.Event.EventEmitter.emit('BX.Calendar:onEntryListReload', {
						isBoundaryOfPastReached: response.data.isBoundaryOfPastReached,
						isBoundaryOfFutureReached: response.data.isBoundaryOfFutureReached,
					});
					resolve(response.data);
				});
			});
		},

		loadExternalEntries: function (params)
		{
			if (params.showLoader)
			{
				this.calendar.showLoader();
			}

			return new Promise((resolve) => {
				this.calendar.triggerEvent('loadEntries',
					{
						params: params,
						onLoadCallback : function(json)
						{
							this.calendar.hideLoader();
							this.appendToEntriesRaw(json.entries);

							if (!params.finishDate && this.entriesRaw.length > 0)
							{
								var finishDate = this.entriesRaw[this.entriesRaw.length - 1].DATE_FROM;
								finishDate = BX.parseDate(finishDate);
								if (finishDate)
								{
									finishDate.setFullYear(finishDate.getFullYear(), finishDate.getMonth(), 0);
									params.finishDate = finishDate;
								}
							}

							if (params.startDate && params.finishDate)
							{
								this.fillChunkIndex(params.startDate, params.finishDate);
							}

							if (BX.type.isFunction(params.finishCallback))
							{
								params.finishCallback(json);
							}

							resolve();
						}.bind(this),
						onErrorCallback : function(error)
						{
							this.calendar.hideLoader();
						}.bind(this)
					});
			});
		},

		appendToEntriesRaw: function(entries)
		{
			const showDeclined = this.calendar.util.getUserOption('showDeclined');
			for (const entry of entries)
			{
				if (
					(!showDeclined || parseInt(entry.CREATED_BY) !== this.calendar.util.userId)
					&& entry.MEETING_STATUS === 'N'
				)
				{
					continue;
				}
				const smartId = this.getUniqueId(entry);
				if (this.loadedEntriesIndex[smartId] === undefined)
				{
					this.entriesRaw.push(entry);
					this.loadedEntriesIndex[smartId] = this.entriesRaw.length - 1;
				}
				else if (entry.CAL_TYPE === this.calendar.util.type
					&& parseInt(entry.OWNER_ID) === parseInt(this.calendar.util.ownerId)
				)
				{
					this.entriesRaw[this.loadedEntriesIndex[smartId]] = entry;
				}
			}
		},

		updateUserIndex: function(userIndex)
		{
			if (!BX.type.isNotEmptyObject(userIndex))
			{
				return;
			}
			for (const id in userIndex)
			{
				if (userIndex.hasOwnProperty(id))
				{
					this.userIndex[id] = userIndex[id];
				}
			}
			BX.Calendar.EntryManager.setUserIndex(this.userIndex);
		},

		getEntriesFromEntriesRaw: function(viewRange)
		{
			const entries = [];
			const activeSectionIndex = this.getActiveSectionsIndex();
			for (const entryRaw of this.entriesRaw)
			{
				if ((entryRaw['~TYPE'] === 'tasks' && !activeSectionIndex['tasks'])
					|| (entryRaw['~TYPE'] !== 'tasks' && entryRaw['SECT_ID'] && !activeSectionIndex[parseInt(entryRaw['SECT_ID'])])
				)
				{
					continue;
				}

				const movedEntry = this.findMovedEntry(entryRaw);
				if (movedEntry)
				{
					if (entryRaw.DATE_FROM === movedEntry.DATE_FROM && entryRaw.DATE_TO === movedEntry.DATE_TO && parseInt(entryRaw.DT_LENGTH) === movedEntry.DT_LENGTH)
					{
						this.movedEntries = this.movedEntries.filter(e => e.ID !== movedEntry.ID);
					}
					else
					{
						entryRaw.DATE_FROM = movedEntry.DATE_FROM;
						entryRaw.DATE_TO = movedEntry.DATE_TO;
						entryRaw.DT_LENGTH = movedEntry.DT_LENGTH;
					}
				}

				const entry = new Entry(this.calendar, entryRaw);
				if (!viewRange || viewRange && entry.applyViewRange(viewRange))
				{
					entries.push(entry);
				}
			}
			return entries;
		},

		addMovedEntry: function(entry)
		{
			const formattedDateFrom = entry.isFullDay() ? this.calendar.util.formatDate(entry.from) : this.calendar.util.formatDateTime(entry.from);
			const formattedDateTo = entry.isFullDay() ? this.calendar.util.formatDate(entry.to) : this.calendar.util.formatDateTime(entry.to);

			let duration = (entry.to.getTime() - entry.from.getTime()) / 1000;
			if (entry.fullDay)
			{
				duration += 86400;
			}

			this.movedEntries = this.movedEntries.filter(e => e.ID !== entry.uid);
			this.movedEntries.push({
				ID: entry.uid,
				RECURRENCE_ID: entry.parentId,
				ORIGINAL_DATE_FROM: this.calendar.util.formatDateTime(BX.parseDate(entry.data.DATE_FROM)),
				DATE_FROM: formattedDateFrom,
				DATE_TO: formattedDateTo,
				DT_LENGTH: duration,
			});
		},

		removeMovedEntry: function(entry)
		{
			this.movedEntries = this.movedEntries.filter(e => e.ID !== entry.uid);
		},

		findMovedEntry: function(entryRaw)
		{
			return this.movedEntries.filter((e) => {
				const isRecursive = !!entryRaw.RRULE;

				if (isRecursive)
				{
					return entryRaw.RECURRENCE_ID === e.RECURRENCE_ID && entryRaw.ORIGINAL_DATE_FROM === e.ORIGINAL_DATE_FROM;
				}

				return entryRaw.ID === e.ID;
			})[0];
		},

		findMovedEntryById: function(id)
		{
			return this.movedEntries.filter((e) => {
				return id === e.ID;
			})[0];
		},

		getActiveSectionsIndex: function()
		{
			const activeSectionIndex = {};
			this.getSections().allActive.forEach((sectionId) => {
				activeSectionIndex[sectionId === 'tasks' ? sectionId : parseInt(sectionId)] = true;
			});
			return activeSectionIndex;
		},

		getSections()
		{
			if (this.calendar.util.type === 'location')
			{
				return this.calendar.roomsManager.getRoomsInfo();
			}
			return this.calendar.sectionManager.getSectionsInfo();
		},

		getUniqueId: function(entryData, entry)
		{
			var sid = entryData.PARENT_ID || entryData.ID;
			if (entryData.RRULE)
			{
				sid += '|' + (entry ? this.calendar.util.formatDate(entry.from) : this.calendar.util.formatDate(BX.parseDate(entryData.DATE_FROM)));
			}

			if (entryData['~TYPE'] === 'tasks')
			{
				sid += '|' + 'task';
			}
			return sid;
		},

		sort: function(a, b)
		{
			if (a.entry.isTask() && !b.entry.isTask())
			{
				 return 1;
			}
			if (!a.entry.isTask() && b.entry.isTask())
			{
				return -1;
			}

			if (a.part.daysCount === b.part.daysCount)
			{
				return a.entry.from.getTime() - b.entry.from.getTime();
			}

			return a.part.daysCount - b.part.daysCount;
		},

		clearLoadIndexCache: function()
		{
			this.pulledEntriesIndex = {};
			this.entriesRaw = [];
			this.loadedEntriesIndex = {};
			this.externalEntryIndex = {};
		},

		checkMeetingByCodes: function(codes)
		{
			var code, n = 0;
			if (codes)
			{
				for (code in codes)
				{
					if (codes.hasOwnProperty(code))
					{
						if (codes[code] != 'users' || n > 0)
						{
							return true;
						}
						n++;
					}
				}
			}
			return false;
		},

		getUserIndex: function()
		{
			return this.userIndex;
		}
	};

	function Entry(calendar, data)
	{
		this.calendar = calendar;
		this.data = data;
		this.id = data.ID || 0;

		if (!this.data.DT_SKIP_TIME)
		{
			this.data.DT_SKIP_TIME = this.data.SKIP_TIME ? 'Y' : 'N';
		}

		if (!BX.Type.isString(this.data.NAME))
		{
			this.data.NAME = BX.message('EC_DEFAULT_ENTRY_NAME');
		}
		else
		{
			this.data.NAME = this.data.NAME.replaceAll(/\r\n|\r|\n/g, ' ');
		}

		this.fullDay = data.DT_SKIP_TIME === 'Y';
		this.parentId = data.PARENT_ID || 0;
		this.accessibility = data.ACCESSIBILITY;
		this.important = data.IMPORTANCE === 'high';
		this.private = !!data.PRIVATE_EVENT;
		this.sectionId = this.isTask() ? 'tasks' : parseInt(data.SECT_ID);
		this.name = this.isLocation()
			? this.calendar.roomsManager.getRoomName(data.SECT_ID) + ': ' + data.NAME
			: data.NAME
		;

		this.parts = [];

		var
			_this = this,
			util = this.calendar.util,
			startDayCode, endDayCode,
			color = data.COLOR || (this.isLocation()
				? this.calendar.roomsManager.getRoom(this.sectionId).color
				: this.calendar.sectionManager.getSection(this.sectionId).color)
			;

		Object.defineProperties(this, {
			startDayCode: {
				get: function(){return startDayCode;},
				set: function(value){startDayCode = util.getDayCode(value);}
			},
			endDayCode: {
				get: function(){return endDayCode;},
				set: function(value){endDayCode = util.getDayCode(value);}
			},
			color: {
				get: function(){return color;},
				set: function(value){color = value;}
			},
			location: {
				value: data.LOCATION,
				writable: true,
				enumerable : true
			}
		});

		this.prepareData();

		this.uid = this.calendar.entryController.getUniqueId(data, this);
	}

	Entry.prototype = {
		prepareData: function()
		{
			if (!this.data.DT_LENGTH)
			{
				this.data.DT_LENGTH = this.data.DURATION || 0;
			}
			if (this.fullDay && !this.data.DT_LENGTH)
			{
				this.data.DT_LENGTH = 86400;
			}


			if (this.isTask())
			{
				this.from = BX.parseDate(this.data.DATE_FROM) || new Date();
				this.to = BX.parseDate(this.data.DATE_TO) || this.from;
			}
			else
			{
				this.from = BX.parseDate(this.data.DATE_FROM) || new Date();
				if (this.fullDay)
				{
					this.from.setHours(0, 0, 0, 0);
				}

				if (this.data.DT_SKIP_TIME !== "Y")
				{
					this.from = new Date(this.from.getTime() - (parseInt(this.data['~USER_OFFSET_FROM']) || 0) * 1000);
				}

				if (this.fullDay)
				{
					// For all-day events we calculate finish date by subtracting one hour + one second
					// 3601 - one hour + one second. To avoid collisions on timezones with season time (mantis #97261)
					// TODO: find better way to calculate from/to dates
					this.to = new Date(this.from.getTime() + (this.data.DT_LENGTH - 3601) * 1000);
					this.to.setHours(0, 0, 0, 0);
				}
				else
				{
					this.to = new Date(this.from.getTime() + this.data.DT_LENGTH * 1000);
				}
			}

			if (!this.data.ATTENDEES_CODES && !this.isTask())
			{
				if (this.data.CAL_TYPE === 'user')
				{
					this.data.ATTENDEES_CODES = ['U' + this.data.OWNER_ID];
				}
				else
				{
					this.data.ATTENDEES_CODES = ['U' + this.data.CREATED_BY];
				}
			}

			this.startDayCode = this.from;
			this.endDayCode = this.to;
		},

		getAttendeesCodes: function()
		{
			return this.data.ATTENDEES_CODES;
		},

		getAttendees: function()
		{
			if (!this.attendeeList && BX.type.isArray(this.data['ATTENDEE_LIST']))
			{
				this.attendeeList = [];
				var userIndex = this.calendar.entryController.getUserIndex();

				this.data['ATTENDEE_LIST'].forEach(function(user)
				{
					if (userIndex[user.id])
					{
						var attendee = BX.clone(userIndex[user.id]);
						attendee.STATUS = user.status;
						attendee.ENTRY_ID = user.entryId;
						this.attendeeList.push(attendee);
					}
				}, this);
			}
			return this.attendeeList || [];
		},

		cleanParts: function()
		{
			this.parts = [];
		},

		startPart: function(part)
		{
			part.partIndex = this.parts.length;
			this.parts.push(part);
			return this.parts[part.partIndex];
		},

		registerPartNode: function(part, params)
		{
			part.params = params;
		},

		checkPartIsRegistered: function(part)
		{
			return BX.type.isPlainObject(part.params);
		},

		getPart: function(partIndex)
		{
			return this.parts[partIndex] || false;
		},

		getWrap: function(partIndex)
		{
			return this.parts[partIndex || 0].params.wrapNode;
		},

		getSectionName: function()
		{
			return this.calendar.sectionManager.getSection(this.sectionId).name || '';
		},

		getDescription: function()
		{
			return this.data.DESCRIPTION || '';
		},

		applyViewRange: function(viewRange)
		{
			var
				viewRangeStart = viewRange.start.getTime(),
				viewRangeEnd = viewRange.end.getTime(),
				fromTime = this.from.getTime(),
				toTime = this.to.getTime();

			if (toTime < viewRangeStart || fromTime > viewRangeEnd)
			{
				return false;
			}

			if (fromTime < viewRangeStart)
			{
				this.displayFrom = viewRange.start;
				this.startDayCode = this.displayFrom;
			}

			if (toTime > viewRangeEnd)
			{
				this.displayTo = viewRange.end;
				this.endDayCode = this.displayTo;
			}
			return true;
		},

		isPersonal: function()
		{
			return (this.data.CAL_TYPE === 'user' && this.data.OWNER_ID == this.calendar.util.userId);
		},

		isMeeting: function()
		{
			return !!this.data.IS_MEETING;
		},

		isResourcebooking: function()
		{
			return this.data.EVENT_TYPE === '#resourcebooking#';
		},

		isTask: function()
		{
			return this.data['~TYPE'] === 'tasks';
		},

		isSharingEvent: function()
		{
			return this.data['EVENT_TYPE'] === '#shared#' || this.data['EVENT_TYPE'] === '#shared_crm#';
		},

		isInvited: function()
		{
			return this.getCurrentStatus() === 'Q';
		},

		isLocation: function()
		{
			return this.data.CAL_TYPE === 'location';
		},

		isFullDay: function()
		{
			return this.fullDay;
		},

		isLongWithTime: function()
		{
			return !this.fullDay && this.calendar.util.getDayCode(this.from) !== this.calendar.util.getDayCode(this.to);
		},

		isExpired: function()
		{
			return this.to.getTime() < new Date().getTime();
		},

		hasEmailAttendees: function()
		{
			if (this.emailAttendeesCache === undefined && BX.type.isArray(this.data['ATTENDEE_LIST']))
			{
				var userIndex = BX.Calendar.EntryManager.getUserIndex();
				var user;
				for (var i = 0; i < this.data['ATTENDEE_LIST'].length; i++)
				{
					user = this.data['ATTENDEE_LIST'][i];
					if ((user.status === 'Y' || user.status === 'Q')
						&& userIndex[user.id]
						&& userIndex[user.id].EMAIL_USER
					)
					{
						this.emailAttendeesCache = true;
						break;
					}
				}
			}
			return this.emailAttendeesCache;
		},

		ownerIsEmailUser: function()
		{
			if (this.ownerIsEmailUserCache === undefined)
			{
				var userIndex = BX.Calendar.EntryManager.getUserIndex();
				this.ownerIsEmailUserCache = userIndex[parseInt(this.data.MEETING_HOST)]
						&& userIndex[parseInt(this.data.MEETING_HOST)].EMAIL_USER;
			}
			return this.ownerIsEmailUserCache;
		},

		isSelected: function()
		{
			return !!this.selected;
		},

		isCrm: function()
		{
			return !!this.data.UF_CRM_CAL_EVENT;
		},

		isFirstReccurentEntry: function()
		{
			return (this.data.DATE_FROM_TS_UTC === Math.floor(BX.parseDate(this.data['~DATE_FROM']).getTime() / 1000) * 1000
				||
				BX.parseDate(this.data['DATE_FROM']).getTime() === BX.parseDate(this.data['~DATE_FROM']).getTime()
				) && !this.data.RECURRENCE_ID;
		},

		isRecursive: function()
		{
			return !!this.data.RRULE;
		},

		getMeetingHost: function()
		{
			return parseInt(this.data.MEETING_HOST);
		},

		getRrule: function()
		{
			return this.data.RRULE;
		},

		hasRecurrenceId: function()
		{
			return this.data.RECURRENCE_ID;
		},

		wasEverRecursive: function()
		{
			return this.data.RRULE || this.data.RECURRENCE_ID;
		},

		deselect: function()
		{
			this.selected = false;
		},

		select: function()
		{
			this.selected = true;
		},

		getUniqueId: function()
		{
			var sid = this.data.PARENT_ID || this.data.PARENT_ID;
			if (this.isRecursive())
				sid += '|' + this.data.DT_FROM_TS;

			if (this.data['~TYPE'] == 'tasks')
				sid += '|' + 'task';

			return sid;
		},

		getCurrentStatus: function()
		{
			var i, user, status = false;
			if (this.isMeeting())
			{
				if (this.calendar.util.userId == this.data.CREATED_BY
					||
					this.calendar.util.userId == this.data.MEETING_HOST
				)
				{
					status = this.data.MEETING_STATUS;
				}
				else if (this.calendar.util.userId == this.data.MEETING_HOST)
				{
					status = this.data.MEETING_STATUS;
				}
				else if (BX.type.isArray(this.data['ATTENDEE_LIST']))
				{
					for (i = 0; i < this.data['ATTENDEE_LIST'].length; i++)
					{
						user = this.data['ATTENDEE_LIST'][i];
						if (this.data['ATTENDEE_LIST'][i].id == this.calendar.util.userId)
						{
							status = this.data['ATTENDEE_LIST'][i].status;
							break;
						}
					}
				}
			}
			return status;
		},

		getReminders: function()
		{
			var res = [];
			if (this.data && this.data.REMIND)
			{
				this.data.REMIND.forEach(function (remind)
				{
					if (remind.type == 'min')
					{
						res.push(remind.count);
					}
					else if (remind.type == 'hour')
					{
						res.push(parseInt(remind.count) * 60);
					}
					if (remind.type == 'day')
					{
						res.push(parseInt(remind.count) * 60 * 24);
					}
				});
			}
			return res;
		},

		getLengthInDays: function()
		{
			var
				from = new Date(this.from.getFullYear(), this.from.getMonth(), this.from.getDate(), 0, 0, 0),
				to = new Date(this.to.getFullYear(), this.to.getMonth(), this.to.getDate(), 0, 0, 0);

			return Math.round((to.getTime() - from.getTime()) / this.calendar.util.dayLength) + 1;
		},

		getColor: function()
		{
			return this.color;
		}
	};

	if (window.BXEventCalendar)
	{
		window.BXEventCalendar.Entry = Entry;
		window.BXEventCalendar.EntryController = EntryController;
	}
	else
	{
		BX.addCustomEvent(window, "onBXEventCalendarInit", function()
		{
			window.BXEventCalendar.Entry = Entry;
			window.BXEventCalendar.EntryController = EntryController;
		});
	}
})(window);
