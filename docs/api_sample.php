<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<title>LiveZilla - Operator Box</title>
</head>

<body>

<table cellspacing="5" style="font-family:verdana,arial;font-size:10px;border:solid 1px gray;background:#eff2f3;">

<?php

define("LIVEZILLA_PATH","./../");
require("./../api.php");
$API = new LiveZillaAPI();

foreach($API->GetOperators() as $operator)
{
	if(!$operator->InExternalGroup)
		continue;
		
	echo "<tr>";
	echo "<td valign=\"top\">";
	echo "<img src=\"". LIVEZILLA_PATH . $operator->GetOperatorPictureFile() ."\" width=\"60\" height=\"45\" style=\"border:solid 1px gray;\">";
	echo "</td>";
	echo "<td valign=\"top\">";
	echo "<b>" . utf8_decode($operator->Fullname) . "</b><br>". utf8_decode($operator->Description) ."<br>Status: ";
	
	if($operator->Status == USER_STATUS_ONLINE)
		echo "<span style=\"color:green;font-weight:bold;\">Online</span><br>";
	else if($operator->Status == USER_STATUS_BUSY)
		echo "<span style=\"color:orange;font-weight:bold;\">Busy</span><br>";
	else if($operator->Status == USER_STATUS_OFFLINE)
		echo "<em>Offline</em><br>";
	else
		echo "<em>Away</em><br>";
		
	echo "<a href=\"javascript:void(window.open('". LIVEZILLA_PATH . FILE_CHAT . "?intid=".$API->Base64UrlEncode($operator->UserId)."&amp;mp=true','','width=600,height=600,left=0,top=0,resizable=yes,menubar=no,location=no,status=yes,scrollbars=yes'))\">Start Chat</a>";
	echo "&nbsp;|&nbsp;<a href=\"mailto:". $operator->Email ."\">Send Email</a>";
		
	echo "<br><br></td>";
	echo "</tr>";
}

?>

</table>
</body>
</html>

