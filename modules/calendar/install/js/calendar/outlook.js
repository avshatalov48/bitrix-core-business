var jsOutlookUtils = {
	encode: function(str)
	{
		var
			i, len = str.length, cur_chr, cur_chr_code,
			out = "",
			bUnicode = false,
			symb_escape = "&\\[]|";
		for (i = 0; i < len; i++)
		{
			cur_chr = str.charAt(i);
			cur_chr_code = cur_chr.charCodeAt(0);

			if (bUnicode && cur_chr_code <= 0x7F) { out += "]"; bUnicode = false; }
			if (!bUnicode && cur_chr_code > 0x7F) { out += "["; bUnicode = true; }

			if (symb_escape.indexOf(cur_chr) >= 0)
				out += "|";

			if (
				(cur_chr_code >= 0x61 && cur_chr_code <= 0x7A)
				||
				(cur_chr_code >= 0x41 && cur_chr_code <= 0x5A)
				||
				(cur_chr_code >= 0x30 && cur_chr_code <= 0x39)
			)
				out += cur_chr;
			else if (cur_chr_code <= 0x0F)
				out += "%0" + cur_chr_code.toString(16).toUpperCase();
			else if (cur_chr_code <= 0x7F)
				out += "%" + cur_chr_code.toString(16).toUpperCase();
			else if (cur_chr_code <= 0x00FF)
				out += "00" + cur_chr_code.toString(16).toUpperCase();
			else if (cur_chr_code <= 0x0FFF)
				out += "0" + cur_chr_code.toString(16).toUpperCase();
			else
				out += cur_chr_code.toString(16).toUpperCase();
		}

		if (bUnicode)
			out += "]";

		return out;
	},

	Sync: function(type, base_url, list_url, list_prefix, list_name, guid, port)
	{
		var
			maxLinkLen = 500,
			maxNameLen = 20,
			host = window.location.host;

		if(!!port)
		{
			host = host.replace(/:\d+/, '') + ':' + port;
		}

		base_url = window.location.protocol + "//" + host + base_url;
		guid = guid.replace(/{/g, '%7B').replace(/}/g, '%7D').replace(/-/g, '%2D');

		var link = "stssync://sts/?ver=1.1"
			+ "&type=" + type
			+ "&cmd=add-folder"
			+ "&base-url=" + jsOutlookUtils.encode(base_url)
			+ "&list-url=" + jsOutlookUtils.encode(list_url)
			+ "&guid=" + guid;

		var names = "&site-name=" + jsOutlookUtils.encode(list_prefix) + "&list-name=" + jsOutlookUtils.encode(list_name);

		if (
			link.length + names.length > maxLinkLen
			&&
			(list_prefix.length > maxNameLen || list_name.length > maxNameLen)
		)
		{
			if (list_prefix.length > maxNameLen)
				list_prefix = list_prefix.substring(0, maxNameLen-1) + "...";
			if (list_name.length > maxNameLen)
				list_name = list_name.substring(0, maxNameLen-1) + "...";

			names = "&site-name=" + jsOutlookUtils.encode(list_prefix) + "&list-name=" + jsOutlookUtils.encode(list_name);
		}

		link += names;

		try {window.location.href = link;}
		catch (e) {}
	}
};