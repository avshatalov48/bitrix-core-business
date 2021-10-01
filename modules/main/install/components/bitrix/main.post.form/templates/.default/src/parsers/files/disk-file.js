import {Loc, Tag, Runtime} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import DiskController from './disk-controller';
import UploadFile from './upload-file';
import Editor from '../../editor';
/*
* @deprecated
* */
export default class DiskFile extends UploadFile
{
	id: string  = 'diskfile';
	regexp = /\[(?:DOCUMENT ID|DISK FILE ID)=([n0-9]+)\]/ig;

	init()
	{
		Array.from(
			this.editor.getContainer()
				.querySelectorAll('.diskuf-selectdialog')
		)
		.forEach((selectorNode, index) => {
			const cid = selectorNode.id.replace('diskuf-selectdialog-', '');
			let controller = this.controllers.get(cid);
			if (!controller)
			{
				controller = new DiskController(cid, selectorNode, this.editor);
				this.controllers.set(cid, controller);

				EventEmitter.subscribe(selectorNode.parentNode,
					'OnFileUploadSuccess',
					({data: [{element_id}, {CID}, blob]}) => {
						if (controller.getId() !== CID || this.values.has(element_id))
						{
							return;
						}
						const [id, fileId, file] = this.parseFile(selectorNode.querySelector('#disk-edit-attach' + element_id));
						this.values.set(id, file);
						if (id !== fileId)
						{
							this.values.set(fileId, file);
						}
						if (blob && blob['insertImageAfterUpload'] && file.image.src)
						{
							this.insertFile(id, file.node);
						}
					});
				EventEmitter.subscribe(selectorNode.parentNode,
					'OnFileUploadRemove',
					({compatData: [fileId, {CID}]}) => {

						if (controller.getId() === CID && this.values.has(fileId))
						{
							const file = this.values.get(fileId);
							this.values.delete(file.id);
							this.values.delete(file.fileId);
							this.deleteFile([file.id, file.fileId]);
						}
					});
				EventEmitter.subscribe(selectorNode.parentNode,
					'OnFileUploadFailed',
					({compatData: [file, {CID}, blob]}) => {
						if (controller.getId() === CID && blob && blob["referrerToEditor"])
						{
							BX.onCustomEvent(blob["referrerToEditor"], "OnImageDataUriCaughtFailed", []);
							BX.onCustomEvent(this.editor, "OnImageDataUriCaughtFailed", [blob["referrerToEditor"]]);
						}
					}
				);
				if (index === 0)
				{
					initVideoReceptionForTheFirstController(this, controller, selectorNode, this.editor);
					initImageReceptionForTheFirstController(this, controller, selectorNode, this.editor);
					EventEmitter.subscribe(this.editor.getEventObject(), 'onFilesHaveCaught', (event: BaseEvent) => {
						event.stopImmediatePropagation();
						controller.diskUfUploader.onChange([...event.getData()]);
					});
				}
			}

			if (selectorNode.querySelector('table.files-list'))
			{
				Array.from(
					selectorNode
						.querySelector('table.files-list')
						.querySelectorAll('tr')
				)
				.forEach((tr) => {
					const [id, fileId, file] = this.parseFile(tr);
					this.values.set(id, file);
					if (id !== fileId)
					{
						this.values.set(fileId, file);
					}
				});
			}
		});
	}

	parseFile(tr)
	{
		const id = String(tr.id.replace('disk-edit-attach', ''));

		const data = {
			id: id,
			name: tr.querySelector('[data-role="name"]') ? tr.querySelector('[data-role="name"]').innerHTML : tr.querySelector('span.f-wrap').innerHTML,
			fileId: tr.getAttribute('bx-attach-file-id'),
			node: tr,
			buttonNode: tr.querySelector('[data-role="button-insert"]'),
			image: {
				src: null,
				lowsrc: null,
				width: null,
				height: null
			}
		};
		const nameNode = tr.querySelector('.f-wrap');
		const insertFile = () => { this.insertFile(id, tr); };
		if (nameNode)
		{
			nameNode.addEventListener('click', insertFile);
			nameNode.style.cursor = 'pointer';
			nameNode.title = Loc.getMessage('MPF_FILE');
		}
		const imageNode = tr.querySelector('img.files-preview');

		if (imageNode && (imageNode.src.indexOf('bitrix/tools/disk/uf.php') >= 0 || imageNode.src.indexOf('/disk/showFile/') >= 0))
		{
			imageNode.addEventListener('click', insertFile);
			imageNode.title = Loc.getMessage('MPF_FILE');
			imageNode.style.cursor = 'pointer';
			data.image.lowsrc = imageNode.lowsrc || imageNode.src;
			data.image.src = (imageNode.rel || imageNode.getAttribute('data-bx-src') || imageNode.src).replace(/&(width|height)=\d+/gi, '');
			const handler = () => {
				data.image.width = imageNode.getAttribute('data-bx-full-width');
				data.image.height = imageNode.getAttribute('data-bx-full-height');
			}
			imageNode.addEventListener('load', handler);
			if (imageNode.complete)
			{
				handler();
			}
		}
		if (tr instanceof HTMLTableRowElement && !data.buttonNode)
		{
			data.buttonNode = Tag.render`
<span class="insert-btn" data-role="button-insert" onclick="${insertFile}">
	<span data-role="insert-btn" class="insert-btn-text">${Loc.getMessage('MPF_FILE_INSERT_IN_TEXT')}</span>
	<span data-role="in-text-btn" class="insert-btn-text" style="display: none;">${Loc.getMessage('MPF_FILE_IN_TEXT')}</span>
</span>`;
			setTimeout(() => {
				if ( tr.querySelector('.files-info'))
				{
					tr.querySelector('.files-info').appendChild(data.buttonNode);
					this.checkButtonsDebounced();
				}
			});
		}
		return [id, data.fileId, data];
	}

	buildText(id, params)
	{
		return `[DISK FILE ID=${id}${params||''}]`;
	}
}

function initVideoReceptionForTheFirstController(diskFileParser: DiskFile, controller: DiskController, selectorNode, editor: Editor)
{
	EventEmitter.subscribe(editor.getEventObject(), 'OnVideoHasCaught', (event: BaseEvent) => {
		const fileToUpload = event.getData();
		const onSuccess = ({data: [{element_id}, {}, blob]}) => {
			if (fileToUpload === blob && diskFileParser.values.has(element_id))
			{
				EventEmitter.unsubscribe(selectorNode.parentNode, 'OnFileUploadSuccess', onSuccess);
				diskFileParser.insertFile(element_id, diskFileParser.values.get(element_id).node);
			}
		}
		EventEmitter.subscribe(selectorNode.parentNode, 'OnFileUploadSuccess', onSuccess);
		controller.exec(() => {
			controller.diskUfUploader.onChange([fileToUpload])
		});
		event.stopImmediatePropagation();
	});
}
function initImageReceptionForTheFirstController(diskFileParser: DiskFile, controller: DiskController, selectorNode, editor: Editor)
{
	EventEmitter.subscribe(editor.getEventObject(), 'OnImageHasCaught', (event: BaseEvent) => {
		event.stopImmediatePropagation();
		const fileToUpload = event.getData();
		return new Promise((resolve, reject) => {
			const onSuccess = ({data: [{element_id}, {}, blob]}) => {
				if (fileToUpload === blob && diskFileParser.values.has(element_id))
				{
					EventEmitter.unsubscribe(selectorNode.parentNode, 'OnFileUploadSuccess', onSuccess);
					EventEmitter.unsubscribe(selectorNode.parentNode, 'OnFileUploadFailed', onFailed);

					const file = diskFileParser.values.get(element_id);
					const html = diskFileParser.buildHTML(element_id, file);
					resolve({image: file.image, html: html});
				}
			}
			const onFailed = ({data: [file, {}, blob]}) => {
				if (fileToUpload === blob)
				{
					EventEmitter.unsubscribe(selectorNode.parentNode, 'OnFileUploadSuccess', onSuccess);
					EventEmitter.unsubscribe(selectorNode.parentNode, 'OnFileUploadFailed', onFailed);
					reject();
				}
			};
			EventEmitter.subscribe(selectorNode.parentNode, 'OnFileUploadSuccess', onSuccess);
			EventEmitter.subscribe(selectorNode.parentNode, 'OnFileUploadFailed', onFailed);

			controller.exec(() => {
				controller.diskUfUploader.onChange([event.getData()])
			});
		});
	});
}

