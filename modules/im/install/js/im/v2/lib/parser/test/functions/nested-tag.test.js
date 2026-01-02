import 'im.v2.test';
import { Tag, Text } from 'main.core';

import { Parser } from '../../src/parser';

describe('NestedTagHandler', () => {
	describe('createCommandFromSendBBCode', () => {
		it('should create valid command with common input data', () => {
			const command = '123abc';
			const commandData = '456XYZ';
			const text = `[SEND=${commandData}]${command}[/SEND]`;

			const actualResult = Parser.decode({ text });
			const expectedResult = getSendBbCodeMessageHTML(command, commandData);

			assert.equal(actualResult, expectedResult);
		});

		it('should create valid command with $" in input data', () => {
			const command = '$"';
			const commandData = '$"';
			const text = `[SEND=${commandData}]${command}[/SEND]`;

			const actualResult = Parser.decode({ text });
			const expectedResult = getSendBbCodeMessageHTML(command, commandData);

			assert.equal(actualResult, expectedResult);
		});

		it('should create valid command with special characters in input data', () => {
			const command = '&$*\'<>()!@#%^/_+-=';
			const commandData = '&$*\'<>()!@#%^/_+-=';
			const text = `[SEND=${commandData}]${command}[/SEND]`;

			const actualResult = Parser.decode({ text });
			const expectedResult = getSendBbCodeMessageHTML(command, commandData);

			assert.equal(actualResult, expectedResult);
		});

		it('should ignore empty input data', () => {
			const command = '';
			const commandData = '';
			const text = `[SEND=${commandData}]${command}[/SEND]`;

			const actualResult = Parser.decode({ text });
			const expectedResult = '[SEND=][/SEND]';

			assert.equal(actualResult, expectedResult);
		});
	});
});

const getSendBbCodeMessageHTML = (command: string, commandData: string): string => {
	const messageNode = Tag.render`
		<span class="bx-im-message-command-wrap">
			<span class="bx-im-message-command" data-entity="send">
				${Text.encode(command)}
			</span>
			<span class="bx-im-message-command-data">
				${Text.encode(commandData)}
			</span>
		</span>
	`;

	return messageNode.outerHTML;
};
