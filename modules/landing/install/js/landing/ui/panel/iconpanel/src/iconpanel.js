import {Content} from 'landing.ui.panel.content';
import {SidebarButton} from 'landing.ui.button.sidebarbutton';
import {Loc} from 'landing.loc';
import {Cache, Dom, Runtime, Tag, Type} from 'main.core';

/**
 * @memberOf BX.Landing.UI.Panel
 */
export class IconPanel extends Content
{
	constructor(...args)
	{
		super(...args);
		this.setEventNamespace('BX.Landing.UI.Panel.IconPanel');
		this.setTitle(Loc.getMessage('LANDING_ICONS_SLIDER_TITLE'));

		Dom.addClass(this.layout, 'landing-ui-panel-icon');
		Dom.addClass(this.overlay, 'landing-ui-panel-icon');

		Dom.attr(this.layout, 'hidden', true);
		Dom.attr(this.overlay, 'hidden', true);

		this.resolver = () => {};

		Dom.append(this.layout, document.body);
	}

	static getInstance(): IconPanel
	{
		if (!IconPanel.instance)
		{
			IconPanel.instance = new IconPanel();
		}

		return IconPanel.instance;
	}

	static cache = new Cache.MemoryCache();

	static getLibraries(): Promise<[{[key: string]: any}]>
	{
		return IconPanel.cache.remember('libraries', () => {
			return Runtime
				.loadExtension([
					'landing.icon.fontawesome',
					'landing.icon.etlineicons',
					'landing.icon.hsicons',
					'landing.icon.simpleline',
					'landing.icon.simplelinepro1',
					'landing.icon.simplelinepro2',
				])
				.then(Object.values);
		});
	}

	makeLayout()
	{
		if (Type.isStringFilled(this.content.innerHTML))
		{
			return;
		}

		IconPanel
			.getLibraries()
			.then((libraries) => {
				libraries.forEach(({id, name: text, categories}) => {
					this.appendSidebarButton(
						new SidebarButton({
							id,
							text,
						}),
					);

					categories.forEach((category) => {
						this.appendSidebarButton(
							new SidebarButton({
								id: category.id,
								text: category.name,
								onClick: this.onCategoryChange.bind(this, category.id),
								child: true,
							}),
						);
					});
				});

				this.onCategoryChange(libraries[0].categories[0].id);
			});
	}

	onCategoryChange(id: string)
	{
		this.content.innerHTML = '';

		IconPanel
			.getLibraries()
			.then((libraries) => {
				libraries.forEach((library) => {
					library.categories.forEach((category) => {
						if (id === category.id)
						{
							const map = new Map();

							const categoryCard = new BX.Landing.UI.Card.BaseCard({
								title: category.name,
								className: 'landing-ui-card-icons',
							});

							category.items.forEach((item) => {
								const icon = Tag.render`
									<span class="${item}" onclick="${this.onChange.bind(this, item)}"></span>
								`;
								const iconLayout = Tag.render`
									<div class="landing-ui-card landing-ui-card-icon">
										${icon}
									</div>
								`;

								Dom.append(iconLayout, categoryCard.body);

								const styles = getComputedStyle(icon, ':before');

								requestAnimationFrame(() => {
									const content = styles.getPropertyValue('content');
									if (map.has(content))
									{
										iconLayout.hidden = true;
									}
									else
									{
										map.set(content, true);
									}
								});
							});

							this.appendCard(categoryCard);
						}
					});
				});
			});
	}

	onChange(icon: string)
	{
		this.resolver(icon);
		void this.hide();
	}

	show(): Promise<any> {
		return new Promise((resolve) => {
			this.resolver = resolve;
			this.makeLayout();
			void super.show();
		});
	}
}