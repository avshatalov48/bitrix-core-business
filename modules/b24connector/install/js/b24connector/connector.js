BX.namespace("BX.B24Connector");

BX.B24Connector =
{
	showPortalChoosingDialog: function(urlTempl, hosts)
	{
		var dialog = new BX.CDialog({
			content: BX.B24Connector.createDialogContent(urlTempl, hosts),
			width: 530,
			height: 500,
			draggable: true,
			resizable: true,
			title: BX.message('B24C_PB_CHOOSE_PORTAL'),
			buttons: [BX.CDialog.btnCancel]
		});

		dialog.adjustSizeEx();
		dialog.Show();
	},

	createDialogContent: function(urlTempl, hosts)
	{
		var i;
		var result =
			'<div class="connector-pupup">'+
				'<div class="connector-pupup-title">'+BX.message('B24C_PB_CHOOSE_PORTALT')+'</div>'+
				'<div class="connector-pupup-sites">'+
					'<div class="connector-pupup-sites-left">'+
						'<div class="connector-pupup-sites-title">'+BX.message('B24C_PB_MY_B24')+'</div>';

		if(hosts.portal)
			for(i in hosts.portal)
				if(hosts.portal.hasOwnProperty(i))
					result += BX.B24Connector.createHostLink(urlTempl, hosts.portal[i].URL, hosts.portal[i].TITLE);

		result +=	'</div>'+
				'<div class="connector-pupup-sites-right">'+
					'<div class="connector-pupup-sites-title">'+BX.message('B24C_PB_MY_SITE')+'</div>';

		if(hosts.admin)
			for(i in hosts.admin)
				if(hosts.admin.hasOwnProperty(i))
					result += BX.B24Connector.createHostLink(urlTempl, hosts.admin[i].URL, hosts.admin[i].TITLE);

		result +=	'</div>'+
				'</div>'+
			'</div>';

		return result;
	},

	createHostLink: function(urlTmpl, host, title)
	{
		return '<a href="'+
			BX.util.htmlspecialchars(urlTmpl.replace('##HOST##', host))+
			'" class="connector-pupup-link">'+
			BX.util.htmlspecialchars(title)+'</a>';
	}
};