import {Content} from 'landing.ui.panel.content';
import {SidebarButton} from 'landing.ui.button.sidebarbutton';
import {IconListCard} from 'landing.ui.card.iconlistcard';
import {BaseButton} from 'landing.ui.button.basebutton';
import {TextField} from 'landing.ui.field.textfield';

import {Loc} from 'landing.loc';
import {Cache, Dom, Runtime, Type, Tag} from 'main.core';

import './css/style.css';

/**
 * @memberOf BX.Landing.UI.Panel
 */
export class IconPanel extends Content
{
	resolver: function;
	iconList: IconListCard;
	searchField: TextField;

	static SUPPORTED_LANG = ['en', 'ru'];
	static DEFAULT_LANG = 'en';

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
		this.iconList = null;

		this.search = Runtime.debounce(this.search, 500).bind(this);
		// todo: add lupa icon after
		this.searchField = new TextField({
			className: 'landing-ui-panel-icon-search',
			placeholder: 'search...',
			textOnly: true,
			onInput: this.search,
		});

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
					'landing.icon.fontawesome6_brands',
					'landing.icon.fontawesome6_1',
					'landing.icon.fontawesome6_2',
					'landing.icon.fontawesome6_3',
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

		Dom.append(this.searchField.getLayout(), this.sidebar);

		IconPanel
			.getLibraries()
			.then((libraries) => {
				let defaultCategory = null;
				libraries.forEach(({id, name: text, active, categories}) => {
					if (active === false)
					{
						return;
					}

					if (!defaultCategory)
					{
						defaultCategory = categories[0].id;
					}

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

				// todo: init current category and icon?
				if (defaultCategory)
				{
					this.onCategoryChange(defaultCategory);
				}
			});

		// bottom buttons
		this.appendFooterButton(
			new BaseButton("save_icon", {
				text: Loc.getMessage("LANDING_ICON_PANEL_BUTTON_CHOOSE"),
				onClick: () => {
					if (this.iconList.getActiveIcon())
					{
						this.resolver({
							iconOptions: this.iconList.getActiveOptions(),
							iconClassName: this.iconList.getActiveIcon(),
						});
					}
					void this.hide();
				},
				className: "landing-ui-button-content-save"
			})
		);
		this.appendFooterButton(
			new BaseButton("cancel_icon", {
				text: Loc.getMessage("LANDING_ICON_PANEL_BUTTON_CANCEL"),
				onClick: this.hide.bind(this),
				className: "landing-ui-button-content-cancel"
			})
		);
	}

	fillIconsList(items: [], title: string)
	{
		this.iconList = new IconListCard();
		this.iconList.setTitle(title);

		items.forEach((item) => {
			if (Type.isObject(item))
			{
				const iconOptions = {
					options: item.options ? item.options : {},
					defaultOption: item.defaultOption ? item.defaultOption : '',
				}

				this.iconList.addItem(item.className, iconOptions);
			}
			else
			{
				this.iconList.addItem(item);
			}
		});

		this.appendCard(this.iconList);
	}

	onCategoryChange(id: string)
	{
		this.content.innerHTML = '';

		if (this.sidebarButtons.getActive())
		{
			this.sidebarButtons.getActive().deactivate();
		}
		this.sidebarButtons.get(id).activate();

		IconPanel
			.getLibraries()
			.then((libraries) => {
				libraries.forEach((library) => {
					if (library.active === false)
					{
						return;
					}

					library.categories.forEach((category) => {
						if (id === category.id)
						{
							this.fillIconsList(category.items, category.name);
						}
					});
				});
			});
	}

	search(query: string)
	{
		// todo: replaces ',' to space
		// mega optimization!
		if (query.trim().length < 2)
		{
			return;
		}

		// dbg
		const date = new Date();
		console.log('search at query "', query, '"was started at', date.getSeconds(), date.getMilliseconds());

		this.content.innerHTML = '';
		if (this.sidebarButtons.getActive())
		{
			this.sidebarButtons.getActive().deactivate();
		}


		// todo: need loader?
		IconPanel
			.getLibraries()
			.then((libraries) => {
				const result = [];
				// todo: can set language_id to collator?
				const collator = new Intl.Collator(undefined, {
					usage: 'search',
					sensitivity: 'base',
					ignorePunctuation: true,
				});
				const preparedQuery = query.toLowerCase().trim().split(' ');
				if (preparedQuery.length === 0)
				{
					return;
				}

				libraries.forEach((library) => {
					if (library.active === false)
					{
						return;
					}

					library.categories.forEach((category) => {
						category.items.forEach((item) => {
							if (
								Type.isObject(item)
								&& item.keywords
								&& item.keywords !== ''
							)
							{
								const isFind = preparedQuery.every((queryWord) => {
									return item.keywords.split(' ').find(word => {
										return collator.compare(queryWord, word) === 0;
									});
								});
								if (isFind)
								{
									result.push(item);
								}
							}
						});
					});
				});

				// print
				const title = 'Search result "' + query.trim() + '"';
				if (result.length > 0)
				{
					this.fillIconsList(result, title);
				}
				else
				{
					this.iconList = new IconListCard();
					this.iconList.setTitle(title);
					Dom.append(this.getNotFoundMessage(), this.iconList.getBody());
					this.appendCard(this.iconList);
				}

				// dbg
				const dateEnd = new Date();
				console.log('search at query"', query, '"was end at____', dateEnd.getSeconds(), dateEnd.getMilliseconds());
			});
	}

	getNotFoundMessage(): HTMLElement
	{
		// todo: remove unnecessary phrases for diff langs
		return IconPanel.cache.remember('notFoundMsg', () => {
			let textMsgId, imageClass;
			const lang = Loc.getMessage('LANGUAGE_ID');
			if (lang === IconPanel.DEFAULT_LANG)
			{
				textMsgId = 'LANDING_ICON_PANEL_NOT_FOUND_EN';
				imageClass = '--en';
			}
			else if (IconPanel.SUPPORTED_LANG.indexOf(Loc.getMessage('LANGUAGE_ID')) !== -1)
			{
				// todo: correct phrases
				textMsgId = 'LANDING_ICON_PANEL_NOT_FOUND_EN';
				imageClass = '--not_found';
			}
			else
			{
				textMsgId = 'LANDING_ICON_PANEL_NOT_FOUND_OTHER';
				imageClass = '--incorrect_lang';
			}

			return Tag.render`<div class="landing-ui-panel-icon-not-found">
				<div class="landing-ui-panel-icon-not-found-image ${imageClass}"></div>
				<div class="landing-ui-panel-icon-not-found-title">
					${Loc.getMessage(textMsgId)}
				</div>
			</div>`;
		});
	}

	show(): Promise<any> {
		return new Promise((resolve) => {
			this.resolver = resolve;
			this.makeLayout();
			void super.show();
		});
	}
}