;(function (window)
{
	BX.namespace('BX.Sender');
	if (BX.Sender.Audio)
	{
		return;
	}

	/**
	 * Audio.
	 *
	 */
	function Audio()
	{
	}
	Audio.prototype.init = function (params)
	{
		this.id = params.id;
		this.fileInputId = params.inputId;
		this.fileInput = BX["MFInput"] ? BX.MFInput.get(params.controlId) : null;
		this.playerNode = BX(params.playerId);
		this.player = BX.Fileman.PlayerManager.getPlayerById(params.playerId);

		this.containerNode = document.querySelector('[data-bx-field="' + this.id + '"]');
		this.preudoValueNode = null;

		if (BX.Sender.Template && BX.Sender.Template.Selector)
		{
			var selector = BX.Sender.Template.Selector;
			BX.addCustomEvent(selector, selector.events.templateSelect, this.onTemplateSelect.bind(this));
		}
		if (this.fileInput)
		{
			BX.addCustomEvent(this.fileInput, "onDeleteFile", this.onDeleteFile.bind(this));
			BX.addCustomEvent(this.fileInput, "onUploadDone", this.onUploadDone.bind(this));
		}

		if (!params.value || !params.value.length)
		{
			this.hidePlayer();
		}
		else if (params.useTemplateValue)
		{
			this.setValue(params.value);
		}
	};
	Audio.prototype.onTemplateSelect = function (template)
	{
		template.messageFields.forEach(function (field) {
			if(field.code !== 'AUDIO_FILE')
			{
				return;
			}
			this.fileInput.clear();
			this.setValue(template.code);
			this.showPlayer();
			this.setPlayerSource(field.value);
		}, this);
	};

	Audio.prototype.onDeleteFile = function()
	{
		this.hidePlayer();
	};

	Audio.prototype.onUploadDone = function(file, item)
	{
		this.clearValue();
		this.showPlayer();
		this.setPlayerSource(file.url);
	};

	Audio.prototype.setPlayerSource = function(value)
	{
		if (!this.player)
			return;

		this.player.setSource(value);
	};

	Audio.prototype.showPlayer = function()
	{
		BX.show(this.playerNode);
	};

	Audio.prototype.hidePlayer = function()
	{
		BX.hide(this.playerNode);
	};

	Audio.prototype.setValue = function(value)
	{
		if (!this.preudoValueNode)
		{
			this.preudoValueNode = BX.create('input', {props: { type: 'hidden', name: this.fileInputId, value: value}});
			BX.append(this.preudoValueNode, this.containerNode);
		}
		else
		{
			this.preudoValueNode.value = value;
		}
	};

	Audio.prototype.clearValue = function()
	{
		if (this.preudoValueNode)
		{
			BX.remove(this.preudoValueNode);
			this.preudoValueNode = null;
		}
	};

	BX.Sender.Audio = new Audio();


})(window);