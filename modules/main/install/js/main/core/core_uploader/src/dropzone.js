import {Type, Runtime, Loc, Dom} from 'main.core';
import {EventEmitter} from 'main.core.events';
import Options from './options';
import type {UploaderType, UploadQueueType} from "./types";

export default class DropZone {
	dndObject;

	constructor(dropZoneNode: ?Element)
	{
		if (Type.isStringFilled(dropZoneNode))
		{
			dropZoneNode = document.getElementById(dropZoneNode);
		}

		if (Type.isDomNode(dropZoneNode) && BX.DD && BX.ajax.FormData.isSupported())
		{
			this.initialize(dropZoneNode);
		}
	}

	initialize(dropZoneNode: ?Element)
	{
		this.dndObject = new BX.DD.dropFiles(dropZoneNode);
		if (!this.dndObject || !this.dndObject.supported())
		{
			return;
		}
		const handlers = {
			dropFiles: ({compatData: [files, e]}) => {
				if (e
					&& e["dataTransfer"]
					&& e["dataTransfer"]["items"]
					&& e["dataTransfer"]["items"].length > 0)
				{
					let replaceFileArray = false;
					const fileCopies = [];
					let item;
					for (let i = 0; i < e["dataTransfer"]["items"].length; i++) {
						item = e["dataTransfer"]["items"][i];
						if (item["webkitGetAsEntry"] && item["getAsFile"])
						{
							replaceFileArray = true;
							const entry = item["webkitGetAsEntry"]();
							if (entry && entry.isFile)
							{
								fileCopies.push(item["getAsFile"]());
							}
						}
					}
					if (replaceFileArray)
						files = fileCopies;
				}
				EventEmitter.emit(this, Options.getEventName('caught'), {files: files});
			},
			dragEnter : ({compatData: [e]}) => {
				let isFileTransfer = false;
				if (e && e["dataTransfer"] && e["dataTransfer"]["types"])
				{
					for (var i = 0; i < e["dataTransfer"]["types"].length; i++)
					{
						if (e["dataTransfer"]["types"][i] === "Files")
						{
							isFileTransfer = true;
							break;
						}
					}
				}
				if (isFileTransfer)
				{
					this.dndObject.DIV.classList.add('bxu-file-input-over');
					BX.onCustomEvent(this, 'dragEnter', [e]); // compatibility event
				}
			},
			dragLeave : ({compatData: [e]}) => {
				this.dndObject.DIV.classList.remove('bxu-file-input-over');
				BX.onCustomEvent(this, 'dragLeave', [e]); // compatibility event
			}
		}
		EventEmitter.subscribe(this.dndObject, 'dropFiles', handlers.dropFiles);
		EventEmitter.subscribe(this.dndObject, 'dragEnter', handlers.dragEnter);
		EventEmitter.subscribe(this.dndObject, 'dragLeave' , handlers.dragLeave);
	}

	destroy()
	{
		EventEmitter.unsubscribeAll(this.dndObject);
		delete this.dndObject.DIV;
		delete this.dndObject;
	}
}
