import { Dom, Tag } from 'main.core';
import { Util } from 'calendar.util';
import { TagSelector } from 'ui.entity-selector';
import { TrackingUsersForm } from './trackingusersform';

/* eslint-disable @bitrix24/bitrix24-rules/no-native-dom-methods */
export class TrackingGroupsForm extends TrackingUsersForm
{
	constructor(options = {})
	{
		super(options);
		this.interfaceType = 'groups';
		this.trackingIdList = options.trackingGroups || [];
		this.collabs = options.collabs || [];
	}

	create()
	{
		if (!this.DOM.innerWrap)
		{
			this.DOM.innerWrap = this.DOM.outerWrap.appendChild(Tag.render`<div></div>`);
		}

		this.selectorWrap = this.DOM.innerWrap.appendChild(
			Dom.create('DIV', { props: { className: 'calendar-list-slider-selector-wrap' } }),
		);

		this.groupTagSelector = new TagSelector({
			dialogOptions: {
				width: 320,
				context: 'CALENDAR',
				preselectedItems: this.trackingIdList.map((id) => ['project', id]),
				events: {
					'Item:onSelect': this.handleGroupSelectorChanges.bind(this),
					'Item:onDeselect': this.handleGroupSelectorChanges.bind(this),
				},
				entities: this.getSelectorEntities(),
			},
		});

		this.groupTagSelector.renderTo(this.selectorWrap);

		// List of sections
		this.sectionsWrap = this.DOM.innerWrap.appendChild(
			Tag.render`<div class="calendar-list-slider-sections-wrap"></div>`,
		);
		this.createButtons();

		this.isCreated = true;
	}

	handleGroupSelectorChanges()
	{
		const selectedItems = this.groupTagSelector.getDialog().getSelectedItems();
		this.trackingIdList = [];
		selectedItems.forEach((item) => {
			if (item.entityType === 'project')
			{
				this.trackingIdList.push(item.id);
			}
		});
		this.updateSectionList();
	}

	updateSectionList()
	{
		if (this.updateSectionLoader)
		{
			Dom.remove(this.updateSectionLoader);
		}
		this.updateSectionLoader = this.sectionsWrap.appendChild(
			Dom.adjust(Util.getLoader(), { style: { height: '140px' } }),
		);

		if (this.updateSectionTimeout)
		{
			clearTimeout(this.updateSectionTimeout);
			this.updateSectionTimeout = null;
		}

		this.checkInnerWrapHeight();
		BX.ajax.runAction('calendar.api.calendarajax.getTrackingSections', {
			data: {
				groupIdList: this.trackingIdList,
				type: this.interfaceType,
			},
		}).then(
			(response) => {
				Dom.clean(this.sectionsWrap);
				this.sectionIndex = {};
				this.checkInnerWrapHeight();

				// Groups calendars
				this.createSectionBlock({
					sectionList: response.data.sections,
					wrap: this.sectionsWrap,
				});
			},
			(response) => {
				Util.displayError(response.errors);
			},
		);
	}

	getSelectedSections(): Array<number>
	{
		const sections = [];
		this.superposedSections.forEach((section) => {
			if (
				this.interfaceType === 'groups'
				&& section.type === 'group'
				&& !this.trackingIdList?.includes(section.ownerId)
				&& !this.collabs?.includes(section.ownerId)
			)
			{
				return;
			}
			sections.push(parseInt(section.id, 10));
		});

		return sections;
	}

	getSelectorEntities(): Array
	{
		return [
			{
				id: 'project',
				options: {
					lockProjectLink: !Util.isProjectFeatureEnabled(),
					lockProjectLinkFeatureId: 'socialnetwork_projects_groups',
					'!type': ['collab'],
				},
			},
		];
	}
}
