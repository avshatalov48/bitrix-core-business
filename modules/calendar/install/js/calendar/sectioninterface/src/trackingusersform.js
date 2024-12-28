import { Runtime, Dom, Event, Loc, Type, Tag, Text } from 'main.core';
import { Util } from 'calendar.util';
import { TagSelector } from 'ui.entity-selector';

/* eslint-disable @bitrix24/bitrix24-rules/no-native-dom-methods */
export class TrackingUsersForm
{
	DOM = {};
	isCreated = false;

	constructor(options = {})
	{
		this.interfaceType = 'users';
		this.DOM.outerWrap = options.wrap;
		this.trackingUsers = options.trackingUsers || [];
		this.trackingUserIdList = this.trackingUsers.map((item) => parseInt(item.ID, 10));
		this.trackingIdList = [];

		this.CHECKED_CLASS = 'calendar-list-slider-item-checkbox-checked';
		this.selectorId = `add-tracking${Util.getRandomInt()}`;
		this.closeCallback = options.closeCallback;

		this.superposedSections = Type.isArray(options.superposedSections) ? options.superposedSections : [];
		this.selected = {};
		this.superposedSections.forEach((section) => {
			this.selected[section.id] = true;
		});

		this.isCreated = false;
		this.keyHandlerBinded = this.keyHandler.bind(this);
	}

	show(): void
	{
		if (!this.isCreated)
		{
			this.create();
		}

		Dom.addClass(this.DOM.outerWrap, 'show');
		this.checkInnerWrapHeight();

		Event.bind(document, 'keydown', this.keyHandlerBinded);

		this.updateSectionList();

		this.firstTrackingUserIdList = Runtime.clone(this.trackingUserIdList);
		this.isOpenedState = true;
	}

	close(): void
	{
		Event.unbind(document, 'keydown', this.keyHandlerBinded);

		this.isOpenedState = false;
		Dom.removeClass(this.DOM.outerWrap, 'show');
		this.DOM.outerWrap.style.cssText = '';

		if (Type.isFunction(this.closeCallback))
		{
			this.closeCallback();
		}
	}

	isOpened(): boolean
	{
		return this.isOpenedState;
	}

	create(): void
	{
		if (!this.DOM.innerWrap)
		{
			this.DOM.innerWrap = this.DOM.outerWrap.appendChild(Tag.render`<div></div>`);
		}

		this.selectorWrap = this.DOM.innerWrap.appendChild(
			Dom.create('DIV', { props: { className: 'calendar-list-slider-selector-wrap' } }),
		);

		this.userTagSelector = new TagSelector({
			dialogOptions: {
				width: 320,
				context: 'CALENDAR',
				preselectedItems: this.trackingUsers.map(
					(item) => ['user', parseInt(item.ID, 10)],
				),
				events: {
					'Item:onSelect': this.handleUserSelectorChanges.bind(this),
					'Item:onDeselect': this.handleUserSelectorChanges.bind(this),
				},
				entities: [
					{
						id: 'user',
					},
				],
			},
		});

		this.userTagSelector.renderTo(this.selectorWrap);

		// List of sections
		this.sectionsWrap = this.DOM.innerWrap.appendChild(
			Tag.render`<div class="calendar-list-slider-sections-wrap"></div>`,
		);
		this.createButtons();

		this.isCreated = true;
	}

	createButtons(): void
	{
		this.DOM.innerWrap.appendChild(
			Tag.render`
				<div class="calendar-list-slider-btn-container">
					<button 
						class="ui-btn ui-btn-sm ui-btn-primary"
						onclick="${this.save.bind(this)}"
					>${Loc.getMessage('EC_SEC_SLIDER_SAVE')}</button>
					<button 
						class="ui-btn ui-btn-link"
						onclick="${this.close.bind(this)}"
					>${Loc.getMessage('EC_SEC_SLIDER_CANCEL')}</button>
				</div>
			`,
		);
	}

	handleUserSelectorChanges(): void
	{
		const selectedItems = this.userTagSelector.getDialog().getSelectedItems();
		this.trackingUserIdList = [];
		selectedItems.forEach((item) => {
			if (item.entityId === 'user')
			{
				this.trackingUserIdList.push(item.id);
			}
		});
		this.updateSectionList();
	}

	save(): void
	{
		BX.ajax.runAction('calendar.api.calendarajax.setTrackingSections', {
			data: {
				userIdList: this.trackingUserIdList,
				groupIdList: this.trackingIdList,
				collabIdList: this.trackingIdList,
				sections: this.prepareTrackingSections(),
				type: this.interfaceType,
			},
		}).then(
			() => {
				location.reload();
			},
			(response) => {
				Util.displayError(response.errors);
			},
		);

		this.close();
	}

	prepareTrackingSections()
	{
		let sections = this.getSelectedSections();

		for (const id in this.sectionIndex)
		{
			if (this.sectionIndex.hasOwnProperty(id) && this.sectionIndex[id].checkbox)
			{
				if (Dom.hasClass(this.sectionIndex[id].checkbox, this.CHECKED_CLASS))
				{
					if (!sections.includes(parseInt(id, 10)))
					{
						sections.push(parseInt(id, 10));
					}
				}
				else if (sections.includes(parseInt(id, 10)))
				{
					sections = sections.filter(
						(section) => parseInt(section, 10) !== parseInt(id, 10),
					);
				}
			}
		}

		return sections;
	}

	getSelectedSections()
	{
		const sections = [];
		this.superposedSections.forEach((section) => {
			if (
				this.interfaceType === 'users'
				&& section.type === 'user'
				&& this.trackingUserIdList
				&& !this.trackingUserIdList.includes(section.ownerId)
			)
			{
				return;
			}

			sections.push(parseInt(section.id, 10));
		});

		return sections;
	}

	updateSectionList(delayExecution)
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

		if (delayExecution !== false)
		{
			this.updateSectionTimeout = setTimeout(() => {
				this.updateSectionList(false);
			}, 300);

			return;
		}

		this.checkInnerWrapHeight();

		BX.ajax.runAction('calendar.api.calendarajax.getTrackingSections', {
			data: {
				userIdList: this.trackingUserIdList,
				type: 'users',
			},
		}).then(
			// Success
			(response) => {
				Dom.clean(this.sectionsWrap);
				this.sectionIndex = {};
				this.checkInnerWrapHeight();

				// Users calendars
				response.data.users.forEach((user) => {
					const sections = response.data.sections.filter((section) => {
						return parseInt(section.OWNER_ID, 10) === parseInt(user.ID, 10);
					});

					this.sectionsWrap.appendChild(Tag.render`
						<div>
							<span class="calendar-list-slider-card-section-title-text">
								${Text.encode(user.FORMATTED_NAME)}
							</span>
						</div>
					`);

					if (sections.length > 0)
					{
						this.createSectionBlock({
							sectionList: sections,
							wrap: this.sectionsWrap,
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
				});
			},
			(response) => {
				Util.displayError(response.errors);
			},
		);
	}

	createSectionBlock(params = {})
	{
		let result = false;
		if (
			Type.isArray(params.sectionList)
			&& params.sectionList.length
			&& Type.isElementNode(params.wrap))
		{
			let listWrap;
			params.wrap.appendChild(Tag.render`
				<div class="calendar-list-slider-widget-content">
					<div class="calendar-list-slider-widget-content-block">
						${listWrap = Tag.render`<ul class="calendar-list-slider-container"></ul>`}
					</div>
				</div>
			`);

			Event.bind(listWrap, 'click', this.sectionClick.bind(this));

			params.sectionList.forEach((section) => {
				const id = section.ID.toString();
				let checkbox;

				const li = listWrap.appendChild(Tag.render`
					<li class="calendar-list-slider-item" data-bx-calendar-section="${id}">
						${checkbox = Tag.render`
							<div class="calendar-list-slider-item-checkbox" style="background: ${section.COLOR}"></div>
						`}
						<div class="calendar-list-slider-item-name">${Text.encode(section.NAME)}</div>
					</li>
				`);

				this.sectionIndex[id] = {
					item: li,
					checkbox,
				};

				if (
					this.selected[id]
					|| !Type.isArray(this.firstTrackingUserIdList)
					|| !this.firstTrackingUserIdList.includes(parseInt(section.OWNER_ID, 10))
				)
				{
					Dom.addClass(checkbox, this.CHECKED_CLASS);
				}
			});
		}

		return result;
	}

	sectionClick(e): void
	{
		const target = Util.findTargetNode(e.target || e.srcElement, this.DOM.outerWrap);
		if (!Type.isElementNode(target))
		{
			return;
		}

		const id = target.getAttribute('data-bx-calendar-section');
		if (id === null)
		{
			return;
		}

		if (!this.sectionIndex[id]?.checkbox)
		{
			return;
		}

		if (Dom.hasClass(this.sectionIndex[id].checkbox, this.CHECKED_CLASS))
		{
			Dom.removeClass(this.sectionIndex[id].checkbox, this.CHECKED_CLASS);
		}
		else
		{
			Dom.addClass(this.sectionIndex[id].checkbox, this.CHECKED_CLASS);
		}
	}

	keyHandler(e)
	{
		if (e.keyCode === Util.getKeyCode('escape'))
		{
			this.close();
		}
		else if (e.keyCode === Util.getKeyCode('enter'))
		{
			this.save();
		}
	}

	checkInnerWrapHeight()
	{
		if (this.checkHeightTimeout)
		{
			clearTimeout(this.checkHeightTimeout);
			this.checkHeightTimeout = null;
		}

		this.checkHeightTimeout = setTimeout(() => {
			if (Dom.hasClass(this.DOM.outerWrap, 'show'))
			{
				if (this.DOM.outerWrap.offsetHeight - this.DOM.innerWrap.offsetHeight < 36)
				{
					const fullHeight = parseInt(this.DOM.innerWrap.offsetHeight, 10) + 200;
					this.DOM.outerWrap.style.maxHeight = `${fullHeight}px`;
				}
			}
			else
			{
				this.DOM.outerWrap.style.maxHeight = '';
			}
		}, 300);
	}
}
