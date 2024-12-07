import { Dom, Tag, Text } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { PopupComponentsMakerItem } from 'ui.popupcomponentsmaker';
import type { TariffHeaderOptions } from './header-builder';
import { HeaderBuilder } from './header-builder';
import './header.css';

export class PopupHeader extends PopupComponentsMakerItem
{
	constructor(options = {})
	{
		options.withoutBackground = true;
		options.backgroundColor = null;
		options.backgroundImage = null;
		super(options);
	}

	getContainer(): HTMLElement
	{
		if (!this.layout.container)
		{
			const theme = this.#getThemePicker()?.getAppliedTheme();
			this.layout.container = Tag.render`<div class="ui-popupcomponentsmaker__header">${this.getContent()}</div>`;
			this.bacgroundNode = Tag.render`<div class="ui-popupcomponentsmaker__header-background"></div>`;
			Dom.append(this.bacgroundNode, this.layout.container);

			if (theme)
			{
				this.#applyTheme(this.bacgroundNode, theme);
			}

			EventEmitter.subscribe(
				'BX.Intranet.Bitrix24:ThemePicker:onThemeApply',
				(event) =>
				{
					this.#applyTheme(this.bacgroundNode, event.data.theme);
				},
			);
		}

		return super.getContainer();
	}

	#getThemePicker(): ?BX.Intranet.Bitrix24.ThemePicker
	{
		return BX.Intranet?.Bitrix24?.ThemePicker.Singleton ?? top.BX.Intranet?.Bitrix24?.ThemePicker.Singleton;
	}

	#applyTheme(container, theme): void
	{
		const previewImage = `url('${Text.encode(theme.previewImage)}')`;
		Dom.style(container, 'backgroundImage', previewImage);
		Dom.removeClass(this.layout.container, 'bitrix24-theme-default bitrix24-theme-dark bitrix24-theme-light');
		let themeClass = 'bitrix24-theme-default';

		if (theme.id !== 'default')
		{
			themeClass = String(theme.id).indexOf('dark:') === 0 ? 'bitrix24-theme-dark' : 'bitrix24-theme-light';
		}

		Dom.addClass(this.layout.container, themeClass);
	}

	static createByJson(popupId: string, options: TariffHeaderOptions): PopupHeader
	{
		const builder = new HeaderBuilder(options);
		const header = new PopupHeader({
			html: builder.render(),
		});

		EventEmitter.subscribe('BX.Main.Popup:onClose', (event) => {
			if (popupId === event.target.uniquePopupId)
			{
				builder.getPlayer()?.stop();
			}
		});

		return header;
	}
}
