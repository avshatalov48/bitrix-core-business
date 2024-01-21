import 'im.v2.test';
import {AvailabilityManager} from '../src/classes/availability-manager';
import {ChatType} from 'im.v2.const';

describe('AvailabilityManager', () => {
	describe('getAvailablePlacements', () => {
		const manager = new AvailabilityManager();

		it('should return all placements if we don\'t provide dialogType', () => {
			const placement = {
				id: 123,
				title: 'test',
				options: {},
			};

			const placements = [{...placement}, {...placement}, {...placement}];
			const result = manager.getAvailablePlacements(placements);

			assert.equal(result.length, 3);
			assert.deepEqual(result, placements);
		});

		it('should return only the placements that can be shown in the group chat', () => {
			const dialogType = ChatType.chat;

			const placements = [
				{options: {context: ['USER']}}, // should be excluded
				{options: {context: ['CHAT']}},
				{options: {context: ['LINES']}}, // should be excluded
				{options: {context: ['CRM']}}, // should be excluded
				{options: {context: ['ALL']}},
			];

			const result = manager.getAvailablePlacements(placements, dialogType);

			assert.equal(result.length, 2);
			assert.deepEqual(result, [placements[1], placements[4]]);
		});

		it('should return only the placements that can be shown in the user chat (1-to-1)', () => {
			const dialogType = ChatType.user;

			const placements = [
				{options: {context: ['USER']}},
				{options: {context: ['CHAT']}}, // should be excluded
				{options: {context: ['LINES']}}, // should be excluded
				{options: {context: ['CRM']}}, // should be excluded
				{options: {context: ['ALL']}},
			];

			const result = manager.getAvailablePlacements(placements, dialogType);

			assert.equal(result.length, 2);
			assert.deepEqual(result, [placements[0], placements[4]]);
		});
	});
});
