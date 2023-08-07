import {Loc, Runtime} from 'main.core';
import { EventEmitter } from 'main.core.events';
import Default from './default';

export default class AITextGenerator extends Default
{
	id: string = 'ai-text-generator';
	buttonParams: ?Object = {
		name: 'AI text generator',
		iconClassName: 'feed-add-post-editor-btn-ai-text',
		disabledForTextarea: false,
		toolbarSort: 399,
		compact: true
	}

	handler()
	{
		Runtime.loadExtension('ai.picker').then(() => {
			const aiTextPicker = new BX.AI.Picker({
				moduleId: 'main',
				contextId: Loc.getMessage('USER_ID'),
				analyticLabel: 'main_post_form_comments_ai_text',
				history: true,
				onSelect: (message) => {
					EventEmitter.emit(this.editor.getEventObject(), 'OnInsertContent', [message.data, message.data]);
				},
			});
			aiTextPicker.setLangSpace(BX.AI.Picker.LangSpace.text);
			aiTextPicker.text();
		});
	}

	parse(content, pLEditor)
	{
		return content;
	}

	unparse(bxTag, oNode)
	{
		return '';
	}
}