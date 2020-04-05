<?
IncludeModuleLangFile(__FILE__);

CModule::AddAutoloadClasses(
	"ldap",
	array(
		"CLDAP" => "classes/general/ldap.php",
		"CLdapServer" => "classes/general/ldap_server.php",
		"__CLDAPServerDBResult" => "classes/general/ldap_server.php",
		"CLdapUtil" => "classes/general/ldap_util.php"
		)
	);

?>
