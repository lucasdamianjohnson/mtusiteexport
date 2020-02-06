<?php

ini_set("allow_url_fopen",true);
ini_set('memory_limit','256M');
//import hmtl dom
include("/export/www_mtu_edu_docroot/htdocs/mtu_resources/php/htmldom/simple_html_dom.php");
//make debug files
mkdir("files/debug",0777);
chmod("files/debug",0777);
$debug_file_folders = fopen("files/debug/folders.html","w") or die("something went wrong writing file");
$debug_file_pdf = fopen("files/debug/pdf.html","w") or die("something went wrong writing file");
$debug_file_xsl = fopen("files/debug/xsl.html","w") or die("something went wrong writing file");
$debug_file_doc = fopen("files/debug/doc.html","w") or die("something went wrong writing file");
$debug_file_ppt = fopen("files/debug/ppt.html","w") or die("something went wrong writing file");
$debug_file_images = fopen("files/debug/images.html","w") or die("something went wrong writing file");
$debug_file_tags = fopen("files/debug/tags.html","w") or die("something went wrong writing file");

$current_time = "<strong>This file was made: ".date("h:i:sa")." ".date("Y/m/d")."</strong><br>";
fwrite($debug_file_folders, $current_time);
fwrite($debug_file_pdf, $current_time);
fwrite($debug_file_doc, $current_time);
fwrite($debug_file_ppt, $current_time);
fwrite($debug_file_images, $current_time);
fwrite($debug_file_tags, $current_time);
function clean_file_name($file_name) {
	$replace = array("."," ","&",",","'","\"","*","#","@","!","(",")","_","|","^",":",";","+","=","?","<",">","[","]","{","}","%20","&amp;");
	$replace_with = array("","-","","","","","","","","","","","-","","","","","",""
		,"","","","","","","","-","");
	$file_name = strtolower($file_name);
	return str_replace($replace, $replace_with, $file_name);
}
function save_image($type,$type_extension,$extension,$file,$file_url,$replace,$replace_with,$img_title,$img_alt,$blank_image_file_size,$img_path,$debug_file_images) {

	$file_name = "";
					if ($img_title != '') {
						
						$img_title = clean_file_name($img_title);
						$file_name = str_replace($replace, $replace_with, $img_title).$type_extension;
					} else if ($img_alt != '') {
						
						$img_alt = clean_file_name($img_alt);
						$file_name = str_replace($replace, $replace_with, $img_alt).$type_extension;

					} else {
						
						$file_name = basename($file_url);
			
						$file_name = strtolower($file_name);
						$file_name = str_replace(array($type,$extension), "", $file_name);
						
						$file_name = str_replace($replace, $replace_with, $file_name).$type_extension;
						if(strpos($file_name,'-orig') > 0) {
							
							$file_name = "image".$file_name; 
						}
						echo "<h1>$file_name</h1>";
						
				
					}
					if (abs((get_file_size($file_url)-$blank_image_file_size)/$blank_image_file_size) < 0.00001) {

					} else {
						
						$dump = "$type Image was downloaded for ".$file_url." as ".$file_name."<br>";
						fwrite($debug_file_images,$dump);
						echo "<h1>files/$img_path$file_name</h1>";
						file_put_contents("files".$img_path.$file_name, $file);
					}

					
}
function download_files($xml,$extension,$debug_file,$ou_root,$parent) {
	foreach($xml->url as $url) {
	if (strripos($url->loc, $extension) != FALSE) {
		$url_get = $url->loc;
		$cursor = $url->loc;
		$replace = array($parent);
		$replace_with = array("");
		$cursor = str_replace($replace, $replace_with, $cursor);
		$file = file_get_contents($url_get);
		$file_dir = "files/".$ou_root."/".dirname($cursor);
		if ($file != ''){
			if (!file_exists($pdf_dir)){
		$debug_out = "<br><strong>made $extension directory at ".$file_dir;
		fwrite($debug_file,$debug_out);
		mkdir( $file_dir, 0777, true );
		chmod($file_dir,0777);
		} 
		//echo "<br>Found pdf put it in files/".$ou_root."/".$cursor;
		$pos = strlen($cursor) - strlen($extension);
		$cursor = substr_replace($cursor, "", $pos);
		$cursor_old = $cursor;

		$cursor = clean_file_name($cursor);
		$debug_out = "<br>Found $extension put in files/".$ou_root."/".$cursor;
		fwrite($debug_file,$debug_out);
	
		if ($cursor_old != $cursor) {
			$debug_converted =  "<br><strong>Found problems with $extension name converted </strong>".$cursor_old."$extension to ".$cursor."$extension"; 
			fwrite($debug_file,$debug_converted);
		}
		file_put_contents("files/".$ou_root."/".$cursor.$extension, $file);
	}
	} 
}
}

function get_file_size($url) {
	$ch = curl_init($url);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch,CURLOPT_REFERER,$_SERVER['PHP_SELF']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, TRUE);
	curl_setopt($ch, CURLOPT_NOBODY, TRUE);

	$data = curl_exec($ch);
	$size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

	curl_close($ch);
	return $size;
} 

	function array_search_partial($arr, $keyword) {
		foreach($arr as $index => $string) {
			if (strpos($keyword, $string) !== FALSE)
				return $index;
			}
	}

function get_images($url,$root,$img_title,$img_alt,$debug_file_images) {
	$blank_image_file_size = doubleval(3039);
	$img_path = $url;

	if (strripos($img_path, $root) != FALSE) {
		$file_replace = basename($img_path);
		$replace = array($file_replace,$root);
		$replace_with = array("","ou-".$root);
		$img_path = str_replace($replace, $replace_with, $img_path);

		if (!file_exists("files".$img_path)){
			echo "<br><strong>made image directory at ".$img_path;
			mkdir( "files".$img_path, 0777, true );
			chmod("files".$img_path,0777);
		} 
	}
	if (strripos($url, ".svg") == FALSE) {
		if (strripos($url, "http://") == FALSE) {
			if (strripos($url, $root) != FALSE) {
				$url = "http://www.mtu.edu".$url;
			}
		}
	}
			//check to see if it is an image editor
		$is_ie_string = array("lthumb","xlthumb","pers","rside","horiz","fshoriz","fshorizw","fshorizj","orig");
		$is_ie = FALSE;
		foreach($is_ie_string as $needle) {
			
			$res = strripos($url, $needle);
			//echo $res."<br />~~";
			if ($res != FALSE) {
				$is_ie = TRUE;
			} else {
				//$saved_filename = $img_path.clean_file_name(basename($url,pathinfo(parse_url($url)['path'], PATHINFO_EXTENSION))).".".pathinfo(parse_url($url)['path'], PATHINFO_EXTENSION);
				$saved_filename = $img_path.clean_file_name(basename($url,pathinfo(parse_url($url)['path'], PATHINFO_EXTENSION)))."-orig.jpg";
				
			if(array_search_partial($is_ie_string,$url) == false) {
				if(strpos($saved_filename,'www.mtu.edu') === false) {
					$file = file_get_contents($url);
					echo "<h1>files".$saved_filename."</h1>";
					file_put_contents("files".$saved_filename, $file);
				}
				
			}
				
			}
		}

	//generate urls for different sized images
		/*if ($is_ie == FALSE ) {
			if (strripos($img_path, $root) != FALSE) {
				$file_name = basename($url);
				$test = explode(".", $file_name);
				$i = 0;
				$exten_test = false;
				$extensions = array("jpg","png","gif");
				while($exten_test == false){
					$i += 1;
					foreach($extensions as $exten) {
						if ($test[$i] == $exten) {
							$exten_test = true;			
							break;
						}

					}

				}

				for($x = 0; $x < $i; $x++) {
					$basename = $basename.$test[$x];
				}

				$file_name = clean_file_name($basename)."-orig".".".$test[$i];
				$image_file = file_get_contents($url);
				if ($image_file != '') {
					fwrite($debug_file_images,"Image was downloaded for ".$url." as ".$file_name."<br>");
					file_put_contents("files".$img_path.$file_name,$image_file);

				} 
			}} else*/
			if (strripos($img_path, $root) != FALSE && $is_ie != FALSE) {
				
				$image_replace_orig = basename($url);
				$replace_for_orig = array("fshorizj","fshoriz","fshorizw","xlthumb","lthumb","pers","rside","horiz","image","origw");
				$replace_with_orig = array("orig","orig","orig","orig","orig","orig","orig","orig","","orig");

				$replace_for_orig_png = array("fshorizj","fshoriz","fshorizw","xlthumb","lthumb","pers","rside","horiz","image","jpg");
				$replace_with_orig_png = array("orig","orig","orig","orig","orig","orig","orig","orig","","","","png");

				$replace_for_lthumb = array("fshorizw","fshorizj","fshoriz","xlthumb","pers","rside","horiz");
				$replace_with_lthumb = array("lthumb","lthumb","lthumb","lthumb","lthumb","lthumb","lthumb");

				$replace_for_xlthumb = array("fshoriz","fshorizw","fshorizj","lthumb","pers","rside","horiz","xxlthumb");
				$replace_with_xlthumb = array("xlthumb","xlthumb","xlthumb","xlthumb","xlthumb","xlthumb","xlthumb","xlthumb");

				$replace_for_pers = array("fshoriz","fshorizw","fshorizj","xlthumb","lthumb","rside","horiz");
				$replace_with_pers = array("pers","pers","pers","pers","pers","pers","pers","");

				$replace_for_rside = array("fshoriz","fshorizw","fshorizj","xlthumb","lthumb","pers","horiz");
				$replace_with_rside = array("rside","rside","rside","rside","rside","rside","rside");

				$replace_for_horiz = array("fshoriz","fshorizw","fshorizj","xlthumb","lthumb","pers","rside");
				$replace_with_horiz = array("horiz","horiz","horiz","horiz","horiz","horiz","horiz");


				$replace_for_fshoriz = array("fshorizw","fshorizj","xlthumb","lthumb","pers","rside","horiz","fsfshoriz");
				$replace_with_fshoriz = array("fshoriz","fshoriz","fshoriz","fshoriz","fshoriz","fshoriz","fshoriz","fshoriz");

				$replace_for_fshorizw = array("fshoriz","fshorizj","xlthumb","lthumb","pers","rside","horiz","fsfshorizww");
				$replace_with_fshorizw = array("fshorizw","fshorizw","fshorizw","fshorizw","fshorizw","fshorizw","fshorizw","fshorizw");

				$replace_for_fshorizj = array("fshorizw","fshoriz","xlthumb","lthumb","pers","rside","horiz","fsfshorizjj");
				$replace_with_fshorizj = array("fshorizj","fshorizj","fshorizj","fshorizj","fshorizj","fshorizj","fshorizj","fshorizj");
				
				$orig_url_jpg = str_replace($replace_for_orig, $replace_with_orig, $image_replace_orig);
				$orig_url_png = str_replace($replace_for_orig_png, $replace_with_orig_png, $image_replace_orig);
				$orig_url_path = str_replace($image_replace_orig, "", $url);

				$orig_url_jpg = $orig_url_path.$orig_url_jpg;
				$orig_url_png = $orig_url_path.$orig_url_png;
					
				//echo $url."<br />";
				$xlthumb_url = str_replace($replace_for_xlthumb, $replace_with_xlthumb, $url);
				//echo "<br />";
				$lthumb_url = str_replace($replace_for_lthumb, $replace_with_lthumb, $url);
				//echo "<br />";
				$pers_url = str_replace($replace_for_pers,$replace_with_pers,$url);
				//echo "<br />";
				$rside_url = str_replace($replace_for_rside,$replace_with_rside,$url);
				//echo "<br />";
				$horiz_url = str_replace($replace_for_horiz,$replace_with_horiz,$url);
				//echo "<br />";
				$fshoriz_url = str_replace($replace_for_fshoriz,$replace_with_fshoriz,$url);
				//echo "<br />";
				$fshorizw_url = str_replace($replace_for_fshorizw,$replace_with_fshorizw,$url);
				//echo "<br />";
				$fshorizj_url = str_replace($replace_for_fshorizj,$replace_with_fshorizj,$url);
				//echo "<br />";
				
				$orig_jpg = file_get_contents($orig_url_jpg);
				$orig_png = file_get_contents($orig_url_png);
				$lthumb = file_get_contents($lthumb_url);
				$xlthumb = file_get_contents($xlthumb_url);
				$pers = file_get_contents($pers_url);
				$rside = file_get_contents($rside_url);
				$horiz = file_get_contents($horiz_url);
				$fshoriz = file_get_contents($fshoriz_url);
				$fshorizw = file_get_contents($fshorizw_url);
				$fshorizj = file_get_contents($fshorizj_url);
				$replace = array(" ", ".","'",",","_");
				$replace_with = array("-","","","","-");
		//function save_image($type,$type_extension,$extension,$file,$file_url,$replace,$replace_with,$img_title,$img_alt,$blank_image_file_size,$img_path,$debug_file_images)
			
			if ($lthumb != ''){
				save_image("-lthumb","-170sq.jpg","jpg",$lthumb,$lthumb_url,$replace,$replace_with,$img_title,$img_alt,$blank_image_file_size,$img_path,$debug_file_images);
			}
				if ($xlthumb != ''){
				save_image("-xlthumb","-250sq.jpg","jpg",$xlthumb,$xlthumb_url,$replace,$replace_with,$img_title,$img_alt,$blank_image_file_size,$img_path,$debug_file_images);
			}
				if ($pers != ''){
				save_image("-pers","-personnel.jpg","jpg",$pers,$pers_url,$replace,$replace_with,$img_title,$img_alt,$blank_image_file_size,$img_path,$debug_file_images);
			}
				if ($rside != ''){
					//echo "<h1>found it: $rside_url</h1>";
				save_image("-rside","-350sidebar.jpg","jpg",$rside,$rside_url,$replace,$replace_with,$img_title,$img_alt,$blank_image_file_size,$img_path,$debug_file_images);
			}
				if ($horiz != '') {
					//echo "<h1>found it: $horiz_url</h1>";
				save_image("-horiz","-515subbanner.jpg","jpg",$horiz,$horiz_url,$replace,$replace_with,$img_title,$img_alt,$blank_image_file_size,$img_path,$debug_file_images);
			}
				if ($fshoriz != ''){
				save_image("-fshoriz","-800banner.jpg","jpg",$fshoriz,$fshoriz_url,$replace,$replace_with,$img_title,$img_alt,$blank_image_file_size,$img_path,$debug_file_images);
			}
				if ($fshorizw != ''){
				save_image("-fshorizw","-1024feature.jpg","jpg",$fshorizw,$fshorizw_url,$replace,$replace_with,$img_title,$img_alt,$blank_image_file_size,$img_path,$debug_file_images);
			}
				if ($fshorizj != ""){
				save_image("-fshorizj","-1600feature.jpg","jpg",$fshorizj,$fshorizj_url,$replace,$replace_with,$img_title,$img_alt,$blank_image_file_size,$img_path,$debug_file_images);
			}		
				if ($orig_png != '') {
						save_image("-orig","-orig.png","png",$orig_png,$orig_url_png,$replace,$replace_with,$img_title,$img_alt,$blank_image_file_size,$img_path,$debug_file_images);

				} else
				if ($orig_jpg != '') {
					save_image("-orig","-orig.jpg","jpg",$orig_jpg,$orig_url_jpg,$replace,$replace_with,$img_title,$img_alt,$blank_image_file_size,$img_path,$debug_file_images);
				} else {
					//echo "<h1>".$url."</h1>";
					echo "<h2>could not find orginal image for: ".$orig_url_jpg."</h2>";
				}
			}
		}
	
	function download_images($url_path,$root,$debug_file_images) {
	
		$page = file_get_html($url_path);
		if ($page != '') {
			foreach($page->find('section[style]') as $image)  {
			
				if(strpos($image->style, "background-image") !== FALSE) {
					$url = explode("'",$image->style);
					get_images($url[1],$root,"","",$debug_file_images);
				}
			}
			foreach($page->find('div[style]') as $image)  {
				
				if(strpos($image->style, "background-image") !== FALSE) {
					$url = explode("'",$image->style);
					get_images($url[1],$root,"","",$debug_file_images);
				}
			}

			foreach($page->find('div[id=main] img') as $image) {
				
				get_images($image->src,$root,$image->title,$image->alt,$debug_file_images);
			}
		}
	}
	function chmod_r($path) {
		$dir = new DirectoryIterator($path);
		foreach ($dir as $item) {
			chmod($item->getPathname(), 0777);
			if ($item->isDir() && !$item->isDot()) {
				chmod_r($item->getPathname());
			}
		}
	}
//get xml file name 
	if(isset($_GET['file'])) {
		$file = trim($_GET['file']);
	} else {
		$file = '';
	}
//echo $file;
	$xml = simplexml_load_file($file) or die("Error: Cannot create object");
//print_r($xml);
	$parent = $xml->url[0]->loc;;
	$root = $xml->url[0]->loc;

	$main = dirname($root);
	
	$replace = array($main,"/");
	$replace_with = array("","");
	$root = str_replace($replace, $replace_with, $root);

	echo $root;
	$ou_root = "ou-".$root;
	mkdir("files/".$ou_root,0777);
	chmod("files/".$ou_root,0777);
	foreach($xml->url as $url) {
		if ((strripos($url->loc, ".pdf") != FALSE) or (strripos($url->loc, ".doc") != FALSE)
			or (strripos($url->loc, ".docx") != FALSE) or (strripos($url->loc, ".ppt") != FALSE) or (strripos($url->loc, ".pptx") != FALSE) or (strripos($url->loc, ".xls") != FALSE) or (strripos($url->loc, ".xlsx") != FALSE)) {

		} else {
			$cursor = $url->loc;
			$replace = array($parent);
			$replace_with = array("");
			$cursor = str_replace($replace, $replace_with, $cursor);

			if (strripos($url->loc, ".html") != FALSE) {
				$cursor_old = $cursor;
				//echo "<br/>";
				//echo "<strong>Found .HTML converted </strong>".$cursor;
				$replace = array(".html");
				$replace_with = array("");
				$cursor = str_replace($replace, $replace_with, $cursor);
				//echo " <strong>to</strong> ".$cursor;
				$debug_converted =  "<br><strong>Found .HTML converted </strong> /".$cursor_old." to /".$cursor."/"; 
				fwrite($debug_file_folders,$debug_converted);
			}
			$cursor_old = $cursor;
			$cursor = clean_file_name($cursor);
			if ($cursor_old != $cursor ) {
				$debug_converted =  "<br><strong>Found problems with folder name converted </strong> /".$cursor_old." to /".$cursor."/"; 
				fwrite($debug_file_folders,$debug_converted);
			}
			mkdir( "files/".$ou_root."/".$cursor, 0777, true );
			chmod_r("files/".$ou_root."/".$cursor);
		} 	
	}

	$replace = array("."," ","&",",","'","\"","*","#","@","!","(",")","_","|","^",":",";","+","=","?","<",">","[","]","{","}","%20");
	$replace_with = array("","-","","","","","","","","","","","-","","","","","",""
			,"","","","","","",""," ");
//function download_files($xml,$extension,$debug_file,$ou_root,$parent) 
	download_files($xml,".pdf",$debug_file_pdf,$ou_root,$parent);
	download_files($xml,".doc",$debug_file_doc,$ou_root,$parent);
	download_files($xml,".docx",$debug_file_doc,$ou_root,$parent);
	download_files($xml,".xslx",$debug_file_xsl,$ou_root,$parent);
	download_files($xml,".ppt",$debug_file_ppt,$ou_root,$parent);
	download_files($xml,".pptx",$debug_file_xsl,$ou_root,$parent);
$keywords = "";
$description = "";
$page_title = "";
$meta_title = "";
$bread_crum = "";
$section_title = ""; //get from left nav
$left_nav = "";
$left_nav_sub = "";
$has_left_nav = "";
$site_title = "";
$curson = "";
foreach ($xml->url as $url) {
	$cursor = $url->loc;
	$replace = array($parent);
	$replace_with = array("");
	$cursor = str_replace($replace, $replace_with, $cursor);
	if (strripos($url->loc, ".pdf") != FALSE) { } else {
	//keeps linebreaks and inserts html on multiple lines
		$page = file_get_html($url->loc,false,null,-1,-1,true,true,DEFAULT_TARGET_CHARSET,false,DEFAULT_BR_TEXT);
	//$page = file_get_html($url->loc);
		echo "<h1>$url->loc</h1>";
		if ($page != '') {
		
			if ($url->loc == "http://www.mtu.edu/".$root."/") {
				$site_title = trim($page->find('div.sitetitle',0)->plaintext);
				$bread_crum = $site_title;
				$section_title = $site_title;
			} else {
				$bread_crum = $page->find('div[id=breadcrumbs] li.active');
				$bread_crum = trim($bread_crum[0]->plaintext);
				$section_title = trim($page->find('ul[id=main_links] ul.firstlevel li.active a',0)->plaintext);
			}

			$page_title = trim($page->find('article[id=content_body] h1',0)->plaintext);

			$keywords = $page->find('meta[name=keywords]',0);
			$description = $page->find('meta[name=description]',0);

			$left_nav = $page->find('ul[id=main_links] ul.firstlevel',0)->innertext;
			$left_nav_sub = $page->find('ul[id=main_links] ul.firstlevel ul.secondlevel',0)->innertext;
			$left_nav_active = $page->find('ul[id=main_links] ul.firstlevel ul.secondlevel li.active',0)->innertext;
	
			$nav_url = str_replace($root, $ou_root, $url->loc);
			if ($left_nav_active != ''){
				$section_title = $page->find('ul[id=main_links] ul.firstlevel ul.secondlevel li.active a',0)->plaintext;
				$left_nav_sub = '';
			}
			if ($section_title == '') {
				$debug_out = "<br><strong>no section title found for url = </strong>".$url->loc;
				fwrite($debug_file_tags, $debug_out);
			}
			if($page_title != '') {
				$section_title = $page_title;
			}
			
			if ($bread_crum != '') {
				
				$bread_crum = str_replace('&gt; ', '', $bread_crum);
			} else {
	
				$debug_out = "<br><strong>no breadcrumb found for url = </strong>".$url->loc;
				fwrite($debug_file_tags, $debug_out);
			}
			if ($left_nav != ''){
    			//$left_nav = $purifier->purify($left_nav);
				$left_nav = $left_nav;
				$has_left_nav = "true";
				$replace = array('href="/'.$root);
				$replace_with = array('href="/'.$ou_root);
				$left_nav = str_replace($replace, $replace_with, $left_nav);

			} else {
				$has_left_nav = "false";

			}
			if ($page_title ==''){
				
				$debug_out = "<br><strong>did not find Page Title url = </strong>".$url->loc;
				fwrite($debug_file_tags, $debug_out);
			}
			
			if ($keywords == '') {
			
				$debug_out = "<br><strong>did not find keywords meta TAG for url = </strong>".$url->loc;
				fwrite($debug_file_tags, $debug_out);
			} else {
				$keywords = $keywords->getAttribute('content'); 
			}
			
			if ($description == '') {
						$debug_out = "<br><strong>did not find description meta TAG for url = </strong>".$url->loc;
				fwrite($debug_file_tags, $debug_out);
			} else {
				$description = $description->getAttribute('content'); 

			}
		} else {
			echo "<br><strong>did not find page</strong>";
			$debug_out = "<br><strong>did not find page for </strong>".$url->loc;
			fwrite($debug_file_tags, $debug_out);
		}
		if (strripos($url->loc, ".html") != FALSE) {

			$replace = array(".html");
			$replace_with = array("");
			$cursor = str_replace($replace, $replace_with, $cursor);
			$cursor = $cursor."/";
			
		}

		$cursor = clean_file_name($cursor);

	
		download_images($url->loc,$root,$debug_file_images);
		$image_path = "/".$ou_root."/".$cursor."images";

		$index_pcf = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
		<?pcf-stylesheet path=\"/_resources/xsl/interior.xsl\" title=\"HTML\" extension=\"html\"?>

		<!DOCTYPE document SYSTEM \"http://commons.omniupdate.com/dtd/standard.dtd\"> 

		<document xmlns:ouc=\"http://omniupdate.com/XSL/Variables\">

			<headcode></headcode> <!-- before closing head tag -->
			<bodycode></bodycode> <!-- after opening body tag -->
			<footcode></footcode> <!-- before closing body tag -->
			<ouc:info><tcf>section.tcf</tcf><tmpl>interior.tmpl</tmpl></ouc:info>

			<ouc:properties label=\"metadata\">
			<title>".$page_title."</title>
			<meta name=\"Description\" content=\"".$description."\" />
			<meta name=\"Keywords\" content=\"".$keywords."\" />
		</ouc:properties>

		<ouc:properties label=\"config\">
		<parameter name=\"page-meta\" type=\"text\" group=\"Everyone\" prompt=\"Page Meta Title\" alt=\"Meta title which appears in the browser tab. Page title by default.\"></parameter>
		<!--<parameter name=\"heading\" type=\"text\" group=\"Everyone\" prompt=\"Page Heading\" alt=\"Meta title which appears in the browser tab. Page title by default.\">".$page_title."</parameter>-->
		<parameter name=\"breadcrumb\" type=\"text\" group=\"Everyone\" prompt=\"Breadcrumb\" alt=\"For index.pcf files, the breadcrumb must be updated in the _props.pcf Parameters instead. Page title by default.\">".$bread_crum."</parameter>
		
		<parameter name=\"acad-banner-visibility\" type=\"checkbox\" group=\"Everyone\" alt=\"Full screen width above the content area. A 1600 Feature Image should be inserted into MultiEdit Content.\" section=\"Page Image Location Options\"><option value=\"true\" selected=\"false\">Show Hero Image</option></parameter>
		<parameter name=\"banner-feature-visibility\" type=\"checkbox\" group=\"Everyone\" alt=\"Width of and above the content area. A 1024 Feature Image should be inserted into MultiEdit Content.\"><option value=\"true\" selected=\"false\">Show Feature Image</option></parameter>
		<parameter name=\"banner-visibility\" type=\"checkbox\" group=\"Everyone\" alt=\"In the content area, next to left sidebar and above right sidebar. An 800 Banner Image or 1024 Feature Image should be inserted into MultiEdit Content.\"><option value=\"true\" selected=\"false\">Show Banner Image</option></parameter>
		<parameter name=\"sub-banner-visibility\" type=\"checkbox\" group=\"Everyone\"  alt=\"In the content area, next to left and right sidebars. A 515 Sub-Banner Image, 800 Banner Image, or 1024 Feature Image should be inserted into MultiEdit Content.\"><option value=\"true\" selected=\"false\">Show Sub-Banner Image</option></parameter>

		<parameter name=\"sidebar-left-visibility\" type=\"checkbox\" group=\"Everyone\" prompt=\"Visibilty\" alt=\"Check the box to display this section.\" section=\"Left Sidebar Section Options\"><option value=\"true\" selected=\"".$has_left_nav."\">Show</option></parameter>
		<parameter name=\"sidebar-right-visibility\" type=\"checkbox\" group=\"Everyone\" prompt=\"Visibilty\" alt=\"Check the box to display this section.\" section=\"Right Sidebar Section Options\"><option value=\"true\" selected=\"false\">Show</option></parameter>
		
		<parameter section=\"LDP Gallery\" name=\"gallery-type\" type=\"select\" group=\"Everyone\" prompt=\"Gallery Type\" alt=\"Select the output type for gallery assets on this page.\">
		<option value=\"flex-slider\" selected=\"true\">Flex Slider</option>
		<option value=\"pretty-photo\" selected=\"false\">Pretty Photo</option>
	</parameter>
</ouc:properties>
<ouc:properties label=\"config-includes\">
<parameter name=\"page-external-include\" type=\"text\" rows=\"3\" group=\"Everyone\" prompt=\"External Include\" alt=\"Included in the head of this page.\" section=\"External Includes\"></parameter>
<parameter name=\"crazyegg-visibility\" type=\"checkbox\" group=\"Admins\" prompt=\"Crazy Egg Visibilty\" alt=\"Check the box to display this section.\" section=\"Crazy Egg Options\"><option value=\"true\" selected=\"false\">Show</option></parameter>
</ouc:properties>	


<ouc:div label=\"banner-type\"  group=\"Everyone\" button=\"hide\"><ouc:multiedit type=\"select\" prompt=\"Banner Type\" alt=\"Select the type of banner to be displayed.\" section=\"Banner Options\" options=\"Image Only:image-only;Text Popup:text-over-image;Text Over Image:text-popup;Video:video;None:none;\" />image-only</ouc:div>
<ouc:div label=\"banner-image\" group=\"Everyone\" button=\"hide\"><ouc:multiedit type=\"image\" prompt=\"Banner Image\" alt=\"Select an image for the banner.\" path=\"".$image_path."\" lockout=\"no\" /><img src=\"\" alt=\"\" /></ouc:div>
<ouc:div label=\"banner-title\" group=\"Everyone\" button=\"hide\"><ouc:multiedit type=\"text\" prompt=\"Image Title\" alt=\"Image Title (used during 'Text Popup' and 'Text Over Image')\"/></ouc:div>
<ouc:div label=\"banner-content\" group=\"Everyone\" button=\"hide\"><ouc:multiedit type=\"textarea\" prompt=\"Banner Content\" alt=\"Enter the banner's content. This will be used for banner types: 'Text Over Image', 'Text Popup'.\" rows=\"10\" editor=\"yes\" /><p>&nbsp;</p></ouc:div>
<ouc:div label=\"banner-display-text-options\"  group=\"Everyone\" button=\"hide\"><ouc:multiedit type=\"select\" prompt=\"Text Over Image Location\" alt=\"Select the location of the overlay text\" options=\"Center:center;Top:top;Bottom:bottom;Left:left;\" />center</ouc:div>
<ouc:div label=\"banner-video\" group=\"Everyone\" button=\"hide\"><ouc:multiedit type=\"text\" prompt=\"Banner Video ID\" alt=\"Input Youtube Video ID. (used on banner images)\" /></ouc:div>

<ouc:div label=\"introcontent\" group=\"Everyone\" button-text=\"Intro Content\">
<ouc:editor csspath=\"/_resources/ou/editor/wysiwyg.css\" cssmenu=\"/_resources/ou/editor/styles.txt\" wysiwyg-class=\"maincontent\"/>
</ouc:div>


<ouc:div label=\"maincontent\" group=\"Everyone\" button-text=\"Main Content\" break=\"break\">
<ouc:editor csspath=\"/_resources/ou/editor/wysiwyg.css\" cssmenu=\"/_resources/ou/editor/styles.txt\" wysiwyg-class=\"maincontent\"/>
</ouc:div>

<ouc:div label=\"additional-content\" group=\"Everyone\" button-text=\"Additional Content\" break=\"break\">
<ouc:editor csspath=\"/_resources/ou/editor/wysiwyg.css\" cssmenu=\"/_resources/ou/editor/styles.txt\" wysiwyg-class=\"additional-content\"/>
</ouc:div>

<ouc:div label=\"sidebar-left-content\" group=\"Everyone\" button-text=\"Left Sidebar Content\" break=\"break\">
<ouc:editor csspath=\"/_resources/ou/editor/wysiwyg.css\" cssmenu=\"/_resources/ou/editor/styles.txt\" wysiwyg-class=\"sidebar-left-content\"/>
</ouc:div>

<ouc:div label=\"sidebar-right-content\" group=\"Everyone\" button-text=\"Right Sidebar Content\" break=\"break\">
<ouc:editor csspath=\"/_resources/ou/editor/wysiwyg.css\" cssmenu=\"/_resources/ou/editor/styles.txt\" wysiwyg-class=\"sidebar-right-content\"/>
</ouc:div>

</document>
";
if ($url->loc == "http://www.mtu.edu/".$root."/") {
	$nav = "<!-- ouc:editor csspath=\"/_resources/ou/editor/nav.css\" cssmenu=\"/_resources/ou/editor/styles-nav.txt\" wysiwyg-class=\"navigation\" -->
	<li><a href=\"#\">Placeholder</a></li>";
	$props = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
	<?pcf-stylesheet path=\"/_resources/xsl/properties.xsl\" title=\"Properties\" extension=\"shtml\"?>
	<?pcf-stylesheet path=\"/_resources/xsl/properties-site-logo.xsl\" title=\"Logo\" alternate=\"no\" extension=\".site-logo.shtml\"?>
	<?pcf-stylesheet path=\"/_resources/xsl/properties-site-contact.xsl\" title=\"Footer Contact\" alternate=\"no\" extension=\".site-contact.shtml\"?>

	<!DOCTYPE document SYSTEM \"http://commons.omniupdate.com/dtd/standard.dtd\"> 

	<document xmlns:ouc=\"http://omniupdate.com/XSL/Variables\">
		<ouc:info><tcf>section-site.tcf</tcf><tmpl>properties-site.tmpl</tmpl></ouc:info>
		<ouc:properties label=\"config\">
		<parameter name=\"site-meta\" type=\"text\" group=\"Everyone\" prompt=\"Site Meta Title\" alt=\"Will be appended after the page title. If left blank will use the default meta title of 'Page Title | Site Title | Michigan Technological Universtiy'\">".$site_title."</parameter>
		<parameter name=\"section-title\" type=\"text\" group=\"Everyone\" prompt=\"Section Title\" alt=\"Enter the friendly name for the site's title.\">".$section_title."</parameter>
		<parameter name=\"breadcrumb\" type=\"text\" group=\"Everyone\" prompt=\"Section Breadcrumb\" alt=\"Enter the friendly name for the site's breadcrumb.\">".$bread_crum."</parameter>
		<parameter name=\"restart-nav\" type=\"checkbox\" group=\"Everyone\" prompt=\"Navigation\" alt=\"\">
		<option value=\"true\" selected=\"false\">Restart Navigation</option></parameter>
		
		<parameter name=\"site-logo-visibility\" type=\"checkbox\" group=\"Everyone\" prompt=\"Visibilty\" alt=\"Check the box to display site's logo.\" section=\"Site Title Logo Options\"><option value=\"true\" selected=\"false\">Show</option></parameter>
		<parameter name=\"site-logo-image\" type=\"filechooser\" group=\"Everyone\" prompt=\"Image\" alt=\"Input/Select the site logo image.\" dependency=\"yes\"></parameter>
		<parameter name=\"site-logo-image-desc\" type=\"text\" group=\"Everyone\" prompt=\"Image Description\" alt=\"[Optional] Input the site logo image description. If left blank, image is considered decorative.\"></parameter>
		<parameter name=\"site-logo-link\" type=\"filechooser\" group=\"Everyone\" prompt=\"Logo Link\" alt=\"[Optional] Input a link for the site logo to point to. If left blank, it will default to the root of the site.\"></parameter>
	</ouc:properties>
	
	<ouc:properties label=\"config-footer\">
	<parameter name=\"foot-contact-head\" type=\"text\" group=\"Everyone\" prompt=\"Department Name\" alt=\"Please enter the department name for the contact information.\" section=\"Site Footer Contact Information\"></parameter>
	<parameter name=\"foot-contact-head-link\" type=\"filechooser\" group=\"Everyone\" prompt=\"Department Name Link\" alt=\"Please enter the link for deparment for the contact information.\" dependency=\"yes\"></parameter>
	<parameter name=\"foot-contact-head-link-homepage\" type=\"filechooser\" group=\"Everyone\" prompt=\"Contact Form Link\" alt=\"Please enter the link for deparmental contact form that will show up in the footer on the homepage.\" dependency=\"yes\"></parameter>
	<parameter name=\"foot-contact-info\" type=\"text\" rows=\"3\" group=\"Everyone\" prompt=\"Address\" alt=\"Please enter the address for the contact information. Type `(br)` to create a line break\"></parameter>
	<parameter name=\"foot-contact-phone\" type=\"text\" group=\"Everyone\" prompt=\"Phone Number\" alt=\"Please enter the phone number for the department, formatted like 906-487-1234.\"></parameter>
	<parameter name=\"foot-contact-fax\" type=\"text\" group=\"Everyone\" prompt=\"Fax Number\" alt=\"Please enter the fax number for the department, formatted like 906-487-1234.\"></parameter>
	<parameter name=\"foot-contact-toll-free\" type=\"text\" group=\"Everyone\" prompt=\"Toll-Free Number\" alt=\"Please enter the toll-free number for the department, formatted like 888-688-1885.\"></parameter>
	<parameter name=\"foot-contact-email\" type=\"text\" group=\"Everyone\" prompt=\"Email\" alt=\"Please enter the email address for the department.\"></parameter>
</ouc:properties>

<ouc:properties label=\"config-includes\">
<parameter name=\"site-external-include\" type=\"text\" rows=\"3\" group=\"Everyone\" prompt=\"External Include\" alt=\"Included in the head of all pages.\" section=\"External Includes\"></parameter>
</ouc:properties>	

<ouc:div label=\"directory-breadcrumbs-global\" group=\"Everyone\" button-text=\"Section's Breadcrumbs\" break=\"break\">
<ouc:editor csspath=\"/_resources/ou/editor/wysiwyg.css\" cssmenu=\"/_resources/ou/editor/styles.txt\" wysiwyg-class=\"maincontent\"/>
</ouc:div>
<ouc:div label=\"directory-sidebar-left\" group=\"Everyone\" button-text=\"Section's Extra Left Sidebar\" break=\"break\">
<ouc:editor csspath=\"/_resources/ou/editor/wysiwyg.css\" cssmenu=\"/_resources/ou/editor/styles.txt\" wysiwyg-class=\"maincontent\"/>
</ouc:div>

<!-- properties-site.tmpl -->
</document>
";
} else {
	$nav = "<!-- ouc:editor csspath=\"/_resources/ou/editor/nav.css\" cssmenu=\"/_resources/ou/editor/styles-nav.txt\" wysiwyg-class=\"navigation\" --><li><a href=\""."/".$ou_root."/".$cursor."\">".$section_title."</a></li>".$left_nav_sub;
	$props = "<?xml version=\"1.0\" encoding=\"utf-8\"?><?pcf-stylesheet path=\"/_resources/xsl/properties.xsl\" title=\"Properties\" extension=\"shtml\"?>
<!DOCTYPE document SYSTEM \"http://commons.omniupdate.com/dtd/standard.dtd\"> 
<document xmlns:ouc=\"http://omniupdate.com/XSL/Variables\">
<ouc:info><tcf>section.tcf</tcf><tmpl>properties.tmpl</tmpl></ouc:info>
<ouc:properties label=\"config\"> <parameter name=\"section-title\" type=\"text\" group=\"Everyone\" prompt=\"Section Title\" alt=\"Used as the heading for the left navigation for tab-level pages.\">".trim($section_title)."</parameter>\n 
<parameter name=\"breadcrumb\" type=\"text\" group=\"Everyone\" prompt=\"Section Breadcrumb\" alt=\"Used as the breadcrumb for index.pcf page. If there is no page at this level, this field should be blank.\">".trim($bread_crum)."</parameter>\n
</ouc:properties>
<ouc:div label=\"directory-sidebar-left\" group=\"Everyone\" button-text=\"Section's Extra Left Sidebar\" break=\"break\">
<ouc:editor csspath=\"/_resources/ou/editor/wysiwyg.css\" cssmenu=\"/_resources/ou/editor/styles.txt\" wysiwyg-class=\"maincontent\"/>
</ouc:div>
<!-- properties.tmpl -->
</document>";

}

$index_pcf_file = fopen("files/".$ou_root."/".$cursor."index.pcf","w") or die("something went wrong writing index file");
fwrite($index_pcf_file,$index_pcf);

$nav_file = fopen("files/".$ou_root."/".$cursor."_nav.shtml","w") or die("something went wrong writing nav file");
fwrite($nav_file,$nav);
$props_file = fopen("files/".$ou_root."/".$cursor."_props.pcf","w") or die("something went wrong writing props file");
fwrite($props_file,$props);

$props = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
	<?pcf-stylesheet path=\"/_resources/xsl/properties.xsl\" title=\"Properties\" extension=\"shtml\"?>

	<!DOCTYPE document SYSTEM \"http://commons.omniupdate.com/dtd/standard.dtd\"> 

	<document xmlns:ouc=\"http://omniupdate.com/XSL/Variables\">
		<ouc:info><tcf>section.tcf</tcf><tmpl>properties.tmpl</tmpl></ouc:info>
		<ouc:properties label=\"config\">
		<parameter name=\"section-title\" type=\"text\" group=\"Everyone\" prompt=\"Section Title\" alt=\"Used as the heading for the left navigation for tab-level pages.\"></parameter> <parameter name=\"breadcrumb\" type=\"text\" group=\"Everyone\" prompt=\"Section Breadcrumb\" alt=\"Used as the breadcrumb for index.pcf page. If there is no page at this level, this field should be blank.\"></parameter>
	</ouc:properties>
	<ouc:div label=\"directory-sidebar-left\" group=\"Everyone\" button-text=\"Section's Extra Left Sidebar\" break=\"break\">
	<ouc:editor csspath=\"/_resources/ou/editor/wysiwyg.css\" cssmenu=\"/_resources/ou/editor/styles.txt\" wysiwyg-class=\"maincontent\"/>
</ouc:div>

<!-- properties.tmpl -->
</document>";


$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("files/".$ou_root), RecursiveIteratorIterator::SELF_FIRST);
foreach($objects as $name => $object){

	chmod($name,0777);
	$file_replace = basename($name);
	$file_replace_len = strlen($file_replace);
	$pos = strlen($name) - strlen($file_replace);
	$path = substr_replace($name, "", $pos);


	if ((strripos($path, "image") == FALSE )|| (strripos($path, "images") == FALSE )) {
		if (strripos($path, "pdf") == FALSE) {
				$purl = str_replace("files/$ou_root", $root, $path);
			
			if (!file_exists($path."_nav.shtml")){
		
			$folders = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
			foreach($folders as $f => $object){ 
				$file_replace = basename($f);
				$file_replace_len = strlen($file_replace);
				$pos = strlen($f) - strlen($file_replace);
				$path_2 = substr_replace($f, "", $pos);
		
				$purl = str_replace("files/$ou_root", $root, $path_2);
			
				$page = file_get_html("http://www.mtu.edu/$purl",false,null,-1,-1,true,true,DEFAULT_TARGET_CHARSET,false,DEFAULT_BR_TEXT);
				if ($page != '') {
					$left_nav_f = $page->find('ul[id=main_links] ul.firstlevel',0)->innertext;
			
					break;
				}
			}
			$nav = "<!-- ouc:editor csspath=\"/_resources/ou/editor/nav.css\" cssmenu=\"/_resources/ou/editor/styles-nav.txt\" wysiwyg-class=\"navigation\" -->
	<li><a href=\""."/".$ou_root."/".$cursor."\">".$section_title."</a></li>".trim($left_nav_f);
				
				$nav_file = fopen($path."_nav.shtml","w") or die("something went wrong writing lost nav");
				fwrite($nav_file,$nav);
				chmod($path."_nav.shtml",0777);
				chmod($path,0777);
			}
			if (!file_exists($path."_props.pcf")){

				$props_file = fopen($path."_props.pcf","w") or die("something went wrong for writing lost props");
				fwrite($props_file,$props);
				chmod($path."_props.pcf",0777);
				chmod($path,0777);
			}
		}

	}
}
}
}
?>
