import {Util} from 'calendar.util';
import {Event, Loc, Runtime, Type} from 'main.core';
import {CalendarSection} from './calendarsection';
import {CalendarTaskSection} from './calendartasksection';
import {EventEmitter} from 'main.core.events';

export { CalendarSection };

export class SectionManager
{
	static newEntrySectionId = null;
	static EXTERNAL_TYPE_LOCAL = 'local';
	static RELOAD_DELAY = 1000;

	constructor(data, config)
	{
		this.setSections(data.sections);
		this.setConfig(config);
		this.addTaskSection();
		this.sortSections();
		EventEmitter.subscribeOnce('BX.Calendar.Section:delete', (event) => {
			this.deleteSectionHandler(event.data.sectionId);
		});

		this.reloadDataDebounce = Runtime.debounce(this.reloadData, SectionManager.RELOAD_DELAY, this);
	}

	setSections(rawSections = [])
	{
		this.sections = [];
		this.sectionIndex = {};

		rawSections.forEach((sectionData) => {
			const section = new CalendarSection(sectionData);
			if (section.canDo('view_time'))
			{
				this.sections.push(section);
				this.sectionIndex[section.getId()] = this.sections.length - 1;
			}
		});
	}

	sortSections()
	{
		this.sectionIndex = {};
		this.sections = this.sections.sort((a, b) => {
			if (Type.isFunction(a.isPseudo) && a.isPseudo())
			{
				return 1;
			}
			else if (Type.isFunction(b.isPseudo) && b.isPseudo())
			{
				return -1;
			}
			return a.name.localeCompare(b.name);
		});

		this.sections.forEach((section, index) => {
			this.sectionIndex[section.getId()] = index;
		});
	}

	setConfig(config)
	{
		this.setHiddenSections(config.hiddenSections);
		this.calendarType = config.type;
		this.ownerId = config.ownerId;
		this.ownerName = config.ownerName || '';
		this.userId = config.userId;
		this.defaultSectionAccess = config.new_section_access || {};

		this.sectionAccessTasks = config.sectionAccessTasks;
		this.showTasks = config.showTasks;
		this.customizationData = config.sectionCustomization || {};
		this.meetSectionId = parseInt(config.meetSectionId, 10);
	}

	addTaskSection()
	{
		if (this.showTasks)
		{
			const taskSection = new CalendarTaskSection(
				this.customizationData['tasks' + this.ownerId],
				{
					type: this.calendarType,
					userId: this.userId,
					ownerId: this.ownerId
				}
			);
			this.sections.push(taskSection);
			this.sectionIndex[taskSection.id] = this.sections.length - 1;
		}
	}

	getCalendarType()
	{
		return this.calendarType;
	}

	handlePullChanges(params)
	{
		if (params.command === 'delete_section')
		{
			const sectionId = parseInt(params.fields.ID, 10);
			if (this.sectionIndex[sectionId])
			{
				this.deleteSectionHandler(sectionId);
				Util.getBX().Event.EventEmitter.emit(
					'BX.Calendar.Section:pull-delete',
					new Event.BaseEvent(
						{
							data: { sectionId: sectionId }
						}
					)
				);
			}
			else
			{
				this.reloadDataDebounce();
			}
		}
		else if (params.command === 'edit_section')
		{
			this.reloadDataDebounce();
			Util.getBX().Event.EventEmitter.emit('BX.Calendar:doRefresh');
		}
		else if (params.command === 'hidden_sections_updated')
		{
			this.setHiddenSections(params.hiddenSections);
			this.reloadDataDebounce();
		}
		else
		{
			this.reloadDataDebounce();
		}
	}

	reloadData()
	{
		BX.ajax.runAction('calendar.api.calendarajax.getSectionList', {
				data: {
					'type': this.calendarType,
					'ownerId': this.ownerId
				}
			})
			.then(response => {
					this.setSections(response.data.sections || []);
					this.sortSections();
					if (response.data.config)
					{
						this.setConfig(config);
					}
					this.addTaskSection();
					Util.getBX().Event.EventEmitter.emit(
						'BX.Calendar.Section:pull-reload-data'
					);
				}
			);
	}

	getSections()
	{
		return this.sections;
	}

	getSuperposedSectionList()
	{
		var i, result = [];
		for (i = 0; i < this.sections.length; i++)
		{
			if (this.sections[i].isSuperposed()
				&& this.sections[i].isActive())
			{
				result.push(this.sections[i]);
			}
		}
		return result;
	}

	getSectionListForEdit()
	{
		const result = [];
		for (let i = 0; i < this.sections.length; i++)
		{
			if (
				this.sections[i].canDo('edit')
				&& !this.sections[i].isPseudo()
				&& this.sections[i].isActive()
				&& !this.sections[i].isLocationRoom()
			)
			{
				result.push(this.sections[i]);
			}
		}
		return result;
	}

	getSection(id)
	{
		return this.sections[this.sectionIndex[id]] || {};
	}

	getDefaultSectionName()
	{
		return Loc.getMessage('EC_DEFAULT_SECTION_NAME');
	}

	getDefaultSectionAccess()
	{
		return this.defaultSectionAccess;
		// return this.calendar.util.config.new_section_access || {};
	}

	saveSection(name, color, access, params)
	{
		return new Promise(resolve => {
			name = (Type.isString(name) && name.trim())
				? name.trim()
				: Loc.getMessage('EC_SEC_SLIDER_NEW_SECTION');

			if (params.section.id)
			{
				// BX.onCustomEvent(this.calendar, 'BXCalendar:onSectionChange', [
				// 	params.section.id,
				// 	{
				// 		name: name,
				// 		color: color
				// 	}]);
			}
			else
			{
				// BX.onCustomEvent(this.calendar, 'BXCalendar:onSectionAddBefore', [{
				// 	name: name,
				// 	color: color
				// }]);
			}

			const isCustomization = params.section.id && params.section.isPseudo();
			BX.ajax.runAction('calendar.api.calendarajax.editCalendarSection', {
					data: {
						analyticsLabel: {
							action: params.section.id ? 'editSection' : 'newSection',
							type: params.section.type || this.calendarType
						},
						id: params.section.id || 0,
						name: name,
						type: params.section.type || this.calendarType,
						ownerId: params.section.ownerId || this.ownerId,
						color: color,
						access: access || null,
						userId: this.userId,
						customization: isCustomization ? 'Y' : 'N',
						external_type: params?.section?.id ? params.section.getExternalType() : 'local'
					}
				})
				.then(
					(response) => {
						if (isCustomization)
						{
							BX.reload();
							return;
						}

						const sectionList = response.data.sectionList || [];
						this.setSections(sectionList);
						this.sortSections();
						this.addTaskSection();

						Util.getBX().Event.EventEmitter.emit(
							'BX.Calendar.Section:edit',
							new Event.BaseEvent(
								{
									data: { sectionList: sectionList }
								}
							)
						);
						resolve(response.data);
					},
					(response) => {
						BX.Calendar.Util.displayError(response.errors);
						resolve(response.data);
					}
				);

		});
	}

	sectionIsShown(id)
	{
		return !BX.util.in_array(id, this.hiddenSections);
	}

	getHiddenSections()
	{
		return this.hiddenSections;
	}

	setHiddenSections(hiddenSections)
	{
		this.hiddenSections = [];
		if (Type.isArray(hiddenSections))
		{
			hiddenSections.forEach((id) => {
				this.hiddenSections.push(id === 'tasks' ? id : parseInt(id));
			});
		}
	}

	saveHiddenSections()
	{
		const calendarContext = Util.getCalendarContext();
		const optionName = calendarContext.util.userIsOwner()
			? 'hidden_sections'
			: 'hidden_sections_' + calendarContext.util.type;

		BX.userOptions.save('calendar', optionName, optionName, this.hiddenSections);
	}

	getSectionsInfo()
	{
		const allActive = [];
		const superposed = [];
		const active = [];
		const hidden = [];

		this.sections.forEach((section) => {
			if(section.isShown() && this.calendarType === 'location' && section.type === 'location')
			{
				if (section.isSuperposed())
				{
					superposed.push(section.id);
				}
				else
				{
					active.push(section.id);
				}
				allActive.push(section.id);
			}
			else if (section.isShown() && this.calendarType !== 'location')
			{
				if (section.isSuperposed())
				{
					superposed.push(section.id);
				}
				else
				{
					active.push(section.id);
				}
				allActive.push(section.id);
			}
			else
			{
				hidden.push(section.id);
			}
		});

		return { superposed, active, hidden, allActive };
	}

	deleteSectionHandler(sectionId)
	{
		if (this.sectionIndex[sectionId] !== undefined)
		{
			this.sections = BX.util.deleteFromArray(this.sections, this.sectionIndex[sectionId]);

			this.sectionIndex = {};
			for (let i = 0; i < this.sections.length; i++)
			{
				this.sectionIndex[this.sections[i].id] = i;
			}
		}
	}

	static getNewEntrySectionId(calendarType = null, ownerId = null)
	{
		const calendarContext = Util.getCalendarContext();
		if (calendarContext && !calendarContext.isExternalMode())
		{
			calendarType = calendarType || calendarContext.util.type;
			if (calendarType === 'location')
			{
				const section = calendarContext.sectionManager.getDefaultSection(
					'user',
					calendarContext.util.userId
				);
				return parseInt(section?.id, 10);
			}
			else
			{
				const section = calendarContext.sectionManager.getDefaultSection(calendarType, ownerId);
				return parseInt(section?.id, 10);
			}
		}

		if (SectionManager.newEntrySectionId)
		{
			return SectionManager.newEntrySectionId;
		}

		return null;
	}

	static setNewEntrySectionId(sectionId)
	{
		SectionManager.newEntrySectionId = parseInt(sectionId);
	}

	static getSectionGroupList(options = {})
	{
		let
			type = options.type,
			ownerId = options.ownerId,
			userId = options.userId,
			followedUserList = options.trackingUsersList || Util.getFollowedUserList(userId),
			sectionGroups = [],
			title;

		// 1. Main group - depends from current view
		if (type === 'user')
		{
			if (userId === ownerId)
			{
				title = Loc.getMessage('EC_SEC_SLIDER_MY_CALENDARS_LIST');
			}
			else
			{
				title = Loc.getMessage('EC_SEC_SLIDER_USER_CALENDARS_LIST');
			}
		}
		else if (type === 'group')
		{
			title = Loc.getMessage('EC_SEC_SLIDER_GROUP_CALENDARS_LIST');
		}
		else if (type === 'location')
		{
			title = Loc.getMessage('EC_SEC_SLIDER_TYPE_LOCATION_LIST');
		}
		else if (type === 'resource')
		{
			title = Loc.getMessage('EC_SEC_SLIDER_TYPE_RESOURCE_LIST');
		}
		else
		{
			title = Loc.getMessage('EC_SEC_SLIDER_TITLE_COMP_CAL');
		}

		sectionGroups.push({
			title: title,
			type: type,
			belongsToView: true
		});

		if (type !== 'user' || userId !== ownerId)
		{
			sectionGroups.push({
				title: Loc.getMessage('EC_SEC_SLIDER_MY_CALENDARS_LIST'),
				type: 'user',
				ownerId: userId
			});
		}

		// 2. Company calendar
		if (type !== 'company' && type !== 'company_calendar' && type !== 'calendar_company')
		{
			sectionGroups.push({
				title: Loc.getMessage('EC_SEC_SLIDER_TITLE_COMP_CAL'),
				type: 'company'
			});
		}

		// 3. Users calendars
		if (Type.isArray(followedUserList))
		{
			followedUserList.forEach((user) => {
				if (parseInt(user.ID) !== ownerId || type !== 'user')
				{
					sectionGroups.push({
						title: BX.util.htmlspecialchars(user.FORMATTED_NAME),
						type: 'user',
						ownerId: parseInt(user.ID)
					});
				}
			});
		}

		// 4. Groups calendars
		sectionGroups.push({
			title: Loc.getMessage('EC_SEC_SLIDER_POPUP_MENU_ADD_GROUP'),
			type: 'group'
		});

		// 5. Resources calendars
		sectionGroups.push({
			title: Loc.getMessage('EC_SEC_SLIDER_TITLE_RESOURCE_CAL'),
			type: 'resource'
		});

		// 6. Location calendars
		sectionGroups.push({
			title: Loc.getMessage('EC_SEC_SLIDER_TITLE_LOCATION_CAL'),
			type: 'location'
		});

		return sectionGroups;
	}

	getSectionAccessTasks()
	{
		return this.sectionAccessTasks;
	}

	getDefaultSection(calendarType = null, ownerId = null)
	{
		let sections = this.getSectionListForEdit();

		calendarType = Type.isString(calendarType) ? calendarType : this.calendarType;
		ownerId = Type.isNumber(ownerId) ? ownerId : this.ownerId;

		let section;

		if (calendarType === 'user')
		{
			const defaultSectionId = this.meetSectionId;
			section = sections.find((item) => {
				return item.type === calendarType
					&& item.ownerId === ownerId
					&& item.id === defaultSectionId;
			});
		}
		else
		{
			sections = sections.sort((section1, section2) => section1.id - section2.id);
		}

		if (!section)
		{
			section = sections.find((item) => {
				return item.type === calendarType
					&& item.ownerId === ownerId
					&& item.canDo('edit')
			});
		}

		return section;
	}

	setDefaultSection(sectionId)
	{
		const section = this.getSection(parseInt(sectionId, 10));

		if (section
			&& section.type === this.calendarType
			&& section.ownerId === this.ownerId)
		{
			const userSettings = Util.getUserSettings();
			const key = this.calendarType + this.ownerId;
			if (userSettings.defaultSections[key] !== section.id)
			{
				userSettings.defaultSections[key] = section.id;
				Util.setUserSettings(userSettings);

				BX.ajax.runAction('calendar.api.calendarajax.updateDefaultSectionId', {
					data: {
						'key': key,
						'sectionId': sectionId
					}
				});
			}
		}
	}

	static saveDefaultSectionId(sectionId, options = {})
	{
		const calendarContext = Util.getCalendarContext();
		if (calendarContext)
		{
			calendarContext.sectionManager.setDefaultSection(sectionId);
		}
		else
		{
			if (Type.isArray(options.sections) && options.calendarType && options.ownerId)
			{
				const section = options.sections.find((item) => {
					const id = parseInt(item.ID || item.id, 10);
					const ownerId = parseInt(item.OWNER_ID || item.ownerId, 10);
					const type = item.CAL_TYPE || item.type;

					return id === parseInt(sectionId,10)
						&& ownerId === parseInt(options.ownerId, 10)
						&& type === options.calendarType;
				});

				if (section)
				{
					const userSettings = Util.getUserSettings();
					const key = options.calendarType + options.ownerId;
					if (userSettings && userSettings.defaultSections[key] !== sectionId)
					{
						userSettings.defaultSections[key] = sectionId;
						Util.setUserSettings(userSettings);
						SectionManager.newEntrySectionId = sectionId;

						BX.ajax.runAction('calendar.api.calendarajax.updateDefaultSectionId', {
							data: {
								'key': key,
								'sectionId': sectionId
							}
						});
					}
				}
			}
		}
	}

	static getSectionExternalConnection(section, sectionExternalType): any
	{
		const calendarContext = Util.getCalendarContext();
		const linkList = section.getConnectionLinks();

		let provider = undefined;
		let connection = undefined;
		let connectionId = linkList.length
			? parseInt(linkList[0].id)
			: parseInt(section.data.CAL_DAV_CON, 10)
		;

		if (connectionId && calendarContext && calendarContext.syncInterface)
		{
			[provider, connection] = calendarContext.syncInterface.getProviderById(connectionId);

			if (
				connection
				&& (!linkList.length || connection.getType() === sectionExternalType)
			)
			{
				return connection;
			}
		}

		return null;
	}
}