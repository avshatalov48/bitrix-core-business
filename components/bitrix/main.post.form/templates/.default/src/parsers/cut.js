import {Loc} from 'main.core';
import Default from './default';

export default class Spoiler extends Default
{
	id: string = 'spoiler';
	name: string = Loc.getMessage('MPF_TOOLBAR_SPOILER');
	buttonParams: ?Object = {
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

	parse(sName, content, pLEditor)
	{
		if (/\[(cut|spoiler)(([^\]])*)\]/gi.test(content))
		{
			content = content.
			replace(/[\001-\006]/gi, '').
			replace(/\[cut(((?:=)[^\]]*)|)\]/gi, '\001$1\001').
			replace(/\[\/cut\]/gi, '\002').
			replace(/\[spoiler([^\]]*)\]/gi, '\003$1\003').
			replace(/\[\/spoiler]/gi, '\004');
			var
				reg1 = /(?:\001([^\001]*)\001)([^\001-\004]+)\002/gi,
				reg2 = /(?:\003([^\003]*)\003)([^\001-\004]+)\004/gi,
				__replace_reg = function(title, body){
					title = title.replace(/^(="|='|=)/gi, '').replace(/("|')?$/gi, '');
					return '<blockquote class="bx-spoiler" id="' + pLEditor.SetBxTag(false, {tag: "spoiler"}) + '" title="' + title + '">' + body + '</blockquote>';
				},
				func = function(str, title, body){return __replace_reg(title, body);};
			while (content.match(reg1) || content.match(reg2))
			{
				content = content.
				replace(reg1, func).
				replace(reg2, func);
			}
		}
		content = content.
			replace(/\001([^\001]*)\001/gi, '[cut$1]').
			replace(/\003([^\003]*)\003/gi, '[spoiler$1]').
			replace(/\002/gi, '[/cut]').
			replace(/\004/gi, '[/spoiler]');
		return content;
	}

	unparse(bxTag, oNode)
	{
		if (bxTag.tag === 'spoiler')
		{
			var name = '', i;
			// Handle childs
			for (i = 0; i < oNode.node.childNodes.length; i++)
			{
				name += this.htmlEditor.bbParser.GetNodeHtml(oNode.node.childNodes[i]);
			}
			name = BX.util.trim(name);
			if (name !== '')
				return "[SPOILER" + (oNode.node.hasAttribute("title") ? '=' + oNode.node.getAttribute("title") : '')+ "]" + name +"[/SPOILER]";
		}
		return "";
	}
}