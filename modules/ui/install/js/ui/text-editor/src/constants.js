import type { NewLineModeType } from './types/new-line-mode-type';

// Node flags
export const UNFORMATTED = 1;

export const NewLineMode: Record<string, NewLineModeType> = {
	LINE_BREAK: 'line-break',
	PARAGRAPH: 'paragraph',
	MIXED: 'mixed',
};
