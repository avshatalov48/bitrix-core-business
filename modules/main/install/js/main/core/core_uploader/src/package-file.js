import {EventEmitter, BaseEvent} from 'main.core.events';
import Options from "./options";
import Package from "./package";

export default class PackageFile  extends EventEmitter {
	static preparationStatusIsDone = 4;
	item: ?BX.UploaderFile;
	id: string;
	name: string;

	isReadyToPack: boolean = true; //null|ready
	packStatus = null; //null|inprogress|done|errored
	packPercent:number = 0;

	uploadStatus = null; //null|inprogress|done|errored
	fileStatus;

	#currentFileToUpload = null;

	constructor(item: BX.UploaderFile, pack: Package)
	{
		super();
		this.setEventNamespace(Options.getEventNamespace());

		this.item = item; // item with a node
		this.id = item.getId();
		this.name = item.name;
		this.fileStatus = Options.fileStatus.ready; // ready|remove|restore
		this.isReadyToPack = item.preparationStatus === this.constructor.preparationStatusIsDone;

		this.copiesCount = item.getThumbs("getCount") + 1;

		EventEmitter.subscribeOnce(item, 'onFileIsDeleted', () => {
			this.fileStatus = Options.fileStatus.removed;
		});

		if (!this.isReadyToPack)
		{
			EventEmitter.subscribeOnce(item,
				'onFileIsPrepared',
				() => {
					this.isReadyToPack = true;
					this.emit('onReady');
				}
			);
			EventEmitter.emit(item,
				'onFileHasToBePrepared',
				new BaseEvent({compatData: [item.getId(), item]})
			);
		}
	}

	isReady(): boolean
	{
		return this.isReadyToPack;
	}

	isRemoved()
	{
		return this.fileStatus === Options.fileStatus.removed;
	}

	isPacked()
	{
		return this.packStatus === Options.uploadStatus.done;
	}

	getId()
	{
		return this.id;
	}

	get size(): number
	{
		return this.item ? (this.item.size || 0) : 0;
	}

	markAsPacked(percentToIncrement:boolean|number)
	{
		if (percentToIncrement === true)
		{
			this.packStatus = Options.uploadStatus.done;
			this.packPercent = 100;
		}
		else
		{
			this.packPercent += percentToIncrement / this.copiesCount;
			this.packPercent = (this.packPercent > 100 ? 100 : this.packPercent);
		}
	}

	packFile(): {
		error: boolean,
		done: boolean,
		file: ?Blob,
		properties: ?Object
	}
	{
		const result = {
			error: false,
			done: true,
			data: null,
		};

		if (this.isRemoved())
		{
			result.data = {
				removed: 'Y',
				name: this.name
			};
			this.markAsPacked(true);
		}

		if (this.isPacked())
		{
			return result;
		}

		let currentBlob;
		let copyName = 'default';

		if (this.packStatus === null)
		{
			result.data = this.item.getProps() || {name: this.name};

			if (this.item['restored'])
			{
				result.data['restored'] = this.item['restored'];
				delete this.item['restored'];
			}
			this.packStatus = Options.uploadStatus.inProgress;
			currentBlob = this.item["file"];
		}
		else if (this.#currentFileToUpload instanceof Blob)
		{
			currentBlob = this.#currentFileToUpload;
			this.#currentFileToUpload = null;
		}
		else
		{
			currentBlob = this.item.getThumbs(null);
			if (currentBlob === null)
			{
				this.markAsPacked(true);
				return result;
			}
			copyName = currentBlob['thumb'];
		}

		let packingPercent = 100;
		if (currentBlob instanceof Blob) // Regular behaviour
		{
			const blob = BX.UploaderUtils.getFilePart(
				currentBlob,
				Options.getUploadLimits('phpUploadMaxFilesize')
			);

			if (blob && blob !== currentBlob)
			{
				if ((blob.packages - blob.package) > 1)
				{
					this.#currentFileToUpload = currentBlob;
				}
				packingPercent = blob.size / currentBlob.size * 100;
				copyName = [
					copyName,
					'.ch', blob.package,
					'.', (blob.start > 0 ? blob.start : "0") +
					'.chs' + blob.packages].join('');
				blob.name = copyName;
			}
			currentBlob = blob;
		}
		if (currentBlob)
		{
			result.data = (result.data || {name: this.name});
			if (currentBlob instanceof Blob)
			{
				result.data[copyName] = currentBlob;
			}
			else
			{
				result.data['files'] = result.data['files'] || {};
				result.data['files'][copyName] = currentBlob;
			}
		}
		if (result.data)
		{
			result.done = false;
			this.markAsPacked(packingPercent);
		}
		else
		{
			this.markAsPacked(true);
		}
		return result;
	}

	parseResponse({file, hash, status})
	{
		// console.log('parseResponse: ', this.getId(), file);
	}
}