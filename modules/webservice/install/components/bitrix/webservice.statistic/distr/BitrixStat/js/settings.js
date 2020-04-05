var bitServer;
var bitLogin;
var bitPassword;

function Load()
{
	lServerTitle.innerHTML = lServerTitleText;
	lLoginTitle.innerHTML = lLoginTitleText;
	lPasswordTitle.innerHTML = lPasswordTitleText;

	System.Gadget.onSettingsClosing = SettingsClosing;

	bitServer = System.Gadget.Settings.read("bitServer");
	bitLogin = System.Gadget.Settings.read("bitLogin");
	bitPassword = System.Gadget.Settings.read("bitPassword");

	server.innerText = bitServer;
	login.innerText = bitLogin;
	password.innerText = bitPassword;
}

function SettingsClosing(event)
{
	//debugger;
    if (event.closeAction == event.Action.commit)
    {
        SaveSettings();
    }
    event.cancel = false;
}

function SaveSettings()
{
	System.Gadget.Settings.write("bitServer", server.value);
	System.Gadget.Settings.write("bitLogin", login.value);
	System.Gadget.Settings.write("bitPassword", password.value);
}
