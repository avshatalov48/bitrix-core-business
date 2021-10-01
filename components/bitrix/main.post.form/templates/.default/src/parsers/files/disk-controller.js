import {EventEmitter, BaseEvent} from 'main.core.events';
import Controller from "./controller";

export default class DiskController extends Controller
{
	diskUfUploader = null;
	diskUfHandler = null;

	constructor(cid, container, editor)
	{
		super(cid, container, editor);

		const _catchHandler = (diskUfUploader) => {
			this.diskUfUploader = diskUfUploader;
			this.exec();
			const func = (BaseEvent: BaseEvent) => {
				EventEmitter.emit(
					editor.getEventObject(),
					'onUploadsHasBeenChanged',
					BaseEvent
				);
			};
			EventEmitter.subscribe(this.diskUfUploader, 'onFileIsInited', func); // new diskUfUploader
			EventEmitter.subscribe(this.diskUfUploader, 'ChangeFileInput', func); // old diskUfUploader
		};

		if (BX.UploaderManager.getById(cid))
		{
			_catchHandler(BX.UploaderManager.getById(cid));
		}
		EventEmitter.subscribeOnce(container.parentNode, 'DiskDLoadFormControllerInit', ({compatData: [diskUfHandler]}) => {
			this.diskUfHandler = diskUfHandler
			if (cid === diskUfHandler.CID && !this.diskUfUploader)
			{
				_catchHandler(diskUfHandler.agent);
			}
		});

		EventEmitter.subscribe(editor.getEventObject(), 'onShowControllers', ({data}) => {
			EventEmitter.emit(container.parentNode, 'DiskLoadFormController', new BaseEvent({compatData: [data]}));
		});
	}

	get isReady()
	{
		return !!this.diskUfUploader;
	}

	getFieldName(): ?string
	{
		if (this.diskUfHandler)
		{
			return this.diskUfHandler.params.controlName;
		}
		return null;
	}

	reinitFrom(data)
	{
		this.exec(() => {
			if (!this.getFieldName())
			{
				return
			}
			Array.from(
				this.container
					.querySelectorAll(`inptut[name="${this.getFieldName()}"]`)
			)
			.forEach(function(inputFile) {
					inputFile.parentNode.removeChild(inputFile);
				}
			);
			let values = null;
			for (let ii in data)
			{
				if (data.hasOwnProperty(ii)
					&& data[ii] && data[ii]['USER_TYPE_ID'] === 'disk_file'
					&& data[ii]['FIELD_NAME'] === this.getFieldName())
				{
					values = data[ii]['VALUE'];
				}
			}

			if (values)
			{
				const files = {};

				values.forEach((id) => {
					let node = document.querySelector('#disk-attach-' + id);
					if (node.tagName !== "A")
					{
						node = node.querySelector('img');
					}
					if (node)
					{
						files['E' + id] = {
							type: 'file',
							id: id,
							name: node.getAttribute("data-bx-title") || node.getAttribute("data-title"),
							size: node.getAttribute("data-bx-size") || '',
							sizeInt: node.getAttribute("data-bx-size") || '',
							width: node.getAttribute("data-bx-width"),
							height: node.getAttribute("data-bx-height"),
							storage: 'disk',
							previewUrl: (node.tagName === "A" ? '' : node.getAttribute("data-bx-src") || node.getAttribute("data-src")),
							fileId: node.getAttribute("bx-attach-file-id")
						};
						if (node.hasAttribute("bx-attach-xml-id"))
							files['E' + id]["xmlId"] = node.getAttribute("bx-attach-xml-id");
						if (node.hasAttribute("bx-attach-file-type"))
							files['E' + id]["fileType"] = node.getAttribute("bx-attach-file-type");
					}
				});
				this.diskUfHandler.selectFile({}, {}, files);
			}
		});
	}
}