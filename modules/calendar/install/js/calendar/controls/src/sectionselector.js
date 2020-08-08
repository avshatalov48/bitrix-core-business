import {Type, Dom, Event} from "main.core";

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
		this.defaultOwnerId = parseInt(params.defaultOwnerId);

		this.zIndex = params.zIndex || 3100;
		this.mode = params.mode;
		this.DOM = {
			outerWrap: params.outerWrap
		};

		this.create();
		this.initEventHandlers();
	}

	create()
	{
		this.DOM.select = this.DOM.outerWrap.appendChild(Dom.create('DIV', {
			props: {className: 'calendar-field calendar-field-select' + (this.mode === 'compact' ? ' calendar-field-tiny' : '')}
		}));

		this.DOM.innerValue = this.DOM.select.appendChild(Dom.create('DIV', {
			props: {className: 'calendar-field-select-icon'},
			style: {backgroundColor : this.getCurrentColor()}
		}));

		if (this.mode === 'full')
		{
			this.DOM.selectInnerText = this.DOM.select.appendChild(Dom.create('SPAN', {text: this.getCurrentTitle()}));
		}
	}

	initEventHandlers()
	{
		Event.bind(this.DOM.select, 'click', BX.delegate(this.openPopup, this));
	}

	openPopup()
	{
		if (this.sectionMenu && this.sectionMenu.popupWindow && this.sectionMenu.popupWindow.isShown())
		{
			return this.sectionMenu.close();
		}

		let
			submenuClass = 'main-buttons-submenu-separator main-buttons-submenu-item main-buttons-hidden-label',
			i, menuItems = [], icon;

		if (Type.isArray(this.sectionGroupList))
		{
			this.sectionGroupList.forEach(function(sectionGroup)
			{
				let filteredList = [], i;
				if (sectionGroup.belongsToView)
				{
					filteredList = this.sectionList.filter(this.sectionBelongsToView, this);
				}
				else if (sectionGroup.type === 'user')
				{
					filteredList = this.sectionList.filter(function(section){
						return SectionSelector.getSectionType(section) === 'user' && SectionSelector.getSectionOwner(section) === sectionGroup.ownerId;
					});
				}
				else if (sectionGroup.type === 'company')
				{
					filteredList = this.sectionList.filter(function(section){
						return SectionSelector.getSectionType(section) === 'company_calendar' || SectionSelector.getSectionType(section) === sectionGroup.type;
					});
				}
				else
				{
					filteredList = this.sectionList.filter(function(section){
						return SectionSelector.getSectionType(section) === sectionGroup.type;
					});
				}

				if (filteredList.length > 0)
				{
					menuItems.push({
						text: '<span>' + sectionGroup.title + '</span>',
						className: submenuClass
					});

					for (i = 0; i < filteredList.length; i++)
					{
						menuItems.push(this.getMenuItem(filteredList[i]));
					}
				}
			}, this);
		}
		else
		{
			for (i = 0; i < this.sectionList.length; i++)
			{
				menuItems.push(this.getMenuItem(this.sectionList[i]));
			}
		}

		this.sectionMenu = BX.PopupMenu.create(
			this.id,
			this.DOM.select,
			menuItems,
			{
				closeByEsc : true,
				autoHide : true,
				zIndex: this.zIndex,
				offsetTop: 0,
				offsetLeft: this.mode === 'compact' ? 40 : 0,
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
				icon = this.sectionMenu.menuItems[i].layout.item.querySelector('.menu-popup-item-icon');
				if (icon)
				{
					icon.style.backgroundColor = this.sectionMenu.menuItems[i].color;
				}
			}
		}

		BX.addClass(this.DOM.select, 'active');

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
			BX.removeClass(this.DOM.select, 'active');
			BX.PopupMenu.destroy(this.id);
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

	getPopup()
	{
		return this.sectionMenu;
	}

	getMenuItem(sectionItem)
	{
		let _this = this;
		return {
			text: BX.util.htmlspecialchars(sectionItem.name || sectionItem.NAME),
			color: sectionItem.color || sectionItem.COLOR,
			className: 'calendar-add-popup-section-menu-item',
			onclick: (function (section)
			{
				return function ()
				{
					_this.DOM.innerValue.style.backgroundColor = section.color || sectionItem.COLOR;
					if (_this.DOM.selectInnerText)
					{
						_this.DOM.selectInnerText.innerHTML = BX.util.htmlspecialchars(section.name || section.NAME);
					}

					if (Type.isFunction(_this.selectCallback))
					{
						section.color = section.color || sectionItem.COLOR;
						section.id = parseInt(section.id || sectionItem.ID);
						_this.selectCallback(section);
					}
					_this.sectionMenu.close();
				}
			})(sectionItem)
		}
	}

	sectionBelongsToView(section)
	{
		return SectionSelector.getSectionType(section) === this.defaultCalendarType && SectionSelector.getSectionOwner(section) === this.defaultOwnerId;
	}

	static getSectionType(section)
	{
		return section.type || section.CAL_TYPE;
	}

	static getSectionOwner(section)
	{
		return parseInt(section.OWNER_ID || section.data.OWNER_ID)
	}

	updateValue()
	{
		this.DOM.innerValue.style.backgroundColor = this.getCurrentColor();

		if (this.mode === 'full')
		{
			this.DOM.select.appendChild(Dom.adjust(this.DOM.selectInnerText, {text: this.getCurrentTitle()}));
		}
	}
}