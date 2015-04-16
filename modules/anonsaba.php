<?php
class AnonsabaCore {
//Anonsaba 2.0 core public static functions.
	public static function Encrypt($val) {
		$salt = salt;
		$encrypt_text = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($salt), $val, MCRYPT_MODE_CBC, md5(md5($salt))));
		return($encrypt_text);
	}
	//Credits to Baba from stack overflow for this code
	public static function GetSize($dir) {
		$ritit = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS));
		$bytes = 0;
		foreach ($ritit as $v) {
			$bytes += $v->GetSize();
		}
		$units = array('B','KB','MB','GB','TB');
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$bytes /= pow(1024, $pow);
		return round($bytes, 2) . ' ' . $units[$pow];
	}
	//
	public static function Log($val1, $val2, $val3) {
		global $db;
		$db->Execute('INSERT INTO `'.prefix.'logs` (`user`, `message`, `time`) VALUES ('.$db->quote($val1).', '.$db->quote($val2).', '.$db->quote($val3).')');
	}
	public static function trip($data){
		$trip = explode("#",$data);
		$name = array_shift($trip);
		$count = count($trip);
		if ($count > 1) {
			$tripcombine = implode("", $trip);
			return $name."!!".substr(crypt($trip[1],self::Encrypt($tripcombine)),-10);
		} elseif($count == 1) {
			return $name."!".substr(crypt($trip[1],self::Encrypt($trip[0])),-10);
		} else {
			return $name;
		}
	}
	public static function Error($val, $val2='') {
		global $twig_data, $twig, $db;
		$twig_data['sitename'] = self::GetConfigOption('sitename');
		$twig_data['version'] = self::GetConfigOption('version');
		$twig_data['errormsg'] = $val;
		$twig_data['errormsgext'] = '';
		if ($val2 != '') {
			$twig_data['errormsgext'] = '<br /><div style="text-align: center;font-size: 1.25em;">' . $val2 . '</div>';
		}
		self::Output('/error.tpl', $twig_data);
		die();
	}
	public static function GetConfigOption($val) {
		global $db;
		return $db->GetOne('SELECT `config_value` FROM `'.prefix.'siteconfig` WHERE `config_name` = '.$db->quote($val));
	}
	public static function Output($val1, $val2) {
		global $twig;
		echo $twig->display($val1, $val2);
	}
	public static function print_page($filename, $contents, $board) {
	global $db;
		$tempfile = tempnam(fullpath . $board . '/res', 'tmp'); /* Create the temporary file */
		$fp = fopen($tempfile, 'w');
		fwrite($fp, $contents);
		fclose($fp);
		if (!@rename($tempfile, $filename)) {
			copy($tempfile, $filename);
			unlink($tempfile);
		}
		chmod($filename, 0664); /* it was created 0600 */
	}
	public static function rrmdir($dir) {
		foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
			$path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
		}
		rmdir($dir);
	}
	public static function ParsePost($msg, $board) {
		global $db;
		$wordfilters = $db->GetAll('SELECT * FROM `'.prefix.'wordfilters`');
		foreach ($wordfilters as $filter) {
			if ($filter['boards'] == 'all' or $filter['boards'] == $board) {
				$word = $filter['word'];
				$replace = $filter['replace'];
				$msg = str_ireplace($word, $replace, $msg);
			}
		}
		return $msg;
	}
	public static function Banned($board='', $ip, $appealed='') {
		global $db, $twig, $twig_date;
		$twig_data['sitename'] = self::GetConfigOption('sitename');
		$twig_data['version'] = self::GetConfigOption('version');
		$twig_data['bans'] = $db->GetAll('SELECT * FROM `'.prefix.'bans` WHERE `ip` = '.$db->quote($ip));
		$twig_data['time'] = time();
		$twig_data['loc'] = isset($_GET['board']) ? url.$_GET['board'] : '';
		$twig_data['location'] = url.$board;
		if ($board != '' ) {
			$twig_data['board'] = $board;
		}
		if ($appealed != '') {
			$twig_data['msg'] = '<font color="green">Appeal successfully sent!</font>';
		}
		self::Output('/banned.tpl', $twig_data);
		die();
	}
	public static function formatReflink($post_board, $post_thread_start_id, $post_id) {
		$return = '	';
		$reflink_noquote = '<a href="' . url . $post_board . '/res/' . $post_thread_start_id . '.html#' . $post_id . '" onclick="return highlight(\'' . $post_id . '\');">';
		$reflink_quote = '<a href="' . url . $post_board . '/res/' . $post_thread_start_id . '.html#i' . $post_id . '" onclick="return insert(\'>>' . $post_id . '\\n\');">';
		$return .= $reflink_noquote . 'No.&nbsp;' . '</a>' . $reflink_quote . $post_id . '</a>';
		return $return . "\n";
	}
	public static function createThumbnail($name, $filename, $new_w, $new_h) {
		$system=explode(".", $filename);
		$system = array_reverse($system);
		if (preg_match("/jpg|jpeg/", $system[0])) {
			$src_img=imagecreatefromjpeg($name);
		} else if (preg_match("/png/", $system[0])) {
			$src_img=imagecreatefrompng($name);
		} else if (preg_match("/gif/", $system[0])) {
			$src_img=imagecreatefromgif($name);
		} else {
			return false;
		}
		if (!$src_img) {
			self::Error('Unable to read uploaded file during thumbnailing.', 'A common cause for this is an incorrect extension when the file is actually of a different type.');
		}
		$old_x = imageSX($src_img);
		$old_y = imageSY($src_img);
		if ($old_x > $old_y) {
			$percent = $new_w / $old_x;
		} else {
			$percent = $new_h / $old_y;
		}
		$thumb_w = round($old_x * $percent);
		$thumb_h = round($old_y * $percent);
		$dst_img = ImageCreateTrueColor($thumb_w, $thumb_h);
		fastImageCopyResampled($dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y, $system);
		if (preg_match("/png/", $system[0])) {
			if (!imagepng($dst_img,$filename,0,PNG_ALL_FILTERS) ) {
				echo 'unable to imagepng.';
				return false;
			}
		} else if (preg_match("/jpg|jpeg/", $system[0])) {
			if (!imagejpeg($dst_img, $filename, 70)) {
				echo 'unable to imagejpg.';
				return false;
			}
		} else if (preg_match("/gif/", $system[0])) {
			if (!imagegif($dst_img, $filename)) {
				echo 'unable to imagegif.';
				return false;
			}
		}
		imagedestroy($dst_img);
		imagedestroy($src_img);
		return true;
	}
}
