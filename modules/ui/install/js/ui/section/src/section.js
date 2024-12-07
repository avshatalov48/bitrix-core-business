import { Tag, Dom, Text, Type, Event } from 'main.core';
import { Row } from './row';
import type { SectionParams } from './types/sections';

export class Section
{
	title: string;
	id: string;
	content: ?HTMLElement;
	isOpen: boolean;
	canCollapse: boolean;
	className: {
		titleIcon: String,
		arrowTop: String,
		arrowDown: String,
		arrowRight: String,
		bodyActive: String
	} = {
		titleIcon: '',
		arrowTop: '--chevron-up',
		arrowDown: '--chevron-down',
		arrowRight: '--chevron-right',
		bodyActive: '--body-active ',
	};
	rowsWrapper: ?HTMLElement;
	sectionWrapper: ?HTMLElement;
	isEnable: ?boolean;
	bannerCode: ?string;
	singleLink: {
		href: String,
		isSidePanel: boolean,
	} = {
			href: '',
			isSidePanel: false,
		};

	constructor(params: SectionParams)
	{
		this.title = Type.isString(params.title) ? params.title : '';

		Type.isStringFilled(params.titleIconClasses) ? (this.className.titleIcon = params.titleIconClasses) : '';
		Type.isStringFilled(params.iconArrowDown) ? (this.className.arrowDown = params.iconArrowDown) : '';
		Type.isStringFilled(params.iconArrowTop) ? (this.className.arrowTop = params.iconArrowTop) : '';
		Type.isStringFilled(params.iconArrowRight) ? (this.className.arrowRight = params.iconArrowRight) : '';

		if (Type.isStringFilled(params.bodyActive))
		{
			this.className.bodyActive += params.bodyActive;
		}

		this.isOpen = Type.isBoolean(params.isOpen) ? params.isOpen : true;
		this.isEnable = Type.isBoolean(params.isEnable) ? params.isEnable : true;
		this.canCollapse = params.canCollapse !== false;
		this.id = Type.isNil(params.id) ? 'section_' + Text.getRandom(8) : params.id;
		this.bannerCode = Type.isStringFilled(params.bannerCode) ? params.bannerCode : null;

		if (params.singleLink)
		{
			Type.isStringFilled(params.singleLink.href) ? (this.singleLink.href = params.singleLink.href) : '';
			Type.isBoolean(params.singleLink.isSidePanel) ? (this.singleLink.isSidePanel = params.singleLink.isSidePanel) : '';
		}
	}

	render(): HTMLElement
	{
		if (this.content)
		{
			return this.content;
		}

		this.content = this.getContent();
		const triggerElements = this.content.querySelectorAll('.ui-section__header');
		const elementList = [...triggerElements];

		if (this.canCollapse && !this.singleLink.href)
		{
			for (const element of elementList)
			{
				if (Type.isElementNode(element))
				{
					element.addEventListener('click', this.toggle.bind(this))
				}
			}
		}
		else if (this.singleLink.href)
		{
			for (const element of elementList)
			{
				if (Type.isElementNode(element))
				{
					Event.bind(element, 'click', () => {
						if (this.singleLink.isSidePanel)
						{
							BX.SidePanel.Instance.open(this.singleLink.href);
						}
						else
						{
							window.open(this.singleLink.href, '_blank');
						}
					});
				}
			}
		}

		return this.content;
	}

	getId(): string
	{
		return this.id;
	}

	toggle(open: ?boolean, withAnimation: boolean = true): void
	{
		const container = this.content;
		let iconNode = this.render().querySelector('.ui-section__collapse-icon');
		this.isOpen = (open === true || open === false) ? open : !this.isOpen;

		const innerContainer = this.content.querySelector('.ui-section__section-body_inner');

		Dom.removeClass(innerContainer, 'ui-section__section-toggle-animation');
		if (withAnimation !== false)
		{
			Dom.addClass(innerContainer, 'ui-section__section-toggle-animation');
		}

		if (this.isOpen)
		{
			Dom.removeClass(iconNode, this.className.arrowDown);
			Dom.addClass(iconNode, this.className.arrowTop);
			Dom.addClass(container, this.className.bodyActive);
		}
		else
		{
			Dom.addClass(iconNode, this.className.arrowDown);
			Dom.removeClass(iconNode, this.className.arrowTop);
			Dom.removeClass(container, this.className.bodyActive);
		}
	}

	getContent(): HTMLElement
	{
		if (this.sectionWrapper)
		{
			return this.sectionWrapper;
		}

		this.sectionWrapper = Tag.render`
			<div id="${this.id}" class="ui-section__wrapper ${this.isOpen ? this.className.bodyActive : ''} ${this.canCollapse || this.singleLink.href ? 'clickable' : ''}">
				<div class="ui-section__header ${!this.title ? '--hidden' : ''}">
					<span class="ui-section__title-icon ${this.className.titleIcon}"></span>
					<span class="ui-section__title">${this.title}</span>
					${this.isEnable ? '' : this.renderLockElement()}
					${this.singleLink.href ? this.#linkIconRender() : this.#collapseIconRender()}
				</div>
				<div class="ui-section__separator ${!this.title ? '--hidden' : ''}"></div>
				<div class="ui-section__content ui-section__section-body_inner">
					<div class="ui-section__section-section-body_container">
						<div class="ui-section__row_box"></div>
					</div>
				</div>
			</div>
		`;

		return this.sectionWrapper;
	}

	#linkIconRender(): HTMLElement
	{
		return Tag.render`<span class="ui-section__collapse-icon clickable ui-icon-set ${this.className.arrowRight}"></span>`;
	}

	#collapseIconRender(): HTMLElement
	{
		if (this.canCollapse)
		{
			return Tag.render`<span class="ui-section__collapse-icon clickable ui-icon-set ${this.isOpen ? this.className.arrowTop : this.className.arrowDown}"></span>`;
		}
		else
		{
			return Tag.render`<span class="ui-section__collapse-icon"></span>`;
		}
	}

	#getRowsWrapper(): HTMLElement
	{
		if (this.rowsWrapper)
		{
			return this.rowsWrapper;
		}

		this.rowsWrapper = this.render().querySelector('.ui-section__row_box');

		return this.rowsWrapper;
	}

	addRows(rows: Array<Row>): void
	{
		rows.forEach((item) => {
			this.append(item.render());
		});
	}

	addRow(row: Row): void
	{
		this.append(row.render());
	}

	append(content: HTMLElement): void
	{
		Dom.append(content, this.#getRowsWrapper());
	}

	prepend(content: HTMLElement)
	{
		Dom.prepend(content, this.#getRowsWrapper());
	}

	renderTo(targetNode: HTMLElement): HTMLElement
	{
		if (!Type.isDomNode(targetNode))
		{
			throw new Error('Target node must be HTMLElement');
		}

		return Dom.append(this.render(), targetNode);
	}

	getBannerCode(): ?string
	{
		return this.bannerCode;
	}

	showBanner(): void
	{
		if (this.getBannerCode())
		{
			BX.UI.InfoHelper.show(this.getBannerCode());
		}
	}

	renderLockElement(): HTMLElement
	{
		const lockElement = Tag.render`<span class="ui-section__title-icon ui-icon-set --lock field-has-lock" onclick="event.stopPropagation()"></span>`;

		Event.bind(lockElement, 'click', () => {
			this.showBanner();
		});

		return lockElement;
	}
}
