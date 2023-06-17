import {Text, Type} from 'main.core';

import {Core} from 'im.v2.application.core';
import {SidebarDetailBlock, SidebarBlock} from 'im.v2.const';

import {Media} from './blocks/media';
import {Audio} from './blocks/audio';
import {Document} from './blocks/document';
import {Other} from './blocks/other';
import {Favorite} from './blocks/favorite';
import {Link} from './blocks/link';
import {Task} from './blocks/task';
import {Main} from './blocks/main';
import {Brief} from './blocks/brief';
import {Meeting} from './blocks/meeting';
import {FileUnsorted} from './blocks/file-unsorted';
import {AvailabilityManager} from './availability-manager';


const BlockClasses = {
	Main, Favorite, Link, Task, Media, Audio, Document, Other, Brief, Meeting, FileUnsorted
};

const BlockToServices = Object.freeze({
	[SidebarBlock.main]: [SidebarDetailBlock.main],
	[SidebarBlock.info]: [SidebarDetailBlock.favorite, SidebarDetailBlock.link],
	[SidebarBlock.task]: [SidebarDetailBlock.task],
	[SidebarBlock.meeting]: [SidebarDetailBlock.meeting],
	[SidebarBlock.brief]: [SidebarDetailBlock.brief],
	[SidebarBlock.file]: [SidebarDetailBlock.media, SidebarDetailBlock.audio, SidebarDetailBlock.document, SidebarDetailBlock.other],
	[SidebarBlock.fileUnsorted]: [SidebarDetailBlock.fileUnsorted],
});

type BlockService = {
	type: string;
	blockManager: Object;
	initialRequest: Object;
	responseHandler: Function;
};

export class SidebarService
{
	blockServices: BlockService[] = [];
	dialogId: string = '';
	chatId: number = 0;
	store: Object = null;
	restClient: Object = null;
	availabilityManager: AvailabilityManager = null;

	constructor(availabilityManager: AvailabilityManager)
	{
		this.store = Core.getStore();
		this.restClient = Core.getRestClient();
		this.availabilityManager = availabilityManager;
	}

	//region public methods
	requestInitialData(): Promise
	{
		if (!Type.isArrayFilled(this.blockServices))
		{
			this.buildBlocks();
		}

		return new Promise((resolve, reject) => {
			this.restClient.callBatch(
				this.getInitialRequestQuery(),
				(result) => resolve(this.handleBatchRequestResult(result)),
				(error) => reject(error)
			);
		});
	}

	getBlockInstance(blockName: string)
	{
		if (!Type.isArrayFilled(this.blockServices))
		{
			this.buildBlocks();
		}

		return this.blockServices.find((block: BlockService) => block.type === blockName.toLowerCase())?.blockManager;
	}

	setChatId(chatId: number)
	{
		this.chatId = chatId;
	}

	setDialogId(dialogId: string)
	{
		this.dialogId = dialogId;
	}
	//endregion

	buildBlocks(): void
	{
		const classNames = this.getServiceClassesForBlocks();

		this.blockServices = classNames.map((className: string): BlockService => {
			const blockManager = new BlockClasses[className](this.chatId, this.dialogId);

			return {
				type: className.toLowerCase(),
				blockManager: blockManager,
				initialRequest: blockManager.getInitialRequest(),
				responseHandler: blockManager.getResponseHandler(),
			};
		});
	}

	getServiceClassesForBlocks(): string[]
	{
		const services = [];

		const blockList = this.availabilityManager.getBlocks();
		blockList.forEach((block: string) => {
			const blockServices = BlockToServices[block];
			if (blockServices)
			{
				services.push(...blockServices);
			}
		});

		return services.map(service => Text.capitalize(service));
	}

	getInitialRequestQuery(): Object
	{
		let query = {};
		this.blockServices.forEach(block => {
			query = Object.assign(query, block.initialRequest);
		});

		return query;
	}

	handleBatchRequestResult(response): Promise
	{
		const responseHandlersResult = [];
		this.blockServices.forEach(block => {
			responseHandlersResult.push(block.responseHandler(response));
		});

		return Promise.all(responseHandlersResult).then(() => {
			return this.setInited();
		}).catch(error => console.error(error));
	}

	setInited(): Promise
	{
		return this.store.dispatch('sidebar/setInited', this.chatId);
	}
}