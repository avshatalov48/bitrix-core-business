import type { LexicalNode, LexicalNodeReplacement } from 'ui.lexical.core';
import type { TextEditor } from 'ui.text-editor';
import type { PluginConstructor } from '../base-plugin';
import BasePlugin from '../base-plugin';
import { ClipboardPlainTableNode } from './clipboard-plain-table-node';

export class ClipboardPlugin extends BasePlugin
{
	static getName(): string
	{
		return 'Clipboard';
	}

	static getNodes(editor: TextEditor): Array<Class<LexicalNode> | LexicalNodeReplacement>
	{
		const nodes = [];

		const tablePluginExists = editor.getPlugins().getConstructors().some(
			(plugin: PluginConstructor): boolean => {
				return plugin.getName() === 'Table';
			},
		);

		if (!tablePluginExists)
		{
			nodes.push(ClipboardPlainTableNode);
		}

		return nodes;
	}
}
