import {Dom, Event, Tag, Type} from 'main.core';
import {BaseCard} from 'landing.ui.card.basecard';
import {Loc} from 'landing.loc';

import './css/style.css';
import {IconPanel} from 'landing.ui.panel.iconpanel';

/**
 * @memberOf BX.Landing.UI.Card
 */
export class IconOptionsCard extends BaseCard
{
	options: [string] = [];

	constructor()
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Card.IconOptionsCard');
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () =>
		{
			return Tag.render`
				<div class="landing-ui-card landing-ui-card-icon-options --hide">
					<div class="landing-ui-card-icon-options-title">${Loc.getMessage('LANDING_ICONS_CHANGE_STYLE')}</div>
					<div class="landing-ui-card-icon-options-items"></div>
				</div>
			`;
		});
	}

	getOptionsLayout(): HTMLElement
	{
		return this.getLayout().querySelector('.landing-ui-card-icon-options-items');
	}

	getOptions(): [string]
	{
		return this.options;
	}

	setOptions(options: [], activeOption: string)
	{
		if (options.length > 0)
		{
			this.options = options;
			Dom.clean(this.getOptionsLayout());
			Dom.removeClass(this.getLayout(), '--hide');
			options.forEach(option =>
			{
				const isActive = (option === activeOption) ? ' --active' : '';
				const optionLayout = Tag.render`<span class="${option}${isActive}"></span>`;

				Event.bind(optionLayout, 'click', this.onOptionClick.bind(this, option));

				Dom.append(optionLayout, this.getOptionsLayout());
			});
		}
	}

	onOptionClick(option)
	{
		this.getOptionsLayout().querySelectorAll('span').forEach(optionItem => {
			Dom.removeClass(optionItem, '--active');
			if (Dom.hasClass(optionItem, option))
			{
				Dom.addClass(optionItem, '--active')
			}
		});

		this.emit('onChange', {option: option});
	}

	setOptionsByItem(classList: [])
	{
		IconPanel
			.getLibraries()
			.then(libraries => {
				if (classList.length > 0)
				{
					const iconOptions = new Set();
					let iconOptionActive;

					libraries.forEach(library =>
					{
						library.categories.forEach(category =>
						{
							category.items.forEach(item =>
							{
								if (Type.isObject(item))
								{
									const foundedOptions = item.options.filter(option =>
										classList.every(iconClass => option.split(' ').includes(iconClass))
									);
									if (foundedOptions.length > 0)
									{
										item.options.forEach(option => {iconOptions.add(option)});
										iconOptionActive = foundedOptions[0];
									}
								}
								else
								{
									if (
										classList.every(iconClass => item.split(' ').includes(iconClass))
									)
									{
										iconOptions.add(item);
										iconOptionActive = item;
									}
								}
							})
						})
					});

					if (iconOptions.size > 0)
					{
						this.setOptions([...iconOptions], iconOptionActive);
					}
				}
			});
	}
}
