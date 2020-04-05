;(function(window) {

	if (BX.adminSubTabControl)
		return;
	
	BX.adminSubTabControl = function(name, unique_name, aTabs, url_link, post_params)
	{
		BX.adminSubTabControl.superclass.constructor.apply(this,[ name, unique_name, aTabs]);
		this.url_link = url_link;
		this.post_params = post_params;
		this.url_settings = '';
	};
	BX.extend(BX.adminSubTabControl, BX.adminTabControl);
	
	BX.adminSubTabControl.prototype.SaveSettings = function()
	{
		var sTabs='', s='';

		var oFieldsSelect;
		var oSelect = BX('selected_tabs');
		if(oSelect)
		{
			var k = oSelect.length;
			for(var i=0; i<k; i++)
			{
				s = oSelect[i].value + '--#--' + oSelect[i].text;
				oFieldsSelect = BX('selected_fields[' + oSelect[i].value + ']');
				if(oFieldsSelect)
				{
					var n = oFieldsSelect.length;
					for(var j=0; j<n; j++)
					{
						s += '--,--' + oFieldsSelect[j].value + '--#--' + jsUtils.trim(oFieldsSelect[j].text);
					}
				}
				sTabs += s + '--;--';
			}
		}

		var bCommon = (document.form_settings.set_default && document.form_settings.set_default.checked);

		var sParam = '';
		sParam += '&p[0][c]=form';
		sParam += '&p[0][n]='+BX.util.urlencode(this.name);
		if(bCommon)
			sParam += '&p[0][d]=Y';
		sParam += '&p[0][v][tabs]=' + BX.util.urlencode(sTabs);

		var options_url = '/bitrix/admin/user_options.php?lang='+BX.message('LANGUAGE_ID')+'&sessid=' + BX.bitrix_sessid();
		options_url += '&action=delete&c=form&n='+this.name+'_disabled';

		BX.showWait();
		this.CloseSettings();
		BX.ajax.post(options_url, sParam, BX.delegate(function() {
			BX.WindowManager.Get().AllowClose(); BX.WindowManager.Get().Close();
			BX.closeWait();
			(new BX.CAdminDialog({
			    'content_url': this.url_link,
			    'content_post': this.post_params,
				'draggable': true,
				'resizable': true,
				'buttons': [BX.CAdminDialog.btnSave, BX.CAdminDialog.btnCancel]
				})).Show();
		}, this));
	};
	
	BX.adminSubTabControl.prototype.DeleteSettings = function(bCommon)
	{
		BX.showWait();
		this.CloseSettings();
		BX.userOptions.del('form', this.name, bCommon, BX.delegate(function () {
			BX.WindowManager.Get().AllowClose(); BX.WindowManager.Get().Close();
			BX.closeWait();
			(new BX.CAdminDialog({
			    'content_url': this.url_link,
			    'content_post': this.post_params,
				'draggable': true,
				'resizable': true,
				'buttons': [BX.CAdminDialog.btnSave, BX.CAdminDialog.btnCancel]
				})).Show();
		}, this));
	};
	
	BX.adminSubTabControl.prototype.DisableSettings = function()
	{
		BX.showWait();
		this.CloseSettings();
		var request = new JCHttpRequest;
		request.Action = BX.delegate(function () {
			BX.closeWait();
			(new BX.CAdminDialog({
			    'content_url': this.url_link,
			    'content_post': this.post_params,
				'draggable': true,
				'resizable': true,
				'buttons': [BX.CAdminDialog.btnSave, BX.CAdminDialog.btnCancel]
				})).Show();
		}, this);
		
		var sParam = '';
		sParam += '&p[0][c]=form';
		sParam += '&p[0][n]='+encodeURIComponent(this.name+'_disabled');
		sParam += '&p[0][v][disabled]=Y';
		request.Send('/bitrix/admin/user_options.php?lang=' + phpVars.LANGUAGE_ID + sParam + '&sessid='+phpVars.bitrix_sessid);
	};

	BX.adminSubTabControl.prototype.EnableSettings = function()
	{
		BX.showWait();
		this.CloseSettings();
		var request = new JCHttpRequest;
		request.Action = BX.delegate(function () {
			BX.closeWait();
			(new BX.CAdminDialog({
			    'content_url': this.url_link,
			    'content_post': this.post_params,
				'draggable': true,
				'resizable': true,
				'buttons': [BX.CAdminDialog.btnSave, BX.CAdminDialog.btnCancel]
				})).Show();
		}, this);
		var sParam = '';
		sParam += '&c=form';
		sParam += '&n='+encodeURIComponent(this.name)+'_disabled';
		sParam += '&action=delete';
		request.Send('/bitrix/admin/user_options.php?lang=' + phpVars.LANGUAGE_ID + sParam + '&sessid='+phpVars.bitrix_sessid);
	};
	
	BX.adminSubTabControl.prototype.CloseSettings =  function()
	{
		BX.WindowManager.Get().Close();
	};
})(window);