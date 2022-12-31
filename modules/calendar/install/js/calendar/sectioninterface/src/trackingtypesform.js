import { Dom, Event, Loc, Tag, Text, Type } from 'main.core';
import { Util } from 'calendar.util';
import {TrackingUsersForm} from "./trackingusersform"

export class TrackingTypesForm extends TrackingUsersForm
{
	constructor(options = {})
	{
		super(options);
		this.trackingGroups = options.trackingGroups || [];
		this.interfaceType = 'company';
		this.selectGroups = true;
		this.selectUsers = false;
		this.addLinkMessage = Loc.getMessage('EC_SEC_SLIDER_SELECT_GROUPS');
	}

	show ()
	{
		if (!this.isCreated)
		{
			this.create();
		}

		this.updateSectionList();
		this.isOpenedState = true;
		Dom.addClass(this.DOM.outerWrap, 'show');
	}

	create()
	{
		if (!this.DOM.innerWrap)
		{
			this.DOM.innerWrap = this.DOM.outerWrap.appendChild(Tag.render`<div></div>`);
		}

		// List of sections
		this.sectionsWrap = this.DOM.innerWrap.appendChild(
			Tag.render`<div class="calendar-list-slider-sections-wrap"></div>`
		);

		this.createButtons();

		this.isCreated = true;
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

		BX.ajax.runAction('calendar.api.calendarajax.getTrackingSections', {
				data: {
					type: 'company'
				}
			})
			.then(
				(response) => {
					Dom.clean(this.sectionsWrap);
					this.sectionIndex = {};
					this.checkInnerWrapHeight();

					if (Type.isArray(response.data.sections)
						&& response.data.sections.length)
					{
						this.createSectionBlock({
							sectionList: response.data.sections,
							wrap: this.sectionsWrap
						});
					}
					else
					{
						this.sectionsWrap.appendChild(Tag.render`
								<div>
									<span class="calendar-list-slider-card-section-title-text">
										${Loc.getMessage('EC_SEC_SLIDER_NO_SECTIONS')}
									</span>
								</div>
							`);
					}
				},
				(response) => {
					Util.displayError(response.errors);
				}
			);

		this.checkInnerWrapHeight();
	}

	save()
	{
		BX.ajax.runAction('calendar.api.calendarajax.setTrackingSections', {
				data: {
					sections: this.prepareTrackingSections(),
				}
			})
			.then(
				(response) => {
					location.reload();
				},
				(response) => {
					Util.displayError(response.errors);
				}
			);

		this.close();
	}

	getSelectedSections()
	{
		const sections = [];
		this.superposedSections.forEach((section) => {
			sections.push(parseInt(section.id));
		}, this);

		return sections;
	}
}








