import {Loc, Tag, Runtime} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import DiskController from './disk-controller';
import UploadFile from './upload-file';
import Editor from '../../editor';
import Default from "../default";
import Controller from "./controller";
/*
* @deprecated
* */
export default class UploadImage extends Default
{
	id: string  = 'uploadimage';
	buttonParams = null;
	regexp = /\[IMAGE ID=((?:\s|\S)*?)?\]/ig;

	values: Map = new Map;
	controllers: Map = new Map();

	constructor(editor: Editor, htmlEditor)
	{
		super(editor, htmlEditor);
		this.init();
		console.log('PostImage: ');

		EventEmitter.subscribe(editor.getEventObject(), 'onReinitializeBefore', ({data: [text, data]}) => {
			this.reinit(text, data);
		});
	}

	init()
	{
		Array.from(
			this.editor.getContainer()
				.querySelectorAll('.file-selectdialog')
		)
		.forEach((selectorNode) => {
			const cid = selectorNode.id.replace('file-selectdialog-', '');
			let controller = this.controllers.get(cid);
			if (!controller)
			{
				controller = new Controller(cid, selectorNode, this.editor);
				EventEmitter.subscribe(selectorNode.parentNode,
					'OnFileUploadSuccess',
					({data: [{element_id}, {id, doc_prefix, CID}]}) => {
						if (cid === id)
						{
							const securityNode = document.querySelector('#' + this.editor.getFormId()) ?
								document.querySelector('#' + this.editor.getFormId()).querySelector('#upload-cid') : null;
							if (securityNode)
							{
								securityNode.value = CID;
							}
							const [id, file] = this.parseFile(selectorNode.querySelector('#' + doc_prefix + element_id));
							this.values.set(id, file);
						}
					});
				EventEmitter.subscribe(selectorNode.parentNode,
					'OnFileUploadRemove',
					({compatData: [fileId, {id}]}) => {
						if (cid === id && this.values.has(fileId))
						{
							this.values.delete(fileId);
						}
					});
			}

			if (selectorNode.querySelector('table.files-list'))
			{
				Array.from(
					selectorNode
						.querySelector('table.files-list')
						.querySelectorAll('tr')
				)
				.forEach((tr) => {
					const [id, file] = this.parseFile(tr);
					this.values.set(id, file);
				});
			}
		});
	}

	parseFile(tr)
	{
		const id = tr.id.replace('wd-doc', '');
		const data = {
			id: id,
			name: tr.querySelector('[data-role="name"]') ? tr.querySelector('[data-role="name"]').innerHTML : tr.querySelector('span.f-wrap').innerHTML,
			node: tr,
			image: {
				src: null,
				lowsrc: null,
				width: null,
				height: null
			}
		};
		return [id, data];
	}

	reinit(text, data)
	{
		this.values.forEach((file, id) => {
			if (file.node && file.node.parentNode)
			{
				file.node.parentNode.removeChild(file.node);
			}
		});
		this.values.clear();

		this.controllers.forEach((controller: Controller) => {
			controller.reinitFrom(data);
		});
	}

	parse(content)
	{
		return content;
	}

	unparse(bxTag, {node})
	{
		return '';
	}
}

