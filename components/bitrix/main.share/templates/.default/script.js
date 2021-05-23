function ShowShareDialog(counter)
{
	var div = document.getElementById("share-dialog"+counter);
	if (!div)
		return;

	if (div.style.display == "block")
	{
		div.style.display = "none";
	}
	else
	{
		div.style.display = "block";
	}
	return false;
}

function CloseShareDialog(counter)
{
	var div = document.getElementById("share-dialog"+counter);

	if (!div)
		return;

	div.style.display = "none";
	return false;
}

function __function_exists(function_name) 
{
	if (typeof function_name == 'string')
	{
		return (typeof window[function_name] == 'function');
	} 
	else
	{
		return (function_name instanceof Function);
	}
}

