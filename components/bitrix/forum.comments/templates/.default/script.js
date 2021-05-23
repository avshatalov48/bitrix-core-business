;(function(window){
	window.__fcOnUCFormAfterShow = function(obj, text, data) {
		if (!BX(obj.form) || !BX(this))
		{
			return;
		}
		var res = data ? data["UF"] : {};
		if (data && data["FILES"])
		{
			res["FILES"] = {
				USER_TYPE_ID : "file",
				FIELD_NAME : "FILE_NEW[]",
				VALUE : data["FILES"],
				CID : BX.message('FCCID')
			}
		}
		window.LHEPostForm.reinitData(obj.getLHE().oEditorId, text, res);
	}
})(window);