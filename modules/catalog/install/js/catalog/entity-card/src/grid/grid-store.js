import { Type, Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';
import 'ui.notification';

export default class GridStore
{
	gridId: String;
	editedRowsIndexes: Array = [];

	constructor(gridId: String)
	{
		this.gridId = gridId;

		EventEmitter.subscribe('onItemIsAdded', this.#onFileIsAddedHandler.bind(this));
		EventEmitter.subscribe('onFileIsDeleted', this.#onFileIsDeletedHandler.bind(this));
	}

	getGrid(): BX.Main.Grid
	{
		return BX.Main.gridManager.getInstanceById(this.gridId);
	}

	#onFileIsAddedHandler(event: BaseEvent)
	{
		const file = event.getCompatData()[0];
		const isFileUploaded = file instanceof File;
		const uploader = event.getCompatData()[2];

		if (uploader && Type.isDomNode(uploader.fileInput) && isFileUploaded)
		{
			const isFileUploaderInGrid = this.getGrid().getContainer().contains(uploader.fileInput)
			if (isFileUploaderInGrid)
			{
				this.#showFileNotification();
			}
		}
	}

	#onFileIsDeletedHandler(event: BaseEvent)
	{
		const uploader = event.getCompatData()[2];

		if (uploader && Type.isDomNode(uploader.fileInput))
		{
			const isFileUploaderInGrid = this.getGrid().getContainer().contains(uploader.fileInput)
			if (isFileUploaderInGrid)
			{
				this.#showFileNotification();
			}
		}
	}

	#showFileNotification()
	{
		BX.UI.Notification.Center.notify({
			id: 'fileCloseNotification',
			blinkOnUpdate: false,
			content: Loc.getMessage('CATALOG_ENTITY_CARD_FILE_CLOSE_NOTIFICATION_2'),
			position: 'top-right',
			width: 'auto',
			autoHideDelay: 5000
		});
	}

	saveEditedRows(): void
	{
		this.editedRowsIndexes = [];

		this.getGrid().getRows().getBodyChild().forEach((row) => {
			if (row.isEdit())
			{
				this.editedRowsIndexes.push(row.getNode().rowIndex);
			}
		});
	}

	loadEditedRows(): void
	{
		const rows = this.getGrid().getRows();

		this.editedRowsIndexes.forEach((index) => {
			const row = rows.getByIndex(index);
			if (row)
			{
				//row.edit(); not used, because for child listeners need fire event
				BX.fireEvent(row.getNode(), 'click');
			}
		});
	}

	#getSupportedAjaxFields(): Array
	{
		const params = this.getGrid().getParam('SUPPORTED_AJAX_FIELDS');
		if (Type.isArray(params))
		{
			return params;
		}

		return [];
	}

	getEditedRowsFields(): Object
	{
		const result = {};

		const fillCellValue = function(result: Object, name: String, editData: Object, value)
		{
			if (Type.isPlainObject(editData) && editData.TYPE === 'MONEY')
			{
				if (Type.isArray(value))
				{
					value.forEach((item) => {
						if (item.RAW_NAME === undefined && item.NAME === name)
						{
							result[name] = item.VALUE;
						}
					});
				}
				else
				{
					console.error('Error value type for `MONEY` column', value);
				}
			}
			else if (Type.isPlainObject(value))
			{
				result[name] = value.VALUE ?? '';
			}
			else if (Type.isArray(value))
			{
				result[name] = [];

				value.forEach((item) => {
					if (Type.isPlainObject(item))
					{
						result[name].push(item.VALUE);
					}
					else
					{
						result[name].push(item);
					}
				});
			}
			else
			{
				result[name] = value;
			}
		};

		const rows = this.getGrid().getRows();
		const headRow = rows.getHeadFirstChild();
		const supportedAjaxFields = this.#getSupportedAjaxFields();

		rows.getBodyChild().filter((row) => row.isEdit()).forEach((row) => {
			const values = {};

			Array.prototype.forEach.call(row.getCells(), (cell, index) => {
				const cellName = headRow.getCellNameByCellIndex(index);
				if (!cellName)
				{
					return;
				}

				if (supportedAjaxFields.length > 0 && !supportedAjaxFields.includes(cellName))
				{
					return;
				}

				const cellValues = row.getCellEditorValue(cell);
				const cellEditData = headRow.getCellEditDataByCellIndex(index);

				fillCellValue(values, cellName, cellEditData, cellValues);
			});

			result[row.getId()] = values;
		});

		return result;
	}
}
