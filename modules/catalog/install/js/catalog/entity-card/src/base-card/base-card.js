import {Dom, Tag, Text, Type} from "main.core";
import TabManager from "../tab/manager";

export class BaseCard
{
	constructor(id, settings = {})
	{
		this.id = Type.isStringFilled(id) ? id : Text.getRandom();
		this.entityId = Text.toInteger(settings.entityId) || 0;
		this.settings = settings;
		this.container = document.getElementById(settings.containerId);

		this.initializeTabManager();
		this.checkFadeOverlay();
	}

	initializeTabManager()
	{
		return new TabManager(this.id, {
			container: document.getElementById(this.settings.tabContainerId),
			menuContainer: document.getElementById(this.settings.tabMenuContainerId),
			data: this.settings.tabs || []
		});
	}

	checkFadeOverlay()
	{
		if (this.entityId <= 0)
		{
			this.overlay = Tag.render`<div class="catalog-entity-overlay"></div>`;
			Dom.append(this.overlay, this.container);

			if (window === window.top)
			{
				this.overlay.style.position = 'absolute';
				this.overlay.style.top = this.overlay.style.left = this.overlay.style.right = '-15px';
			}
		}
	}
}
