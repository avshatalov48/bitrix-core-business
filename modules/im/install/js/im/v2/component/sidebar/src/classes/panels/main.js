import { Text } from 'main.core';

import { Core } from 'im.v2.application.core';
import { callBatch } from 'im.v2.lib.rest';
import { SidebarDetailBlock, SidebarMainPanelBlock } from 'im.v2.const';
import { SidebarManager } from 'im.v2.lib.sidebar';

import { Favorite } from './favorite';
import { getChatId } from './helpers/get-chat-id';
import { Link } from './link';
import { File } from './file';
import { Task } from './task';
import { Meeting } from './meeting';
import { MembersService as Members } from './members';
import { Multidialog } from './multidialog';

import { FileUnsorted } from './file-unsorted';

import type { Store } from 'ui.vue3.vuex';
import type { RestClient } from 'rest.client';

const MainPanelServiceClasses = {
	Members,
	Favorite,
	Link,
	Task,
	File,
	Meeting,
	FileUnsorted,
	Multidialog,
};

const BlockToServices = Object.freeze({
	[SidebarMainPanelBlock.chat]: [SidebarDetailBlock.members],
	[SidebarMainPanelBlock.copilot]: [SidebarDetailBlock.members],
	[SidebarMainPanelBlock.task]: [SidebarDetailBlock.members],
	[SidebarMainPanelBlock.copilotInfo]: [SidebarDetailBlock.favorite],
	[SidebarMainPanelBlock.info]: [SidebarDetailBlock.favorite, SidebarDetailBlock.link],
	[SidebarMainPanelBlock.fileList]: [SidebarDetailBlock.file],
	[SidebarMainPanelBlock.fileUnsortedList]: [SidebarDetailBlock.fileUnsorted],
	[SidebarMainPanelBlock.taskList]: [SidebarDetailBlock.task],
	[SidebarMainPanelBlock.meetingList]: [SidebarDetailBlock.meeting],
	[SidebarMainPanelBlock.multidialog]: [SidebarDetailBlock.multidialog],
});

type BlockService = {
	initialQuery: Object;
	responseHandler: Function;
};

export class Main
{
	blockServices: BlockService[] = [];
	dialogId: string;
	store: Store;
	restClient: RestClient;

	constructor({ dialogId })
	{
		this.store = Core.getStore();
		this.restClient = Core.getRestClient();
		this.dialogId = dialogId;
		this.buildBlocks();
	}

	// region public methods
	async requestInitialData(): Promise
	{
		const query = this.getInitialQuery();
		const response = await callBatch(query);

		return this.handleBatchRequestResult(response);
	}
	// endregion

	buildBlocks(): BlockService[]
	{
		const classNames = this.getServiceClassesForBlocks();

		this.blockServices = classNames.map((ClassName: string): BlockService => {
			const blockService = new MainPanelServiceClasses[ClassName]({
				dialogId: this.dialogId,
			});

			return {
				initialQuery: blockService.getInitialQuery(),
				responseHandler: blockService.getResponseHandler(),
			};
		});
	}

	getServiceClassesForBlocks(): string[]
	{
		const services = [];
		const sidebarConfig = SidebarManager.getInstance().getConfig(this.dialogId);
		const blockList = sidebarConfig.getBlocks(this.dialogId);

		blockList.forEach((block: string) => {
			const blockServices = BlockToServices[block];
			if (blockServices)
			{
				services.push(...blockServices);
			}
		});

		return services.map((service) => Text.capitalize(service));
	}

	getInitialQuery(): Object
	{
		let query = {};
		this.blockServices.forEach((block) => {
			query = Object.assign(query, block.initialQuery);
		});

		return query;
	}

	handleBatchRequestResult(response): Promise
	{
		const responseHandlersResult = [];
		this.blockServices.forEach((block) => {
			responseHandlersResult.push(block.responseHandler(response));
		});

		return Promise.all(responseHandlersResult)
			.then(() => {
				return this.setInited();
			})
			.catch((error) => {
				console.error(error);
			});
	}

	setInited(): Promise
	{
		return this.store.dispatch('sidebar/setInited', getChatId(this.dialogId));
	}
}
