import {SidebarService} from '../src/classes/sidebar-service';
import {MockRestResponse} from './mock-rest-response';
import {RestMethod} from 'im.v2.const';
import {Type} from 'main.core';

const BLOCKS_FOR_TEST = ['main', 'task', 'meeting'];
const CHAT_ID = 123;
const DIALOG_ID = 'chat123';
const REQUEST_ITEMS_LIMIT = 50;

describe('SidebarService', () => {
	let sidebarService;
	const sandbox = sinon.createSandbox();

	beforeEach(() => {
		const getBlocksStub = sinon.stub();
		getBlocksStub.returns(BLOCKS_FOR_TEST);

		class MockAvailabilityManager {
			// eslint-disable-next-line bitrix-rules/no-short-class-property
			getBlocks = getBlocksStub;
		}

		sidebarService = new SidebarService(new MockAvailabilityManager());
		sidebarService.setChatId(CHAT_ID);
		sidebarService.setDialogId(DIALOG_ID);
		sidebarService.buildBlocks();

		sandbox.spy(sidebarService, 'setInited');
	});

	afterEach(() => {
		sandbox.restore();
	});

	describe('getBlockInstance', () => {
		it('should return the block instance for the specified block', () => {
			assert.strictEqual(sidebarService.getBlockInstance('main'), sidebarService.blockServices[0].blockManager);
		});

		it('should return undefined for an unknown block', () => {
			const blockInstance = sidebarService.getBlockInstance('some_unknown_block');
			assert.strictEqual(Type.isNil(blockInstance), true);
		});
	});

	describe('buildBlocks', () => {
		it('should return correct length if blockServices array', () => {
			assert.strictEqual(sidebarService.blockServices.length, BLOCKS_FOR_TEST.length);
		});
		it('should create a block service for each block in the availability manager', () => {
			assert.deepStrictEqual(sidebarService.blockServices.map((block) => block.type), BLOCKS_FOR_TEST);
		});
	});

	describe('getServiceClassesForBlocks', () => {
		it('should return the service classes for each block in the availability manager', () => {
			assert.deepStrictEqual(sidebarService.getServiceClassesForBlocks(), ['Main', 'Task', 'Meeting']);
		});
	});

	describe('getInitialRequestQuery', () => {
		it('should return the initial request query for all block services', () => {
			assert.deepStrictEqual(sidebarService.getInitialRequestQuery(), {
				[RestMethod.imDialogUsersList]: [RestMethod.imDialogUsersList, {dialog_id: DIALOG_ID, limit: REQUEST_ITEMS_LIMIT}],
				[RestMethod.imChatTaskGet]: [RestMethod.imChatTaskGet, {chat_id: CHAT_ID, limit: REQUEST_ITEMS_LIMIT}],
				[RestMethod.imChatCalendarGet]: [RestMethod.imChatCalendarGet, {chat_id: CHAT_ID, limit: REQUEST_ITEMS_LIMIT}],
			});
		});
	});

	describe('handleBatchRequestResult', () => {
		it('should handle the batch request result and set chat as inited', () => {
			const batchResult = {
				[RestMethod.imDialogUsersList]: {
					data: () => {
						return MockRestResponse.users;
					},
					error: () => null
				},
				[RestMethod.imChatTaskGet]: {
					data: () => {
						return {
							'users': MockRestResponse.users,
							'list': MockRestResponse.tasksList
						};
					},
					error: () => null
				},
				[RestMethod.imChatCalendarGet]: {
					data: () => {
						return {
							'users': MockRestResponse.users,
							'list': MockRestResponse.meetingsList
						};
					},
					error: () => null
				},
			};
			sidebarService.handleBatchRequestResult(batchResult).then(() => {
				assert(sidebarService.setInited.calledOnce);
			});
		});
	});
});