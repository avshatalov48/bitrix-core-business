import { ajax } from 'main.core';
import { Alert, AlertColor } from 'ui.alerts';
import { GridController } from './grid-controller';
import { SettingsForm } from './settings-form';

export class PropertyDirectorySettings
{
	gridController: GridController;
	settingsForm: SettingsForm;
	signedParameters: String;
	errorAlert: Alert;

	constructor(options)
	{
		this.gridController = new GridController(options);
		this.signedParameters = options.signedParameters;
		this.settingsForm = SettingsForm.createApp(this.gridController, options);

		this.initErrorAlert();
		this.initSaveButton();
	}

	removeGridSelectedRows()
	{
		this.gridController.removeGridSelectedRows();
	}

	initSaveButton(): void
	{
		const button = document.querySelector('#ui-button-panel-save');
		if (button)
		{
			button.addEventListener('click', async (e) => {
				e.preventDefault();

				await this.clearErrors();

				ajax
					.runComponentAction('bitrix:iblock.property.type.directory.settings', 'save', {
						data: this.getFormData(),
						mode: 'class',
						signedParameters: this.signedParameters,
					})
					.then((response) => {
						button.classList.remove('ui-btn-wait');

						location.reload();
					})
					.catch((response) => {
						button.classList.remove('ui-btn-wait');

						this.showErrors(response.errors);
					})
				;
			});
		}
	}

	clearErrors(): Promise
	{
		return new Promise((resolve, reject) => {
			const animateClosingDelay = 300;

			this.errorAlert.hide();

			setTimeout(resolve, animateClosingDelay);
		});
	}

	showErrors(errors)
	{
		this.errorAlert.setText(errors.map((i) => i.message).join('<br>'));
		this.errorAlert.renderTo(document.querySelector('#ui-button-panel'));
	}

	initErrorAlert(): Alert
	{
		this.errorAlert = new Alert({
			color: AlertColor.DANGER,
			animated: true,
			customClass: 'iblock-property-type-directory-settings-errors-container',
		});
	}

	getFormData(): FormData
	{
		const result = new FormData();

		result.append('fields[DIRECTORY_NAME]', this.settingsForm.getDirectoryName());
		result.append('fields[DIRECTORY_TABLE_NAME]', this.settingsForm.getDirectoryValue());

		let newRowsCount = 0;
		this.gridController.getGridBodyRows().forEach((row) => {
			let id = parseInt(row.getId());
			if (isNaN(id) || !id)
			{
				newRowsCount++;
				id = 'n' + newRowsCount;
			}

			const rowValues = row.getEditorValue();
			if (row.isShown() === false)
			{
				rowValues.UF_DELETE = 'Y';
			}

			for (const fieldName in rowValues)
			{
				if (Object.hasOwnProperty.call(rowValues, fieldName))
				{
					result.append(`fields[DIRECTORY_ITEMS][${id}][${fieldName}]`, rowValues[fieldName]);
				}
			}
		});

		return result;
	}
}
