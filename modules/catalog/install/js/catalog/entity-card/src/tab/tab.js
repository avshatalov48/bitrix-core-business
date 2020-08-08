import {Dom, Event, Text, Type} from 'main.core';
import LazyLoader from './lazy-loader';

export default class Tab
{
	constructor(id, settings)
	{
		this.id = Type.isStringFilled(id) ? id : Text.getRandom();
		this.settings = Type.isObjectLike(settings) ? settings : {};
		this.data = Type.isObjectLike(this.settings.data) ? this.settings.data : {};

		this.manager = settings.manager || null;

		this.container = this.settings.container;
		this.menuContainer = this.settings.menuContainer;

		this.active = Type.isBoolean(this.data.active) ? this.data.active : false;
		this.enabled = Type.isBoolean(this.data.enabled) ? this.data.enabled : true;

		Event.bind(
			this.menuContainer.querySelector('a.catalog-entity-section-tab-link'),
			'click',
			this.onMenuClick.bind(this)
		);

		this.loader = null;

		if (Type.isObjectLike(this.data.loader))
		{
			this.loader = new LazyLoader(this.id, {
				...this.data.loader,
				...{
					tabId: this.id,
					container: this.container
				}
			});
		}
	}

	isEnabled()
	{
		return this.enabled;
	}

	isActive()
	{
		return this.active;
	}

	setActive(active)
	{
		active = !!active;

		if (this.isActive() === active)
		{
			return;
		}

		this.active = active;

		if (this.isActive())
		{
			this.showTab()
		}
		else
		{
			this.hideTab()
		}
	}

	showTab()
	{
		Dom.addClass(this.container, 'catalog-entity-section-tab-content-show');
		Dom.removeClass(this.container, 'catalog-entity-section-tab-content-hide');
		Dom.addClass(this.menuContainer, 'catalog-entity-section-tab-current');

		this.container.style.display = '';
		this.container.style.position = 'absolute';
		this.container.style.top = 0;
		this.container.style.left = 0;
		this.container.style.width = '100%';

		(new BX.easing({
			duration: 350,
			start: {opacity: 0, translateX: 100},
			finish: {opacity: 100, translateX: 0},
			transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
			step: (state) => {
				this.container.style.opacity = state.opacity / 100;
				this.container.style.transform = 'translateX(' + state.translateX + '%)';
			},
			complete: () => {
				Dom.removeClass(this.container, 'catalog-entity-section-tab-content-show');
				this.container.style.cssText = '';

				Event.EventEmitter.emit(window, 'onEntityDetailsTabShow', [this]);
			}
		})).animate();

	}

	hideTab()
	{
		Dom.addClass(this.container, 'catalog-entity-section-tab-content-hide');
		Dom.removeClass(this.container, 'catalog-entity-section-tab-content-show');
		Dom.removeClass(this.menuContainer, 'catalog-entity-section-tab-current');

		(new BX.easing({
			duration: 350,
			start: {opacity: 100},
			finish: {opacity: 0},
			transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
			step: (state) => {
				this.container.style.opacity = state.opacity / 100;
			},
			complete: () => {
				this.container.style.display = 'none';
				this.container.style.transform = 'translateX(100%)';
				this.container.style.opacity = 0;
			}
		})).animate();
	}

	onMenuClick(event)
	{
		if (this.isEnabled())
		{
			if (this.loader && !this.loader.isLoaded())
			{
				this.loader.load();
			}

			this.manager.selectItem(this);
		}

		event.preventDefault()
	}
}