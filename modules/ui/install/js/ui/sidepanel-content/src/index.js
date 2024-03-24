import { Dom, Loc, Tag, Text, Event } from 'main.core';
import { SidePanel } from 'main.sidepanel';
import { Layout } from 'ui.sidepanel.layout';
import 'ui.helper';

export const StubType = Object.freeze({
	notAvailable: 'notAvailable',
	noAccess: 'noAccess',
	noConnection: 'noConnection',
});

export const StubLinkType = Object.freeze({
	helpdesk: 'helpdesk',
	href: 'href',
});

type StubNotAvailableOptions = {
	title: string | null,
	desc: string | null,
	type: $Keys<typeof StubType> | null,
	link?: LinkOptions | null,
}

type LinkOptions = {
	text: string,
	value: string,
	type: $Keys<typeof StubLinkType> | null
}

export class StubNotAvailable
{
	#options: StubNotAvailableOptions;

	constructor(options?: StubNotAvailableOptions)
	{
		this.#options = {
			title: Text.encode(options?.title || Loc.getMessage('UI_SIDEPANEL_CONTENT_TITLE')),
			desc: Text.encode(options?.desc || Loc.getMessage('UI_SIDEPANEL_CONTENT_DESC')),
			type: options?.type || StubType.noAccess,
			link: options?.link || null,
		};
	}

	openSlider(): void
	{
		const noSectionTypes = new Set([StubType.noAccess]);
		SidePanel.Instance.open(
			'sign:stub-no-connection',
			{
				width: 590,
				cacheable: false,
				contentCallback: () => {
					return Layout.createContent({
						design: {
							section: !noSectionTypes.has(this.#options.type),
						},
						content: () => this.render(),
					});
				},
			},
		);
	}

	render(): HTMLElement
	{
		if (this.#options.type === StubType.noAccess)
		{
			return this.#renderNoAccess();
		}

		if (this.#options.type === StubType.notAvailable)
		{
			return this.#renderNotAvailable();
		}

		if (this.#options.type === StubType.noConnection)
		{
			return this.#renderNoConection();
		}

		throw new Error('wrong stub type');
	}

	#renderNoAccess(): HTMLElement
	{
		return Tag.render`
			<div class="ui-slider-no-access">
				<div class="ui-slider-no-access-inner">
					<div class="ui-slider-no-access-title">${this.#options.title}</div>
					<div class="ui-slider-no-access-subtitle">${this.#options.desc}</div>
					<div class="ui-slider-no-access-img">
						<div class="ui-slider-no-access-img-inner"></div>
					</div>
					${this.#renderLinkElement()}
				</div>
			</div>
		`;
	}

	#renderNotAvailable(): HTMLElement
	{
		return Tag.render`
			<div class="ui-sidepanel-content-404-container">
				<div class="ui-sidepanel-content-404-image">
					<img alt="" src="/bitrix/components/bitrix/ui.sidepanel.content/templates/.default/images/stub-not-available.svg">
				</div>
				<div class="ui-sidepanel-content-404-title">${this.#options.title}</div>
				<div class="ui-sidepanel-content-404-description">
					<p>${this.#options.desc}</p>
				</div>
			</div>
		`;
	}

	#renderNoConection(): HTMLElement
	{
		return Tag.render`
			<div class="ui-slider-no-connection">
				<div class="ui-slider-no-connection-inner">
					<div class="ui-slider-no-connection-title">${this.#options.title}</div>
					<div class="ui-slider-no-connection-subtitle">${this.#options.desc}</div>
					<div class="ui-sidepanel-content-no-connection-image"></div>
					${this.#renderLinkElement()}
				</div>
			</div>
		`;
	}

	renderTo(container: HTMLElement): void
	{
		Dom.append(this.render(), container);
	}

	#renderLinkElement(): HTMLElement | null
	{
		if (!this.#options.link)
		{
			return null;
		}

		const linkElement = Tag.render`
			<a href="javascript:void(0);" class="ui-sidepanel-content-link-href">${Text.encode(this.#options.link.text)}</a>
		`;

		if (this.#options.link.type === StubLinkType.helpdesk)
		{
			Event.bind(linkElement, 'click', (event) => {
				event.preventDefault();
				top.BX.Helper.show(`redirect=detail&code=${Text.encode(this.#options.link.value)}`);
			});
		}

		if (this.#options.link.type === StubLinkType.href)
		{
			Dom.attr(linkElement, 'href', Text.encode(this.#options.link.value));
		}

		return linkElement;
	}
}
