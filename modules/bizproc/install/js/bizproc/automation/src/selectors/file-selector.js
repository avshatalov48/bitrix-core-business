import { Type, Runtime, Tag, Dom, Event, Text, Loc } from 'main.core';
import { MenuManager } from 'main.popup';
import { SelectorContext, Helper } from 'bizproc.automation';
import { InlineSelector } from './inline-selector';

export class FileSelector extends InlineSelector
{
	static TYPE = {
		None: '',
		Disk: 'disk',
		File: 'file',
	};

	#type: string = FileSelector.TYPE.None;
	#multiple: boolean = false;
	#required: boolean = false;
	#valueInputName: string = '';
	#typeInputName: string = '';
	#useDisk: boolean = false;
	#label: string = '';
	#labelFile: string = '';
	#labelDisk: string = '';

	#diskUploader: ?BX.Bizproc.Automation.DiskUploader = null;

	#diskControllerNode: ?HTMLDivElement = null;
	#fileItemsNode: ?HTMLSpanElement = null;
	#fileControllerNode: ?HTMLDivElement = null;

	#inputWrapper: HTMLElement;
	#menu: ?Menu;

	constructor(props: { context: SelectorContext })
	{
		super(props);

		this.context.set(
			'fileFields',
			this.context.fields.filter((field) => field.Type === 'file'),
		);
	}

	destroy()
	{
		if (this.#menu)
		{
			this.#menu.close();
		}
	}

	renderTo(targetInput: Element)
	{
		this.targetInput = targetInput;

		const selected = this.parseTargetProperties();

		this.targetInput.appendChild(this.#createBaseNode());
		this.#showTypeControlLayout(selected);
	}

	parseTargetProperties()
	{
		let config = JSON.parse(this.targetInput.getAttribute('data-config'));
		if (!Type.isPlainObject(config))
		{
			config = {};
		}

		if (config.type)
		{
			this.#type = config.type;
		}
		else
		{
			this.#type = this.context.get('fileFields').length > 0 ? FileSelector.TYPE.File : FileSelector.TYPE.Disk;
		}

		this.#multiple = config.multiple || false;
		this.#required = config.required || false;
		this.#valueInputName = config.valueInputName || '';
		this.#typeInputName = config.typeInputName || '';
		this.#useDisk = config.useDisk || false;
		this.#label = config.label || 'Attachment';
		this.#labelFile = config.labelFile || 'File';
		this.#labelDisk = config.labelDisk || 'Disk';

		if (config.selected && config.selected.length > 0)
		{
			return Runtime.clone(config.selected);
		}
	}

	#createBaseNode(): HTMLDivElement
	{
		const idSalt = Helper.generateUniqueId();
		let fileRadio = null;

		const fileTypeOptions = [];

		if (this.context.get('fileFields').length > 0)
		{
			fileRadio = Tag.render`
				<input
					id="type-1${idSalt}"
					class="bizproc-automation-popup-select-input"
					type="radio"
					name="${this.#typeInputName}"
					value="${FileSelector.TYPE.File}"
					${this.#type === FileSelector.TYPE.File ? 'checked' : ''}
				/>
			`;
		}

		const diskFileRadio = Tag.render`
			<input
				id="type-2${idSalt}"
				class="bizproc-automation-popup-select-input"
				type="radio"
				name="${this.#typeInputName}"
				value="${FileSelector.TYPE.Disk}"
				${this.#type === FileSelector.TYPE.Disk ? 'checked' : ''}
			/>
		`;

		// fileTypeOptions.push(Tag.render`
		// 	<span class="bizproc-automation-popup-settings-title">${this.#label}:</span>
		// `);

		if (fileRadio)
		{
			fileTypeOptions.push(fileRadio, Tag.render`
				<label
					class="bizproc-automation-popup-settings-link"
					for="type-1${idSalt}"
					onclick="${this.#onTypeChange.bind(this, FileSelector.TYPE.File)}"
				>
				${this.#labelFile}
				</label>
			`);
		}

		fileTypeOptions.push(diskFileRadio, Tag.render`
			<label
				class="bizproc-automation-popup-settings-link"
				for="type-2${idSalt}"
				onclick="${this.#onTypeChange.bind(this, FileSelector.TYPE.Disk)}"
			>
			${this.#labelDisk}
			</label>
		`);

		return Tag.render`
			<div class="bizproc-automation-popup-settings-block">
				<span class="bizproc-automation-popup-settings-title">${this.#label}:</span>
				<div class="bizproc-automation-popup-settings-tab-head">
					${fileTypeOptions}
				</div>							
			</div>
		`;
	}

	#showTypeControlLayout(selected: Array): void
	{
		if (this.#type === FileSelector.TYPE.Disk)
		{
			this.#hideFileControllerLayout();
			this.#showDiskControllerLayout(selected);
		}
		else if (this.#type === FileSelector.TYPE.File)
		{
			this.#hideDiskControllerLayout();
			this.#showFileControllerLayout(selected);
		}
		else
		{
			this.#hideFileControllerLayout();
			this.#hideDiskControllerLayout();
		}
	}

	#showDiskControllerLayout(selected: Array): void
	{
		if (this.#diskControllerNode)
		{
			Dom.show(this.#diskControllerNode);
		}
		else
		{
			this.#diskControllerNode = Dom.create('div');

			this.targetInput.appendChild(this.#diskControllerNode);

			const diskUploader = this.#getDiskUploader();
			diskUploader.layout(this.#diskControllerNode);
			diskUploader.show(true);

			if (selected)
			{
				this.addItems(selected);
			}
		}
	}

	#hideDiskControllerLayout(): void
	{
		if (this.#diskControllerNode)
		{
			Dom.hide(this.#diskControllerNode);
		}
	}

	#showFileControllerLayout(selected: Array): void
	{
		if (this.#fileControllerNode)
		{
			Dom.show(this.#fileControllerNode);
		}
		else
		{
			this.#fileItemsNode = Dom.create('span', {
				attrs: {
					className: 'bizproc-automation-popup-settings-tab-item-box',
				},
			});
			this.#fileControllerNode = Dom.create('div', {
				attrs: {
					className: 'bizproc-automation-popup-settings-tab-inner',
				},
				children: [this.#fileItemsNode],
			});
			this.targetInput.appendChild(this.#fileControllerNode);
			const addButtonNode = Dom.create('a', {
				attrs: { className: 'bizproc-automation-popup-settings-link bizproc-automation-popup-settings-link-thin' },
				text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_ADD_FILE'),
			});

			const addButtonDesc = Dom.create('div', {
				attrs: {
					className: 'bizproc-automation-popup-settings-desc',
				},
				text: Loc.getMessage('BIZPROC_AUTOMATION_CMP_ADD_FILE_LEGEND'),
			});

			Dom.append(addButtonDesc, addButtonNode);

			this.#fileControllerNode.appendChild(addButtonNode);

			Event.bind(addButtonNode, 'click', this.#onFileFieldAddClick.bind(this, addButtonNode));

			if (selected)
			{
				this.addItems(selected);
			}
		}
	}

	#hideFileControllerLayout(): void
	{
		if (this.#fileControllerNode)
		{
			Dom.hide(this.#fileControllerNode);
		}
	}

	#getDiskUploader(): BX.Bizproc.Automation.DiskUploader
	{
		if (!this.#diskUploader)
		{
			this.#diskUploader = BX.Bizproc.Automation.DiskUploader.create(
				'',
				{
					msg: {
						diskAttachFiles: Loc.getMessage('BIZPROC_AUTOMATION_CMP_DISK_ATTACH_FILE'),
						diskAttachedFiles: Loc.getMessage('BIZPROC_AUTOMATION_CMP_DISK_ATTACHED_FILES'),
						diskSelectFile: Loc.getMessage('BIZPROC_AUTOMATION_CMP_DISK_SELECT_FILE'),
						diskSelectFileLegend: Loc.getMessage('BIZPROC_AUTOMATION_CMP_DISK_SELECT_FILE_LEGEND_MSGVER_1'),
						diskUploadFile: Loc.getMessage('BIZPROC_AUTOMATION_CMP_DISK_UPLOAD_FILE'),
						diskUploadFileLegend: Loc.getMessage('BIZPROC_AUTOMATION_CMP_DISK_UPLOAD_FILE_LEGEND'),
					},
				},
			);

			this.#diskUploader.setMode(1);
		}

		return this.#diskUploader;
	}

	#onTypeChange(newType: string): void
	{
		if (this.#type !== newType)
		{
			this.#type = newType;
			this.#showTypeControlLayout();
		}
	}

	#addFileItem(item)
	{
		if (this.#isFileItemSelected(item))
		{
			return false;
		}

		const node = this.#createFileItemNode(item);
		if (!this.#multiple)
		{
			Dom.clean(this.#fileItemsNode);
		}

		this.#fileItemsNode.appendChild(node);
	}

	#isFileItemSelected(item: object)
	{
		return Boolean(this.#fileItemsNode.querySelector(`[data-file-id="${item.id}"]`));
	}

	addItems(items: Array<object>)
	{
		if (this.#type === FileSelector.TYPE.File)
		{
			for (const fileItem of items)
			{
				this.#addFileItem(fileItem);
			}
		}
		else
		{
			this
				.#getDiskUploader()
				.setValues(
					FileSelector.#convertToDiskItems(items),
				);
		}
	}

	static #convertToDiskItems(items: Array<object>)
	{
		return items.map((item) => ({
			ID: item.id,
			NAME: item.name,
			SIZE: item.size,
			VIEW_URL: '',
		}));
	}

	#removeFileItem(item)
	{
		const itemNode = this.#fileItemsNode.querySelector(`[data-file-id="${item.id}"]`);
		if (itemNode)
		{
			this.#fileItemsNode.removeChild(itemNode);
		}
	}

	#onFileFieldAddClick(addButtonNode, event)
	{
		const self = this;
		if (!this.#menu)
		{
			this.#menu = MenuManager.create(
				Helper.generateUniqueId(),
				addButtonNode,
				this.context.get('fileFields').map((field) => ({
					text: Text.encode(field.Name),
					field,
					onclick()
					{
						this.popupWindow.close();
						self.onFieldSelect(field);
					},
				})),
				{
					autoHide: true,
					offsetLeft: Dom.getPosition(addButtonNode).width / 2,
					angle: {
						position: 'top',
						offset: 0,
					},
				},
			);
		}

		this.#menu.show();
		event.preventDefault();
	}

	onFieldSelect(field)
	{
		this.#addFileItem({
			id: field.Id,
			expression: field.Expression,
			name: field.Name,
			type: FileSelector.TYPE.File,
		});
	}

	#createFileItemNode(item)
	{
		const itemField = this.context.get('fileFields').find((field) => field.Expression === item.expression);
		const label = itemField?.Name || '';

		return Tag.render`
			<span
				class="bizproc-automation-popup-autocomplete-item"
				data-file-id="${item.id}"
				data-file-expression="${item.expression}"
			>
				<span class="bizproc-automation-popup-autocomplete-name">${label}</span>
				<span
					class="bizproc-automation-popup-autocomplete-delete"
					onclick="${this.#removeFileItem.bind(this, item)}"
				></span>
			</span>
		`;
	}

	onBeforeSave()
	{
		let ids = [];
		if (this.#type === FileSelector.TYPE.Disk)
		{
			ids = this.#getDiskUploader().getValues();
		}
		else if (this.#type === FileSelector.TYPE.File)
		{
			ids = (
				[...this.#fileItemsNode.childNodes]
					.map((node) => node.getAttribute('data-file-expression'))
					.filter((id) => id !== '')
			);
		}

		const wrapper = Tag.render`<div></div>`;
		for (const id of ids)
		{
			Dom.append(
				Tag.render`
					<input
						type="hidden"
						name="${this.#valueInputName + (this.#multiple ? '[]' : '')}"
						value="${id}"
					/>
				`,
				wrapper,
			);
		}

		if (this.#inputWrapper)
		{
			Dom.replace(this.#inputWrapper, wrapper);
		}
		else
		{
			Dom.append(wrapper, this.targetInput);
		}
		this.#inputWrapper = wrapper;
	}
}
