import { EventEmitter } from 'main.core.events';

import { EventType } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';

type MentionTextToInsert = string;
type MentionReplacementMap = {[textToReplace: string]: MentionTextToInsert};

const MentionSymbols: Set<string> = new Set(['@', '+']);
const WAIT_FOR_NEXT_SYMBOL_TIME = 10;
const WAIT_FOR_LAST_SYMBOL_TIME = 10;

export const MentionManagerEvents = Object.freeze({
	showMentionPopup: 'showMentionPopup',
	hideMentionPopup: 'hideMentionPopup',
	insertMention: 'insertMention',
});

export class MentionManager extends EventEmitter
{
	#mentionPopupOpened: boolean = false;
	#mentionSymbol: string = '';
	#textarea: HTMLTextAreaElement;

	#mentionReplacementMap: MentionReplacementMap = {};

	static eventNamespace = 'BX.Messenger.v2.Textarea.MentionManager';

	constructor(textarea: HTMLTextAreaElement)
	{
		super();
		this.setEventNamespace(MentionManager.eventNamespace);
		this.#textarea = textarea;
	}

	// region 'popup'
	onActiveMentionKeyDown(event): void
	{
		if (!this.#mentionPopupOpened)
		{
			return;
		}

		this.#onOpenedMentionKeyDown(event);
	}

	onKeyDown(event): void
	{
		this.#onClosedMentionKeyDown(event);
	}

	onMentionPopupClose()
	{
		this.#mentionPopupOpened = false;
	}

	#onClosedMentionKeyDown(event: KeyboardEvent)
	{
		if (!this.#isOpenMentionCombination(event))
		{
			return;
		}

		setTimeout((): void => {
			if (!this.#checkMentionSymbol())
			{
				return;
			}

			this.#mentionPopupOpened = true;
			this.emit(MentionManagerEvents.showMentionPopup, {
				mentionQuery: '',
			});
		}, WAIT_FOR_NEXT_SYMBOL_TIME);
	}

	#onOpenedMentionKeyDown(event: KeyboardEvent)
	{
		if (this.#isCloseMentionCombination(event))
		{
			this.#sendHidePopupEvent();

			return;
		}

		if (this.#isNavigateCombination(event))
		{
			event.preventDefault();

			return;
		}

		if (this.#isInsertMentionCombination(event))
		{
			event.preventDefault();
			this.#sendInsertMentionEvent(event);

			return;
		}

		setTimeout((): void => {
			if (!this.#isValidQuery())
			{
				this.#sendHidePopupEvent();

				return;
			}

			this.emit(MentionManagerEvents.showMentionPopup, {
				mentionQuery: this.#getQueryWithoutMentionSymbol(),
			});
		}, WAIT_FOR_LAST_SYMBOL_TIME);
	}

	#checkMentionSymbol(): boolean
	{
		const cursorPosition = this.#textarea.selectionEnd;
		this.#mentionSymbol = this.#textarea.value.slice(cursorPosition - 1, cursorPosition);
		if (!MentionSymbols.has(this.#mentionSymbol))
		{
			return false;
		}

		const symbolBeforeMentionSymbol = this.#textarea.value.slice(cursorPosition - 2, cursorPosition - 1);

		return symbolBeforeMentionSymbol.length === 0 || this.#hasWhitespace(symbolBeforeMentionSymbol);
	}

	#isValidQuery(): boolean
	{
		const query = this.#getQuery();
		if (query.length === 0)
		{
			return false;
		}

		const firstQuerySymbol = this.#getQueryWithoutMentionSymbol().slice(0, 1);
		if (firstQuerySymbol.length === 0)
		{
			return true;
		}

		if (!this.#isValidFirstSymbol(firstQuerySymbol))
		{
			return false;
		}

		return !this.#hasWhitespace(firstQuerySymbol);
	}

	#isCloseMentionCombination(event: KeyboardEvent): boolean
	{
		return event.key === 'Escape';
	}

	#isInsertMentionCombination(event: KeyboardEvent): boolean
	{
		return event.key === 'Enter';
	}

	#isOpenMentionCombination(event: KeyboardEvent): boolean
	{
		return event.key === '+' || event.key === '@';
	}

	#sendHidePopupEvent()
	{
		this.#mentionPopupOpened = false;
		this.#mentionSymbol = '';

		this.emit(MentionManagerEvents.hideMentionPopup);
	}

	#sendInsertMentionEvent(event)
	{
		event.preventDefault();
		EventEmitter.emit(EventType.mention.selectItem);
		this.#sendHidePopupEvent();
	}

	#getTextBeforeCursor(): string
	{
		return this.#textarea.value.slice(0, Math.max(0, this.#textarea.selectionEnd));
	}

	#getMentionSymbolIndex(): number
	{
		const textBeforeCursor = this.#getTextBeforeCursor();

		return textBeforeCursor.lastIndexOf(this.#mentionSymbol);
	}

	#getQuery(): string
	{
		const textBeforeCursor = this.#getTextBeforeCursor();
		const mentionSymbolIndex = this.#getMentionSymbolIndex();
		if (mentionSymbolIndex < 0)
		{
			return '';
		}

		return textBeforeCursor.slice(mentionSymbolIndex, this.#textarea.selectionEnd);
	}

	#getQueryWithoutMentionSymbol(): string
	{
		return this.#getQuery().slice(1);
	}

	#isNavigateCombination(event: KeyboardEvent): boolean
	{
		return event.key === 'ArrowUp' || event.key === 'ArrowDown';
	}

	#isValidFirstSymbol(firstQuerySymbol: string): boolean
	{
		return !(this.#hasNumber(firstQuerySymbol)
			|| this.#hasWhitespace(firstQuerySymbol)
			|| this.#hasSpecialSymbol(firstQuerySymbol));
	}

	#hasWhitespace(text: string): boolean
	{
		return (/^\s/).test(text);
	}

	#hasNumber(text: string): boolean
	{
		return (/\d$/).test(text);
	}

	#hasSpecialSymbol(text: string): boolean
	{
		const regex = /[!"#$%&'()*+,./<>@\\^_|-]/;

		return regex.test(text);
	}
	// endregion 'popup'

	// region 'replace'
	setMentionReplacements(mentionsMap: MentionReplacementMap): void
	{
		this.#mentionReplacementMap = mentionsMap;
	}

	addMentionReplacement(textToReplace: string, textToInsert: string): MentionReplacementMap
	{
		this.#mentionReplacementMap[textToReplace] = textToInsert;

		return this.#mentionReplacementMap;
	}

	getMentionSymbol(): string
	{
		return this.#mentionSymbol;
	}

	replaceMentions(text: string): string
	{
		let resultText = text;
		Object.entries(this.#mentionReplacementMap).forEach(([textToReplace, textToInsert]) => {
			resultText = resultText.replaceAll(textToReplace, textToInsert);
		});

		return resultText;
	}

	extractMentions(text: string): MentionReplacementMap
	{
		const CHAT_MENTION_CODE = 'chat';

		const mentions = {};
		const mentionRegExp = /\[(?<type>user|chat)=(?<dialogId>\w+)](?<mentionText>.*?)\[\/(user|chat)]/gi;

		const matches = text.matchAll(mentionRegExp);
		for (const match of matches)
		{
			const { mentionText } = match.groups;
			let { type: mentionType, dialogId } = match.groups;

			mentionType = mentionType.toLowerCase();
			if (mentionType === CHAT_MENTION_CODE)
			{
				dialogId = `${mentionType}${dialogId}`;
			}

			mentions[mentionText] = Utils.text.getMentionBbCode(dialogId, mentionText);
		}

		return mentions;
	}

	clearMentionSymbol()
	{
		this.#mentionSymbol = '';
	}

	clearMentionReplacements(): void
	{
		this.#mentionReplacementMap = {};
	}
	// endregion 'replace'
}
