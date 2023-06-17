import {RestMethod} from 'im.v2.const';
import {FileUnsorted} from '../../src/classes/blocks/file-unsorted';

const REQUEST_ITEMS_LIMIT = 50;

describe('FileUnsorted', () => {
	let fileUnsorted;
	let mockRestClient;
	let mockUserManager;

	beforeEach(() => {
		mockRestClient = {
			callMethod: () => Promise.resolve({data: () => ({})}),
		};
		mockUserManager = {
			addUsers: () => {},
		};

		const $MockBitrix = {
			Data: {
				get: () => ({
					store: {
						dispatch: () => {
							return Promise.resolve([]);
						}
					},
				}),
			},
			RestClient: {
				get: () => ({
					callBatch: () => {},
				}),
			},
		};
		fileUnsorted = new FileUnsorted($MockBitrix, 1, '1');
	});

	describe('getInitialRequest', () => {
		it('should return the expected initial request object', () => {
			const initialRequest = fileUnsorted.getInitialRequest();
			assert.deepEqual(initialRequest, {
				[RestMethod.imDiskFolderListGet]: [RestMethod.imDiskFolderListGet, {chat_id: 1, limit: REQUEST_ITEMS_LIMIT}],
			});
		});
	});

	describe('getResponseHandler', () => {
		it('should return a function that rejects the promise if no response is provided', () => {
			const responseHandler = fileUnsorted.getResponseHandler();
			return responseHandler().catch(error => {
				assert.equal(error.message, 'SidebarInfo service error: no response');
			});
		});

		it('should return a function that rejects the promise if there is a request error', () => {
			const responseHandler = fileUnsorted.getResponseHandler();
			const response = {
				[RestMethod.imDiskFolderListGet]: {error: () => 'some error'}
			};
			return responseHandler(response).catch(error => {
				assert.equal(error.message, `SidebarInfo service error: ${RestMethod.imDiskFolderListGet}: some error`);
			});
		});

		it('should return a function that resolves the promise if there is no error', () => {
			fileUnsorted.extractLoadFileError = () => null;
			fileUnsorted.updateModels = () => Promise.resolve();
			const responseHandler = fileUnsorted.getResponseHandler();
			const response = {[RestMethod.imDiskFolderListGet]: {data: () => ({})}};
			return responseHandler(response).then(result => {
				assert.equal(result, undefined);
			});
		});
	});

	describe('extractLoadFileError', () => {
		it('should return null if the response does not contain an error', () => {
			const response = {
				[RestMethod.imDiskFolderListGet]: {
					error: () => null
				}
			};

			const errorMessage = fileUnsorted.extractLoadFileError(response);
			assert.equal(errorMessage, null);
		});

		it('should return a null if the response does not contain a result for imDiskFolderListGet', () => {
			const response = {};

			const errorMessage = fileUnsorted.extractLoadFileError(response);

			assert.equal(errorMessage, null);
		});
	});
});