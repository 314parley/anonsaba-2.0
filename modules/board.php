<?php
class BoardCore {
	var $board = array();
	public function Board($board, $extra = true) {
		global $db, $CURRENTLOCALE;
			if ($board != '') {
			$results = $db->GetAll('SELECT * FROM `'.prefix.'boards` WHERE `name` = '.$db->quote($board).' LIMIT 1');
			foreach ($results[0] as $key=>$line) {
				if (!is_numeric($key)) {
					$this->board[$key] = $line;
				}
			}
			if ($extra) {
				//$this->board['boardlist'] = $this->DisplayBoardList(); #COME BACK
				// Get the unique posts for this board
				$this->board['uniqueposts']   = $db->GetOne('SELECT COUNT(DISTINCT `ipid`) FROM `'.prefix.'posts` WHERE `boardname` = '.$db->quote($this->board['name']).' AND  `deleted` = 0');
				$this->board['filetypes_allowed'] = $db->GetAll('SELECT '.prefix.'filetypes.name FROM '.prefix.'boards, '.prefix.'filetypes, '.prefix.'board_filetypes WHERE '.prefix.'boards.id = '.$this->board['id'].' AND '.prefix.'board_filetypes.boardid = '.$this->board['id'].' AND '.prefix.'board_filetypes.fileid = '.prefix.'filetypes.id ORDER BY '.prefix.'filetypes.name DESC;');
			}
		}
	}
	public function RefreshAll() {
		self::RefreshPages();
		self::RefreshThreads();
	}
	public function microtime_float() {
		list($utime, $time) = explode(" ", microtime());
		return ((float)$utime + (float)$time);
	}
	public function RefreshPages() {
		global $db, $twig, $twig_data, $CURRENTLOCALE;
		$this->board['filetypes'] = array();
		$twig_data['filetypes'] = $this->board['filetypes'];
		$maxpages = $this->board['boardpages'];
		$postperpage = $this->board['postperpage'];
		$i = 0;
		$liststooutput = 0;
		$p = $db->GetOne('SELECT COUNT(*) FROM `'.prefix.'posts` WHERE `boardname` = '.$db->quote($this->board['name']).' AND `parent` = 0 AND `deleted` = 0 ORDER BY `bumped` DESC');
		$totalpages = floor($p/$postperpage);
		if ($totalpages < 1) {
			$totalpages = 0;
		}
		$twig_data['numpages'] = $totalpages;
		$files = $db->GetAll('SELECT * FROM `'.prefix.'files` WHERE `board` = '.$db->quote($this->board['name']));
		while ($i <= $totalpages) {
			$newposts = Array();
			$twig_data['thispage'] = $i;
			$executiontime_start_page = $this->microtime_float();
			$stuff = $db->GetAll('SELECT * FROM `'.prefix.'posts` WHERE `boardname` = '.$db->quote($this->board['name']).' AND `parent` = 0 AND `deleted` = 0 ORDER BY `sticky` DESC, `bumped` DESC LIMIT '.$this->board['postperpage'].' OFFSET '. $this->board['postperpage'] * $i);
			foreach ($stuff as $k=>$thread) {
				if ($thread['deleted_time'] == 0 && $this->board['markpage'] > 0 && $i >= $this->board['markpage']) {
					$db->Execute('UPDATE `'.prefix.'posts` SET `deleted_time` = \'' . (time() + 7200) . '\' WHERE `boardname` = '.$db->quote($this->board['name']).' AND `id` = \'' . $thread['id'] . '\'');
					//$db->Execute('DELETE FROM `'.prefix.'reports` WHERE `postid` = '.$thread['id']. " AND `board` = " . $db->quote($this->board['name']));
					self::RefreshThreads($thread['id']);
					$twig_data['replythread'] = 0;
				}
				$thread = self::BuildPosts($thread, true);
				$omitids = '""';
				$stickied = $db->GetOne('SELECT COUNT(*) FROM `'.prefix.'posts` WHERE `boardname` = '.$db->quote($this->board['name']).' AND `id` = '.$thread['id'].' AND `deleted` = 0 AND `sticky` = 1 ORDER BY `id` DESC');
				if ($stickied > 0) {
					$limit = 1;
				} else {
					$limit = 3;
				}
				$posts = $db->GetAll('SELECT * FROM `'.prefix.'posts` WHERE `boardname` = '.$db->quote($this->board['name']).' AND `parent` = '.$thread['id'].' AND `deleted` = 0 ORDER BY `id` DESC LIMIT '.$limit);
				foreach ($posts as $key=>$post) {
					$omitids .= ', '.$post['id'];
					$posts[$key] = self::BuildPosts($post, true);
				}
				$posts = array_reverse($posts);
				array_unshift($posts, $thread);
				$newposts[] = $posts;
				unset($posts);
				$posts = $db->GetOne('SELECT COUNT(*) FROM `'.prefix.'posts` WHERE `boardname` = '.$db->quote($this->board['name']).' AND `parent` = '.$thread['id'].' AND `deleted` = 0');
				$replycount['replies'] = $posts;
				$newposts[$k][0]['replies'] = (isset($replycount['replies']) ? $replycount['replies'] : '');
			}
			if (!isset($header)){
				$header = str_replace('<!sm_threadid>', 0, self::PageHeader());
			}
			if (!isset($postbox)) {
				$postbox = str_replace('<!sm_threadid>', 0, self::Postbox());
			}
			$twig_data['posts'] = $newposts;
			$twig_data['files'] = $files;
			$twig_data['timgh'] = AnonsabaCore::GetConfigOption('timgh');
			$twig_data['timgw'] = AnonsabaCore::GetConfigOption('timgw');
			$twig_data['rimgh'] = AnonsabaCore::GetConfigOption('rimgh');
			$twig_data['rimgw'] = AnonsabaCore::GetConfigOption('rimgw');
			$content = $header.$postbox.$twig->render('/board/board_page.tpl', $twig_data).self::Footer($this->microtime_float() - $executiontime_start_page);
			$content = str_replace('\t', '',$content);
			$content = str_replace('&nbsp;\r\n', '&nbsp;',$content);
			if ($i == 0) {
				self::PrintPage(fullpath.$this->board['name'].'/board.html', $content, $this->board['name']);
			} else {
				self::PrintPage(fullpath.$this->board['name'].'/'.$i.'.html', $content, $this->board['name']);
			}
			$i++;
		}
		// Delete old pages
		$dir = fullpath.$this->board['name'];
		$files = glob($dir.'/*.html');
		if (is_array($files)) {
			foreach ($files as $htmlfile) {
				if (preg_match('/[0-9+].html/', $htmlfile)) {
					if (substr(basename($htmlfile), 0, strpos(basename($htmlfile), '.html'))>$totalpages) {
						@unlink($htmlfile);
					}
				}
				if (preg_match('/list[0-9+].html/', $htmlfile)) {
					if (substr(basename($htmlfile), 4, strpos(basename($htmlfile), '.html'))>($liststooutput+1)) {
						@unlink($htmlfile);
					}
				}
			}
		}
	}
	public function RefreshThreads($id = 0) {
		global $db, $twig, $twig_data, $CURRENTLOCALE;
		$numimages = 0;
		if ($id == 0) {
			$header = self::PageHeader(1);
			$postbox = self::Postbox(1);
			$threads = $db->GetAll('SELECT * FROM `'.prefix.'posts` WHERE `boardname` = '.$db->quote($this->board['name']).' AND `parent` = 0 AND `deleted` = 0 ORDER BY `bumped` DESC');
			if (count($threads) > 0) {
				foreach($threads as $thread) {
					$executiontime_start_thread = $this->microtime_float();
					$posts = $db->GetAll('SELECT * FROM `'.prefix.'posts` WHERE `boardname` = '.$db->quote($this->board['name']).' AND (`id` = '. $thread['id']. ' OR `parent` = ' . $thread['id'] . ') AND `deleted` = 0 ORDER BY `time` ASC');
					$files = $db->GetAll('SELECT * FROM `'.prefix.'files` WHERE `board` = '.$db->quote($this->board['name']));
					if (isset($posts[0]['deleted']) && $posts[0]['deleted'] == 0) { 
						// There might be a chance that the post was deleted during another RegenerateThreads() session, if there are no posts, move on to the next thread.
						if(count($posts) > 0) {
							foreach ($posts as $key=>$post) {
								$posts[$key] = self::BuildPosts($post, false);
							}
							$header_replaced = str_replace("<!sm_threadid>", $thread['id'], $header);
							$twig_data['replythread'] = $thread['id'];
							$twig_data['posts'] = $posts;
							$twig_data['files'] = $files;
							$twig_data['timgh'] = AnonsabaCore::GetConfigOption('timgh');
							$twig_data['timgw'] = AnonsabaCore::GetConfigOption('timgw');
							$twig_data['rimgh'] = AnonsabaCore::GetConfigOption('rimgh');
							$twig_data['rimgw'] = AnonsabaCore::GetConfigOption('rimgw');
							$twig_data['postcount'] = count($posts);
							$postbox_replaced = str_replace("<!sm_threadid>", $thread['id'], $postbox);
							$reply	 = $twig->render('/board/reply_header.tpl', $twig_data);
							$content = $twig->render('/board/thread.tpl', $twig_data);
							if (!isset($footer)) { 
								$footer = self::Footer($this->microtime_float() - $executiontime_start_thread);
							}
							$content = $header_replaced.$reply.$postbox_replaced.$content.$footer;
							$content = str_replace("\t", '',$content);
							$content = str_replace("&nbsp;\r\n", '&nbsp;',$content);
							self::PrintPage(fullpath.$this->board['name'].'/res/' . $thread['id'] . '.html', $content, $this->board['name']);
						}
					}
				}
			}
		} else {
			$executiontime_start_thread = $this->microtime_float();
			// Build only that thread
			$thread = $db->GetAll('SELECT * FROM `'.prefix.'posts` WHERE `boardname` = '.$this->board['name'].' AND (`id` = '.$id.' OR `parent` = '.$id.') `deleted` = 0 ORDER BY `time`');
			$files = $db->GetAll('SELECT * FROM `'.prefix.'files` WHERE `id` = '.$id.' AND `board` = '.$db->quote($this->board['name']));
			if (isset($thread[0]['deleted']) && $thread[0]['deleted'] == 0) { 
				foreach ($thread as $key=>$post) {
					$thread[$key] = self::BuildPosts($post, false);
				}
				$header = self::PageHeader($id);
				$postbox = self::Postbox($id);
				$header = str_replace("<!sm_threadid>", $id, $header);
				$twig_data['replythread'] = $id;
				$postbox = str_replace("<!sm_threadid>", $id, $postbox);
				$twig_data['threadid'] = $thread[0]['id'];
				$twig_data['posts'] = $thread;	
				$twig_date['files'] = $files;
				$twig_data['timgh'] = AnonsabaCore::GetConfigOption('timgh');
				$twig_data['timgw'] = AnonsabaCore::GetConfigOption('timgw');
				$twig_data['rimgh'] = AnonsabaCore::GetConfigOption('rimgh');
				$twig_data['rimgw'] = AnonsabaCore::GetConfigOption('rimgw');			
				$postbox = $twig->render('/board/reply_header.tpl', $twig_data).$postbox;
				$content = $twig->render('/board/thread.tpl', $twig_data);
				
				if (!isset($footer)) { 
					$footer = self::Footer($this->microtime_float() - $executiontime_start_thread);
				}
				$content = $header.$postbox.$content.$footer;
				$content = str_replace("\t", '',$content);
				$content = str_replace("&nbsp;\r\n", '&nbsp;',$content);
				self::PrintPage(fullpath.$this->board['name'] . '/res/' . $id . '.html', $content, $this->board['name']);
			}
		}
	}
	public function BuildPosts($post, $page) {
		global $CURRENTLOCALE, $db;
		$post['reflink'] = AnonsabaCore::formatReflink($this->board['name'], (($post['parent'] == 0) ? ($post['id']) : ($post['parent'])), $post['id']);
		return $post;
	}
	public function PageHeader($replythread = '0', $liststart = '0', $liststooutput = '-1') {
		global $db, $twig, $twig_data, $CURRENTLOCALE;
		$html = array();
		$html['title'] = '';
		$twig_data['url'] = url;
		$twig_data['board'] = $this->board;
		$twig_data['replythread'] = $replythread;
		$twig_data['section'] = $db->GetAll('SELECT * FROM `'.prefix.'sections`');
		$twig_data['sfwads'] = $db->GetAll('SELECT * FROM `'.prefix.'ads` WHERE `type` = "sfw"');
		$twig_data['nsfwads'] = $db->GetAll('SELECT * FROM `'.prefix.'ads` WHERE `type` = "nsfw"');
		$output = '';
		$results = $db->GetAll('SELECT `name` FROM `'.prefix.'sections` ORDER BY `order` ASC');
		$boards = array();
		foreach($results as $line) {
			$results2 = $db->GetAll('SELECT * FROM `'.prefix.'boards` WHERE `section` = '.$db->quote($line['name']).' ORDER BY `name` ASC');
			foreach($results2 as $line2) {
				$boards[$line['name']][$line2['name']]['name'] = htmlspecialchars($line2['name']);
				$boards[$line['name']][$line2['name']]['desc'] = htmlspecialchars($line2['desc']);
			}
		}
		$twig_data['boards'] = $boards;
		$header = $twig->render('/board/header.tpl', $twig_data);
		return $header;
	}
	public function Postbox($replythread = 0) {
		global $db, $twig, $twig_data;
		$postbox = '';
		if ($this->board['captcha'] ==  1) {
			require_once(KU_ROOTDIR.'recaptchalib.php');
			$publickey = "6LfOL9MSAAAAAKqvyC66AknJ0gcWuMlVJC33vRwt";
			$twig_data['recaptcha'] = recaptcha_get_html($publickey);
		}
		$twig_data['fullpath'] = url;
		$twig_data['mod'] = $_COOKIE['mod'];
		$postbox .= $twig->render('/board/post_box.tpl', $twig_data);
		return $postbox;
	}
	public function Footer($executiontime) {
		global $db, $twig, $twig_data;
		$footer = '';
		$twig_data['version'] = AnonsabaCore::GetConfigOption('version');
		$twig_data['sitename'] = AnonsabaCore::GetConfigOption('sitename');
		if ($executiontime != ''){ 
			$twig_data['executiontime'] = round($executiontime, 4);
		}
		$footer = $twig->render('/board/footer.tpl', $twig_data);
		return $footer;
	}
	public function PrintPage($filename, $contents, $board) {
		if ($board !== true) {
			AnonsabaCore::print_page($filename, $contents, $board);
		} else {
			echo $contents;
		}
	}
}
