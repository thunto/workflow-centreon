<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

Adapted to Pear library Quickform & Template_PHPLIB by Merethis company, under direction of Cedrick Facon

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
	if (!isset($oreon))
		exit();
			

	# Path to the configuration dir
	$path = "./menu/";
	require_once ("./include/common/common-Func.php");

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# Var init
	$sep = NULL;
	$elemArr = array(1=>array(), 2=>array(), 3=>array());

	# Special Case
	# Put the authentification in the URL
	$auth = NULL;

	# block headerHTML
	$lca =& $oreon->user->lcaHStrName;
	$version = $oreon->user->get_version();

	$fileStatus = $oreon->Nagioscfg["status_file"];
	$fileOreonConf = $oreon->optGen["oreon_path"];

/*
	$color["OK"] = " style='background:" . $oreon->optGen["color_ok"] . "'";
	$color["CRITICAL"] = " style='background:" . $oreon->optGen["color_critical"] . "'";
	$color["WARNING"] = " style='background:" . $oreon->optGen["color_warning"] . "'";
	$color["PENDING"] = " style='background:" . $oreon->optGen["color_pending"] . "'";
	$color["UNKNOWN"] = " style='background:" . $oreon->optGen["color_unknown"] . "'";
	$color["UP"] = " style='background:" . $oreon->optGen["color_up"] . "'";
	$color["DOWN"] = " style='background:" . $oreon->optGen["color_down"] . "'";
	$color["UNREACHABLE"] = " style='background:" . $oreon->optGen["color_unreachable"] . "'";
*/

	$color["OK"] = $oreon->optGen["color_ok"] . "'";
	$color["CRITICAL"] = $oreon->optGen["color_critical"] . "'";
	$color["WARNING"] = $oreon->optGen["color_warning"] . "'";
	$color["PENDING"] =  $oreon->optGen["color_pending"] . "'";
	$color["UNKNOWN"] =  $oreon->optGen["color_unknown"] . "'";
	$color["UP"] =  $oreon->optGen["color_up"] . "'";
	$color["DOWN"] =  $oreon->optGen["color_down"] . "'";
	$color["UNREACHABLE"] =  $oreon->optGen["color_unreachable"] . "'";

	$tpl->assign("urlLogo", $skin.'Images/logo_oreon.gif');
	$tpl->assign("lang", $lang);
	$tpl->assign("color", $color);
	$tpl->assign("lca", $lca);
	$tpl->assign("version", $version);
	$tpl->assign("fileStatus", $fileStatus);
	$tpl->assign("fileOreonConf", $fileOreonConf);
	$tpl->assign("date_time_format_status", $lang["date_time_format_status"]);





	# Grab elements for level 1
	$rq = "SELECT * FROM topology WHERE topology_parent IS NULL AND topology_id IN (".$oreon->user->lcaTStr.") AND topology_show = '1' ORDER BY topology_order";
	$res =& $pearDB->query($rq);

	if (PEAR::isError($pearDB)) {
		print "Mysql Error : ".$pearDB->getMessage();
	}
	
	for($i = 0; $res->numRows() && $res->fetchInto($elem);)	{
		$elemArr[1][$i] = array("Menu1ClassImg" => $level1 == $elem["topology_page"] ? "menu1_bgimg" : NULL,
								"Menu1Url" => "oreon.php?p=".$elem["topology_page"].$elem["topology_url_opt"],
								"Menu1Name" => array_key_exists($elem["topology_name"], $lang) ? $lang[$elem["topology_name"]] : "#UNDEF#");
		$i++;
	}

	$userUrl = "oreon.php?p=50104&o=c";
	$userName = $oreon->user->get_name();
    $userName .= " ( ";
    $userName .= $oreon->user->get_alias();
    $userName .= " ) ";
    $logDate= date($lang['header_format']);
    $logOut= $lang['m_logout'];
    $logOutUrl= "index.php?disconnect=1";

	# Grab elements for level 2
	$rq = "SELECT * FROM topology WHERE topology_parent = '".$level1."' AND topology_id IN (".$oreon->user->lcaTStr.") AND topology_show = '1'  ORDER BY topology_order";
	$res2 =& $pearDB->query($rq);
	if (PEAR::isError($pearDB)) {
		print "Mysql Error : ".$pearDB->getMessage();
	}
	
	$firstP = NULL;
	$sep = "&nbsp;";
	for($i = 0; $res2->numRows() && $res2->fetchInto($elem); $i++)	{
		$elem["topology_url"] == "./ext/osm/osm_jnlp.php" ? $auth = "?al=".md5($oreon->user->get_alias())."&pwd=".$oreon->user->get_passwd() : $auth = NULL;
		$firstP ? null : $firstP = $elem["topology_page"];
	    $elemArr[2][$i] = array("Menu2Sep" => $sep,
								"Menu2Url" => "oreon.php?p=".$elem["topology_page"].$elem["topology_url_opt"],
								"Menu2UrlPopup" => $elem["topology_popup"],
								"Menu2UrlPopupOpen" => $elem["topology_url"].$auth,
								"Menu2Name" => array_key_exists($elem["topology_name"], $lang) ? $lang[$elem["topology_name"]] : "#UNDEF#",
								"Menu2Popup" => $elem["topology_popup"] ? true : false);
		$sep = "|";
	}

	# Grab elements for level 3
	$rq = "SELECT * FROM topology WHERE topology_parent = '".($level2 ? $level1.$level2 : $firstP)."' AND topology_id IN (".$oreon->user->lcaTStr.") AND topology_show = '1' ORDER BY topology_order";

	$res3 =& $pearDB->query($rq);
	if (PEAR::isError($pearDB)) {
		print "Mysql Error : ".$pearDB->getMessage();
	}
	
	for($i = 0; $res3->fetchInto($elem);)	{
		if (!$oreon->optGen["perfparse_installed"] && ($elem["topology_page"] == 60204 || $elem["topology_page"] == 60405 || $elem["topology_page"] == 60505 || $elem["topology_page"] == 20206 || $elem["topology_page"] == 40201 || $elem["topology_page"] == 40202 || $elem["topology_page"] == 60603))
			;
		else	{
		    $elemArr[3][$elem["topology_group"]][$i] = array("Menu3Icone" => $elem["topology_icone"],
									"Menu3Url" => "oreon.php?p=".$elem["topology_page"].$elem["topology_url_opt"],
									"Menu3UrlPopup" => $elem["topology_url"],
									"Menu3Name" => array_key_exists($elem["topology_name"], $lang) ? $lang[$elem["topology_name"]] : "#UNDEF#",
									"Menu3Popup" => $elem["topology_popup"] ? true : false);
			 $i++;
		}
	}





	# Create Menu Level 1-2-3
	$tpl->assign("Menu1Color", "menu_1");
	$tpl->assign("Menu1ID", "menu1_bgcolor");

	$tpl->assign("UserInfoUrl", $userUrl);
	$tpl->assign("UserName", $oreon->user->get_alias());
	$tpl->assign("Date", $logDate);
	$tpl->assign("LogOut", $logOut);
	$tpl->assign("LogOutUrl", $logOutUrl);
	$tpl->assign("Menu2Color", "menu_2");
	$tpl->assign("Menu2ID", "menu2_bgcolor");
	$tpl->assign("Menu3Color", "menu_3");
	$tpl->assign("Menu3ID", "menu3_bgcolor");
	$tpl->assign("connected_users", $lang["m_connected_users"]);
	$tpl->assign("main_menu", $lang["m_main_menu"]);

	# Assign for Smarty Template
	$tpl->assign("elemArr1", $elemArr[1]);
	count($elemArr[2]) ? $tpl->assign("elemArr2", $elemArr[2]) : NULL;
	count($elemArr[3]) ? $tpl->assign("elemArr3", $elemArr[3]) : NULL;

	# Legend icon
	$tpl->assign("legend1", $lang['m_help']);
	$tpl->assign("legend2", $lang['lgd_legend']);


	# User Online	
	$tab_user = array();
	$res =& $pearDB->query("SELECT session.session_id, contact.contact_alias, contact.contact_admin, session.user_id, session.ip_address FROM session, contact WHERE contact.contact_id = session.user_id");
	if (PEAR::isError($pearDB)) {
		print "Mysql Error : ".$pearDB->getMessage();
	}
	while ($res->fetchInto($session)){
		$tab_user[$session["user_id"]] = array();
		$tab_user[$session["user_id"]]["ip"] = $session["ip_address"];
		$tab_user[$session["user_id"]]["id"] = $session["user_id"];
		$tab_user[$session["user_id"]]["alias"] = $session["contact_alias"];
		$tab_user[$session["user_id"]]["admin"] = $session["contact_admin"];
	}
	
	$tpl->assign("tab_user", $tab_user);

	# Display
	$tpl->display("BlockHeader.ihtml");
	$tpl->display("BlockMenuType1.ihtml");
	count($elemArr[2]) ? $tpl->display("BlockMenuType2.ihtml") : NULL;
	count($elemArr[3]) ? $tpl->display("BlockMenuType3.ihtml") : print '<div id="contener"><!-- begin contener --><table id="Tcontener"><tr><td id="Tmainpage" class="TcTD">';
	
?>