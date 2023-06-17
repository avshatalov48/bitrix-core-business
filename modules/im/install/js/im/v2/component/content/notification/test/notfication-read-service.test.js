import {NotificationReadService} from '../src/classes/notification-read-service';

describe('NotificationReadService', () => {
	let service;
	let clock;
	const sandbox = sinon.createSandbox();
	// TODO: mock real store getters
	const $MockBitrix = {
		Data: {
			get: () => ({
				store: {
					dispatch: sinon.stub(),
					getters: {
						'notifications/getById': sinon.stub().callsFake(id => {
							return {id: id, read: false};
						})
					}
				},
			}),
		},
		RestClient: {
			get: () => ({
				callBatch: () => {},
				callMethod: sinon.stub().returns(Promise.resolve()),
			}),
		},
	};

	beforeEach(() => {
		service = new NotificationReadService();
		clock = sinon.useFakeTimers();
	});

	afterEach(() => {
		sandbox.restore();
		clock.restore();
	});

	describe('addToReadQueue', () => {
		it('should add to read queue if notificationIds is a filled array', () => {
			const notificationIds = [111, 222];

			service.addToReadQueue(notificationIds);

			assert.ok(service.itemsToRead.has(111, 222));
		});

		it('should not add to read queue if notificationIds is not a filled array', () => {
			const notificationIds = [];

			service.addToReadQueue(notificationIds);

			assert.ok(service.itemsToRead.size === 0);
		});

		it('should not add to read queue if notificationIds contains non-numbers', () => {
			const notificationIds = [1, '2', 3];

			service.addToReadQueue(notificationIds);

			assert.ok(service.itemsToRead.size === 2);
		});

		it('should not add to read queue if notification is already read', () => {
			service.store.getters = {
				'notifications/getById': sinon.stub().returns({read: true})
			};

			service.addToReadQueue([1, 2, 3]);

			assert.ok(service.itemsToRead.size === 0);
		});
	});

	describe('readRequest', () => {
		it('should make an API call to mark the notifications as read', () => {
			service.addToReadQueue([22, 1, 21]);

			service.readRequest();

			assert.ok(service.restClient.callMethod.calledOnce);
			assert.deepEqual(service.restClient.callMethod.firstCall.args[1], {id: 1});
		});

		it('should clear the itemsToRead after making the API call', () => {
			service.addToReadQueue([1, 2, 33]);

			service.readRequest();

			assert.ok(service.itemsToRead.size === 0);
		});
	});

	describe('readAll', () => {
		it('should make an API call to mark all notifications as read', () => {
			service.readAll();

			assert.ok(service.restClient.callMethod.calledOnce);
			assert.deepEqual(service.restClient.callMethod.firstCall.args[1], {id: 0});
		});

		it('should dispatch the readAll action to the store', () => {
			service.store.dispatch = sinon.stub();

			service.readAll();

			assert.ok(service.store.dispatch.calledOnce);
			assert.deepEqual(service.store.dispatch.firstCall.args, ['notifications/readAll']);
		});
	});

	describe('changeReadStatus', () => {
		it('should call notifications/read with correct arguments', () => {
			service.changeReadStatus(1);

			assert.ok(service.store.dispatch.calledWith('notifications/read', {ids: [1], read: true}));
		});

		it('changeReadStatus should call restClient.callMethod', async () => {
			service.changeReadStatus(1);

			clock.next();

			assert(service.restClient.callMethod.calledOnce);
		});
	});
});
