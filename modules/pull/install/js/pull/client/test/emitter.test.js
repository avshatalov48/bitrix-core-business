import { Emitter } from '../src/emitter';
import { isFunction } from 'pull.util';
import { SenderType } from '../src/consts';

import * as _ from 'lodash';

const BXEventSender = {
	onCustomEvent(target: any, eventName: string, eventParams: any) {},
};

describe('Emitter', () => {
	describe('attachCommandHandler', () => {
		const emitter = new Emitter();

		it('throws exception when handler does not contain moduleId', () => {
			assert.throws(() => emitter.attachCommandHandler({}), {
				name: 'TypeError',
			});
		});

		it('throws exception if getSubscriptionType returns invalid value', () => {
			const handler = {
				getModuleId: () => 'test',
				getSubscriptionType: () => 'test',
			};
			assert.throws(() => emitter.attachCommandHandler(handler), {
				name: 'Error',
			});
		});

		it('returns callback to unsubscribe', () => {
			const handler = {
				getModuleId: () => 'test',
			};
			const cb = emitter.attachCommandHandler(handler);
			assert(isFunction(cb), 'callback must be a function');
			cb();
		});
	});

	describe('subscribe', () => {
		const emitter = new Emitter();

		it('throws exception when params is not an object', () => {
			assert.throws(() => emitter.subscribe(1), {
				name: 'TypeError',
			});
		});
	});

	describe('broadcastMessage', () => {
		const emitter = new Emitter();
		const moduleId = 'test_module';
		const command = 'test_command';
		const body = { a: 'b' };

		const eventParamsMatcher = ([gotCommand, gotBody]) => gotCommand === command && _.isEqual(gotBody, body);
		const eventModuleAndParamsMatcher = ([gotModuleId, gotCommand, gotBody]) => gotModuleId === moduleId && gotCommand === command && _.isEqual(gotBody, body);

		it('emits global server event', () => {
			global.BX = BXEventSender;

			const mockBX = sinon.mock(BXEventSender);

			mockBX.expects('onCustomEvent').withArgs(window, 'onPullEvent-test_module', sinon.match(eventParamsMatcher));
			mockBX.expects('onCustomEvent').withArgs(window, 'onPullEvent', sinon.match(eventModuleAndParamsMatcher));

			emitter.broadcastMessage({
				command,
				module_id: moduleId,
				params: body,
				extra: {
					sender: {
						type: SenderType.Backend,
					},
				},
			});

			mockBX.verify();
		});

		it('emits global client event', () => {
			global.BX = BXEventSender;
			const extra = {
				sender: {
					type: SenderType.Client,
				},
			};

			const mockBX = sinon.mock(BXEventSender);

			mockBX.expects('onCustomEvent').withArgs(sinon.match.any, 'onPullClientEvent-test_module', sinon.match(eventParamsMatcher));
			mockBX.expects('onCustomEvent').withArgs(sinon.match.any, 'onPullClientEvent', sinon.match(eventModuleAndParamsMatcher));

			emitter.broadcastMessage({
				command,
				module_id: moduleId,
				params: body,
				extra,
			});

			mockBX.verify();
		});
	});
});
