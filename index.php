<?php
//Anonsaba 2.0 Main page
	require './config/config.php';
	require './modules/anonsaba.php';
	//Is Anonsaba even installed?
	if (!file_exists(fullpath.'.installed')) {
		die('It appears you haven\'t installed Anonsaba 2.0 please click <a href="/install/install.php">here</a> to install!');
	}
	$twig_data['sitename'] = AnonsabaCore::GetConfigOption('sitename');
	$twig_data['slogan'] = AnonsabaCore::GetConfigOption('slogan');
	$twig_data['version'] = AnonsabaCore::GetConfigOption('version');
	$twig_data['irc'] = AnonsabaCore::GetConfigOption('irc');
	//DONT TOUCH
	isset($_GET['view']) ? $_GET['view'] : '';
	if ($_GET['view'] == '') {
		$entries = $db->GetAll("SELECT * FROM `" . prefix . "front` WHERE `type` = 'news' ORDER BY `date` DESC LIMIT 5 OFFSET ".($_GET['page'] * 5));
	} elseif ($_GET['view'] == 'faq') {
		$entries = $db->GetAll("SELECT * FROM `" . prefix . "front` WHERE `type` = 'faq' ORDER BY `date` DESC");
	} elseif ($_GET['view'] == 'rules') {
		$entries = $db->GetAll("SELECT * FROM `" . prefix . "front` WHERE `type` = 'rules' ORDER BY `date` ASC");
	}
	$pages = $db->GetOne("SELECT COUNT(*) FROM `" .prefix. "front` WHERE `type` = 'news'");
	$sections = array();
	$results_boardsexist = $db->GetAll("SELECT `id` FROM `".prefix."boards` LIMIT 1");
	if (count($results_boardsexist) >= 0) {
		$sections = $db->GetAll("SELECT * FROM `" .prefix. "sections` ORDER BY `order` ASC");
		foreach($sections AS $key=>$section) {
			$results = $db->GetAll("SELECT * FROM `" .prefix. "boards` WHERE `section` = '" . $section['name'] . "' ORDER BY `name` ASC");
			foreach($results AS $line) {
				$sections[$key]['boards'][] = $line;
			}
		}
	}
	$query1 = $db->GetAll('SELECT * FROM `'.prefix.'boards` WHERE `recentpost` = 0');
	$board = array();
	foreach ($query1 as $line) {
		$board[] = $line['name'];
	}
	$newboard = implode(', ', $board);
	if ($query1) {
		$execute = 'AND `boardname` NOT IN ('.$db->quote($newboard).') ';
	} else {
		$execute = '';
	}
	$results = $db->GetAll('SELECT * FROM `'.prefix.'boards` ORDER BY `name` ASC');
	foreach ($results as $line) {
		$total = AnonsabaCore::GetSize(fullpath.$line['name']);
	}
	$twig_data['content'] = $total;
	$twig_data['recentpost'] = $db->GetAll('SELECT * FROM `'.prefix.'posts` WHERE `deleted` = 0 '.$execute.'ORDER BY `time` DESC LIMIT 5');
	$twig_data['currentusers'] = $db->GetOne('SELECT COUNT(DISTINCT `ipid`) FROM `'.prefix.'posts` WHERE `deleted` = 0');
	$twig_data['entries'] = $entries;
	$twig_data['view'] = $_GET['view'];
	$twig_data['boards'] = $sections;
	$twig_data['totalposts'] = $db->GetOne('SELECT COUNT(*) FROM `'.prefix.'posts` WHERE `deleted` = 0 '.$execute);
	$twig_data['pages'] = ($pages/5);
	AnonsabaCore::Output('/index.tpl', $twig_data);

