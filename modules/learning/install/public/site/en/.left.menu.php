<?
$aMenuLinks = Array(
	Array(
		"Courses list", 
		"index.php",
		Array(), 
		Array(), 
		"" 
	),

	Array(
		"My Courses",
		"mycourses.php",
		Array(), 
		Array(), 
		"\$GLOBALS['USER']->IsAuthorized()" 
	),


	Array(
		"Grade Book",
		"gradebook.php",
		Array(), 
		Array(), 
		"\$GLOBALS['USER']->IsAuthorized()"  
	),

	Array(
		"Profile",
		"profile.php",
		Array(), 
		Array(), 
		"\$GLOBALS['USER']->IsAuthorized()"  
	),


);
?>