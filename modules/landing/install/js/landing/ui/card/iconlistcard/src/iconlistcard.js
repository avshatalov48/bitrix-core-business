import {Dom, Event, Tag, Type} from 'main.core';
import {BaseEvent} from "main.core.events";
import {Loc} from 'landing.loc';
import {BaseCard} from 'landing.ui.card.basecard';
import {IconOptionsCard} from 'landing.ui.card.iconoptionscard';

import 'ui.fonts.opensans';
import './css/style.css';

/**
 * @memberOf BX.Landing.UI.Card
 */
export class IconListCard extends BaseCard
{
	items: Map;
	activeIcon: ?string;
	itemsContainer: ?HTMLDivElement;

	constructor(
		options: {
			id?: string,
			header?: string,
			description?: string,
			context?: string,
			icon?: string,
			angle?: boolean,
			closeable?: boolean,
			hideActions?: boolean,
			restoreState?: boolean,
			more?: string | () => {},
		},
	)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Card.IconListCard');
		this.title = '';
		this.items = new Map();
		this.activeIcon = null;

		this.previewOptionsCard = new IconOptionsCard();
		this.previewOptionsCard.subscribe('onChange', this.onPreviewOptionClick.bind(this));
		Dom.append(this.previewOptionsCard.getLayout(), this.getPreviewOptions());
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () =>
		{
			return Tag.render`
				<div class="landing-ui-card landing-ui-card-icons">
					<div class="landing-ui-card-header-wrapper">
						${this.getHeader()}
						${this.getPreview()}
					</div>
					<div class="landing-ui-card-body-wrapper">
						${this.getBody()}
					</div>
				</div>
			`;
		});
	}

	getPreview(): HTMLDivElement
	{
		return this.cache.remember('preview', () =>
		{
			return Tag.render`
				<div class="landing-ui-card-preview --hide">
					<div class="landing-ui-card-preview-icon"></div>
					<div class="landing-ui-card-preview-options"></div>
				</div>
			`;
		});
	}

	getPreviewIcon(): HTMLElement
	{
		return this.getPreview().querySelector('.landing-ui-card-preview-icon');
	}

	getPreviewOptions(): HTMLElement
	{
		return this.getPreview().querySelector('.landing-ui-card-preview-options');
	}

	setPreviewIcon(className: string)
	{
		const icon = Tag.render`<span class="${className}"></span>`;
		Dom.clean(this.getPreviewIcon());
		Dom.append(icon, this.getPreviewIcon());
	}

	addItem(item: string, additional: ?{})
	{
		if (this.getBody().childElementCount === 0)
		{
			this.itemsContainer = Tag.render`<div class="landing-ui-card-icons-container"></div>`;
			Dom.append(this.itemsContainer, this.getBody());
		}

		const icon = Tag.render`
			<div class="landing-ui-card landing-ui-card-icon">
				<span class="${item}"></span>
			</div>
		`;
		Event.bind(icon, 'click', this.onItemClick.bind(this, icon, additional));
		Dom.append(icon, this.itemsContainer);

		// todo: need?
		// duplicate control
		const styles = getComputedStyle(icon.querySelector('span'), ':before');
		requestAnimationFrame(() => {
			const content = styles.getPropertyValue('content');
			if (content === 'none')
			{
				console.warn('Attention, item "' + item + '" has no content');
			}
			if (this.items.has(content))
			{
				icon.hidden = true;
			}
			else
			{
				this.items.set(content, true);
			}
		});
	}

	onItemClick(item: HTMLElement, additional: ?{}): void
	{
		const prevActive = this.getBody().querySelector('.landing-ui-card-icon.--active');
		if (prevActive)
		{
			Dom.removeClass(prevActive, '--active');
		}
		Dom.addClass(item, '--active');

		this.activeIcon = item.firstElementChild.className;

		if (Type.isObject(additional))
		{
			this.setPreviewIcon(additional.defaultOption);
			this.previewOptionsCard.setOptions(additional.options, additional.defaultOption);
		}
		else
		{
			this.setPreviewIcon(this.activeIcon);
			this.previewOptionsCard.setOptions([this.activeIcon], this.activeIcon);
		}
		Dom.removeClass(this.getPreview(), '--hide');
	}

	onPreviewOptionClick(event: BaseEvent)
	{
		const option = event.getData().option;
		this.activeIcon = option;
		this.setPreviewIcon(option);
	}

	getActiveIcon(): ?string
	{
		return this.activeIcon;
	}

	getActiveOptions(): [string]
	{
		return this.previewOptionsCard.getOptions();
	}
}
