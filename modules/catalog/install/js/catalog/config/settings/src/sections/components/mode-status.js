import { ExternalCatalogPlacement } from 'catalog.external-catalog-placement';
import { ModeList } from 'catalog.store-enable-wizard';
import { OneCPlanRestrictionSlider } from 'catalog.tool-availability-manager';
import { ajax as Ajax, Dom, Event, Loc, Tag } from 'main.core';
import { Label, LabelColor, LabelSize } from 'ui.label';

type ModeStatusParams = {
	isInventoryManagementEnabled: boolean,
	is1cRestricted: boolean,
	currentMode: string,
	onecStatusUrl: Object,
};

export default class ModeStatus
{
	#isInventoryManagementEnabled: boolean;
	#is1cRestricted: boolean;
	#currentMode: string;
	#onecStatusUrl: string;
	#rootElement: HTMLElement;

	constructor(params: ModeStatusParams)
	{
		this.#isInventoryManagementEnabled = params.isInventoryManagementEnabled;
		this.#is1cRestricted = params.is1cRestricted;
		this.#currentMode = params.currentMode;
		this.#onecStatusUrl = params.onecStatusUrl;
		this.#rootElement = Tag.render`
			<div id="inventoryManagementStatus">
			</div>
		`;
	}

	initialize(): HTMLElement
	{
		let statusText = '';
		let statusColor = '';
		let labelStatus = '';

		if (this.#currentMode === ModeList.MODE_1C)
		{
			if (this.#isInventoryManagementEnabled)
			{
				statusText = Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_STATUS_CHECKING');
				statusColor = LabelColor.LIGHT;
				labelStatus = 'loading';

				ExternalCatalogPlacement.create().initialize()
					.then(() => {
						this.update({
							text: Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_STATUS_CONNECTED'),
							color: LabelColor.LIGHT_GREEN,
						});
					})
					.catch(() => {
						this.update({
							text: Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_STATUS_1C_NOT_CONNECTED'),
							color: LabelColor.LIGHT_RED,
						});
					});
			}
			else
			{
				statusText = Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_STATUS_1C_NOT_CONNECTED');
				statusColor = LabelColor.LIGHT;
			}
		}

		const label = new Label({
			text: statusText,
			color: statusColor,
			size: LabelSize.LG,
			fill: true,
			status: labelStatus,
		});

		this.#render(label);

		return this.#rootElement;
	}

	#refreshAppLink(): void
	{
		Ajax.runComponentAction(
			'bitrix:catalog.config.settings',
			'refreshAppLink',
			{
				mode: 'class',
			},
		).then((response) => {
			if (!response.data)
			{
				return;
			}

			this.#onecStatusUrl = response.data;
		});
	}

	#refreshStatus(): void
	{
		ExternalCatalogPlacement.create().reset();
	}

	update({ text, color }: { text: string, color: string }): void
	{
		if (!this.#rootElement)
		{
			return;
		}

		const label = new Label({
			text,
			color,
			size: LabelSize.LG,
			fill: true,
		});

		this.#render(label);
	}

	#render(label: Label): void
	{
		const settingsLinkElement = this.#getSettingsLinkElement();
		const labelElement = label.render();

		let clickHandler = () => {};

		if (this.#is1cRestricted)
		{
			clickHandler = (event) => {
				event.preventDefault();

				OneCPlanRestrictionSlider.show();
			};
		}
		else if (this.#onecStatusUrl.type === 'app')
		{
			clickHandler = (event) => {
				event.preventDefault();

				top.BX.rest.AppLayout.openApplication(
					this.#onecStatusUrl.value,
					{
						source: 'inventory-management',
					},
					false,
					() => {
						this.#refreshStatus();
						this.initialize();
					},
				);
			};
		}
		else
		{
			clickHandler = (event) => {
				event.preventDefault();

				BX.SidePanel.Instance.open(this.#onecStatusUrl.value, {
					customLeftBoundary: 0,
					cacheable: false,
					loader: 'market:detail',
					width: 1162,
					events: {
						onClose: () => {
							this.#refreshAppLink();
							this.#refreshStatus();
							this.initialize();
						},
					},
				});
			};
		}

		Event.bind(settingsLinkElement, 'click', clickHandler);

		Dom.clean(this.#rootElement);
		Dom.append(labelElement, this.#rootElement);
		Dom.append(settingsLinkElement, this.#rootElement);
	}

	#getSettingsLinkElement(): HTMLElement
	{
		const before = this.#is1cRestricted ? '<span class="tariff-lock"></span>' : '';

		return Tag.render`
			<span class="catalog-settings-inventory-management-mode-settings-container">
				${before}
				<a href="${this.#onecStatusUrl.value}" class="catalog-settings-inventory-management-mode-settings" data-slider-ignore-autobinding="true">
					${Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_STATUS_SETTINGS')}
				</a>
			</span>
		`;
	}
}
