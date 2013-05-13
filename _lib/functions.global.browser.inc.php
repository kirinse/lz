<?php

/****************************************************************************************
* LiveZilla functions.global.inc.php
* 
* Improper changes in this file may cause critical errors. It is strongly 
* recommended to desist from editing this file directly.
* 
***************************************************************************************/ 

if(!defined("IN_LIVEZILLA"))
	die();
	
function getWebBrowser()
{
	$currentBrowser = Array();
	$currentBrowser['platform'] = "Unknown";
	$currentBrowser['browser'] = "Unknown";
	$currentBrowser['version'] = "Unknown";
	$currentBrowser['allowed'] = true;
	$currentBrowser['agent'] = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : ""; 

	if($currentBrowser['agent'] != "")
	{
		if(eregi("chrome", $currentBrowser['agent']))
		{
		    $currentBrowser['browser']="Chrome";
		}
		elseif(eregi("safari", $currentBrowser['agent']))
		{
		    $currentBrowser['browser']="Safari";
		}
		elseif(eregi("seamonkey", $currentBrowser['agent']))
		{
		    $currentBrowser['browser']="SeaMonkey";
		}
		elseif(eregi("firefox", $currentBrowser['agent']))
		{
			$currentBrowser['allowed'] = true;
		    $currentBrowser['browser']="Firefox";
		    $contents = stristr($currentBrowser['agent'], "Firefox");
		    $contents = explode("/",$contents);
		    $currentBrowser['version'] = (isset($contents[1])) ? $contents[1] : "unknown";
		}
		elseif(eregi("opera",$currentBrowser['agent']))
		{
		    $contents = stristr($currentBrowser['agent'], "opera");
			if (eregi("/", $contents))
			{
		        $contents = explode("/",$contents);
		        $currentBrowser['browser'] = $contents[0];
		        $contents = explode(" ",$contents[1]);
		        $currentBrowser['version'] = $contents[0];
		    }
			else
			{
	        	$contents = explode(" ",stristr($contents,"opera"));
	        	$currentBrowser['browser'] = $contents[0];
	        	$currentBrowser['version'] = $contents[1];
	    	}
			$currentBrowser['allowed'] = ($currentBrowser['version'] >= 8);
		}
		elseif(eregi("msie",$currentBrowser['agent']) && !eregi("opera",$currentBrowser['agent']))
		{
		    $contents = explode(" ",stristr($currentBrowser['agent'],"msie"));
			$currentBrowser['browser'] = "MSIE";
		    $currentBrowser['browser'] = $contents[0];
			if(isset($contents[1]))
		   		$currentBrowser['version'] = $contents[1];
			$currentBrowser['allowed'] = ($currentBrowser['version'] >= 5.5);
		}
		elseif(eregi("webtv",$currentBrowser['agent']))
		{
			$currentBrowser['allowed'] = false;
		    $contents = explode("/",stristr($currentBrowser['agent'],"webtv"));
		    $currentBrowser['browser'] = $contents[0];
		    $currentBrowser['version'] = $contents[1];
		}
		elseif(eregi("netpositive", $currentBrowser['agent']))
		{
			$currentBrowser['allowed'] = false;
		    $contents = explode("/",stristr($currentBrowser['agent'],"NetPositive"));
		    $currentBrowser['platform'] = "BeOS";
		    $currentBrowser['browser'] = $contents[0];
		    $currentBrowser['version'] = $contents[1];
		}
		elseif(eregi("omniweb",$currentBrowser['agent']))
		{
			$currentBrowser['allowed'] = false;
		    $contents = explode("/",stristr($currentBrowser['agent'],"omniweb"));
		    $currentBrowser['browser'] = $contents[0];
		    $currentBrowser['version'] = $contents[1];
		}
		elseif(eregi("microsoft internet explorer", $currentBrowser['agent']))
		{
			$currentBrowser['allowed'] = false;
		    $currentBrowser['browser'] = "MSIE";
		    $currentBrowser['version'] = "1.0";
		    $var = stristr($currentBrowser['agent'], "/");
		    if (ereg("308|425|426|474|0b1", $var))
		        $currentBrowser['version'] = "1.5";
		}
		elseif(eregi("galeon",$currentBrowser['agent']))
		{
			$currentBrowser['allowed'] = false;
		    $contents = explode(" ",stristr($currentBrowser['agent'],"galeon"));
		    $contents = explode("/",$contents[0]);
		    $currentBrowser['browser'] = $contents[0];
		    $currentBrowser['version'] = $contents[1];
		}
	
		elseif(eregi("mspie",$currentBrowser['agent']) || eregi('pocket', $currentBrowser['agent']))
		{
			$currentBrowser['allowed'] = false;
		    $contents = explode(" ",stristr($currentBrowser['agent'],"mspie"));
		    $currentBrowser['browser'] = "MSPIE";
		    $currentBrowser['platform'] = "WindowsCE";
		    if (eregi("mspie", $currentBrowser['agent']))
		        $currentBrowser['version'] = $contents[1];
		    else
			{
		        $contents = explode("/",$currentBrowser['agent']);
		        $currentBrowser['version'] = $contents[1];
		    }
		}
		elseif(eregi("konqueror",$currentBrowser['agent']))
		{
		    $contents = explode(" ",stristr($currentBrowser['agent'],"Konqueror"));
		    $contents = explode("/",$contents[0]);
		    $currentBrowser['browser'] = $contents[0];
		    $currentBrowser['version'] = $contents[1];
			$currentBrowser['allowed'] = ($currentBrowser['version'] >= 3.5);
		}
		elseif(eregi("icab",$currentBrowser['agent']))
		{
			$currentBrowser['allowed'] = false;
		    $contents = explode(" ",stristr($currentBrowser['agent'],"icab"));
		    $currentBrowser['browser'] = $contents[0];
		    $currentBrowser['version'] = $contents[1];
		}
		elseif(eregi("phoenix", $currentBrowser['agent']))
		{
			$currentBrowser['allowed'] = true;
		    $currentBrowser['browser'] = "Phoenix";
		    $contents = explode("/", stristr($currentBrowser['agent'],"Phoenix/"));
		    $currentBrowser['version'] = $contents[1];
		}
		elseif(eregi("firebird", $currentBrowser['agent']))
		{
			$currentBrowser['allowed'] = true;
		    $currentBrowser['browser']="Firebird";
		    $contents = stristr($currentBrowser['agent'], "Firebird");
		    $contents = explode("/",$contents);
		    $currentBrowser['version'] = $contents[1];
		}
		elseif(eregi("mozilla",$currentBrowser['agent']) && eregi("rv:[0-9].[0-9][a-b]",$currentBrowser['agent']) && !eregi("netscape",$currentBrowser['agent']))
		{
		    $currentBrowser['browser'] = "Mozilla";
		    $contents = explode(" ",stristr($currentBrowser['agent'],"rv:"));
		    eregi("rv:[0-9].[0-9][a-b]",$currentBrowser['agent'],$contents);
		    $currentBrowser['version'] = str_replace("rv:","",$contents[0]);
			$currentBrowser['allowed'] = ($currentBrowser['version'] >= 1.7);
		}
		elseif(eregi("mozilla",$currentBrowser['agent']) && eregi("rv:[0-9]\.[0-9]",$currentBrowser['agent']) && !eregi("netscape",$currentBrowser['agent']))
		{
		    $currentBrowser['browser'] = "Mozilla";
		    $contents = explode(" ",stristr($currentBrowser['agent'],"rv:"));
		    eregi("rv:[0-9]\.[0-9]\.[0-9]",$currentBrowser['agent'],$contents);
		    $currentBrowser['version'] = str_replace("rv:","",$contents[0]);
			$currentBrowser['allowed'] = ($currentBrowser['version'] >= 1.7);
		}
		elseif(eregi("libwww", $currentBrowser['agent']))
		{
			$currentBrowser['allowed'] = false;
		    if (eregi("amaya", $currentBrowser['agent']))
			{
		        $contents = explode("/",stristr($currentBrowser['agent'],"amaya"));
		        $currentBrowser['browser'] = "Amaya";
		        $contents = explode(" ", $contents[1]);
		        $currentBrowser['version'] = $contents[0];
		    }
			else
			{
		        $contents = explode("/",$currentBrowser['agent']);
		        $currentBrowser['browser'] = "Lynx";
		        $currentBrowser['version'] = $contents[1];
		    }
		}
		elseif(eregi("netscape",$currentBrowser['agent']))
		{
		    $contents = explode(" ",stristr($currentBrowser['agent'],"netscape"));
		    $contents = explode("/",$contents[0]);
		    $currentBrowser['browser'] = $contents[0];
		    $currentBrowser['version'] = $contents[1];
			$currentBrowser['allowed'] = ($currentBrowser['version'] >= 7);
		}
		elseif(eregi("mozilla",$currentBrowser['agent']) && !eregi("rv:[0-9]\.[0-9]\.[0-9]",$currentBrowser['agent']))
		{
		    $contents = explode(" ",stristr($currentBrowser['agent'],"mozilla"));
		    $contents = explode("/",$contents[0]);
			if(count($contents) > 1)
			{
		   		$currentBrowser['browser'] = "Netscape";
		    	$currentBrowser['version'] = $contents[1];
				$currentBrowser['allowed'] = ($currentBrowser['version'] >= 7);
			}
			else
				$currentBrowser['allowed'] = false;
		}
	}
	return $currentBrowser;
}
?>