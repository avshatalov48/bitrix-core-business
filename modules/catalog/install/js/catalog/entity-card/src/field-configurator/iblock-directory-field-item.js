import {Event, Tag, Text} from "main.core";

export default class IblockDirectoryFieldItem extends BX.UI.EntityEditorUserFieldListItem
{
	fileChanged = false;

	static create(id, settings)
	{
		const self = new this;
		self.initialize(id, settings);
		return self;
	}

	layout()
	{
		if (this._hasLayout)
		{
			return;
		}

		this._wrapper = Tag.render`
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-row"></div>
			`;

		this._fileInput = Tag.render`
				<input class="input-image-hidden" value="${BX.prop.getString(this._data, 'FILE_ID', '')}" type="file" accept="image/*">
			`;
		Event.bind(this._fileInput, 'change', this.onFileLoaderChange.bind(this));
		const link = BX.prop.getString(this._data, 'IMAGE_SRC', '');
		this._wrapper.appendChild(
			Tag.render`
			<label class="catalog-dictionary-item ${link === '' ? 'catalog-dictionary-item-empty' : ''}">
				<img src="${link}" alt="">
				${this._fileInput}
			</label>
			`
		);

		const labelText = Text.encode(BX.prop.getString(this._data, 'TEXT', ''));
		this._labelInput = Tag.render`
				<input 
					class="ui-ctl-element" 
					value="${labelText}"
					placeholder="${BX.message('CATALOG_ENTITY_CARD_NEW_FIELD_ITEM_PLACEHOLDER')}"
				>
			`;
		this._wrapper.appendChild(this._labelInput);

		const deleteButton = Tag.render`
				<div class="ui-entity-editor-content-remove-block"></div>
			`;

		Event.bind(deleteButton, 'click', this.onDeleteButtonClick.bind(this));
		this._wrapper.appendChild(deleteButton);

		var anchor = BX.prop.getElementNode(this._settings, 'anchor');
		if (anchor)
		{
			this._container.insertBefore(this._wrapper, anchor);
		}
		else
		{
			this._container.appendChild(this._wrapper);
		}

		this._hasLayout = true;
	}

	onFileLoaderChange(event)
	{
		const input = event.target;
		if (input.files && input.files[0])
		{
			const reader = new FileReader();
			reader.onload = function(e) {
				input.parentNode.querySelector('img').src = e.target.result;
			};

			this.fileChanged = true;
			reader.readAsDataURL(input.files[0]);
			input.parentNode.classList.remove('catalog-dictionary-item-empty');
		}
	}

	isFileChanged()
	{
		return this.fileChanged;
	}

	prepareData()
	{
		const textValue = this._labelInput ? BX.util.trim(this._labelInput.value) : '';
		const fileValue = (this._fileInput && this._fileInput.files && this._fileInput.files[0]) ? this._fileInput.files[0] : {};

		const data = {
			'VALUE': {
				value: textValue,
				file: fileValue
			},
			'XML_ID': '',
			'FILE_ID': ''
		};
		const xmlId = BX.prop.getString(this._data, 'ID', '');
		if (BX.type.isNotEmptyString(xmlId))
		{
			data['XML_ID'] = xmlId;
			data['FILE_ID'] = BX.prop.getString(this._data, 'FILE_ID', '');
		}

		return data;
	}
}