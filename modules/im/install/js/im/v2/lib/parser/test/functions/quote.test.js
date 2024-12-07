import 'im.v2.test';
import { Tag } from 'main.core';

import { ParserQuote } from '../../src/functions/quote';

const QUOTE_NAME = 'Ivan';
const QUOTE_DATE = 'today, 12:41';
const QUOTE_TEXT = 'some text';
const QUOTE_DATA_CONTEXT = 'chat786/8002';

describe('ParserQuote', () => {
	describe('decodeQuote', () => {
		it('should return a message with a filled context tag if the quote is from the same chat', () => {
			const quoteText = getQuoteText(`${QUOTE_NAME} [${QUOTE_DATE}] #${QUOTE_DATA_CONTEXT}<br />${QUOTE_TEXT}`);
			const contextDialogId = 'chat786';

			const result = ParserQuote.decodeQuote(quoteText, { contextDialogId });

			const quoteParams = {
				name: QUOTE_NAME,
				date: QUOTE_DATE,
				text: QUOTE_TEXT,
				dataContext: QUOTE_DATA_CONTEXT,
			};

			const expectedResult = getMessageHTML(quoteParams);
			assert.equal(result, expectedResult);
		});

		it('should return a message with an empty context tag if the quote is not from the same chat', () => {
			const contextDialogId = 'chat123';
			const expectedContextTag = 'none';
			const quoteText = getQuoteText(`${QUOTE_NAME} [${QUOTE_DATE}] #${QUOTE_DATA_CONTEXT}<br />${QUOTE_TEXT}`);

			const result = ParserQuote.decodeQuote(quoteText, { contextDialogId });

			const quoteParams = {
				name: QUOTE_NAME,
				date: QUOTE_DATE,
				text: QUOTE_TEXT,
				dataContext: expectedContextTag,
			};
			const expectedResult = getMessageHTML(quoteParams);

			assert.equal(result, expectedResult);
		});

		it('should return the same message if there is no quote', () => {
			const contextDialogId = 'chat123';
			const messageText = 'hello';

			const result = ParserQuote.decodeQuote(messageText, { contextDialogId });

			assert.equal(result, messageText);
		});

		it('should return a quote text, if there is only some string in square brackets', () => {
			const contextDialogId = 'chat123';
			const expectedContextTag = 'none';
			const quoteText = getQuoteText('[some text]');

			const result = ParserQuote.decodeQuote(quoteText, { contextDialogId });

			const quoteParams = {
				text: 'some text',
				dataContext: expectedContextTag,
			};
			const expectedResult = getMessageHTML(quoteParams);

			assert.equal(result, expectedResult);
		});

		it('should return a message with line break if the quote contains line break', () => {
			const contextDialogId = 'chat123';
			const expectedContextTag = 'none';
			const QUOTE_TEXT_WITH_BR = 'some <br /> text';
			const quoteText = getQuoteText(`${QUOTE_NAME} [${QUOTE_DATE}] #${QUOTE_DATA_CONTEXT}<br />${QUOTE_TEXT_WITH_BR}`);

			const result = ParserQuote.decodeQuote(quoteText, { contextDialogId });

			const quoteParams = {
				name: QUOTE_NAME,
				date: QUOTE_DATE,
				text: QUOTE_TEXT_WITH_BR,
				dataContext: expectedContextTag,
			};
			const expectedResult = getMessageHTML(quoteParams);

			assert.equal(result, expectedResult);
		});

		it('should return a message without line break if the quote contains line break at the end of the text', () => {
			const contextDialogId = 'chat123';
			const expectedContextTag = 'none';
			const QUOTE_TEXT_WITH_BR = 'some text<br />';
			const quoteText = getQuoteText(`${QUOTE_NAME} [${QUOTE_DATE}] #${QUOTE_DATA_CONTEXT}<br />${QUOTE_TEXT_WITH_BR}`);

			const result = ParserQuote.decodeQuote(quoteText, { contextDialogId });

			const quoteParams = {
				name: QUOTE_NAME,
				date: QUOTE_DATE,
				text: QUOTE_TEXT_WITH_BR,
				dataContext: expectedContextTag,
			};
			const expectedResult = getMessageHTML(quoteParams);

			assert.equal(result, expectedResult);
		});
	});

	describe('decodeArrowQuote', () => {
		it('should return message without chat context in data attribute', () => {
			const quoteText = '&gt;&gt;test';
			const expectedResult = '<div data-context="none" class="bx-im-message-quote --inline"><div class="bx-im-message-quote__wrap">test</div></div>';

			const result = ParserQuote.decodeArrowQuote(quoteText);

			assert.equal(result, expectedResult);
		});
	});
});

const getQuoteText = (content: string) => {
	const delimiter = '------------------------------------------------------';
	const br = '<br />';

	return `${delimiter}${br}${content}${br}${delimiter}`;
};

type MessageParams = {
	name?: string,
	date?: string,
	text: string,
	dataContext: string,
	hasUserBlock?: boolean,
};

const getMessageHTML = (messageParams: MessageParams) => {
	const { name = '', date = '', dataContext, text } = messageParams;

	let userBlock = '';
	if (name && date)
	{
		userBlock = Tag.render`
			<div class="bx-im-message-quote__name">
				<div class="bx-im-message-quote__name-text">${name}</div>
				<div class="bx-im-message-quote__name-time">${date}</div>
			</div>
		`;
	}

	const messageNode = Tag.render`
		<div class="bx-im-message-quote" data-context="${dataContext}">
			<div class="bx-im-message-quote__wrap">
				${userBlock}
				<div class="bx-im-message-quote__text">${text}</div>
			</div>
		</div>
	`;

	return messageNode.outerHTML;
};
