/**
 * Bitrix HTML Editor 3.0
 * Date: 24.04.13
 * Time: 4:23
 * Spellcheck extention
 */
(function() {
	function Spellchecker(editor)
	{
		this.editor = editor;
		this.usePspell = this.editor.config.usePspell;
		this.useCustomSpell = this.editor.config.useCustomSpell;
		this.lang = BX.message.LANGUAGE_ID;
	}

	Spellchecker.prototype = {
		CheckDocument: function()
		{
			this.wordList = [];
			this.nodesMap = [];
			var _this = this;
			this.ProcessChildren(this.editor.GetIframeDoc().body);

			if (this.wordList.length > 0)
			{
				this.editor.Request({
					postData: this.editor.GetReqData('spellcheck_words',
						{
							words: this.wordList,
							lang: this.lang,
							use_pspell: this.usePspell,
							use_custom_spell: this.useCustomSpell
						}
					),
					handler: function(res)
					{
						_this.editor.GetDialog('Spell').ShowResult(res);
					}
				});
			}
			else
			{
				this.editor.GetDialog('Spell').ShowResult({});
			}
		},

		ProcessChildren: function(node)
		{
			if (node && node.childNodes.length > 0)
			{
				var child, i;
				for (i = 0; i < node.childNodes.length; i++)
				{
					child = node.childNodes[i];
					//check if it's element node
					if (child.nodeType == 1)
					{
						this.ProcessChildren(child);
					}
					else if (child.nodeType == 3 && child.nodeValue)
					{
						this.HandleNodeValue(child);
					}
				}
			}
		},

		HandleNodeValue: function(node)
		{
			var
				separator = new RegExp("[\000-\100\133-\140\173-\177\230\236\246-\377\240]+","i"),
				arrWords = node.nodeValue.split(separator),
				i;

			for (i = 0; i < arrWords.length; i++)
			{
				if (arrWords[i].length > 1)
				{
					this.wordList.push(arrWords[i]);
					this.nodesMap.push({word: arrWords[i], node: node, ind: this.wordList.length});
				}
			}
		},

		GetWordByIndex: function(ind)
		{
			return this.nodesMap[ind] || false;
		},

		ChangeWord: function(ind, replacement)
		{
			if (replacement !== false)
			{
				var wordData = this.GetWordByIndex(ind);
				wordData.node.nodeValue = wordData.node.nodeValue.replace(wordData.word, replacement);
			}
		},

		AddWord: function(word)
		{
			this.editor.Request({
				postData: this.editor.GetReqData('spellcheck_add_word',
					{
						word: word,
						lang: this.lang,
						use_pspell: this.usePspell,
						use_custom_spell: this.useCustomSpell
					}
				),
				handler: function(res){}
			});
		}
	};


	// Specialchars dialog
	function SpellDialog(editor, params)
	{
		this.editor = editor;
		params = {
			id: 'bx_spell',
			width: 570,
			resizable: false,
			className: 'bxhtmled-char-dialog'
		};
		this.id = 'spell' + this.editor.id;
		// Call parrent constructor
		SpellDialog.superclass.constructor.apply(this, [editor, params]);

		this.oDialog.ClearButtons();
		this.oDialog.SetButtons([this.oDialog.btnClose]);

		BX.addCustomEvent(this.oDialog, 'onWindowUnRegister', function(){editor.synchro.FullSyncFromIframe();});

		this.SetContent(this.Build());
		BX.addCustomEvent(this, "OnDialogSave", BX.proxy(this.Save, this));
	}
	BX.extend(SpellDialog, window.BXHtmlEditor.Dialog);

	SpellDialog.prototype.Build = function()
	{
		var _this = this;
		this.pCont = BX.create('DIV', {props: {className: 'bxhtmled-spell-wrap bxhtmled-spell-wrap-notice'}});
		var
			leftCont = this.pCont.appendChild(BX.create('SPAN', {props: {className: 'bxhtmled-spell-left'}})),
			rightCont = this.pCont.appendChild(BX.create('SPAN', {props: {className: 'bxhtmled-spell-right'}}));

		this.waitCont = this.pCont.appendChild(BX.create('SPAN', {props: {className: 'bxhtmled-notice-cont'}, text: BX.message('BXEdSpellWait')}));
		this.noErrorsCont = this.pCont.appendChild(BX.create('SPAN', {props: {className: 'bxhtmled-notice-cont'}, text: BX.message('BXEdSpellNoErrors'), style: {display: 'none'}}));

		leftCont.appendChild(BX.create('LABEL', {props: {className: 'bxhtmled-spell-lbl'}, text: BX.message('BXEdSpellErrorLabel') + ': ', attrs: {'for': this.id + '-spell-word'}}));
		this.pWrongWordInp = leftCont.appendChild(BX.create('INPUT', {props: {type: 'text', id: this.id + '-spell-word'}}));
		leftCont.appendChild(BX.create('LABEL', {props: {className: 'bxhtmled-spell-lbl'}, text: BX.message('BXEdSpellSuggestion') + ': ', attrs: {'for': this.id + '-suggestion'}}));

		this.pSuggestSel = leftCont.appendChild(BX.create('INPUT', {props: {type: 'text', id: this.id + '-suggestion'}}));
		this.oSuggestion = new SuggestionSelector(this.editor,
			{
				id: this.id + '-suggestion-selector',
				input: this.pSuggestSel,
				value: ''
			}
		);


		this.skipBut = rightCont.appendChild(BX.create('INPUT', {props: {type: 'button', id: this.id + '-skip', value: BX.message('BXEdSpellcheckSkip')}}));
		this.skipAllBut = rightCont.appendChild(BX.create('INPUT', {props: {type: 'button', id: this.id + '-skip-all', value: BX.message('BXEdSpellcheckSkipAll')}}));
		this.replaceBut = rightCont.appendChild(BX.create('INPUT', {props: {type: 'button', id: this.id + '-replace', value: BX.message('BXEdSpellcheckReplace')}}));
		this.replaceAllBut = rightCont.appendChild(BX.create('INPUT', {props: {type: 'button', id: this.id + '-replace-all', value: BX.message('BXEdSpellcheckReplaceAll')}}));
		this.addWordBut = rightCont.appendChild(BX.create('INPUT', {props: {type: 'button', id: this.id + '-custom', value: BX.message('BXEdSpellcheckAddCustom')}}));

		BX.bind(this.skipBut, 'click', BX.proxy(this.SkipWord, this));
		BX.bind(this.skipAllBut, 'click', BX.proxy(this.SkipAll, this));
		BX.bind(this.replaceBut, 'click', BX.proxy(this.ReplaceWord, this));
		BX.bind(this.replaceAllBut, 'click', BX.proxy(this.ReplaceAll, this));
		BX.bind(this.addWordBut, 'click', BX.proxy(this.AddWord, this));

		return this.pCont;
	};



	SpellDialog.prototype.SetValues = BX.DoNothing;
	SpellDialog.prototype.GetValues = BX.DoNothing;

	SpellDialog.prototype.Show = function(savedRange)
	{
		this.savedRange = savedRange;
		if (this.savedRange)
		{
			this.editor.selection.SetBookmark(this.savedRange);
		}

		BX.addClass(this.pCont, 'bxhtmled-spell-wrap-notice');
		this.waitCont.style.display = '';
		this.noErrorsCont.style.display = 'none';

		this.SetTitle(BX.message('BXEdSpellcheck'));
		// Call parrent Dialog.Show()
		SpellDialog.superclass.Show.apply(this, arguments);
	};

	SpellDialog.prototype.ShowResult = function(result)
	{
		this.waitCont.style.display = 'none';
		this.words = [];
		//this.wrongWordsIndex = {};
		this.curInd = 0;
		if (result.words && result.words.length > 0)
		{
			this.words = result.words;
			//for (var i = 0; i < result.words.length; i++)
			//{
				//this.wrongWordsIndex[result.words[i][0]] = i;
			//}

			BX.removeClass(this.pCont, 'bxhtmled-spell-wrap-notice');
			this.HandleWord(0);
		}
		else
		{
			this.noErrorsCont.style.display = 'block';
		}
	};

	SpellDialog.prototype.HandleWord = function(ind)
	{
		var
			word = this.words[ind],
			wordData = this.editor.Spellchecker.GetWordByIndex(word[0]);

		if (word && wordData)
		{
			this.pWrongWordInp.value = wordData.word;
			this.SetSuggestions(word[1]);
		}
		else
		{
			this.SkipWord();
		}
	};

	SpellDialog.prototype.GetSuggestion = function()
	{
		return this.pSuggestSel.value;
	};

	SpellDialog.prototype.SetSuggestions = function(suggestions)
	{
		this.bCreated = false;
		this.oSuggestion.SetValue(suggestions[0] || '');
		this.oSuggestion.UpdateValues(this.oSuggestion.GetValues(suggestions));
	};

	SpellDialog.prototype.SkipWord = function()
	{
		this.curInd++;
		if (this.curInd < this.words.length)
		{
			this.HandleWord(this.curInd);
		}
		else
		{
			this.Close();
		}
	};

	SpellDialog.prototype.SkipAll = function()
	{
		this.curInd = this.words.length - 1;
		this.Close();
	};

	SpellDialog.prototype.ReplaceWord = function()
	{
		if (this.words[this.curInd])
		{
			this.editor.Spellchecker.ChangeWord(this.words[this.curInd][0], this.GetSuggestion());
		}
		this.SkipWord();
	};

	SpellDialog.prototype.ReplaceAll = function()
	{
		var i;
		for (i = this.curInd; i < this.words.length; i++)
		{
			this.editor.Spellchecker.ChangeWord(this.words[i][0], this.words[i][1][0] || false);
		}
		this.curInd = i;
		this.Close();
	};

	SpellDialog.prototype.AddWord = function()
	{
		this.editor.Spellchecker.AddWord(this.pWrongWordInp.value);
		this.SkipWord();
	};


	function SuggestionSelector(editor, params)
	{
		// Call parrent constructor
		SuggestionSelector.superclass.constructor.apply(this, arguments);
		this.bMultiple = false;
		this.Init();
	};
	BX.extend(SuggestionSelector, window.BXHtmlEditor.ComboBox);

	SuggestionSelector.prototype.OnChange = function()
	{
	//	this.values = this.GetClasses();
	//	this.bCreated = false;
	};

	SuggestionSelector.prototype.GetValues = function(suggestions)
	{
		this.values = [];
		for (var i = 0; i < suggestions.length; i++)
		{
			this.values.push({NAME: suggestions[i]});
		}
		return this.values;
	};

	// Specialchars dialog END




	function __run()
	{
		window.BXHtmlEditor.Spellchecker = Spellchecker;
		window.BXHtmlEditor.dialogs.Spell = SpellDialog;
	}

	if (window.BXHtmlEditor)
	{
		__run();
	}
	else
	{
		BX.addCustomEvent(window, "OnBXHtmlEditorInit", __run);
	}
})();