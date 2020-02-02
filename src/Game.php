<?php 

namespace App;

class Game
{
    private $frames;
    private $delays;
    private $loops; 

    private $sessionFileNameWords;
    private $sessionFileNameAnswers;
    private $replaceChar = '';
    private $words;
    private $answers;
    private $session;
    private $header;
    private $footer;
    private $fontSize;
    private $logo;
    private $logoPath = __DIR__ . '/../assets/energi.png';
    private $logoResizedPath;

    public function __construct(
        ?string $words,
        ?bool $lock,
        ?string $answers,
        string $session,
        bool $header = false,
        bool $footer = false,
        int $fontSize = 45,
        bool $logo = false
    ) {
        $this->logo = $logo;
        $this->header = $header;
        $this->footer = $footer;
        $this->session = $session;
        $this->fontSize = $fontSize;
        $this->sessionFileNameWords =  __DIR__ . "/../tmp/words_$session.txt";
        $this->sessionFileNameAnswers =  __DIR__ . "/../tmp/answers_$session.txt";
        $this->answers = ($lock === false) ? $this->setAnswers($answers) : $this->getAnswers();
        $this->words = ($lock === false && !empty($words)) ? $this->setWords($words) : $this->getWords();
        $this->renderFrames($this->renderWords());
    }

    public function getFrames() :array
    {
        return $this->frames;
    }

    public function getDelays() :array
    {
        return $this->delays;
    }

    public function getLoops() :?int
    {
        return $this->loops;
    }

    public function renderWords() :?string
    {
        $this->words = str_replace('<br>', PHP_EOL, $this->words);

        $letters = [
            'a' => $this->replaceChar,
            'b' => $this->replaceChar,
            'c' => $this->replaceChar,
            'd' => $this->replaceChar,
            'e' => $this->replaceChar,
            'f' => $this->replaceChar,
            'g' => $this->replaceChar,
            'h' => $this->replaceChar,
            'i' => $this->replaceChar,
            'j' => $this->replaceChar,
            'k' => $this->replaceChar,
            'l' => $this->replaceChar,
            'm' => $this->replaceChar,
            'n' => $this->replaceChar,
            'o' => $this->replaceChar,
            'p' => $this->replaceChar,
            'q' => $this->replaceChar,
            'r' => $this->replaceChar,
            's' => $this->replaceChar,
            't' => $this->replaceChar,
            'u' => $this->replaceChar,
            'v' => $this->replaceChar,
            'w' => $this->replaceChar,
            'x' => $this->replaceChar,
            'y' => $this->replaceChar,
            'z' => $this->replaceChar
        ];

        $chars = str_split($this->answers);
        foreach($chars as $char){
            unset($letters[$char]);
        }

        return strtr($this->words, $letters);
    }

    public function setWords($words) :?string
    {
        file_put_contents($this->sessionFileNameWords, $words);
        return $words;
    }
    
    public function getWords() :string
    {
        return file_get_contents($this->sessionFileNameWords);
    }

    public function setAnswers($answers) :?string
    {
        file_put_contents($this->sessionFileNameAnswers, $answers);
        return $answers;
    }

    public function getAnswers() :?string
    {
        return file_get_contents($this->sessionFileNameAnswers);
    }

    private function renderFrames($words) :void
    {
        $frames = [];
        $delays = [];

        $font = __DIR__ . '/../assets/monofur.ttf';
        $fontSize = $this->fontSize;
        $headerHeight = ($this->header) ? 10 : 0;
        $footerHeight = ($this->footer) ? 10 : 0;
        $headerHeight = ($this->header && !$this->footer) ?: 20;
        $footerHeight = (!$this->header && $this->footer) ?: 20;
        $xoffset = $fontSize/3;
        $yoffset = $fontSize*1.2 + $headerHeight/2 + $footerHeight/2;
        $yoffset = (strlen(preg_replace("/[^[:alnum:][:space:]]/u", 'f', $words)) <= 1 && !ctype_alnum($words)) ? $fontSize/1.2 + $headerHeight/2 + $footerHeight/2 : $fontSize*1.2 + $headerHeight/2 + $footerHeight/2;

        // Wrap long text
        $words = wordwrap($words, 35, PHP_EOL);

        // Create coordinates from text length to calculate width and height
        $coordinates = $this->calculateTextBox($fontSize, 0, $font, $words);
        $width = $coordinates['width'] + 30;
        $height = $coordinates['height'] + 30 + $headerHeight + $footerHeight;
        $height = (strlen(preg_replace("/[^[:alnum:][:space:]]/u", 'f', $words)) <= 1 && !ctype_alnum($words)) ? $coordinates['height']+30 : $height;

        // Create background image
        $im = imagecreatetruecolor($width, $height);
        $black = imagecolorallocate($im, 0, 0, 0);
        $white = imagecolorallocate($im, 255, 255, 255);
        $green = imagecolorallocate($im, 51, 102, 102);
        imagefilledrectangle($im, 0, 0, $width, $height, $white);

        // Write words
        imagettftext($im, $fontSize, 0, $xoffset, $yoffset, $green, $font, $words);

        // Set the coordinates
        $yPadding = ($this->header && $this->footer) ? 20 : 0;
        $x = $coordinates['width'] /2 - 60;
        $y = $coordinates['height']+40+$yPadding;

        // Header
        if ($this->header) imagettftext($im, 10, 0, $x, 15, $black, $font, ' Congratulations ');

        // Footer
        if ($this->footer) imagettftext($im, 10, 0, $x, $y, $black, $font, 'with  Energi team');

        // Write file
        imagepng($im, __DIR__ . "/../tmp/temp_$this->session.png",0);

        // Resize logo
        if ($this->logo) $this->resizeLogo($width, $height);

        // Gif frames
        for($i = 0; $i <= 1; $i++) {
            if($i == 1 && $this->logo) {
                $logo = imagecreatefrompng($this->logoResizedPath);
                ;
                ob_start();
                imagegif($logo);
                $frames[]=ob_get_contents();
                $delays[]=300;
                $loops = 1;
                ob_end_clean();
                ob_start();
                imagegif($im);
                $frames[]=ob_get_contents();
                $delays[]=300;
                $loops = 1;
                ob_end_clean();
                break;
            } else {
                ob_start();
                imagegif($im);
                $frames[]=ob_get_contents();
                $delays[]=300;
                $loops = 0;
                ob_end_clean();
            }
        }

        $this->frames = $frames;
        $this->delays = $delays;
        $this->loops = $loops;   
    }

    private function resizeLogo(int $newWidth, int $newHeight):void
    {
        $this->logoResizedPath = __DIR__ . "/../tmp/temp_logo_$this->session.png";

        // Resize logo
        $img = imagecreatefrompng($this->logoPath);
        list($width, $height) = getimagesize($this->logoPath);
        $newLogoWidth = ($height / $width) * $newHeight;
        $tmp = imagecreatetruecolor($newLogoWidth, $newHeight);
        imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newLogoWidth, $newHeight, $width, $height);
        imagepng($tmp, $this->logoResizedPath);

        // Create destination image.
        $png = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($png, false);
        imagesavealpha($png, true);

        // Transparency
        $color = imagecolorallocatealpha($png, 0, 0, 0, 127);
        imagefill($png, 0, 0, $color);

        // Load source image.
        $logo = imagecreatefrompng($this->logoResizedPath);
        imagealphablending($logo, false);
        imagesavealpha($logo, true);
        $sizex = imagesx($logo);
        $sizey = imagesy($logo);

        // Copy to destination and save to file.
        imagecopyresampled( $png, $logo,
        $newWidth/2-$newLogoWidth/2, 0,
        0, 0,
        $sizex, $sizey,
        $sizex, $sizey);

        // Save file
        imagepng($png, $this->logoResizedPath);
    }

    private function calculateTextBox(int $font_size, int $font_angle, string $font_file, string $text) :array
    {
        $box   = imagettfbbox($font_size, $font_angle, $font_file, $text);
        
        if( !$box ) return false;

        $min_x = min( array($box[0], $box[2], $box[4], $box[6]) );
        $max_x = max( array($box[0], $box[2], $box[4], $box[6]) );
        $min_y = min( array($box[1], $box[3], $box[5], $box[7]) );
        $max_y = max( array($box[1], $box[3], $box[5], $box[7]) );
        $width  = ( $max_x - $min_x );
        $height = ( $max_y - $min_y );
        $left   = abs( $min_x ) + $width;
        $top    = abs( $min_y ) + $height;

        // to calculate the exact bounding box write the text in a large image
        $img     = @imagecreatetruecolor( $width << 2, $height << 2 );
        $white   =  imagecolorallocate( $img, 255, 255, 255 );
        $black   =  imagecolorallocate( $img, 0, 0, 0 );

        imagefilledrectangle($img, 0, 0, imagesx($img), imagesy($img), $black);

        // text is completely in the image
        imagettftext( $img, $font_size,
            $font_angle, $left, $top,
            $white, $font_file, $text);

        // start scanning (0=> black => empty)
        $rleft  = $w4 = $width<<2;
        $rright = 0;
        $rbottom   = 0;
        $rtop = $h4 = $height<<2;
        for( $x = 0; $x < $w4; $x++ )
            for( $y = 0; $y < $h4; $y++ )
                if( imagecolorat( $img, $x, $y ) ){
                    $rleft   = min( $rleft, $x );
                    $rright  = max( $rright, $x );
                    $rtop    = min( $rtop, $y );
                    $rbottom = max( $rbottom, $y );
                }
                
        // destroy img and serve the result
        imagedestroy( $img );

        return  [
            "left"   => $left - $rleft,
            "top"    => $top  - $rtop,
            "width"  => $rright - $rleft + 1,
            "height" => $rbottom - $rtop + 1 
        ];
    }
}