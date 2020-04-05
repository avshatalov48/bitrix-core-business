<?
$MESS["LDAP_OPTIONS_SAVE"] = "Save";
$MESS["LDAP_OPTIONS_RESET"] = "Reset";
$MESS["LDAP_OPTIONS_GROUP_LIMIT"] = "Maximum number of entries that can be returned on a single LDAP search operation:";
$MESS["LDAP_OPTIONS_USE_NTLM"] = "Use NTLM authentication<sup><span class=\"required\">1</span></sup>";
$MESS["LDAP_OPTIONS_USE_NTLM_MSG"] = "<sup><span class=\"required\">1</span></sup> - Before using NTLM authentication, you have to configure the web server modules involved and specify the NTLM authentication domains in the AD server settings.";
$MESS["LDAP_CURRENT_USER"] = "Current user login for NTLM authentication (domain\\login):";
$MESS["LDAP_CURRENT_USER_ABS"] = "Undefined";
$MESS["LDAP_OPTIONS_NTLM_VARNAME"] = "PHP variable containing NTLM user login (usually REMOTE_USER):";
$MESS["LDAP_NOT_USE_DEFAULT_NTLM_SERVER"] = "Do not use";
$MESS["LDAP_DEFAULT_NTLM_SERVER"] = "Default domain server:";
$MESS["LDAP_OPTIONS_DEFAULT_EMAIL"] = "Default user email address (if not specified):";
$MESS["LDAP_OPTIONS_NEW_USERS"] = "Create new user account on first successful login";
$MESS["LDAP_BITRIXVM_BLOCK"] = "Redirect NTLM Authentication to Ports 8890 and 8891";
$MESS["LDAP_BITRIXVM_SUPPORT"] = "Redirect NTLM authentication";
$MESS["LDAP_BITRIXVM_NET"] = "Restrict NTLM redirection to this subnet:";
$MESS["LDAP_BITRIXVM_HINT"] = "Specify here the subnet whose users will be redirected when authenticating via NTLM.<br> For example: <b>192.168.1.0/24</b> or <b>192.168.1.0/255.255.255.0</b>.<br>Separate multiple IP ranges with a semicolon.<br> Leave the field empty to redirect all users.";
$MESS["LDAP_WRONG_NET_MASK"] = "NTLM authentication subnet address and mark are incorrect.<br> Use the following format:<br> subnet/mask <br> xxx.xxx.xxx.xxx/xxx.xxx.xxx.xxx <br> xxx.xxx.xxx.xxx/xx<br>Separate multiple IP ranges with a semicolon.";
$MESS["LDAP_WITHOUT_PREFIX"] = "Check authentication at all available LDAP servers if login doesn't include prefix";
$MESS["LDAP_DUPLICATE_LOGIN_USER"] = "Create a user even if a user with specified login name exists:";
?>