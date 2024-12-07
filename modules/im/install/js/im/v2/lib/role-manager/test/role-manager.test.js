import 'im.v2.test';
import { UserRole } from 'im.v2.const';
import { getChatRoleForUser } from 'im.v2.lib.role-manager';
import { Core } from 'im.v2.application.core';

const FAKE_CURRENT_USER_ID = 111;

describe('Role Manager', () => {
	// eslint-disable-next-line init-declarations
	let getUserIdStub;

	beforeEach(() => {
		getUserIdStub = sinon.stub(Core, 'getUserId');
		getUserIdStub.returns(FAKE_CURRENT_USER_ID);
	});

	afterEach(() => {
		getUserIdStub.restore();
	});

	describe('P&P format', () => {
		it('Should return correct role type for owner', async () => {
			const chatConfig = {
				owner: FAKE_CURRENT_USER_ID,
				manager_list: [1, 2, 3],
			};

			const result = getChatRoleForUser(chatConfig);

			assert.deepEqual(result, UserRole.owner);
		});

		it('Should return correct role type for manager', async () => {
			const chatConfig = {
				owner: 1,
				manager_list: [FAKE_CURRENT_USER_ID, 2, 3],
			};

			const result = getChatRoleForUser(chatConfig);

			assert.deepEqual(result, UserRole.manager);
		});

		it('Should return correct role type for member', async () => {
			const chatConfig = {
				owner: 1,
				manager_list: [1, 2, 3],
			};

			const result = getChatRoleForUser(chatConfig);

			assert.deepEqual(result, UserRole.member);
		});
	});

	describe('REST format', () => {
		it('Should return correct role type for owner', async () => {
			const chatConfig = {
				ownerId: FAKE_CURRENT_USER_ID,
				managers: [1, 2, 3],
			};

			const result = getChatRoleForUser(chatConfig);

			assert.deepEqual(result, UserRole.owner);
		});

		it('Should return correct role type for manager', async () => {
			const chatConfig = {
				ownerId: 1,
				managers: [FAKE_CURRENT_USER_ID, 2, 3],
			};

			const result = getChatRoleForUser(chatConfig);

			assert.deepEqual(result, UserRole.manager);
		});

		it('Should return correct role type for member', async () => {
			const chatConfig = {
				ownerId: 1,
				managers: [1, 2, 3],
			};

			const result = getChatRoleForUser(chatConfig);

			assert.deepEqual(result, UserRole.member);
		});
	});
});
