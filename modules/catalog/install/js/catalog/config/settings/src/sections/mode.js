import { Event, Loc, Runtime, Tag } from 'main.core';
import { Button, ButtonColor } from 'ui.buttons';
import { SettingsSection } from 'ui.form-elements.field';
import { UI } from 'ui.notification';
import { Section, Row } from 'ui.section';
import { EnableWizardOpener, ModeList, Disabler, AnalyticsContextList } from 'catalog.store-enable-wizard';
import CatalogPage from '../catalog-page';
import ModeStatus from './components/mode-status';

export default class Mode
{
	#parentPage: CatalogPage;
	#inventoryManagementParams: Object;
	#configCatalogSource: ?string = null;
	#inventoryManagementDisabler: Disabler = null;

	constructor(params: Object)
	{
		this.#parentPage = params.parentPage;
		this.#inventoryManagementParams = params.inventoryManagementParams;

		this.#configCatalogSource = params.configCatalogSource;
		this.#inventoryManagementDisabler = new Disabler({
			hasConductedDocumentsOrQuantities: this.#inventoryManagementParams.hasConductedDocumentsOrQuantities,
			events: {
				onDisabled: () => {
					this.#parentPage.onInventoryManagementModeChanged({
						isEnabled: false,
						mode: ModeList.MODE_B24,
					});
				},
			},
		});
	}

	buildSection(): SettingsSection
	{
		const section = new Section({
			title: Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_SECTION_TITLE'),
			titleIconClasses: 'ui-icon-set --settings',
			isOpen: true,
		});

		const modeSection = new SettingsSection({
			parent: this.#parentPage,
			section,
		});

		section.append(
			(new Row({
				content: this.#getCurrentModeBlock(),
			})).render(),
		);

		return modeSection;
	}

	openInventoryManagementSlider(): void
	{
		let sliderUrl = '/bitrix/components/bitrix/catalog.store.enablewizard/slider.php';
		if (this.#configCatalogSource)
		{
			sliderUrl += `?inventoryManagementSource=${this.#configCatalogSource}`;
		}
		new EnableWizardOpener().open(
			sliderUrl,
			{
				urlParams: {
					analyticsContextSection: AnalyticsContextList.SETTINGS,
				},
			},
		)
			.then((slider) => {
				if (!slider)
				{
					return;
				}

				const isEnabled = slider.getData().get('isInventoryManagementEnabled');
				const mode = slider.getData().get('inventoryManagementMode');
				if (
					(isEnabled !== undefined && isEnabled !== this.#inventoryManagementParams.isEnabled)
					|| (mode !== undefined && mode !== this.#inventoryManagementParams.currentMode)
				)
				{
					this.#parentPage.onInventoryManagementModeChanged({
						isEnabled,
						mode,
					});

					document.querySelector('.catalog-settings-inventory-management-mode-wrapper')?.scrollIntoView();

					if (
						this.#inventoryManagementParams.isEnabled
						&& isEnabled
						&& mode !== this.#inventoryManagementParams.currentMode
					)
					{
						UI.Notification.Center.notify({
							content: Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_CHANGED'),
						});

						this.#parentPage.updateDataAfterSave();
					}
				}
			});
	}

	#getCurrentModeBlock(): HTMLElement
	{
		const isInventoryManagementEnabled = this.#inventoryManagementParams.isEnabled;
		const currentMode = this.#inventoryManagementParams.currentMode;
		const is1cRestricted = this.#inventoryManagementParams.is1cRestricted;

		let modeLogo = '';

		if (currentMode === ModeList.MODE_1C)
		{
			modeLogo = Tag.render`
				<div class="catalog-settings-inventory-management-mode-external-logo"></div>
			`;
		}
		else
		{
			modeLogo = Loc.getMessage('CAT_CONFIG_SETTINGS_B24_LOGO')
				.replace('[color]', '<span class="catalog-settings-inventory-management-mode-b24-numbers">')
				.replace('[/color]', '</span>')
			;
			modeLogo = Tag.render`
				<span class="catalog-settings-inventory-management-mode-b24-name">${modeLogo}</span>
			`;
		}

		const changeModeButton = new Button({
			text: Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_CHANGE'),
			color: ButtonColor.LIGHT,
			onclick: (button, event) => {
				this.#sendEvent('disable_clicked');

				this.openInventoryManagementSlider();
			},
		});

		const toggleButton = new Button({
			text: isInventoryManagementEnabled
				? Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_DISABLE')
				: Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_ENABLE'),
			color: isInventoryManagementEnabled ? ButtonColor.LIGHT : ButtonColor.PRIMARY,
			onclick: (button, event) => {
				if (isInventoryManagementEnabled)
				{
					this.#sendEvent('change_mode_clicked');

					this.#inventoryManagementDisabler.open();
				}
				else
				{
					this.openInventoryManagementSlider();
				}
			},
			round: !isInventoryManagementEnabled,
		});

		const showChangeModeButton = (
			this.#inventoryManagementParams.availableModes.includes(ModeList.MODE_1C)
			&& isInventoryManagementEnabled
		);

		let descriptionContent = Loc.getMessage('CAT_CONFIG_SETTINGS_B24_MODE_DESCRIPTION');
		let descriptionClass = 'catalog-settings-inventory-management-mode-description';
		if (currentMode === ModeList.MODE_1C)
		{
			const onecStatusUrl = this.#inventoryManagementParams.onecStatusUrl;
			descriptionContent = (new ModeStatus({
				currentMode,
				isInventoryManagementEnabled,
				onecStatusUrl,
				is1cRestricted,
			})).initialize();
			descriptionClass = 'catalog-settings-inventory-management-mode-status';
		}

		return Tag.render`
			<div>
				<div class="catalog-settings-inventory-management-mode-wrapper">
					<div class="catalog-settings-inventory-management-mode-inner">
						<div class="catalog-settings-inventory-management-mode-selected ${isInventoryManagementEnabled ? '' : '--disabled'}">
							<div class="catalog-settings-inventory-management-mode-name">${modeLogo}</div>
							<div class="${descriptionClass}">
								${descriptionContent}
							</div>
						</div>
						<div class="catalog-settings-inventory-management-mode-buttons">
							${showChangeModeButton ? changeModeButton.render() : ''}
							${toggleButton.render()}
						</div>
					</div>
				</div>
				<div>
					<p class="catalog-settings-inventory-management-mode-warning">
						${Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_CHANGE_WARNING').replace('[break]', '<br/>')}
					</p>
					${this.#getHelpLink()}
				</div>
			</div>
		`;
	}

	#sendEvent(event: string): void
	{
		Runtime.loadExtension('ui.analytics')
			.then((exports) => {
				const { sendData } = exports;

				sendData({
					tool: 'inventory',
					category: 'settings',
					c_section: 'settings',
					p1: `mode_${this.#inventoryManagementParams.currentMode}`,
					event,
				});
			});
	}

	#getHelpLink(): HTMLElement
	{
		const result = Tag.render`
			<a class="catalog-settings-inventory-management-mode-help ui-section__link">
				${Loc.getMessage('CAT_CONFIG_SETTINGS_MODE_HELP')}
			</a>
		`;
		Event.bind(result, 'click', () => {
			if (top.BX && top.BX.Helper)
			{
				const helpCode = this.#inventoryManagementParams.availableModes.length > 1
					? '20233748'
					: '15992592';

				top.BX.Helper.show(`redirect=detail&code=${helpCode}`);
			}
		});

		return result;
	}
}
