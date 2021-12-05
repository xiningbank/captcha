<?php
//namespace xxxx;

use think\Session;

class MyCaptcha
{
    protected $key = 'mycaptcha';

    public function setKey(string $key): self {
        $this->key = $key;
        return $this;
    }

    public function getKey(): string {
        return $this->key;
    }

    public function create() {
        // Adapted for The Art of Web: www.the-art-of-web.com
        // Please acknowledge use of this code by including this header.
        // initialise image with dimensions of 120 x 30 pixels
        $image = @imagecreatetruecolor(120, 30) or die("Cannot Initialize new GD image stream");

        // set background to white and allocate drawing colours
        $background = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);
        imagefill($image, 0, 0, $background);
        $linecolor = imagecolorallocate($image, 0xCC, 0xCC, 0xCC);
        $textcolor = imagecolorallocate($image, 0x33, 0x33, 0x33);

        // draw random lines on canvas
        for($i=0; $i < 6; $i++) {
            imagesetthickness($image, rand(1,3));
            imageline($image, 0, rand(0,30), 120, rand(0,30), $linecolor);
        }

        // add random digits to canvas
        $digit = '';
        for($x = 15; $x <= 95; $x += 20) {
            $digit .= ($num = rand(0, 9));
            imagechar($image, rand(3, 5), $x, rand(2, 14), $num, $textcolor);
        }

        // record digits in session variable
        $captcha = password_hash($digit, PASSWORD_BCRYPT, ['cost' => 10]);
        Session::set($this->key, $captcha);

        // display image and clean up
        header('Content-type: image/png');
        ob_end_clean();
        imagepng($image);
        imagedestroy($image);
    }

    public function verify($code): bool {
        if (!$captcha = Session::get($this->key)) {
            return false;
        }

        // $code = mb_strtolower($code, 'UTF-8');

        if (!password_verify($code, $captcha)) {
            return false;
        }

        Session::delete($this->key);

        return true;
    }
}
