import type { EditorState, LexicalEditor } from 'ui.lexical.core';

export type InitialEditorStateType = undefined | null | string | EditorState | ((editor: LexicalEditor) => void);
