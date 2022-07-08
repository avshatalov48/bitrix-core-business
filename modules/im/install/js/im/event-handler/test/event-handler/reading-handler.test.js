import {ReadingHandler} from "im.event-handler";
import {Type} from "main.core";
import {VuexBuilder} from "ui.vue.vuex";
import {ApplicationModel, MessagesModel, DialoguesModel} from "im.model";
import {RestMethod} from "im.const";

describe('Message reader', () => {
	let vuex = null;
	let store = null;
	let restClient = null;
	let $Bitrix = null;
	let sandbox = null;
	let clock = null;

	const chatId = 58;
	const dialogId = 'chat17';

	before(async () => {
		vuex = await new VuexBuilder()
			.addModel(ApplicationModel.create())
			.addModel(MessagesModel.create())
			.addModel(DialoguesModel.create())
			.build();

		store = vuex.store;
		store.state.application.dialog.chatId = chatId;
		store.state.application.dialog.dialogId = dialogId;

		restClient = {
			callMethod: () => {}
		};
		$Bitrix = {
			Data: {
				get()
				{
					return { store };
				}
			},
			RestClient: {
				get()
				{
					return restClient;
				}
			}
		};
	});

	beforeEach(() => {
		sandbox = sinon.createSandbox();
		sandbox.replace(restClient, 'callMethod', sandbox.fake.resolves());
		clock = sandbox.useFakeTimers();
	});

	afterEach(() => {
		sandbox.restore();
		clock.restore();
	});

	it('should exist', () => {
		assert.equal(Type.isFunction(ReadingHandler), true);
	});

	describe('reading process', () => {
		it('should store messages to read in queue', () => {
			const messageIds = [15, 16, 17];

			const instance = new ReadingHandler($Bitrix);
			instance.readMessage(messageIds[0]);
			instance.readMessage(messageIds[1]);
			instance.readMessage(messageIds[2]);

			assert.equal(instance.messagesToRead.length, messageIds.length);
			assert.equal(instance.messagesToRead[0], messageIds[0]);
			assert.equal(instance.messagesToRead[1], messageIds[1]);
			assert.equal(instance.messagesToRead[2], messageIds[2]);
		});

		it('should clear queue after timer expires', () => {
			const messageIds = [15, 16, 17];

			const instance = new ReadingHandler($Bitrix);
			instance.readMessage(messageIds[0]);
			instance.readMessage(messageIds[1]);
			instance.readMessage(messageIds[2]);

			clock.tick(1000);
			assert.equal(instance.messagesToRead.length, 0);
		});

		it('should call readMessages mutation for message with max ID', () => {
			const messageIds = [6, 121, 15, 16, 17, 3];
			const instance = new ReadingHandler($Bitrix);
			sandbox.spy(instance.store, 'dispatch');

			messageIds.forEach(id => {
				instance.readMessage(id);
			});

			clock.tick(1000);
			assert.equal(instance.store.dispatch.callCount, 1);
			assert.equal(instance.store.dispatch.getCall(0).args[0], 'messages/readMessages');
			assert.equal(instance.store.dispatch.getCall(0).args[1].chatId, chatId);
			assert.equal(instance.store.dispatch.getCall(0).args[1].readId, Math.max(...messageIds));
		});

		it('should set chat counter returned from read mutation', () => {
			const newCounter = 5;
			sandbox.replace(
				ReadingHandler.prototype,
				'readMessageOnClient',
				sandbox.fake.resolves({counter: newCounter})
			);

			const instance = new ReadingHandler($Bitrix);
			sandbox.spy(instance.store, 'dispatch');

			return instance.readMessageOnClient(7).then(readResult => {
				return instance.decreaseChatCounter(readResult.counter);
			}).then(() => {
				assert.equal(instance.store.dispatch.callCount, 1);
				assert.equal(instance.store.dispatch.getCall(0).args[0], 'dialogues/decreaseCounter');
				assert.equal(instance.store.dispatch.getCall(0).args[1].dialogId, dialogId);
				assert.equal(instance.store.dispatch.getCall(0).args[1].count, newCounter);
			});
		});

		it('should call im.dialog.read method for message with max ID', () => {
			const messageIds = [6, 121, 15, 16, 17, 3];
			const instance = new ReadingHandler($Bitrix);

			instance.messagesToRead = [...messageIds];
			return instance.processMessagesToRead().then(() => {
				clock.tick(2000);
				assert.equal(restClient.callMethod.callCount, 1);
				assert.equal(restClient.callMethod.getCall(0).args[0], RestMethod.imDialogRead);
				assert.equal(restClient.callMethod.getCall(0).args[1].DIALOG_ID, dialogId);
				assert.equal(restClient.callMethod.getCall(0).args[1].MESSAGE_ID, Math.max(...messageIds));
			});
		});
	});
});