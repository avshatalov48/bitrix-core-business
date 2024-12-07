import { Loc } from 'main.core';
import { Alert, AlertColor, AlertSize } from 'ui.alerts';
import { BaseSettingsPage, SettingsSection, SettingsRow, SettingsField } from 'ui.form-elements.field';
import ReservationMode from '../fields/reservation-mode';
import { Checker } from 'ui.form-elements.view';
import { Section, Row } from 'ui.section';
import 'ui.icon-set.crm';
import 'ui.icon-set.editor';

export default class ReservationSection
{
	#reservationEntities: Object;
	#parentPage: BaseSettingsPage;

	static MODE_FIELD_NAME = 'reservationSettings[deal][mode]';
	static PERIOD_FIELD_NAME = 'reservationSettings[deal][period]';
	static AUTO_WRITE_OFF_FIELD_NAME = 'reservationSettings[deal][autoWriteOffOnFinalize]';

	constructor(params: Object)
	{
		this.#reservationEntities = params.reservationEntities;
		this.#parentPage = params.parentPage;
	}

	// todo: implement actual dynamic settings from the scheme parameter when reservation in other entities is implemented
	buildSection(): SettingsSection
	{
		const section = new Section({
			title: Loc.getMessage('CAT_CONFIG_SETTINGS_RESERVATION_SECTION_TITLE'),
			titleIconClasses: 'ui-icon-set --proposal-settings',
			isOpen: true,
		});

		const settingsSection = new SettingsSection({
			parent: this.#parentPage,
			section,
		});

		const dealSettings = this.#reservationEntities[0]?.settings;
		if (!dealSettings)
		{
			return settingsSection;
		}

		section.append(
			(new Row({
				content: (new Alert({
					text: `
						${Loc.getMessage('CAT_CONFIG_SETTINGS_RESERVATION_SECTION_DESCRIPTION')}
						<a class="ui-section__link" onclick="top.BX.Helper.show('redirect=detail&code=15706692&anchor=reservation')">
							${Loc.getMessage('INTRANET_SETTINGS_CANCEL_MORE')}
						</a>
					`,
					inline: true,
					size: AlertSize.SMALL,
					color: AlertColor.PRIMARY,
				})).getContainer(),
			})).render(),
		);

		const modeFieldName = ReservationSection.MODE_FIELD_NAME;
		const modeSetting = dealSettings.scheme.find((schemeElement) => {
			return schemeElement.code === 'mode';
		});
		const modeValue = dealSettings.values.mode;

		const periodFieldName = ReservationSection.PERIOD_FIELD_NAME;
		const periodSetting = dealSettings.scheme.find((schemeElement) => {
			return schemeElement.code === 'period';
		});
		const periodValue = dealSettings.values.period;

		new SettingsRow({
			row: {
				separator: 'bottom',
				className: '--block',
			},
			parent: settingsSection,
			child: new SettingsField({
				fieldView: (new ReservationMode({
					mode: {
						fieldName: modeFieldName,
						setting: modeSetting,
						value: modeValue,
					},
					period: {
						fieldName: periodFieldName,
						setting: periodSetting,
						value: periodValue,
					},
				})),
			}),
		});

		const autoWriteOffSetting = dealSettings.scheme.find((schemeElement) => {
			return schemeElement.code === 'autoWriteOffOnFinalize';
		});
		const autoWriteOffValue = dealSettings.values.autoWriteOffOnFinalize;

		const checker = new Checker({
			inputName: ReservationSection.AUTO_WRITE_OFF_FIELD_NAME,
			title: autoWriteOffSetting.name,
			checked: autoWriteOffValue,
			hintOn: autoWriteOffSetting.description,
			isFieldDisabled: autoWriteOffSetting.disabled,
			hideSeparator: true,
		});

		new SettingsRow({
			parent: settingsSection,
			child: new SettingsField({
				fieldView: checker,
			}),
		});

		return settingsSection;
	}
}
