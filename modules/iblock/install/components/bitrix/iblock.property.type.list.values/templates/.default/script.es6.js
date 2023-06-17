import { Reflection, ajax } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Alert, AlertColor } from "ui.alerts";

class PropertyListValues
{
	grid: BX.Main.Grid;
	signedParameters: String;
	errorAlert: Alert;

	constructor(options)
	{
		this.grid = BX.Main.gridManager.getInstanceById(options.gridId);
		this.signedParameters = options.signedParameters;

		this.initAppendRowButton();
		this.initSaveButton();
		this.initGrid();
		this.initErrorAlert();
	}

	getGridBodyRows(): Array
	{
		return this.grid.getRows().getBodyChild();
	}

	initGrid(): void
	{
		EventEmitter.subscribe('Grid::updated', (event) => {
			const grid = event.getCompatData()[0];
			if (grid && grid.getId() === this.grid.getId())
			{
				const delayToExitStream = 10;
				setTimeout(this.initGridRows.bind(this), delayToExitStream);
			}
		});

		this.initGridRows();
	}

	initGridRows(): void
	{
		const bodyRows = this.getGridBodyRows();
		if (bodyRows.length === 0)
		{
			for (let i = 0; i < 5; i++)
			{
				this.appendNewRowToGrid();
			}
		}
		else
		{
			bodyRows.forEach((row) => {
				row.edit();
			});
		}
	}

	getGridValues(): Object
	{
		const result = {};

		let newRowsCount = 0;
		this.getGridBodyRows().forEach((row) => {
			let id = parseInt(row.getId());
			if (isNaN(id) || !id)
			{
				newRowsCount++;
				id = 'n' + newRowsCount;
			}

			result[id] = row.getEditorValue();
		});

		return result;
	}

	reloadGrid()
	{
		this.grid.reload();
	}

	initAppendRowButton(): void
	{
		const button = document.querySelector('.iblock-property-type-list-values-append-row');
		if (button)
		{
			button.addEventListener('click', (e) => {
				e.preventDefault();

				this.appendNewRowToGrid();
			});
		}
	}

	appendNewRowToGrid()
	{
		const newRow = this.grid.appendRowEditor();
		newRow.setId('');
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
					.runComponentAction('bitrix:iblock.property.type.list.values', 'save', {
						data: {
							values: this.getGridValues(),
						},
						mode: 'class',
						signedParameters: this.signedParameters,
					})
					.then((response) => {
						button.classList.remove('ui-btn-wait');

						this.reloadGrid();
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
			customClass: 'iblock-property-type-list-values-errors-container',
		});
	}
}

Reflection.namespace('BX.Iblock').PropertyListValues = PropertyListValues;
