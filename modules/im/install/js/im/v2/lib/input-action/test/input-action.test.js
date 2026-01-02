import 'im.v2.test';
import { Core } from 'im.v2.application.core';

import { InputActionListener, InputAction } from '../src/input-action';

let clock = null;
let sandbox = null;

describe('InputActionListener', () => {
	before(() => {
		sandbox = sinon.createSandbox();

		return Core.ready();
	});

	beforeEach(() => {
		sandbox.spy(Core.getStore(), 'dispatch');
		sandbox.stub();
		clock = sinon.useFakeTimers(Date.now());
	});

	afterEach(() => {
		Core.getStore().state.chats.inputActions.collection = {};
		clock.restore();
		sandbox.restore();
		InputActionListener.getInstance().clear();
	});

	it('should call model start action', () => {
		const payload = {
			type: InputAction.writing,
			dialogId: '1',
			userId: 1,
			userName: 'userName',
		};

		InputActionListener.getInstance().startAction(payload);

		sinon.assert.calledOnce(Core.getStore().dispatch);
		sinon.assert.calledWith(Core.getStore().dispatch, 'chats/inputActions/start', payload);
	});

	it('should call model stop action after timeout', async () => {
		const payload = {
			type: InputAction.writing,
			dialogId: '1',
			userId: 1,
			userName: 'userName',
		};

		InputActionListener.getInstance().startAction(payload);

		await clock.next();

		sinon.assert.calledTwice(Core.getStore().dispatch);
		const dispatchCall = Core.getStore().dispatch.getCall(1);
		sinon.assert.calledWith(dispatchCall, 'chats/inputActions/stop', payload);
	});

	it('should not call model start action if it is started already', () => {
		const payload = {
			type: InputAction.writing,
			dialogId: '1',
			userId: 1,
			userName: 'userName',
		};

		InputActionListener.getInstance().startAction(payload);
		sinon.assert.calledOnce(Core.getStore().dispatch);

		InputActionListener.getInstance().startAction(payload);
		sinon.assert.calledOnce(Core.getStore().dispatch);
	});
});
