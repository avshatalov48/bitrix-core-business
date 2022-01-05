;(function(window) {

	function SectionController(calendar, data, config)
	{
		if (!data.sections)
			data.sections = [];

		this.calendar = calendar;
		this.sections = [];
		this.sectionIndex = {};
		this.hiddenSections = config.hiddenSections || [];

		this.prepareData({sections: data.sections});

		if (this.calendar.showTasks)
		{
			var taskSection = new TaskSection(this.calendar, config.sectionCustomization['tasks'+this.calendar.util.ownerId]);
			this.sections.push(taskSection);
			this.sectionIndex[taskSection.id] = this.sections.length - 1;
		}

		BX.addCustomEvent("BXCalendar:onSectionDelete", BX.proxy(this.unsetSectionHandler, this));
		this.sortSections();
	}

	SectionController.prototype = {
		prepareData: function (params)
		{
			var i, section;

			for (i = 0; i < params.sections.length; i++)
			{
				section = new Section(this.calendar, params.sections[i]);
				this.sections.push(section);
				this.sectionIndex[section.id] = this.sections.length - 1;
			}
		},

		sortSections: function()
		{
			var i;
			this.sectionIndex = {};
			this.sections = this.sections.sort(function(a, b)
			{
				if (BX.type.isFunction(a.isPseudo) && a.isPseudo())
				{
					return 1;
				}
				else if (BX.type.isFunction(b.isPseudo) && b.isPseudo())
				{
					return -1;
				}
				return a.name.localeCompare(b.name);
			});

			for (i = 0; i < this.sections.length; i++)
			{
				this.sectionIndex[this.sections[i].id] = i;
			}
		},

		getCurrentSection: function()
		{
			var
				section = false,
				i,
				lastUsed = this.calendar.util.getUserOption('lastUsedSection');

			if (lastUsed)
			{
				section = this.getSection(lastUsed);
				if (!section || !section.name
					|| !section.canDo('add')
					//|| !section.belongsToView()
					|| section.isPseudo()
					|| !section.isActive())
				{
					section = false;
				}
			}

			if (!section)
			{
				for (i = 0; i < this.sections.length; i++)
				{
					if (this.sections[i].canDo('add')
						&& this.sections[i].belongsToView()
						&& !this.sections[i].isPseudo()
						&& this.sections[i].isActive())
					{
						section = this.sections[i];
						break;
					}
				}
			}

			if (!section && this.calendar.isExternalMode() && this.sections.length > 0)
			{
				section = this.sections[0];
			}

			return section;
		},

		getSectionList: function()
		{
			var i, result = [];
			for (i = 0; i < this.sections.length; i++)
			{
				this.sections[i].id = parseInt(this.sections[i].id);
				if (this.sections[i].canDo('view_event') && this.sections[i].isActive())
				{
					result.push(this.sections[i]);
				}
			}
			return result;
		},

		getSuperposedSectionList: function()
		{
			var i, result = [];
			for (i = 0; i < this.sections.length; i++)
			{
				if (this.sections[i].canDo('view_event')
					&& this.sections[i].isSuperposed()
					&& this.sections[i].isActive())
				{
					result.push(this.sections[i]);
				}
			}
			return result;
		},

		getSectionListForEdit: function()
		{
			var i, result = [];
			for (i = 0; i < this.sections.length; i++)
			{
				if (this.sections[i].canDo('add')
					//&& (!this.sections[i].isSuperposed() || this.sections[i].belongsToView())
					&& !this.sections[i].isPseudo()
					&& this.sections[i].isActive())
				{
					result.push(this.sections[i]);
				}
			}
			return result;
		},

		getSectionGroupList: function()
		{
			if (!this.sectionGroups)
			{
				this.sectionGroups = [];
				var title;
				// 1. Main group - depends from current view
				if (this.calendar.util.type === 'user')
				{
					title = BX.message('EC_SEC_SLIDER_MY_CALENDARS_LIST');
				}
				else if (this.calendar.util.type === 'group')
				{
					title = BX.message('EC_SEC_SLIDER_GROUP_CALENDARS_LIST');
				}
				else if (this.calendar.util.type === 'location')
				{
					title = BX.message('EC_SEC_SLIDER_TYPE_LOCATION_LIST');
				}
				else if (this.calendar.util.type === 'resource')
				{
					title = BX.message('EC_SEC_SLIDER_TYPE_RESOURCE_LIST');
				}
				else
				{
					title = BX.message('EC_SEC_SLIDER_TITLE_COMP_CAL');
				}
				this.sectionGroups.push({
					title: title,
					type: this.calendar.util.type,
					belongsToView: true
				});

				// 2. Company calendar
				this.sectionGroups.push({
					title: BX.message('EC_SEC_SLIDER_TITLE_COMP_CAL'),
					type: 'company'
				});

				// 3. Users calendars
				this.calendar.util.getSuperposedTrackedUsers().forEach(function(user)
				{
					this.sectionGroups.push({
						title: BX.util.htmlspecialchars(user.FORMATTED_NAME),
						type: 'user',
						ownerId: parseInt(user.ID)
					});
				}, this);

				// 4. Groups calendars
				this.sectionGroups.push({
					title: BX.message('EC_SEC_SLIDER_POPUP_MENU_ADD_GROUP'),
					type: 'group'
				});

				// 5. Resources calendars
				this.sectionGroups.push({
					title: BX.message('EC_SEC_SLIDER_TITLE_RESOURCE_CAL'),
					type: 'resource'
				});

				// 6. Location calendars
				this.sectionGroups.push({
					title: BX.message('EC_SEC_SLIDER_TITLE_LOCATION_CAL'),
					type: 'location'
				});
			}

			return this.sectionGroups;
		},

		getSection: function(id)
		{
			return this.sections[this.sectionIndex[id]] || {};
		},

		getDefaultSectionName: function()
		{
			return BX.message('EC_DEFAULT_SECTION_NAME');
		},

		getDefaultSectionColor: function()
		{
			var
				sectionList = this.getSectionListForEdit(),
				usedColors = {}, i, color,
				defaultColors = this.calendar.util.getDefaultColors();

			for (i = 0; i < sectionList.length; i++)
			{
				usedColors[sectionList[i].color] = true;
			}

			for (i = 0; i < defaultColors.length; i++)
			{
				color = defaultColors[i];
				if (!usedColors[color])
				{
					return color;
				}
			}

			return defaultColors[this.calendar.util.randomInt(0, defaultColors.length)];
		},

		getDefaultSectionAccess: function()
		{
			return this.calendar.util.config.new_section_access || {};
		},

		saveSection: function(name, color, access, params)
		{
			var promise = new BX.Promise();
			name = BX.util.trim(name) || BX.message('EC_SEC_SLIDER_NEW_SECTION');

			if (params.section.id)
			{
				BX.onCustomEvent(this.calendar, 'BXCalendar:onSectionChange', [
					params.section.id,
					{
						name: name,
						color: color
					}]);
			}
			else
			{
				BX.onCustomEvent(this.calendar, 'BXCalendar:onSectionAddBefore', [{
					name: name,
					color: color
				}]);
			}

			var isCustomization = params.section.id && params.section.isPseudo();
			BX.ajax.runAction('calendar.api.calendarajax.editCalendarSection', {
					data: {
						analyticsLabel: {
							action: params.section.id ? 'editSection' : 'newSection',
							type: params.section.type || this.calendar.util.type
						},
						id: params.section.id || 0,
						name: name,
						type: params.section.type || this.calendar.util.type,
						ownerId: params.section.ownerId || this.calendar.util.ownerId,
						color: color,
						access: access || null,
						userId: this.calendar.util.userId,
						customization: isCustomization ? 'Y' : 'N'
					}
				})
				.then(
					// Success
					BX.delegate(function (response)
					{
						if (params.section.id && params.section.color.toLowerCase() !== color.toLowerCase())
						{
							this.calendar.reload();
						}

						if (isCustomization)
						{
							this.sections[this.sectionIndex[params.section.id]].updateData({NAME: name, COLOR: color});
						}
						else
						{
							var section = response.data.section;
							if (section)
							{
								if (params.section.id)
								{
									this.sections[this.sectionIndex[params.section.id]].updateData(section);
								}
								else
								{
									this.prepareData({sections: [section]});
								}
								this.sortSections();
							}
						}

						BX.onCustomEvent(this.calendar, 'BXCalendar:onSectionAdd', [{
							name: name,
							color: color
						}]);

						promise.fulfill(response.data);
					}, this),
					// Failure
					BX.delegate(function (response)
					{
						this.calendar.displayError(response.errors);
						promise.fulfill(response.errors);
					}, this)
				);

			return promise;
		},

		sectionIsShown: function(id)
		{
			return !BX.util.in_array(id, this.hiddenSections);
		},

		getSectionsInfo: function()
		{
			var
				i,
				allActive = [],
				superposed = [],
				active = [],
				hidden = [];

			for (i = 0; i < this.sections.length; i++)
			{
				if (this.sections[i].canDo('view_time')
					&& (
						this.sections[i].belongsToView()
						|| this.sections[i].isSuperposed()
						|| this.sections[i].isPseudo()
					)
				)
				{
					if (this.sections[i].isShown())
					{
						if (this.sections[i].isSuperposed())
						{
							superposed.push(this.sections[i].id);
						}
						else
						{
							active.push(this.sections[i].id);
						}
						allActive.push(this.sections[i].id);
					}
					else
					{
						hidden.push(this.sections[i].id);
					}
				}
			}

			return {
				superposed: superposed,
				active: active,
				hidden: hidden,
				allActive: allActive
			};
		},

		unsetSectionHandler: function(sectionId)
		{
			if (this.sectionIndex[sectionId] !== undefined)
			{
				this.sections = BX.util.deleteFromArray(this.sections, this.sectionIndex[sectionId]);
				for (var i = 0; i < this.sections.length; i++)
				{
					this.sectionIndex[this.sections[i].id] = i;
				}
			}
		}
	};

	function Section(calendar, data)
	{
		this.calendar = calendar;
		this.updateData(data);
	}

	Section.prototype = {
		updateData: function(data)
		{
			if (!this.data)
			{
				this.data = data || {};
				this.type = data.CAL_TYPE || '';
				this.ownerId = parseInt(data.OWNER_ID) || 0;

				Object.defineProperties(this, {
					id: {
						value: data.ID,
						writable: false
					},
					color: {
						value: data.COLOR,
						writable: true,
						enumerable : true
					}
				});
			}

			this.color = this.data.COLOR = data.COLOR;
			this.name = this.data.NAME = data.NAME;
		},

		isShown: function()
		{
			return this.calendar.sectionController.sectionIsShown(this.id);
		},

		remove: function()
		{
			if (confirm(BX.message('EC_SEC_DELETE_CONFIRM')))
			{
				BX.onCustomEvent(this.calendar, 'BXCalendar:onSectionDelete', [this.id]);
				BX.ajax.runAction('calendar.api.calendarajax.deleteCalendarSection', {
					data: {
						id: this.id
					}
				})
				.then(
					// Success
					function(response)
					{
						var reload = true;
						var section;
						for (var i = 0; i < this.calendar.sectionController.sections.length; i++)
						{
							section = this.calendar.sectionController.sections[i];
							if (section.belongsToView())
							{
								reload = false;
								break;
							}
						}

						if (reload)
						{
							BX.reload();
						}
						else
						{
							BX.Calendar.SectionManager.setNewEntrySectionId(this.calendar.sectionController.getCurrentSection().id);
							this.calendar.reload();
						}
					}.bind(this),
					// Failure
					function(response)
					{
						this.calendar.displayError(response.errors);
					}.bind(this)
				);
			}
		},

		hideGoogle: function()
		{
			if (confirm(BX.message('EC_CAL_GOOGLE_HIDE_CONFIRM')))
			{
				this.hide();
				BX.onCustomEvent(this.calendar, 'BXCalendar:onSectionDelete', [this.id]);

				BX.ajax.runAction('calendar.api.calendarajax.hideExternalCalendarSection', {
					data: {
						id: this.id
					}
				})
				.then(
					// Success
					BX.delegate(function (response)
					{
						this.calendar.reload();
					}, this),
					// Failure
					BX.delegate(function (response)
					{
						this.calendar.displayError(response.errors);
					}, this)
				);
			}
		},

		getLink: function()
		{
			return this.data && this.data.LINK ? this.data.LINK : '';
		},

		canBeConnectedToOutlook: function()
		{
			return !this.isPseudo() && this.data.OUTLOOK_JS && !(this.data.CAL_DAV_CAL && this.data.CAL_DAV_CON) && !BX.browser.IsMac();
		},

		connectToOutlook: function()
		{
			if (!window.jsOutlookUtils)
			{
				BX.loadScript('/bitrix/js/calendar/outlook.js', BX.delegate(function ()
				{
					try
					{
						eval(this.data.OUTLOOK_JS);
					}
					catch (e)
					{
					}
				}, this));
			}
			else
			{
				try
				{
					eval(this.data.OUTLOOK_JS);
				}
				catch (e)
				{
				}
			}
		},

		canDo: function(action)
		{
			//access
			//add
			//edit
			//edit_section
			//view_full
			//view_time
			//view_title
			if (BX.util.in_array(action, ['access','add','edit']) && this.isVirtual())
			{
				return false;
			}

			// if (BX.util.in_array(action, ['access','add','edit','edit_section']) && this.isSuperposed() && !this.belongsToView())
			// {
			// 	return false;
			// }

			if (action === 'view_event')
			{
				action = 'view_time';
			}

			return this.data.PERM && this.data.PERM[action];
		},

		isSuperposed: function()
		{
			return !this.isPseudo() && !!this.data.SUPERPOSED;
		},

		isPseudo: function()
		{
			return false;
		},

		isVirtual: function()
		{
			return (this.data.CAL_DAV_CAL && this.data.CAL_DAV_CAL.indexOf('@virtual/events/') !== -1)
				|| (this.data.GAPI_CALENDAR_ID && this.data.GAPI_CALENDAR_ID.indexOf('@group.v.calendar.google.com') !== -1)
				|| (this.data.EXTERNAL_TYPE === 'google_readonly')
				|| (this.data.EXTERNAL_TYPE === 'google_freebusy')
		},

		isGoogle: function()
		{
			return this.data.GAPI_CALENDAR_ID;
		},

		isCalDav: function()
		{
			return !this.isPseudo() && this.data.CAL_DAV_CAL && this.data.CAL_DAV_CON;
		},

		isCompanyCalendar: function()
		{
			return !this.isPseudo() && this.type !== 'user' && this.type !== 'group' && !parseInt(this.data.OWNER_ID);
		},

		belongsToView: function()
		{
			return this.type === this.calendar.util.type && parseInt(this.data.OWNER_ID) === parseInt(this.calendar.util.ownerId);
		},

		belongsToOwner: function()
		{
			return this.belongsToUser(this.calendar.util.userId);
		},

		belongsToUser: function(userId)
		{
			return this.data.CAL_TYPE === 'user'
				&& parseInt(this.data.OWNER_ID) === parseInt(userId)
				&& this.data.ACTIVE !== 'N';
		},

		isActive: function()
		{
			return this.data.ACTIVE !== 'N';
		}
	};


	function TaskSection(calendar, params)
	{
		this.calendar = calendar;
		var
			defaultColor = '#ff5b55',
			defaultName;

		if (!params)
			params = {};

		if (this.calendar.util.userIsOwner())
		{
			defaultName = BX.message('EC_SEC_MY_TASK_DEFAULT');
		}
		else if(this.calendar.util.isUserCalendar())
		{
			defaultName = BX.message('EC_SEC_USER_TASK_DEFAULT');
		}
		else if(this.calendar.util.isGroupCalendar())
		{
			defaultName = BX.message('EC_SEC_GROUP_TASK_DEFAULT');
		}

		var data = {
			ID: 'tasks',
			NAME: params.name || defaultName,
			COLOR: params.color || defaultColor,
			PERM: {
				edit_section:true,
				view_full:true,
				view_time:true,
				view_title:true
			}
		};
		Section.apply(this, [calendar, data]);
	}
	TaskSection.prototype = Object.create(Section.prototype);
	TaskSection.prototype.constructor = TaskSection;
	TaskSection.prototype.isPseudo = function()
	{
		return true;
	};
	TaskSection.prototype.updateData = function(data)
	{
		if (!this.data)
		{
			this.data = data || {};
			this.type = data.CAL_TYPE || '';

			Object.defineProperties(this, {
				id: {
					value: data.ID,
					writable: false
				},
				color: {
					value: data.COLOR,
					writable: true,
					enumerable : true
				}
			});
		}

		this.color = this.data.COLOR = data.COLOR;
		this.name = this.data.NAME = data.NAME;
	};

	if (window.BXEventCalendar)
	{
		window.BXEventCalendar.SectionController = SectionController;
		window.BXEventCalendar.Section = Section;
	}
	else
	{
		BX.addCustomEvent(window, "onBXEventCalendarInit", function()
		{
			window.BXEventCalendar.SectionController = SectionController;
			window.BXEventCalendar.Section = Section;
		});
	}
})(window);