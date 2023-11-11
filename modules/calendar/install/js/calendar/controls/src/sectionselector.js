import {Type, Dom, Event, Tag, Text, Loc} from "main.core";
import "ui.icons.b24";
import {MenuManager, MenuItem} from "main.popup";

export class SectionSelector
{
	constructor(params)
	{
		this.id = params.id || 'section-select-' + Math.round(Math.random() * 1000000);
		this.sectionList = params.sectionList;
		this.sectionGroupList = params.sectionGroupList;
		this.selectCallback = params.selectCallback;
		this.openPopupCallback = params.openPopupCallback;
		this.closePopupCallback = params.closePopupCallback;
		this.getCurrentSection = params.getCurrentSection;

		this.defaultCalendarType = params.defaultCalendarType;
		this.defaultOwnerId = parseInt(params.defaultOwnerId) || 0;

		this.zIndex = params.zIndex || 3200;
		this.mode = params.mode; // full|compact|textselect
		this.DOM = {
			outerWrap: params.outerWrap
		};

		this.create();
		this.initEventHandlers();
	}

	create()
	{
		if (this.mode === 'textselect')
		{
			this.DOM.select = this.DOM.outerWrap;
			this.DOM.selectImageWrap = this.DOM.select.appendChild(Tag.render`<span class="calendar-field-choice-calendar-img"></span>`);

			this.DOM.selectInnerText = this.DOM.select.appendChild(Tag.render`<span class="calendar-field-choice-calendar-name">${Text.encode(Loc.getMessage('EC_CALENDAR_SECTION_TITLE') + ' ' + this.getCurrentTitle())}</span>`);
		}
		else if(this.mode === 'location')
		{
			this.DOM.select = this.DOM.outerWrap;
			this.DOM.selectImageWrap = this.DOM.select.appendChild(Tag.render`<span class="calendar-field-choice-calendar-img"></span>`);

			this.DOM.selectInnerText = this.DOM.select.appendChild(Tag.render`<span class="calendar-field-choice-calendar-name calendar-field-choice-calendar-name-location">${Text.encode(Loc.getMessage('EC_CALENDAR_LOCATION_TITLE') + ' ' + this.getCurrentTitle())}</span>`);
		}
		else
		{
			this.DOM.select = this.DOM.outerWrap.appendChild(Tag.render`<div class="calendar-field calendar-field-select"></div>`);

			this.DOM.innerValue = this.DOM.select.appendChild(Tag.render`
				<div class="calendar-field-select-icon" style="background-color: ${this.getCurrentColor()}"></div>`
			);

			if (this.mode === 'full')
			{
				this.DOM.selectInnerText = this.DOM.select.appendChild(Tag.render`<span>${Text.encode(this.getCurrentTitle())}</span>`)
			}
		}
	}

	initEventHandlers()
	{
		Event.bind(this.DOM.select, 'click', this.openPopup.bind(this));
	}

	openPopup()
	{
		if (this.viewMode)
		{
			return false;
		}

		if (this.sectionMenu && this.sectionMenu.popupWindow && this.sectionMenu.popupWindow.isShown())
		{
			return this.sectionMenu.close();
		}

		const menuItems = [];
		const sectionIdList = [];
		const sectionList = this.getSectionList();
		let i;

		if (Type.isArray(this.sectionGroupList))
		{
			this.sectionGroupList.forEach((sectionGroup) => {
				let filteredList;
				if (sectionGroup.belongsToView)
				{
					filteredList = sectionList.filter((section) => {
						return SectionSelector.getSectionType(section) === this.defaultCalendarType
							&& SectionSelector.getSectionOwner(section) === this.defaultOwnerId;
					}, this);
				}
				else if (sectionGroup.type === 'user' || sectionGroup.type === 'location')
				{
					filteredList = sectionList.filter((section) => {
						return SectionSelector.getSectionType(section) === 'user'
							&& SectionSelector.getSectionOwner(section) === sectionGroup.ownerId;
					});
				}
				else if (sectionGroup.type === 'company')
				{
					filteredList = sectionList.filter((section) => {
						return SectionSelector.getSectionType(section) === 'company_calendar'
							|| SectionSelector.getSectionType(section) === 'calendar_company'
							|| SectionSelector.getSectionType(section) === sectionGroup.type;
					});
				}
				else
				{
					filteredList = sectionList.filter((section) => {
						return SectionSelector.getSectionType(section) === sectionGroup.type;
					});
				}

				filteredList = filteredList.filter((section) => {
					const id = parseInt(section.id || section.ID);
					if (sectionIdList.includes(id))
						return false;
					sectionIdList.push(id);
					return true;
				});

				if (filteredList.length > 0)
				{
					menuItems.push(
						new MenuItem({
						text: sectionGroup.title,
						delimiter: true
					}));

					for (let i = 0; i < filteredList.length; i++)
					{
						menuItems.push(this.getMenuItem(filteredList[i]));
					}
				}
			});
		}
		else
		{
			for (i = 0; i < sectionList.length; i++)
			{
				menuItems.push(this.getMenuItem(sectionList[i]));
			}
		}

		let offsetLeft = 0;
		if (this.mode === 'compact')
		{
			offsetLeft = 40;
		}
		else if (this.mode === 'textselect' || this.mode === 'location')
		{
			offsetLeft = 0;
		}

		this.sectionMenu = MenuManager.create(
			this.id,
			this.DOM.select,
			menuItems,
			{
				closeByEsc : true,
				autoHide : true,
				zIndex: this.zIndex,
				offsetTop: 0,
				offsetLeft: offsetLeft,
				angle: this.mode === 'compact'
			}
		);

		this.sectionMenu.popupWindow.contentContainer.style.overflow = "auto";
		this.sectionMenu.popupWindow.contentContainer.style.maxHeight = "400px";

		if (this.mode === 'full')
		{
			this.sectionMenu.popupWindow.setWidth(this.DOM.select.offsetWidth - 2);
			this.sectionMenu.popupWindow.contentContainer.style.overflowX = "hidden";
		}

		this.sectionMenu.show();

		// Paint round icons for section menu
		for (i = 0; i < this.sectionMenu.menuItems.length; i++)
		{
			if (this.sectionMenu.menuItems[i].layout.item)
			{
				let icon = this.sectionMenu.menuItems[i].layout.item.querySelector('.menu-popup-item-icon');
				if (icon)
				{
					icon.style.backgroundColor = this.sectionMenu.menuItems[i].color;
				}
			}
		}

		Dom.addClass(this.DOM.select, 'active');

		if (Type.isFunction(this.openPopupCallback))
		{
			this.openPopupCallback(this);
		}

		BX.addCustomEvent(this.sectionMenu.popupWindow, 'onPopupClose', BX.delegate(function()
		{
			if (Type.isFunction(this.openPopupCallback))
			{
				this.closePopupCallback();
			}
			Dom.removeClass(this.DOM.select, 'active');
			MenuManager.destroy(this.id);
			this.sectionMenu = null;
		}, this));
	}

	getCurrentColor()
	{
		return (this.getCurrentSection() || {}).color || false;
	}

	getCurrentTitle()
	{
		return (this.getCurrentSection() || {}).name || '';
	}

	getSectionList()
	{
		return this.sectionList.filter((section) => {
			return (section.PERM && section.PERM.edit) || (Type.isFunction(section.canDo) && section.canDo('edit'));
		});
	}

	updateSectionImageNode(section)
	{
		if (!Type.isElementNode(this.DOM.selectImageWrap))
		{
			return;
		}

		if (section === undefined)
		{
			section = this.sectionList.find((section) => {
				return parseInt(section.id) === parseInt(this.getCurrentSection().id);
			});
		}

		if (section && section.type)
		{
			const imageSrc = SectionSelector.getSectionImage(section);
			let imageNode;
			if (imageSrc)
			{
				imageNode = Tag.render`<img class="calendar-field-choice-calendar-img-value" src="${encodeURI(imageSrc)}">`;
			}
			else if (section.type === 'group')
			{
				imageNode = Tag.render`<div class="ui-icon ui-icon-common-user-group"><i></i></div>`;
			}
			else if (section.type === 'user')
			{
				imageNode = Tag.render`<div class="ui-icon ui-icon-common-user"><i></i></div>`;
			}
			else
			{
				imageNode = Tag.render`<div class="ui-icon ui-icon-common-bitrix24"><i></i></div>`;
			}

			Dom.clean(this.DOM.selectImageWrap);
			this.DOM.selectImageWrap.appendChild(imageNode);
		}
	}

	getPopup()
	{
		return this.sectionMenu;
	}

	getMenuItem(sectionItem)
	{
		return {
			html: BX.util.htmlspecialchars(sectionItem.name || sectionItem.NAME),
			color: sectionItem.color || sectionItem.COLOR,
			className: 'calendar-add-popup-section-menu-item' + (this.mode === 'full' ? ' section-menu-item-full' : ''),
			onclick: ((section) =>
			{
				return () => {
					if (Type.isDomNode(this.DOM.innerValue))
					{
						this.DOM.innerValue.style.backgroundColor = section.color || sectionItem.COLOR;
					}

					this.updateSectionImageNode(section);
					if (Type.isFunction(this.selectCallback))
					{
						if (!section.color && sectionItem.COLOR)
						{
							section.color = sectionItem.COLOR;
						}
						if (!section.id && sectionItem.ID)
						{
							section.id = sectionItem.ID;
						}
						this.selectCallback(section);
					}
					this.sectionMenu.close();
					this.updateValue();
				}
			})(sectionItem)
		}
	}

	static getSectionType(section)
	{
		return section.type || section.CAL_TYPE;
	}

	static getSectionImage(section = {})
	{
		return section.data ? section.data.IMAGE : (section.IMAGE || '');
	}

	static getSectionOwner(section)
	{
		return parseInt(section.OWNER_ID || section.data.OWNER_ID)
	}

	updateValue()
	{
		if (Type.isDomNode(this.DOM.innerValue))
		{
			this.DOM.innerValue.style.backgroundColor = this.getCurrentColor();
		}

		if (this.mode === 'full')
		{
			this.DOM.select.appendChild(Dom.adjust(this.DOM.selectInnerText, {
				text: this.getCurrentTitle(),
				props: {
					title: this.getCurrentTitle()
				}
			}));
		}
		else if (this.mode === 'textselect')
		{
			this.updateSectionImageNode();
			this.DOM.select.appendChild(Dom.adjust(this.DOM.selectInnerText, {
				props: {
					title: Loc.getMessage('EC_CALENDAR_SECTION_TITLE') + ' ' + this.getCurrentTitle()
				},
				text: Loc.getMessage('EC_CALENDAR_SECTION_TITLE') + ' ' + this.getCurrentTitle(),
			}));
		}
		else if (this.mode === 'location')
		{
			this.updateSectionImageNode();
			this.DOM.select.appendChild(Dom.adjust(this.DOM.selectInnerText, {
				props: {
					title: Loc.getMessage('EC_CALENDAR_LOCATION_TITLE') + ' ' + this.getCurrentTitle()
				},
				text: Loc.getMessage('EC_CALENDAR_LOCATION_TITLE') + ' ' + this.getCurrentTitle(),
			}));
		}
	}

	setViewMode(viewMode)
	{
		this.viewMode = viewMode;
		if (this.viewMode)
		{
			Dom.addClass(this.DOM.outerWrap, 'calendar-section-selector-readonly');
			if (this.DOM.outerWrap !== this.DOM.select)
			{
				Dom.removeClass(this.DOM.select, 'calendar-field-select');
				Dom.addClass(this.DOM.select, 'calendar-section-selector-readonly');
			}
		}
		else
		{
			Dom.removeClass(this.DOM.outerWrap, 'calendar-section-selector-readonly');
		}
	}
}
