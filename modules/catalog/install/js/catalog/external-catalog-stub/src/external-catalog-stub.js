import { Slider } from 'catalog.config.settings';
import { Event, Loc, Tag } from 'main.core';
import { SidePanel } from 'ui.sidepanel';
import './style.css';

type StubParams = {
	title: string,
	text: string,
	icon: string,
}

export class ExternalCatalogStub
{
	static showCatalogStub(): void
	{
		SidePanel.Instance.open('catalog:external-catalog-stub', {
			contentCallback: (slider) => {
				return ExternalCatalogStub.#getStubLayout({
					title: Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_EMPTY_STATE_TITLE_CATALOG'),
					text: Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_EMPTY_STATE_CATALOG_TEXT'),
					icon: '1c',
				});
			},
			width: 880,
		});
	}

	static showDocsStub(): void
	{
		SidePanel.Instance.open('catalog:external-catalog-docs-stub', {
			contentCallback: (slider) => {
				return ExternalCatalogStub.#getStubLayout({
					title: Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_EMPTY_STATE_TITLE'),
					text: Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_EMPTY_STATE_DOCS_TEXT'),
					icon: 'docs',
				});
			},
			width: 880,
		});
	}

	static #getStubLayout(params: StubParams): HTMLElement
	{
		const settingsButton = Tag.render`
			<div class="ui-btn ui-btn-success ui-btn-round ui-btn-lg">
				${Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_EMPTY_STATE_BUTTON')}
			</div>
		`;
		Event.bind(settingsButton, 'click', () => {
			Slider.open();
		});

		return Tag.render`
			<div class="inventory-management__empty-state --1c">
				<div class="inventory-management__empty-state-title">
					${params.title}
				</div>
				<div class="inventory-management__empty-state-text">
					${params.text}
				</div>
				<div class="inventory-management__empty-state-logo --${params.icon}"></div>
				${settingsButton}
			</div>
		`;
	}
}
