import { Dom, Event } from 'main.core';

export class Base
{
	constructor(widgetElement)
	{
		this.element = widgetElement;
	}

	deleteContextDependentContainer(options)
	{
		if (!options)
		{
			return;
		}

		this.mainContainer = options.mainContainer ?? null;
		this.sidebarContainer = options.sidebarContainer ?? null;
		const sidebarElements = document.querySelectorAll('.landing-sidebar');
		let isInsideSidebar = false;
		sidebarElements.forEach((sidebarElement) => {
			if (sidebarElement.contains(this.element))
			{
				isInsideSidebar = true;
			}
		});

		if (isInsideSidebar && this.mainContainer)
		{
			this.mainContainer.remove();
		}

		if (!isInsideSidebar && this.sidebarContainer)
		{
			this.sidebarContainer.remove();
		}
	}

	toggleExtendViewButtonBehavior(options)
	{
		if (!options)
		{
			return;
		}

		this.extendButton = options.extendButton ?? null;
		this.viewAllButton = options.viewAllButton ?? null;
		this.isShowExtendButton = options.isShowExtendButton ?? null;
		this.grid = options.grid ?? null;
		this.gridExtendedClass = options.gridExtendedClass ?? null;
		this.buttonHideClass = options.buttonHideClass ?? null;

		if (this.extendButton && this.viewAllButton)
		{
			if (this.isShowExtendButton)
			{
				Event.bind(this.extendButton, 'click', () => {
					if (this.grid)
					{
						Dom.addClass(this.grid, this.gridExtendedClass);
						setTimeout(() => {
							Dom.addClass(this.extendButton, this.buttonHideClass);
							Dom.removeClass(this.viewAllButton, this.buttonHideClass);
						}, 300);
					}
				});
			}
			else
			{
				Dom.addClass(this.extendButton, this.buttonHideClass);
				Dom.removeClass(this.viewAllButton, this.buttonHideClass);
			}
		}
	}
}
