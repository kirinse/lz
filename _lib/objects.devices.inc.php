<?php
/****************************************************************************************
* LiveZilla objects.devices.inc.php
* 
* Copyright 2011 LiveZilla GmbH
* All rights reserved.
* LiveZilla is a registered trademark.
* 
* Improper changes to this file may cause critical errors.
***************************************************************************************/ 


class DeviceDetector
{
	public $Browser;
	public $OperatingSystem;
	public $AgentType = AGENT_TYPE_UNKNOWN;
	public $BrowserVersionUnknown;
	public $OperatingSystemVersionUnknown;
	public $OperatingSystemUnknown;
	
	function DeviceDetector()
	{
	
	
	}
	
	function DetectOperatingSystem()
	{
		$OSYSTEMS = array(
		"debian"=>"Debian",
		"freebsd"=>"Free BSD",
		"iphoneos%"=>"IPhone %",
		"iphoneos%"=>"IPhone %",
		"blackberry%"=>"Blackberry %",
		"ipad"=>"IPad",
		"macosx100"=>"MAC OS X 10.0 (Cheetah)",
		"macosx101"=>"MAC OS X 10.1 (Puma)",
		"macosx102"=>"MAC OS X 10.2 (Jaguar)",
		"macosx103"=>"MAC OS X 10.3 (Panther)",
		"macosx104"=>"MAC OS X 10.4 (Tiger)",
		"macosx105"=>"MAC OS X 10.5 (Leopard)",
		"macosx106"=>"MAC OS X 10.6 (Snow Leopard)",
		"macos%"=>"Mac OS %",
		"ubuntu"=>"Ubuntu (Linux)",
		"windows98"=>"Windows 98",
		"windowsme"=>"Windows ME",
		"windowsnt31"=>"Windows NT 3.1",
		"windowsnt35"=>"Windows NT 3.5",
		"windowsnt351"=>"Windows NT 3.51",
		"windowsnt40"=>"Windows NT 4.0",
		"windowsnt50"=>"Windows 2000",
		"windowsnt51"=>"Windows XP",
		"windowsnt52"=>"Windows XP",
		"windowsxp"=>"Windows XP",
		"windowsnt60"=>"Windows Vista",
		"windowsnt61"=>"Windows 7",
		"mediacenterpc%"=>"Windows Media Center %",
		"fedora%"=>"Fedora %",
		"symbianos%"=>"Symbian %",
		"dangerhiptop%"=>"Danger Hiptop %",
		"linux"=>"Linux",
		"windows"=>"Windows CE / Mobile",
		"nintendodsi"=>"Nintendo DSi",
		"nexiannxg911"=>"Nexian NX-G911",
		"lglg"=>"LG Smartphone",
		"samsung"=>"Samsung Smartphone",
		"htc"=>"HTC Smartphone",
		"nokia"=>"Nokia Smartphone",
		"sony"=>"Sony Smartphone",
		"playstation"=>"Sony PlayStation",
		"playstationport"=>"PlayStation Portable"
		);
		
		$MOBILEDEVICES = array(
		"midp"
		);
	
		$this->OperatingSystemUnknown = true;
		$os = (!empty($_SERVER["HTTP_USER_AGENT"])) ? str_replace(array("."," ","/","_","-","["),array("","","","","",""),strtolower($_SERVER["HTTP_USER_AGENT"])) : "";
		$lists = array($OSYSTEMS,$MOBILEDEVICES);
		foreach($lists as $index => $list)
		{
			foreach($list as $oskey => $osname)
			{
				$fixkey = str_replace("%","",$oskey);
				if(strpos($os,$fixkey) !== false)
				{
					if($index == 0)
					{
						$mversion = @substr($os,strpos($os,$fixkey)+strlen($fixkey),1);
						if(is_numeric($mversion) && $mversion > 0)
							$this->OperatingSystem = str_replace("%",$mversion,$osname);
						else
						{
							$this->OperatingSystem = str_replace(" %","",$osname);
							
							if(strpos($oskey,"%") !== false)
								$this->OperatingSystemVersionUnknown = true;
						}
					}
					else
						$this->OperatingSystem = "Unknown Smartphone";
					$this->OperatingSystemUnknown = false;
					return;
				}
			}
		}
	}
	
	function DetectBrowser()
	{
		$BROWSERS = array(
		"msie%"=>"Internet Explorer %",
		"firefox%"=>"FireFox %",
		"opera%"=>"Opera %",
		"chrome%"=>"Chrome %",
		"safari%"=>"Safari %",
		"applewebkit%"=>"Safari %",
		"netscape%"=>"Netscape %",
		"seamonkey%"=>"SeaMonkey %",
		"konqueror%"=>"Konqueror %",
		"iceweasel%"=>"Iceweasel %",
		"nokia"=>"Nokia Mini Map",
		"granparadiso"=>"Mozilla Gran Paradiso",
		"bolt%"=>"Bolt %",
		"lynx%"=>"Lynx %",
		"netfront%"=>"Netfront %",
		"ucbrowser%"=>"UC Browser %",
		"isilox%"=>"iSilo %",
		"amaya"=>"Amaya",
		"flock"=>"Flock",
		"novarravision%"=>"Novarra-Vision %",
		"galeon"=>"Galeon",
		"aol"=>"AOL Explorer",
		"omniweb"=>"OmniWeb",
		"icab"=>"iCab",
		"avant"=>"Avant",
		"kmeleon"=>"K-Meleon",
		"camino"=>"Camino",
		"maxthon"=>"Maxthon",
		"ucweb"=>"UCWEB Mobile",
		"polaris%"=>"Polaris %",
		"netpositive"=>"NetPositive",
		"lynx"=>"Lynx",
		"elinks"=>"eLinks",
		"dillo"=>"Dillo",
		"ibrowse"=>"iBrowse",
		"klondike"=>"Klondike WAP",
		"crazybrowser"=>"Crazy Browser",
		"mauiwapbrowser"=>"Maui WAP",
		"operamini"=>"Opera Mini",
		"thunderbrowse"=>"ThunderBrowse",
		"shiretoko"=>"FireFox 3",
		"namoroka"=>"FireFox 3",
		"minefield"=>"FireFox 4 (Minefield)",
		"bonecho"=>"FireFox 2 (BonEcho)",
		"blackberry"=>"BlackBerry Mobile",
		"jasmine%"=>"Jasmine Mobile V%",
		"waterfox%"=>"Waterfox %",
		"upbrowser%"=>"UP.Browser %",
		"teleca"=>"Teleca",
		"dolfin%"=>"Dolfin %",
		"obigo"=>"Obigo",
		"midori"=>"Midori"
		);
		
		$BOTS = array(
		"googlebot"=>"Google",
		"mediapartners-google"=>"Google adsense",
		"yahoo-verticalcrawler"=>"Yahoo",
		"yahoo!slurp"=>"Yahoo",
		"yahoo-mm"=>"Yahoo-MMCrawler / Yahoo-MMAudVid",
		"inktomi"=>"inktomi",
		"slurp"=>"inktomi",
		"fast-webcrawler"=>"Fast AllTheWeb",
		"msnbot"=>"MSN / Bing",
		"askjeeves"=>"Ask Jeeves",
		"teoma"=>"Ask Jeeves",
		"scooter"=>"Altavista",
		"openbot"=>"Openbot",
		"iaarchiver"=>"Alexa Crawler",
		"zyborg"=>"Looksmart",
		"almaden"=>"IBM",
		"baiduspider"=>"Baidu",
		"psbot"=>"PSBot",
		"gigabot"=>"Gigabot",
		"naverbot"=>"Naverbot",
		"surveybot"=>"Surveybot",
		"boithocom-dc"=> "Boitho",
		"answerbus"=>"answerbus.com",
		"sohu-search"=>"Sohu",
		"postrank"=>"Postrank",
		"mailru"=>"Mail.ru",
		"yandex"=>"Yandex",
		"alexacom"=>"Alexa.com",
		"twiceler"=>"Twiceler",
		"jakartacommons"=>"Jakarta Commons HttpClient Component",
		"youdaobot"=>"YoudaoBot",
		"comodo"=>"Comodo",
		"sogouwebspider"=>"Sogou",
		"sosospider"=>"Soso",
		"tineye"=>"Tineye",
		"jobsde"=>"Jobs.de",
		"kscrawler"=>"Kindsight",
		"twengabot"=>"Twenga",
		"zschobot"=>"Zscho",
		"sheenbot"=>"SheenBot",
		"speedyspider"=>"Entireweb",
		"spinn3r"=>"spinn3r News Crawler",
		"exabot"=>"Exalead",
		"nigmaru"=>"Nigma.ru",
		"isrccrawler"=>"Microsoft Research (MSR)",
		"heritrix"=>"Archive.org",
		"www80legscom"=>"80legs.com Web Crawler",
		"mj12bot"=>"Majestic-12",
		"thumbshotsbot"=>"ThumbShots",
		"jobkereso"=>"JobKereso",
		"jobroboterspider"=>"Job Roboter"
		);

		$MISC = array(
		"w3ccssvalidator"=>"W3C CSS Validator",
		"w3cvalidator"=>"W3C (X)HTML Validator",
		"wdgvalidator"=>"WDG Validator",
		"libwwwperl"=>"libwww-perl",
		"pythonurllib"=>"python-urllib",
		"pycurl"=>"Python PycURL",
		"nessus"=>"Nessus Client",
		"xenulinksleuth"=>"Xenu Link Sleuth",
		"livezilla"=>"LiveZilla",
		"httrack"=>"HTTrack",
		"webcopier"=>"WebCopier",
		"wget"=>"WGet",
		"teleportpro"=>"Teleport Pro"
		);
		
		$browser = (!empty($_SERVER["HTTP_USER_AGENT"])) ? str_replace(array('.',' ','/','_','-','['),array('','','','','',''),strtolower($_SERVER["HTTP_USER_AGENT"])) : "";
		$lists = array($BOTS,$BROWSERS,$MISC);
		foreach($lists as $index => $list)
		{
			foreach($list as $brkey => $brname)
			{
				$fixkey = str_replace("%","",$brkey);
				if(strpos($browser,$fixkey) !== false)
				{
					if($index==1)
					{
						$mversion = @substr($browser,strpos($browser,$fixkey)+strlen($fixkey),1);
						if(is_numeric($mversion) && $mversion > 0)
							$this->Browser = str_replace("%",$mversion,$brname);
						else
						{
							$this->Browser = str_replace(" %","",$brname);
							$this->BrowserVersionUnknown = true;
						}
					}
					else
					{
						$this->Browser = $brname;
						$this->BrowserVersionUnknown = true;
					}
					$this->AgentType=$index;
					return;
				}
			}
		}
	}
}
?>
