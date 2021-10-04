<?php
require_once QA_INCLUDE_DIR . 'db/cache.php';
require_once QA_INCLUDE_DIR . 'app/posts.php';

require 'GDText/Box.php';
require 'GDText/Color.php';
require 'GDText/TextWrapping.php';
require 'GDText/VerticalAlignment.php';
require 'GDText/HorizontalAlignment.php';
require 'GDText/Struct/Point.php';
require 'GDText/Struct/Rectangle.php';

use GDText\Box;
use GDText\Color;
use GDText\TextWrapping;
use GDText\Struct\Point;
use GDText\Struct\Rectangular;
use GDText\VerticalAlignment;
use GDText\HorizontalAlignment;

class qa_zwp_page
{
	private $directory;
	private $urltoroot;

	public function load_module($directory, $urltoroot)
	{
		$this->directory = $directory;
		$this->urltoroot = $urltoroot;
	}

	public function suggest_requests() // for display in admin interface
	{
		return array(
			array(
				'title' => 'ZWP',
				'request' => 'zwp-plugin-page',
				'nav' => 'null', // 'M'=main, 'F'=footer, 'B'=before main, 'O'=opposite main, null=none
			),
		);
	}


	public function match_request($request)
	{
		return $request == 'zwp-plugin-page';
	}


	public function process_request($request)
	{
        qa_report_process_stage('init_qpicture');        
        qa_db_connect('qa_image_db_fail_handler');
        
        qa_initialize_postdb_plugins();
        $width=1200;
        $height=630;

        $image_width=400;
        $image_height=147;

        $postid = qa_get('qa_id');
        $cachetype = 'qpicture_' . $width;
        $cacheddata = qa_db_cache_get($cachetype, $postid); // see if we've cached the scaled down version
        header('Cache-Control: max-age=2592000, public'); // allows browsers and proxies to cache images too

        if (isset($cacheddata)) {
            header('Content-Type: image/png');
            echo $cacheddata;
        } else {    
            $question = qa_post_get_full($postid);

            $font = QA_BASE_DIR . "qa-plugin/zwp-picture-page/fonts/font.ttf";
            $text = $question["title"];

            $fontSize = 80;
    
            $debug = false;
            $final = imagecreatetruecolor($width, $height);
            $backgroundColor = imagecolorallocate($final, 255, 255, 255);
            imagefill($final, 0, 0, $backgroundColor);
      
            do {
                $im = imagecreatetruecolor($width, $height-$image_height);
                $backgroundColor = imagecolorallocate($im, 255, 255, 255);
                imagefill($im, 0, 0, $backgroundColor);
        
                $box = new Box($im);
                $box->setFontFace($font);
                $box->setFontSize($fontSize);
                $box->setFontColor(new Color(56, 126, 119));
                $box->setBox($width*0.1, 0, $width*0.8, $height);
                $box->setTextAlign('center', 'center');
                $box->setTextWrapping(TextWrapping::WrapWithOverflow);
        
                if($debug) {
                    $box->enableDebug();
                }
        
                $box->draw($text);
                if($debug) {
                    print("overflow <br>");
                }
                $fontSize -= 2;
            }while($box->IsOverflow());
    
            $icon = imagecreatefrompng(QA_BASE_DIR . "qa-plugin/zwp-picture-page/static/q2a_400x147.png");
            imagecopy($final, $im, 0, 0, 0, 0, $width, $height-$image_height);
            imagecopy($final, $icon, 0, 0, 0, 0, $image_width, $image_height);
    
            if(!$debug) {
                header("Content-type: image/png");
                $content = imagepng($final);
                qa_db_cache_set($cachetype, $postid, $content);
                echo $content;
            }
        }
        qa_db_disconnect();
	}
}
