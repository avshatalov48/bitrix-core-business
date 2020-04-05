;(function(window) {

	function SectionController(calendar, data, config)
	{
		this.calendar = calendar;
		this.sections = [];
		this.sectionIndex = {};
		this.hiddenSections = config.hiddenSections;

		this.prepareData({sections: data.sections});

		if (this.calendar.showTasks)
		{
			var taskSection = new TaskSection(this.calendar, config.sectionCustomization.tasks);
			this.sections.push(taskSection);
			this.sectionIndex[taskSection.id] = this.sections.length - 1;
		}

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
				if (a.isPseudo())
				{
					return 1;
				}
				else if (b.isPseudo())
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
					|| !section.belongsToView()
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

			return section;
		},

		getSectionList: function()
		{
			var i, result = [];
			for (i = 0; i < this.sections.length; i++)
			{
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
					&& (!this.sections[i].isSuperposed() || this.sections[i].belongsToView())
					&& !this.sections[i].isPseudo()
					&& this.sections[i].isActive())
				{
					result.push(this.sections[i]);
				}
			}
			return result;
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

			var url = this.calendar.util.getActionUrl();
			url += (url.indexOf('?') == -1) ? '?' : '&';
			url += '&markAction=' + (params.section.id ? 'editSection' : 'newSection');
			url += '&markType=' + this.calendar.util.type;

			this.calendar.request({
				url: url,
				type: 'post',
				data: {
					action: 'section_edit',
					id: params.section.id || 0,
					name: name,
					color: color,
					access: access
				},
				handler: BX.delegate(function(response)
				{
					//if (oRes.accessNames)
					//	_this.HandleAccessNames(oRes.accessNames);
					if (params.section.id)
					{
						this.sections[this.sectionIndex[params.section.id]].updateData(response.calendar);
					}
					else
					{
						this.prepareData({sections: [response.calendar]});
					}
					this.sortSections();

					BX.onCustomEvent(this.calendar, 'BXCalendar:onSectionAdd', [{
						name: name,
						color: color
					}]);

				}, this)
			});
		},

		sectionIsShown: function(id)
		{
			return !BX.util.in_array(id, this.hiddenSections);
		},

		getHiddenSections: function()
		{
			return this.hiddenSections || [];
		},

		setHiddenSections: function(hiddenSections)
		{
			this.hiddenSections = hiddenSections;
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
				if (this.sections[i].canDo('view_time'))
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
			this.data = data || {};

			this.color = data.COLOR;
			this.textColor = data.TEXT_COLOR;
			this.name = data.NAME || '';
			this.type = data.CAL_TYPE || '';

			Object.defineProperties(this, {
				id: {
					value: data.ID,
					writable: false,
					enumerable : true
				},
				color: {
					value: data.COLOR,
					writable: true,
					enumerable : true
				},
				textColor: {
					value: data.TEXT_COLOR,
					writable: true,
					enumerable : true
				}
			});
		},

		isShown: function()
		{
			return this.calendar.sectionController.sectionIsShown(this.id);
		},

		show: function()
		{
			if (!this.isShown())
			{
				var hiddenSections = this.calendar.sectionController.getHiddenSections();
				hiddenSections = BX.util.deleteFromArray(hiddenSections, BX.util.array_search(this.id, hiddenSections));
				this.calendar.sectionController.setHiddenSections(hiddenSections);
				BX.userOptions.save('calendar', 'hidden_sections', 'hidden_sections', hiddenSections);
			}
		},

		hide: function()
		{
			if (this.isShown())
			{
				var hiddenSections = this.calendar.sectionController.getHiddenSections();
				hiddenSections.push(this.id);
				this.calendar.sectionController.setHiddenSections(hiddenSections);
				BX.userOptions.save('calendar', 'hidden_sections', 'hidden_sections', hiddenSections);
			}
		},

		remove: function()
		{
			if (confirm(BX.message('EC_SEC_DELETE_CONFIRM')))
			{
				BX.onCustomEvent(this.calendar, 'BXCalendar:onSectionDelete', [this.id]);

				this.calendar.request({
					type: 'post',
					data: {
						action: 'section_delete',
						id: this.id
					},
					handler: BX.delegate(function(response)
					{
						this.calendar.reload();
						//return oRes.result ? _this.DeleteSectionClientSide(el) : false;
					}, this)
				});
			}
		},

		hideGoogle: function()
		{
			if (confirm(BX.message('EC_CAL_GOOGLE_HIDE_CONFIRM')))
			{
				this.hide();
				BX.onCustomEvent(this.calendar, 'BXCalendar:onSectionDelete', [this.id]);

				this.calendar.request({
					type: 'post',
					data: {
						action: 'section_caldav_hide',
						id: this.id
					},
					handler: BX.delegate(function(response)
					{
						this.calendar.reload();
					}, this)
				});
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

			if (BX.util.in_array(action, ['access','add','edit','edit_section']) && this.isSuperposed() && !this.belongsToView())
			{
				return false;
			}

			if (action == 'view_event')
				action = 'view_time';

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
				|| (this.data.GAPI_CALENDAR_ID && this.data.GAPI_CALENDAR_ID.indexOf('@group.v.calendar.google.com') !== -1);
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
			return !this.isPseudo() && this.type != 'user' && this.type != 'group' && !parseInt(this.data.OWNER_ID);
		},

		belongsToView: function()
		{
			return this.type == this.calendar.util.type && this.data.OWNER_ID == this.calendar.util.ownerId;
		},

		belongToOwner: function()
		{
			return this.data.CAL_TYPE == 'user'
				&& this.data.OWNER_ID == this.calendar.util.userId
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