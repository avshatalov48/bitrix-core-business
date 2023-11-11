import { EventType } from 'im.v2.const';
import { EventEmitter } from 'main.core.events';

const MentionSymbols: Set<string> = new Set(['@', '+']);
const WAIT_FOR_NEXT_SYMBOL_TIME = 300;
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

	static eventNamespace = 'BX.Messenger.v2.Textarea.MentionManager';

	constructor(textarea: HTMLTextAreaElement)
	{
		super();
		this.setEventNamespace(MentionManager.eventNamespace);
		this.#textarea = textarea;
	}

	onKeyDown(event)
	{
		if (this.#mentionPopupOpened)
		{
			this.#onOpenedMentionKeyDown(event);
		}

		this.#onClosedMentionKeyDown(event);
	}

	onMentionPopupClose()
	{
		this.#mentionPopupOpened = false;
	}

	getMentionSymbol(): string
	{
		return this.#mentionSymbol;
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

		if (this.#isInsertMentionCombination(event))
		{
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

	#hasWhitespace(text: string): boolean
	{
		return (/^\s/).test(text);
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

		if (/\d$/.test(firstQuerySymbol))
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
		EventEmitter.emit(EventType.mention.selectFirstItem);
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
}
