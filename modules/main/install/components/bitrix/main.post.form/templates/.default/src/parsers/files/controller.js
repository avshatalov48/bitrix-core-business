import {EventEmitter, BaseEvent} from 'main.core.events';
import Editor from "../../editor";

export default class Controller
{
	actionPool: Array = [];
	cid: String;
	container: Element;
	editor: Editor;

	constructor(cid, container, editor)
	{
		this.cid = cid;
		this.container = container;
		this.editor = editor;
		EventEmitter.subscribe(editor.getEventObject(), 'onShowControllers', ({data}) => {
			EventEmitter.emit(container.parentNode, 'BFileDLoadFormController', new BaseEvent({compatData: [data]}));
		});
		EventEmitter.subscribe(editor.getEventObject(), 'onCollectControllers', (event) => {
			event.data[cid] = {values: []};
		});
	}

	get isReady()
	{
		return true;
	}

	exec(callback = null): void
	{
		if (callback)
		{
			this.actionPool.push(callback);
		}
		if (this.isReady)
		{
			try{
				let action;
				while ((action = this.actionPool.shift()) && action)
				{
					action.apply(this);
				}
			}
			catch(e)
			{
				console.log('error in attachments controllers: ', e);
			}
		}
	}

	getId(): string
	{
		return this.cid;
	}

	getFieldName(): ?string
	{
		return null;
	}

	reinitFrom(data)
	{
		this.exec(() => {
			if (!this.getFieldName())
			{
				return
			}
			this.container.querySelector(`inptut[name="${this.getFieldName()}"]`)
				.forEach(function(inputFile) {
						inputFile.parentNode.removeChild(inputFile);
					}
				);
		});
	}
}