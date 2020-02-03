<?php

use App\Game;
use Lib\AnimatedGif;

require 'vendor/autoload.php';

header('Content-type: image/gif');
header('Content-Disposition: filename="glyphs.gif"');

$logo = ($_GET['logo'] == 1) ? true : false ;
$loop = ($_GET['loop'] == 1) ? true : false ;
$header = ($_GET['header'] == 1) ? true : false ;
$footer = ($_GET['footer'] == 1) ? true : false ;
$fontSize = (!empty($_GET['fontsize'])) ? $_GET['fontsize'] : 45 ;
$defaultSession = (!empty($_GET['session'])) ? $_GET['session'] : 'default' ;

$game = new Game($_GET['words'], $_GET['lock'], $_GET['answers'], $defaultSession, $header, $footer, $fontSize, $logo, $loop);
$gif = new AnimatedGif($game->getFrames(), $game->getDelays(), $game->getLoops());

$gif->display();
		
header( 'Expires: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );
?>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-F2Y2E5LB0V"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-F2Y2E5LB0V');
</script>