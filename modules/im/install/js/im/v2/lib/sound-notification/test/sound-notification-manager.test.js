import 'im.v2.test';
import { SoundNotificationManager } from 'im.v2.lib.sound-notification';
import { SoundType, Settings, UserStatus } from 'im.v2.const';

BX.localStorage = {
	set: () => {},
};
global.BX = BX;
global.window.BX = BX;

describe('SoundNotificationManager', () => {
	let sandbox = null;
	let soundManager = null;

	beforeEach(() => {
		sandbox = sinon.createSandbox();
		const callManagerMock = {
			hasCurrentCall: sandbox.stub(),
		};
		const desktopManagerMock = {
			isDesktopActive: sandbox.stub().returns(false),
		};
		const storeMock = {
			getters: {
				'application/settings/get': sandbox.stub(),
			},
		};
		storeMock.getters['application/settings/get'].withArgs(Settings.user.status).returns(UserStatus.online);
		storeMock.getters['application/settings/get'].withArgs(Settings.notification.enableSound).returns(true);

		const soundPlayerMock = {
			playSingle: sandbox.stub(),
			playLoop: sandbox.stub(),
			stop: sandbox.stub(),
		};
		soundManager = new SoundNotificationManager(
			storeMock,
			desktopManagerMock,
			callManagerMock,
			soundPlayerMock,
		);
	});

	afterEach(() => {
		sandbox.restore();
	});

	describe('playOnce()', () => {
		it('should not play when there is an active call', () => {
			soundManager.callManager.hasCurrentCall.returns(true);

			soundManager.playOnce(SoundType.newMessage1);

			assert.equal(soundManager.soundPlayer.playSingle.calledOnce, false);
		});

		it('should play sound if no active call', () => {
			soundManager.callManager.hasCurrentCall.returns(false);

			soundManager.playOnce(SoundType.newMessage1);

			assert.equal(soundManager.soundPlayer.playSingle.calledOnce, true);
		});

		it('should not play sound if user is in DND mode', () => {
			soundManager.store.getters['application/settings/get'].withArgs(Settings.user.status).returns(UserStatus.dnd);

			soundManager.playOnce(SoundType.newMessage1);

			assert.equal(soundManager.soundPlayer.playSingle.calledOnce, false);
		});

		it('should not play sound if sound is disabled', () => {
			soundManager.store.getters['application/settings/get'].withArgs(Settings.notification.enableSound).returns(false);

			soundManager.playOnce(SoundType.newMessage1);

			assert.equal(soundManager.soundPlayer.playSingle.calledOnce, false);
		});

		it('should call playSingle method with provided sound type', () => {
			soundManager.playOnce(SoundType.start);

			assert.equal(soundManager.soundPlayer.playSingle.calledWithExactly(SoundType.start), true);
		});
	});

	describe('forcePlayOnce()', () => {
		it('should play even there is an active call', () => {
			soundManager.callManager.hasCurrentCall.returns(true);

			soundManager.forcePlayOnce(SoundType.newMessage1);

			assert.equal(soundManager.soundPlayer.playSingle.calledOnce, true);
		});

		it('should play sound if no active call', () => {
			soundManager.callManager.hasCurrentCall.returns(false);

			soundManager.forcePlayOnce(SoundType.newMessage1);

			assert.equal(soundManager.soundPlayer.playSingle.calledOnce, true);
		});

		it('should play sound if user is in DND mode', () => {
			soundManager.store.getters['application/settings/get'].withArgs(Settings.user.status).returns(UserStatus.dnd);

			soundManager.forcePlayOnce(SoundType.newMessage1);

			assert.equal(soundManager.soundPlayer.playSingle.calledOnce, true);
		});

		it('should not play sound event if sound is disabled', () => {
			soundManager.store.getters['application/settings/get'].withArgs(Settings.notification.enableSound).returns(false);

			soundManager.forcePlayOnce(SoundType.newMessage1);

			assert.equal(soundManager.soundPlayer.playSingle.calledOnce, false);
		});
	});

	describe('playLoop()', () => {
		it('should not play when there is an active call', () => {
			soundManager.callManager.hasCurrentCall.returns(true);

			soundManager.playLoop(SoundType.start);

			assert.equal(soundManager.soundPlayer.playLoop.calledOnce, false);
		});

		it('should play sound if no active call', () => {
			soundManager.callManager.hasCurrentCall.returns(false);

			soundManager.playLoop(SoundType.start);

			assert.equal(soundManager.soundPlayer.playLoop.calledOnce, true);
		});

		it('should not play sound if user is in DND mode', () => {
			soundManager.store.getters['application/settings/get'].withArgs(Settings.user.status).returns(UserStatus.dnd);

			soundManager.playLoop(SoundType.start);

			assert.equal(soundManager.soundPlayer.playLoop.calledOnce, false);
		});

		it('should not play sound if sound is disabled', () => {
			soundManager.store.getters['application/settings/get'].withArgs(Settings.notification.enableSound).returns(false);

			soundManager.playLoop(SoundType.start);

			assert.equal(soundManager.soundPlayer.playLoop.calledOnce, false);
		});
	});
	describe('stop()', () => {
		it('should be called with passed sound type', () => {
			soundManager.playLoop(SoundType.start);
			soundManager.stop(SoundType.start);

			assert.equal(soundManager.soundPlayer.stop.calledWithExactly(SoundType.start), true);
		});
	});
});
