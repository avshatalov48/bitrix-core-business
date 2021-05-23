function OpenMenuNode(oThis)
{
	if (oThis.parentNode.className == '')
		oThis.parentNode.className = 'menu-close';
	else
		oThis.parentNode.className = '';
	return false;
}
