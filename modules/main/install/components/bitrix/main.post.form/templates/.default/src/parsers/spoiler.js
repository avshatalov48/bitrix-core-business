import {Loc} from 'main.core';
import Default from './default';

export default class Spoiler extends Default
{
	id: string = 'spoiler';
	buttonParams: ?Object = {
		name: Loc.getMessage('MPF_SPOILER'),
		iconClassName: 'spoiler',
		disabledForTextarea: false,
		src: Loc.getMessage('MPF_TEMPLATE_FOLDER') + '/images/lhespoiler.svg',
		toolbarSort: 205,
	}

	handler()
	{
		let result;
		// Iframe
		if (!this.htmlEditor.bbCode || !this.htmlEditor.synchro.IsFocusedOnTextarea())
		{
			result = this.htmlEditor.action.actions.formatBlock.exec('formatBlock', 'blockquote', 'bx-spoiler', false, {bxTagParams : {tag: "spoiler"}});
		}
		else // bbcode + textarea
		{
			result = this.htmlEditor.action.actions.formatBbCode.exec('quote', {tag: 'SPOILER'});
		}
		return result;
	}

	parse(content, pLEditor)
	{
		if (/\[spoiler(([^\]])*)\]/gi.test(content))
		{
			content = content.
				replace(/[\x01-\x02]/gi, '').
				replace(/\[spoiler([^\]]*)\]/gi, '\x01$1\x01').
				replace(/\[\/spoiler]/gi, '\x02');
			const reg2 = /(?:\x01([^\x01]*)\x01)([^\x01-\x02]+)\x02/gi;

			while (content.match(reg2))
			{
				content = content.replace(reg2, function(str, title, body) {
					title = title.replace(/^(="|='|=)/gi, '').replace(/("|')?$/gi, '');
					return `<blockquote class="bx-spoiler" id="${this.htmlEditor.SetBxTag(false, {tag: "spoiler"})}" title="${title}">${body}</blockquote>`;
				}.bind(this));
			}
		}
		content = content.
			replace(/\001([^\001]*)\001/gi, '[spoiler$1]').
			replace(/\002/gi, '[/spoiler]');
		return content;
	}

	unparse(bxTag, oNode)
	{
		let name = '';
		for (let i = 0; i < oNode.node.childNodes.length; i++)
		{
			name += this.htmlEditor.bbParser.GetNodeHtml(oNode.node.childNodes[i]);
		}
		name = name.trim();
		if (name !== '')
		{
			return "[SPOILER" + (oNode.node.hasAttribute("title") ? '=' + oNode.node.getAttribute("title") : '')+ "]" + name +"[/SPOILER]";
		}
		return "";
	}
}