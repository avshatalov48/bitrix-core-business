<?
$aMenuLinks = Array(
	Array(
		"Каталог курсов", 
		"index.php",
		Array(), 
		Array(), 
		"" 
	),

	Array(
		"Мои курсы",
		"mycourses.php",
		Array(), 
		Array(), 
		"\$GLOBALS['USER']->IsAuthorized()" 
	),


	Array(
		"Журнал обучения",
		"gradebook.php",
		Array(), 
		Array(), 
		"\$GLOBALS['USER']->IsAuthorized()"  
	),

	Array(
		"Анкета специалиста",
		"profile.php",
		Array(), 
		Array(), 
		"\$GLOBALS['USER']->IsAuthorized()"  
	),


);
?>