import { Event, Loc } from 'main.core';
import { Alert, AlertColor, AlertSize } from 'ui.alerts';
import { BaseSettingsPage, SettingsSection, SettingsRow, SettingsField } from 'ui.form-elements.field';
import { Section, Row } from 'ui.section';
import { Selector } from 'ui.form-elements.view';

export default class CostPriceCalculation
{
	#parentPage: BaseSettingsPage;
	#costPriceCalculationParams: Object;

	constructor(params: Object)
	{
		this.#costPriceCalculationParams = params.costPriceCalculationParams;
		this.#parentPage = params.parentPage;
	}

	buildSection(): SettingsSection
	{
		const section = new Section({
			title: Loc.getMessage('CAT_CONFIG_SETTINGS_COST_PRICE_CALCULATION_SECTION_TITLE'),
			titleIconClasses: 'ui-icon-set --numbered-list',
			isOpen: true,
		});

		const costPriceCalculationSection = new SettingsSection({
			parent: this.#parentPage,
			section,
		});

		section.append(
			(new Row({
				content: (new Alert({
					text: `
							${Loc.getMessage('CAT_CONFIG_SETTINGS_COST_PRICE_CALCULATION_SECTION_HINT')}
							<a class="ui-section__link" onclick="top.BX.Helper.show('redirect=detail&code=17858278')">
								${Loc.getMessage('INTRANET_SETTINGS_CANCEL_MORE')}
							</a>
						`,
					inline: true,
					size: AlertSize.SMALL,
					color: AlertColor.PRIMARY,
				})).getContainer(),
			})).render(),
		);

		const selector = new Selector({
			label: Loc.getMessage('CAT_CONFIG_SETTINGS_COST_PRICE_CALCULATION_METHOD'),
			name: 'costPriceCalculationMethod',
			items: this.#costPriceCalculationParams.items,
			hints: this.#costPriceCalculationParams.hints,
			isFieldDisabled: true,
		});

		selector.getInputNode().setAttribute('required', 'required');
		Event.bind(selector.getInputNode(), 'change', () => {
			const alert = (new Alert({
				text: `
					${Loc.getMessage('CAT_CONFIG_SETTINGS_COST_PRICE_CALCULATION_SECTION_WARNING')}
					<a class="ui-section__link" onclick="top.BX.Helper.show('redirect=detail&code=17858278')">
						${Loc.getMessage('INTRANET_SETTINGS_CANCEL_MORE')}
					</a>
				`,
				inline: true,
				size: AlertSize.SMALL,
				color: AlertColor.WARNING,
			})).getContainer();

			const row = (new Row({
				content: alert,
			})).render();

			section.prepend(row);
		});

		new SettingsRow({
			parent: costPriceCalculationSection,
			child: new SettingsField({
				fieldView: selector,
			}),
		});

		return costPriceCalculationSection;
	}
}
