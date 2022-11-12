import {Tag, Loc, Dom, Event, Text, Extension,} from 'main.core';
import {EventEmitter} from 'main.core.events';

import DefaultTab from './default-tab';

function isImage(name, type, size)
{
	type = type ? String(type) : null;
	size = size ? Number(size) : null;
	name = String(name).toLowerCase();
	let ext = name.split('.').pop();
	if (ext === name)
	{
		ext = null;
	}

	return (
		(type === null || type.indexOf("image/") === 0)
		&& (size === null || (size < 20 * 1024 * 1024))
		&& (ext !== name && 'jpg,bmp,jpeg,jpe,gif,png,webp'.split(',').indexOf(ext) >= 0));
}

export default class UploadTab extends DefaultTab
{
	static priority = 3;
	#fileId;
	#fileAccept = 'image/*';

	constructor(options: ?{
		fileAccept: ?String
	})
	{
		super();
		this.#fileId = ['fileUpload_', (new Date()).valueOf()].join('_');
		if (options && options.fileAccept)
		{
			this.#fileAccept = options.fileAccept;
		}
	}

	getHeader(): ?String
	{
		return null;
	}

	getBody(): Element {
		return this.cache.remember('body', () => {
			const res =  Tag.render`
			<div>
				<div class="ui-avatar-editor__btn-back" data-bx-role="button-back"></div>
				<div class="ui-avatar-editor__upload-link-container">
					<div data-bx-role="error-container" class="ui-avatar-editor__upload-error-desc"></div>
					<label for="${this.#fileId}" class="ui-avatar-editor__upload-link">
						${Loc.getMessage('JS_AVATAR_EDITOR_PICK_UP_THE_FILE')}
						<input type="file" id="${this.#fileId}" data-bx-role="file-button" accept="${Text.encode(this.#fileAccept)}" />
					</label>
					<div class="ui-avatar-editor__upload-desc">
						${Loc.getMessage('JS_AVATAR_EDITOR_DROP_FILES_INTO_THIS_AREA')}
					</div>
				</div>
				<div class="ui-avatar-editor__upload-info">
					<div class="ui-avatar-editor__upload-info-item"><!-- place for limit text --></div>
				</div>
			</div>`;
			const f = (event) => {
				const {target} = event;
				const fileButton = res.querySelector('[data-bx-role="file-button"]');
				const file = Array.from(target && target.files ? target.files : fileButton.files).shift();
				if (isImage(file.name, file.type, file.size))
				{
					this.emit('onSetFile', file);
				}
				Event.unbindAll(fileButton);
				const node = fileButton.cloneNode(true, {value : ""});
				Dom.adjust(node, {props : {value : ""}, attrs: {}});
				node.setAttribute("new", "Y" + (new Date()).valueOf());
				fileButton.parentNode.insertBefore(node, fileButton);
				fileButton.parentNode.removeChild(fileButton);
				Event.bind(node, "change", f)
			};
			Event.bind(res.querySelector('[data-bx-role="file-button"]'), 'change', f);

			const dropZone = new BX.DD.dropFiles(res);
			if (dropZone && dropZone.supported())
			{
				EventEmitter.subscribe(
					dropZone,
					'dropFiles',
					(files, e) => {
						if (e && e["dataTransfer"] && e["dataTransfer"]["items"] && e["dataTransfer"]["items"].length > 0)
						{
							const fileCopy = [];
							Array
								.from(e["dataTransfer"]["items"])
								.forEach((item) => {
									if (item["webkitGetAsEntry"] && item["getAsFile"])
									{
										let entry = item["webkitGetAsEntry"]();
										if (entry && entry.isFile )
										{
											fileCopy.push(item["getAsFile"]());
										}
									}
								});
							if (fileCopy.length > 0)
							{
								files = fileCopy;
							}
						}
						f({target: {files: files}});
					},
					{compatMode: true}
				);
				EventEmitter.subscribe(
					dropZone,
					'dragEnter',
					(e) => {
						if (e
							&& e["dataTransfer"]
							&& e.dataTransfer.types
							&& e.dataTransfer.items
						)
						{
							const isFileTransfer = Array.from(e.dataTransfer.types)
								.filter((type) => {
									return type === "Files";
								}).length > 0;

							if (isFileTransfer)
							{
								Dom.addClass(res.parentNode, 'dnd-over');
							}
						}
					},
					{compatMode: true}
				);
				EventEmitter.subscribe(
					dropZone,
					'dragLeave',
					() => { Dom.removeClass(res.parentNode, 'dnd-over'); },
					{compatMode: true}
				);
			}
			res.querySelector('[data-bx-role="button-back"]').onclick = () => {
				this.emit('onClickBack');
			};

			return res;
		});
	}

	deleteError()
	{
		this.getBody().querySelector('[data-bx-role="error-container"]').innerText = '';
		Dom.removeClass(this.getBodyContainer(), 'ui-avatar-editor--error');
	}

	static get code()
	{
		return 'upload';
	}
}
