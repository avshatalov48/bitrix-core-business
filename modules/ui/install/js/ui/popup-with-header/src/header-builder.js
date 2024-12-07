import { Tag, Type, Dom } from 'main.core';
import { RoundPlayer, PlayerOptions } from './round-player';
import { PopupComponentsMakerItem } from 'ui.popupcomponentsmaker';
import { Button, ButtonColor, ButtonSize } from 'ui.buttons';
import { Icon, Actions } from 'ui.icon-set.api.core';
import { FeaturePromotersRegistry } from 'ui.info-helper';

export type TitleHeaderOptions = {
	title: string | HTMLElement | null,
	subtitle: string | HTMLElement | null
}

export type ButtonOptions = {
	label: string,
	url: string,
}

export type DescriptionHeaderOptions = {
	title: string,
	subtitle: ?string,
	subtitleDescription: ?string,
	moreLabel: ?string,
	code: ?string,
	roundContent: PlayerOptions | string
}

export type TariffHeaderOptions = {
	icon: ?Icon,
	iconClass: ?string,
	top: TitleHeaderOptions,
	info: DescriptionHeaderOptions,
	button: ?ButtonOptions,
	analyticsCallback: ?Function,
}

export class HeaderBuilder
{
	#options: TariffHeaderOptions = null;
	#content: HTMLElement;
	#player: ?RoundPlayer;

	constructor(options: TariffHeaderOptions)
	{
		this.#options = options;
	}

	buildPlayer(playerOptions: PlayerOptions): RoundPlayer
	{
		return new RoundPlayer({
			wrapper: playerOptions.wrapper,
			pausePlayerWidth: playerOptions.width,
			scale: playerOptions.scale,
			posterUrl: playerOptions.posterUrl,
			videos: playerOptions.videos,
			loop:  playerOptions.loop,
			autoplay: playerOptions.autoplay,
			muted:  playerOptions.muted,
			analyticsCallback: this.#options.analyticsCallback,
		});
	}

	renderPlayer(playerOptions: PlayerOptions): HTMLElement
	{
		if (this.#player)
		{
			return this.#player
		}

		const wrapper = Tag.render`<div class="ui-popupcomponentsmaker__round-player-box"/>`;
		this.#player = this.buildPlayer({ ...playerOptions, wrapper: wrapper });

		if (this.#player)
		{
			return this.#player.render();
		}

		return Tag.render``;
	}

	getPlayer(): RoundPlayer
	{
		return this.#player;
	}

	renderTitle(titleOptions: TitleHeaderOptions): HTMLElement
	{
		const title = Tag.render`
			<div class="ui-popupcomponentsmaker-header-tariff__header-content">
				<div class="ui-popupcomponentsmaker-header-tariff__title">${titleOptions.title}</div>
			</div>
		`;
		if (!Type.isNil(titleOptions.subtitle))
		{
			Dom.append(Tag.render`<div class="ui-popupcomponentsmaker-header-tariff__subtitle">${titleOptions.subtitle}</div>`, title);
		}

		return title;
	}

	renderDescription(descriptionOptions: DescriptionHeaderOptions): HTMLElement
	{
		const descriptionText = Tag.render`
		<div class="ui-popupcomponentsmaker-header-tariff__box">
			<div class="ui-popupcomponentsmaker-header-tariff__title">${descriptionOptions.title}</div>
		</div>`;
		if (!Type.isNil(descriptionOptions.subtitle))
		{
			Dom.append(Tag.render`<div class="ui-popupcomponentsmaker-header-tariff__subtitle">${descriptionOptions.subtitle}</div>`, descriptionText);
		}

		if (!Type.isNil(descriptionOptions.subtitleDescription))
		{
			Dom.append(Tag.render`<div class="ui-popupcomponentsmaker-header-tariff__text">${descriptionOptions.subtitleDescription}</div>`, descriptionText);
		}

		if (!Type.isNil(descriptionOptions.code))
		{
			const onclick = (e) => {
				e.stopPropagation();
				FeaturePromotersRegistry.getPromoter({ code: descriptionOptions.code }).show();
			};
			Dom.append(Tag.render`<a onclick="${onclick}" target="_blank" class="ui-popupcomponentsmaker-header-tariff__more">${descriptionOptions.moreLabel}<div class="ui-icon-set --chevron-right ui-popupcomponentsmaker-header-tariff__more-icon"></div></a>`, descriptionText);
		}

		let roundContent = '';
		if (Type.isPlainObject(descriptionOptions.roundContent))
		{
			roundContent = this.renderPlayer(descriptionOptions.roundContent);
		}
		else if (Type.isStringFilled(descriptionOptions.roundContent))
		{
			roundContent = this.renderIcon(descriptionOptions.roundContent);
		}
		else if (Type.isDomNode(descriptionOptions.roundContent))
		{
			roundContent = this.embedIcon(descriptionOptions.roundContent);
		}

		const descriptionBlock = Tag.render`
			<div class="ui-popupcomponentsmaker-header-tariff__message-wrapper">
				${roundContent}
				${descriptionText}
			</div>
		`;

		const description = new PopupComponentsMakerItem({
			html: descriptionBlock,
			withoutBackground: false,
		});

		Dom.addClass(description.getContainer(), 'ui-popupcomponentsmaker-header-tariff__section-message-wrapper');
		description.getContainer().style.marginTop = '14px';
		description.getContainer().classList.add('--transparent');

		return description.getContainer();
	}

	renderBtn(btnOptions: ButtonOptions | Button): HTMLElement
	{
		const btn = btnOptions instanceof Button
			? btnOptions
			: new Button({
				text: btnOptions.label,
				color: ButtonColor.LIGHT_BORDER,
				size: ButtonSize.SMALL,
				link: btnOptions.url,
				onclick: () => {
					if (this.#options.analyticsCallback)
					{
						this.#options.analyticsCallback('click-button-header', btnOptions.url);
					}
				},
				round: true,
				noCaps: true,
			})
		;
		btn.addClass('ui-popupcomponentsmaker-header-tariff__button ui-btn-themes');

		return btn.render();
	}

	renderIcon(iconClass: string): HTMLElement
	{
		if (Type.isStringFilled(iconClass))
		{
			return Tag.render`
				<div class="ui-popupcomponentsmaker-header-tariff__icon">
					<div class="ui-icon-set ${iconClass}"></div>
				</div>
			`;
		}

		return Tag.render``;
	}

	embedIcon(icon: HTMLElement): HTMLElement
	{
		if (Type.isDomNode(icon))
		{
			return Tag.render`
				<div class="ui-popupcomponentsmaker-header-tariff__icon">
					${icon}
				</div>
			`;
		}

		return Tag.render``;
	}

	render(): HTMLElement
	{
		if (this.#content)
		{
			return this.#content;
		}
		let btnContent = '';
		if (this.#options.button)
		{
			btnContent = Tag.render`
				<div class="ui-popupcomponentsmaker-header-tariff__button-bar">
					${this.renderBtn(this.#options.button)}
				</div>`;
		}
		this.#content = Tag.render`
			<div class="ui-popupcomponentsmaker-header-tariff__wrapper">
				<div class="ui-popupcomponentsmaker-header-tariff__title-section">
					${this.#options.icon instanceof HTMLElement ? this.embedIcon(this.#options.icon) : this.renderIcon(this.#options.iconClass)}
					${this.renderTitle(this.#options.top)}
				</div>
				
				${this.renderDescription(this.#options.info)}
				${btnContent}
				
			</div>
		`;

		return this.#content;
	}
}
