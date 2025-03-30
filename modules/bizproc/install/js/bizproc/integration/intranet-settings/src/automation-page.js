import { Checker } from 'ui.form-elements.view';
import { SettingsSection, BaseSettingsPage } from 'ui.form-elements.field';
import { Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Section } from 'ui.section';

export class AutomationPage extends BaseSettingsPage
{
	static get type(): string
	{
		return 'automation';
	}

	constructor()
	{
		super();

		this.titlePage = Loc.getMessage('BIZPROC_INTRANET_SETTINGS_TITLE_PAGE_AUTOMATION') ?? '';
		this.descriptionPage = Loc.getMessage('BIZPROC_INTRANET_SETTINGS_DESCRIPTION_PAGE_AUTOMATION') ?? '';
	}

	getType(): string
	{
		return this.constructor.type;
	}

	appendSections(contentNode: HTMLElement): void
	{
		this.#buildAdditionalSection().renderTo(contentNode);
	}

	#buildAdditionalSection(): ?SettingsSection
	{
		if (!this.hasValue('SECTION_MAIN'))
		{
			return null;
		}

		const additionalSection = new Section(this.getValue('SECTION_MAIN'));

		const sectionSettings = new SettingsSection({
			section: additionalSection,
			parent: this,
		});

		if (this.hasValue('crm_activity_wait_for_closure_task'))
		{
			const showQuitField = new Checker(this.getValue('crm_activity_wait_for_closure_task'));
			this.#subscribeAnalytics(showQuitField, 'crm_activity_wait_for_closure_task');
			AutomationPage.addToSectionHelper(showQuitField, sectionSettings);
		}

		if (this.hasValue('crm_activity_wait_for_closure_comments'))
		{
			const newUserField = new Checker(this.getValue('crm_activity_wait_for_closure_comments'));
			this.#subscribeAnalytics(newUserField, 'crm_activity_wait_for_closure_comments');
			AutomationPage.addToSectionHelper(newUserField, sectionSettings);
		}

		return sectionSettings;
	}

	#subscribeAnalytics(checker: Checker, tag: string): void
	{
		EventEmitter.subscribe(
			checker,
			'change',
			() => {
				this.getAnalytic()?.addEvent(
					`bizproc_${tag}`,
					{
						tool: 'crm',
						category: 'bizproc_operations',
						event: 'settings_edit',
						type: tag,
						c_element: checker.isChecked() ? 'on' : 'off',
					},
				);
			},
		);
	}
}
