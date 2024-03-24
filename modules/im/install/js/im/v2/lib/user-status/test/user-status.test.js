import { DateTimeFormat } from 'main.date';

import 'im.v2.test';
import { Core } from 'im.v2.application.core';
import { Utils } from 'im.v2.lib.utils';
import { RecentService } from 'im.v2.provider.service';
import { UsersModel, type ImModelUser } from 'im.v2.model';

import { UserStatusManager } from '../src/user-status';

let clock = null;
let sandbox = null;

describe('user status', () => {
	before(() => {
		sandbox = sinon.createSandbox();

		return Core.ready();
	});

	beforeEach(() => {
		sandbox.stub(RecentService, 'getInstance').returns({
			loadFirstPage: sandbox.stub(),
			lol: 'kek',
		});
		clock = sinon.useFakeTimers(Date.now());
	});

	afterEach(() => {
		Core.getStore().state.users.collection = {};
		Core.getStore().state.users.absentCheckInterval = null;
		clock.restore();
		sandbox.restore();
		UserStatusManager.getInstance().clear();
	});

	describe('birthday', () => {
		it('should add isBirthday flag for user if his birthday is today', () => {
			const user = addUser({
				birthday: DateTimeFormat.format('d-m', new Date()),
			});

			const modelUser: ImModelUser = Core.getStore().getters['users/get'](user.id);
			assert.equal(modelUser.isBirthday, true);
		});

		it('should remove isBirthday flag when day ends', () => {
			const user = addUser({
				birthday: DateTimeFormat.format('d-m', new Date()),
			});

			let modelUser: ImModelUser = Core.getStore().getters['users/get'](user.id);
			assert.equal(modelUser.isBirthday, true);

			const timeToMidnight = Utils.date.getTimeToNextMidnight() + 100;
			clock.tick(timeToMidnight);
			modelUser = Core.getStore().getters['users/get'](user.id);
			assert.equal(modelUser.isBirthday, false);
		});

		it('should request first page of recent list on a new day to get new birthday list', () => {
			addUser({
				birthday: DateTimeFormat.format('d-m', new Date()),
			});

			const timeToMidnight = Utils.date.getTimeToNextMidnight() + 100;
			clock.tick(timeToMidnight);

			assert.equal(RecentService.getInstance().loadFirstPage.calledOnce, true);
		});
	});

	describe('vacation', () => {
		it('should add isAbsent flag for user if he is on a vacation', () => {
			const currentDate = new Date();
			const user = addUser({
				absent: currentDate.setDate(currentDate.getDate() + 1),
			});

			const modelUser: ImModelUser = Core.getStore().getters['users/get'](user.id);
			assert.equal(modelUser.isAbsent, true);
		});

		it('should remove isAbsent flag on update if user is not on a vacation', () => {
			const currentDate = new Date();
			const user = addUser({
				absent: currentDate.setDate(currentDate.getDate() + 1),
			});

			let modelUser: ImModelUser = Core.getStore().getters['users/get'](user.id);
			assert.equal(modelUser.isAbsent, true);

			const updatedUser = addUser({
				absent: false,
			});

			modelUser = Core.getStore().getters['users/get'](updatedUser.id);
			assert.equal(modelUser.isAbsent, false);
		});

		it('should check user on the next day and remove isAbsent flag if vacation is over', () => {
			const absentFinishDate = new Date();
			absentFinishDate.setDate((new Date()).getDate() + 1);
			absentFinishDate.setHours(0, 0, 0, 0);
			const user = addUser({
				absent: absentFinishDate,
			});

			let modelUser: ImModelUser = Core.getStore().getters['users/get'](user.id);
			assert.equal(modelUser.isAbsent, true);

			const timeToMidnight = Utils.date.getTimeToNextMidnight() + 100;
			clock.tick(timeToMidnight);

			modelUser = Core.getStore().getters['users/get'](user.id);
			assert.equal(modelUser.isAbsent, false);
		});

		it('should check user every day after the next day and remove isAbsent flag if vacation is over', () => {
			const absentFinishDate = new Date();
			absentFinishDate.setDate((new Date()).getDate() + 2);
			absentFinishDate.setHours(0, 0, 0, 0);
			const user = addUser({
				absent: absentFinishDate,
			});

			let modelUser: ImModelUser = Core.getStore().getters['users/get'](user.id);
			assert.equal(modelUser.isAbsent, true);

			const timeToMidnight = Utils.date.getTimeToNextMidnight() + 100;
			const day = 1000 * 60 * 60 * 24;
			clock.tick(timeToMidnight + day);

			modelUser = Core.getStore().getters['users/get'](user.id);
			assert.equal(modelUser.isAbsent, false);
		});
	});
});

const getUser = (params): ImModelUser => {
	return { ...UsersModel.prototype.getElementState(), ...params };
};

const addUser = (params): ImModelUser => {
	const user = getUser({
		id: 1,
		...params,
	});
	Core.getStore().dispatch('users/set', user);

	return user;
};
