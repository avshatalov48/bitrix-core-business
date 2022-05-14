import {Loc, Tag, Runtime} from 'main.core';
import {EventEmitter} from 'main.core.events';
import Default from '../default';
import Editor from '../../editor';
import Controller from './controller';
/*
* @deprecated
* */
export default class UploadFile extends Default
{
	id: string  = 'uploadfile';
	buttonParams = null;
	regexp = /\[FILE ID=((?:\s|\S)*?)?\]/ig;

	values: Map = new Map;
	controllers: Map = new Map();

	constructor(editor: Editor, htmlEditor)
	{
		super(editor, htmlEditor);
		this.checkButtonsDebounced = Runtime.debounce(this.checkButtons, 500, this);
		this.init();
		EventEmitter.subscribe(editor.getEditor(), 'OnContentChanged', this.checkButtons.bind(this));
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
		.forEach((selectorNode, index) => {
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
							this.deleteFile([fileId]);
						}
					});
				if (index === 0)
				{
					EventEmitter.subscribe(this.editor.getEventObject(), 'onFilesHaveCaught', (event: BaseEvent) => {
						event.stopImmediatePropagation();
						if (window['BfileFD' + cid])
						{
							window['BfileFD' + cid].agent.UploadDroppedFiles([...event.getData()])
						}
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
			buttonNode: tr.querySelector('[data-role="button-insert"]'),
			image: {
				src: null,
				lowsrc: null,
				width: null,
				height: null
			}
		};
		const insertFile = () => {
			this.insertFile(id, tr);
		};
		const nameNode = tr.querySelector('.f-wrap');
		if (nameNode)
		{
			nameNode.addEventListener('click', insertFile);
			nameNode.style.cursor = 'pointer';
			nameNode.title = Loc.getMessage('MPF_FILE');
		}
		const imageNode = tr.querySelector('img');
		if (imageNode)
		{
			imageNode.addEventListener('click', insertFile);
			imageNode.title = Loc.getMessage('MPF_FILE');
			imageNode.style.cursor = 'pointer';
			data.image.lowsrc = imageNode.lowsrc || imageNode.src;
			data.image.src = imageNode.rel || imageNode.src;
			data.image.width = imageNode.getAttribute('data-bx-full-width');
			data.image.height = imageNode.getAttribute('data-bx-full-height');
		}

		if (tr instanceof HTMLTableRowElement && tr.querySelector('.files-info'))
		{
			if (!data.buttonNode)
			{
				data.buttonNode = Tag.render`
<span type="button" onclick="${insertFile}" data-role="button-insert" class="insert-btn">
	<span data-role="insert-btn" class="insert-btn-text">${Loc.getMessage('MPF_FILE_INSERT_IN_TEXT')}</span>
	<span data-role="in-text-btn" class="insert-btn-text">${Loc.getMessage('MPF_FILE_IN_TEXT')}</span>
</span>`;

				tr.querySelector('.files-info').appendChild(data.buttonNode);
				this.checkButtonsDebounced();
			}
		}

		return [id, data];
	}

	buildHTML(id, data, htmlData = null): string
	{
		const tagId = this.htmlEditor.SetBxTag(false, {tag: this.id, fileId: id});
		let html = `<span data-bx-file-id="${id}" id="${tagId}" style="color: #2067B0; border-bottom: 1px dashed #2067B0; margin:0 2px;">${data.name}</span>`;

		if (data.image.src)
		{
			let additional = [];
			if (htmlData)
			{
				additional.push(`style="width:${htmlData.width}px;height:${htmlData.height}px;"`);
			}
			else if (data.image.width && data.image.height)
			{
				additional.push(`style="width:${data.image.width}px;height:${data.image.height}px;" `);
				additional.push(`onload="this.style.width='auto';this.style.height='auto';"`);
			}

			html = `<img style="max-width: 90%;"  data-bx-file-id="${id}" id="${tagId}" src="${data.image.src}" lowsrc="${data.image.lowsrc}" ${additional.join(' ')}/>`
		}

		return html;
	}

	buildText(id, params)
	{
		return `[FILE ID=${id}${params||''}]`;
	}

	insertFile(id: string, node)
	{
		const data = this.values.get(String(id));

		if (data)
		{
			EventEmitter.emit(this.editor.getEventObject(), 'OnInsertContent', [this.buildText(id), this.buildHTML(id, data)]);
		}
	}

	deleteFile(fileIds)
	{
		const content = this.htmlEditor.GetContent();

		if (this.htmlEditor.GetViewMode() === 'wysiwyg')
		{
			const doc = this.htmlEditor.GetIframeDoc();

			for (let ii in this.htmlEditor.bxTags)
			{
				if (this.htmlEditor.bxTags.hasOwnProperty(ii)
					&& typeof this.htmlEditor.bxTags[ii] === 'object'
					&& this.htmlEditor.bxTags[ii]['tag'] === this.id
					&& fileIds.indexOf(String(this.htmlEditor.bxTags[ii]['fileId'])) >= 0
					&& doc.getElementById(ii)
				)
				{
					const node = doc.getElementById(ii);
					node.parentNode.removeChild(node);
				}
			}
			this.htmlEditor.SaveContent();
		}
		else/* if (this.regexp.test(content))*/
		{
			const content2 = content.replace(this.regexp, function(str, foundId) {
					return fileIds.indexOf(foundId) >= 0 ? '' : str;
			});
			this.htmlEditor.SetContent(content2);
			this.htmlEditor.Focus();
		}
	}

	checkButtons(event: ?BaseEvent)
	{
		const content = event ? event.compatData[0] : this.htmlEditor.GetContent();
		const matches = [...content.matchAll(this.regexp)]
			.map(([match, id]) => {
				return id;
			});

		this.values.forEach((data, id) => {
			if (!data.buttonNode)
			{
				return;
			}
			const mark = matches.indexOf(id) >= 0;
			if (mark === true && data.buttonNode.className !== 'insert-text')
			{
				data.buttonNode.className = 'insert-text';
				data.buttonNode.querySelector('[data-role="insert-btn"]').style.display = 'none';
				data.buttonNode.querySelector('[data-role="in-text-btn"]').style.display = '';
			}
			else if (mark !== true && data.buttonNode.className !== 'insert-btn')
			{
				data.buttonNode.className = 'insert-btn';
				data.buttonNode.querySelector('[data-role="insert-btn"]').style.display = '';
				data.buttonNode.querySelector('[data-role="in-text-btn"]').style.display = 'none';
			}
		});
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
		if (!this.regexp.test(content))
		{
			return content;
		}
		content = content.replace(
			this.regexp,
			function(str, id, width, height)
			{
				if (this.values.has(id))
				{
					return this.buildHTML(id, this.values.get(id), (width > 0 && height > 0 ? {width, height} : null));
				}
				return str;
			}.bind(this)
		);
		return content;
	}

	unparse(bxTag, {node})
	{
		const width = parseInt(node.hasAttribute('width') ? node.getAttribute('width') : 0);
		const height = parseInt(node.hasAttribute('height') ? node.getAttribute('height') : 0);
		let params = '';

		if (width > 0 && height > 0)
		{
			params = ' WIDTH=' + width + ' HEIGHT=' + height;
		}

		const id = node.getAttribute('data-bx-file-id');
		return this.buildText(id, params);
	}
}
