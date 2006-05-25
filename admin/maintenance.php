<?php
/**
 * MyBB 1.2
 * Copyright � 2006 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybboard.com
 * License: http://www.mybboard.com/eula.html
 *
 * $Id$
 */

require "./global.php";

// Load language packs for this section
global $lang;
$lang->load("maintenance");

checkadminpermissions("canrunmaint");
logadmin();

switch($mybb->input['action'])
{
	case "cache":
		addacpnav($lang->nav_cache_manager, "maintenance.php?action=cache");
		break;
	case "do_cache":
		if($view)
		{
			addacpnav($lang->cache_manager, "maintenance.php?action=cache");
			addacpnav($lang->nav_view_cache);
		}
		break;
	case "rebuildstats":
		addacpnav($lang->rebuildstats);
		break;
}

if($mybb->input['action'] == "do_cache")
{
	$cacheitem = $mybb->input['cacheitem'];
	if($mybb->input['view'])
	{
		cpheader();
		starttable();
		$query = $db->simple_select(TABLE_PREFIX."datacache", "*", "title='".$db->escape_string($cacheitem)."'");
		$cacheitem = $db->fetch_array($query);
		$cachecontents = unserialize($cacheitem['cache']);
		if(empty($cachecontents))
		{
			$cachecontents = $lang->cache_empty;
		}
		ob_start();
		print_r($cachecontents);
		$data = htmlspecialchars_uni(ob_get_contents());
		ob_end_clean();
		makelabelcode("<pre>$data</pre>", "");
		endtable();
		cpfooter();
	}
	if($mybb->input['refresh'])
	{
		if(method_exists($cache, "update$cacheitem"))
		{
			$func = "update$cacheitem";
			$cache->$func();
			cpredirect("maintenance.php?action=cache", $lang->cache_updated);
		}
		else
		{
			cpmessage($lang->nocache_update);
		}
	}
}

if($mybb->input['action'] == "cache")
{
	cpheader();
	starttable();
	tableheader($lang->cache_manager, "", "4");
	echo "<tr>\n";
	echo "<td class=\"subheader\">$lang->name</td>\n";
	echo "<td class=\"subheader\" align=\"center\">$lang->size</td>\n";
	echo "<td class=\"subheader\" align=\"center\" colspan=\"2\">$lang->options</td>\n";
	echo "</tr>\n";
	$query = $db->simple_select(TABLE_PREFIX."datacache", "title,cache");
	while($cacheitem = $db->fetch_array($query))
	{
		$size = getfriendlysize(strlen($cacheitem['cache']));
		$bgcolor = getaltbg();
		startform("maintenance.php", "", "do_cache");
		makehiddencode("cacheitem", $cacheitem['title']);
		echo "<tr>\n";
		echo "<td class=\"$bgcolor\" width=\"50%\">$cacheitem[title]</td>\n";
		echo "<td class=\"$bgcolor\" align=\"center\" width=\"15%\">$size</td>\n";
		echo "<td class=\"$bgcolor\" align=\"center\"><input type=\"submit\" name=\"view\" value=\"$lang->view_contents\" class=\"submitbutton\"></td>";
		if(method_exists($cache, "update".$cacheitem['title']))
		{
			echo "<td class=\"$bgcolor\" align=\"center\"><input type=\"submit\" name=\"refresh\" value=\"$lang->refresh_cache\" class=\"submitbutton\"></td>";
		}
		else
		{
			echo "<td class=\"$bgcolor\">&nbsp;</td>";
		}
		echo "</tr>\n";
		endform();
	}
	endtable();
	cpfooter();
}

if($mybb->input['action'] == "do_rebuildstats")
{
	$cache->updatestats();
	cpmessage($lang->stats_rebuilt);
}

if($mybb->input['action'] == "rebuildstats")
{
	cpheader();
	startform("maintenance.php", "" , "do_rebuildstats");
	starttable();
	tableheader($lang->rebuildstats);
	$button = makebuttoncode("rebuildstatssubmit", $lang->proceed);
	makelabelcode("<div align=\"center\">$lang->rebuildstats_notice<br /><br />$button</div>");
	endtable();
	endform();
	cpfooter();
}
?>