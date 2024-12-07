import { createCommand, type LexicalCommand } from 'ui.lexical.core';

export const HIDE_DIALOG_COMMAND: LexicalCommand = createCommand('HIDE_DIALOG_COMMAND');
export const DIALOG_VISIBILITY_COMMAND: LexicalCommand = createCommand('DIALOG_VISIBILITY_COMMAND');
export const DRAG_START_COMMAND: LexicalCommand = createCommand('DRAG_START_COMMAND');
export const DRAG_END_COMMAND: LexicalCommand = createCommand('DRAG_END_COMMAND');
