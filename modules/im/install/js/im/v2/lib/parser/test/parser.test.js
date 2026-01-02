/* eslint-disable prefer-template */
/* eslint-disable no-console */
import 'im.v2.test';
import { Tag } from 'main.core';
import * as AllConst from 'im.v2.const';
import * as CoreProxy from '../src/utils/core-proxy';
import { Parser } from '../src/parser';

let getBigSmileOptionStub = null;
let constStub = null;

beforeEach(() => {
	getBigSmileOptionStub = sinon.stub(CoreProxy, 'getBigSmileOption').returns(true);
	constStub = sinon.stub(CoreProxy, 'getConst').returns(AllConst);
});

afterEach(() => {
	getBigSmileOptionStub.restore();
	constStub.restore();
});

describe('Performance', () => {
	// Normal time is ~1-2ms on Mac M3 Pro. It was about 200ms before optimization.
	it('should not hang on text with 2000 square brackets', () => {
		const difficultText = '['.repeat(1000) + ' some text ' + ']'.repeat(1000);
		console.time('Parser.decode with 2000 brackets');
		const result = Parser.decode({ text: difficultText });
		console.timeEnd('Parser.decode with 2000 brackets');

		assert.ok(result.includes('some text'));
	});
});

describe('Nested Tags Handling', () => {
	/*
		[code]
			[send=[b]test[/b]]Test![/send]
			[put=[b]test[/b]]Test![/put]
		[/code]
	*/
	it('should not parse [send] and [put] tags inside [code] block', () => {
		const text = '[code][send=[b]test[/b]]Test![/send][put=[b]test[/b]]Test![/put][/code]';

		const expected = Tag.render`
			<div class="bx-im-message-content-code">
				[send=[b]test[/b]]Test![/send]
				[put=[b]test[/b]]Test![/put]
			</div>
		`.outerHTML;

		const actual = Parser.decode({ text });

		assert.equal(actual, expected);
	});

	it('should not parse bb codes inside [send] params', () => {
		const text = '[send=[b]test[/b]]Test![/send]';

		const expected = Tag.render`
			<span class="bx-im-message-command-wrap">
				<span class="bx-im-message-command" data-entity="send">Test!</span>
				<span class="bx-im-message-command-data">[b]test[/b]</span>
			</span>
		`.outerHTML;

		const actual = Parser.decode({ text });

		assert.equal(actual, expected);
	});

	it('should not parse bb codes inside [put] params', () => {
		const text = '[put=[b]test[/b]]Test![/put]';
		const expected = Tag.render`
			<span class="bx-im-message-command-wrap">
				<span class="bx-im-message-command" data-entity="put">Test!</span>
				<span class="bx-im-message-command-data">[b]test[/b]</span>
			</span>
		`.outerHTML;

		const actual = Parser.decode({ text });

		assert.equal(actual, expected);
	});
});
