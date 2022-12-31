import { Dom, Event, Loc, Tag, Text, Type } from 'main.core';
import { Util } from 'calendar.util';
import { TagSelector } from 'ui.entity-selector';
import { TrackingUsersForm } from "./trackingusersform"

export class TrackingGroupsForm extends TrackingUsersForm
{
	constructor(options = {})
	{
		super(options);
		this.interfaceType = 'groups';
		this.trackingGroupIdList = options.trackingGroups || [];
	}

	create()
	{
		if (!this.DOM.innerWrap)
		{
			this.DOM.innerWrap = this.DOM.outerWrap.appendChild(Tag.render`<div></div>`);
		}

		this.selectorWrap = this.DOM.innerWrap.appendChild(
			Dom.create('DIV', { props: { className: 'calendar-list-slider-selector-wrap' } })
		);

		this.groupTagSelector = new TagSelector({
			dialogOptions: {
				width: 320,
				context: 'CALENDAR',
				preselectedItems: this.trackingGroupIdList.map((id) => {
					return ['project', id];
				}),
				events: {
					'Item:onSelect': this.handleGroupSelectorChanges.bind(this),
					'Item:onDeselect': this.handleGroupSelectorChanges.bind(this),
				},
				entities: [
					{
						id: 'project'
					}
				]
			}
		});

		this.groupTagSelector.renderTo(this.selectorWrap);

		// List of sections
		this.sectionsWrap = this.DOM.innerWrap.appendChild(
			Tag.render`<div class="calendar-list-slider-sections-wrap"></div>`
		);
		this.createButtons();

		this.isCreated = true;
	}

	handleGroupSelectorChanges()
	{
		const selectedItems = this.groupTagSelector.getDialog().getSelectedItems();
		this.trackingGroupIdList = [];
		selectedItems.forEach((item) => {
			if (item.entityId === 'project')
			{
				this.trackingGroupIdList.push(item.id);
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
			Dom.adjust(Util.getLoader(), {style: {height: '140px'}})
		);

		if (this.updateSectionTimeout)
		{
			this.updateSectionTimeout = clearTimeout(this.updateSectionTimeout);
		}

		this.checkInnerWrapHeight();
		BX.ajax.runAction('calendar.api.calendarajax.getTrackingSections', {
				data: {
					groupIdList: this.trackingGroupIdList,
					type: 'groups'
				}
			})
			.then(
				(response) => {
					Dom.clean(this.sectionsWrap);
					this.sectionIndex = {};
					this.checkInnerWrapHeight();

					// Groups calendars
					this.createSectionBlock({
						sectionList: response.data.sections,
						wrap: this.sectionsWrap
					});
				},
				(response) => {
					Util.displayError(response.errors);
				}
			);
	}

	getSelectedSections()
	{
		const sections = [];
		this.superposedSections.forEach((section) => {
			if (
				this.interfaceType === 'groups'
				&& section.type === 'group'
				&& this.trackingGroupIdList
				&& !this.trackingGroupIdList.includes(section.ownerId)
			)
			{
				return;
			}
			sections.push(parseInt(section.id));
		}, this);

		return sections;
	}
}