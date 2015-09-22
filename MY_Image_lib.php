<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Extend Image Library Class
 *
 * This class is a library class that extends the CI Image Lib class.
 *
 * @package     CodeIgniter
 * @category    Library
 */
class MY_Image_lib extends CI_Image_lib {

    // local variables to hold original width and height
    var $new_width;
    var $new_height;

    /**
     * Initialize
     *
     * Overload initialize function to keep
     * original width/height
     *
     * @access  public
     * @param   array
     * @return  bool
     */
    public function initialize($props = array()) {

        // set original width/height
        if (isset($props['width'])) $this->new_width = $props['width'];
        if (isset($props['height'])) $this->new_height = $props['height'];

        // call parent initialize
        parent::initialize($props);
    }

    /**
     * Crop and Resize
     *
     * Resize the image proportionally and best case crop
     *
     * @access  public
     */
    public function resize_and_crop() {

        // make sure source exists
        if (!file_exists($this->full_src_path)) return FALSE;

        // get destination extension
        $dest_ext = strtolower(substr(strrchr($this->dest_image, "."),1));

        // find destination mime type
        if ($dest_ext == "gif") {
            $dest_mime = "image/gif";
        } elseif (($dest_ext == "jpg") || ($dest_ext == "jpeg")) {
            $dest_mime = "image/jpeg";
        } elseif ($dest_ext == "png") {
            $dest_mime = "image/png";
        }
        if (!isset($dest_mime)) return FALSE;

        // create image resource from source image
        if ($this->mime_type == "image/gif") {
            $image_s = imagecreatefromgif($this->full_src_path);
        } elseif (($this->mime_type == "image/jpeg") || ($this->mime_type == "image/pjpeg")) {
            $image_s = imagecreatefromjpeg($this->full_src_path);
        } elseif ($this->mime_type == "image/png") {
            $image_s = imagecreatefrompng($this->full_src_path);
        }
        if (!isset($image_s)) return FALSE;

        // create truecolor image
        $image_d = imagecreatetruecolor($this->new_width, $this->new_height);

        // special case for png to make a white background or transparent background
        if ($this->mime_type == "image/png")  {
            if ($dest_ext == "png") {
                imagealphablending($image_d, false);
                $colorTransparent = imagecolorallocatealpha($image_d, 0, 0, 0, 127);
                imagefill($image_d, 0, 0, $colorTransparent);
                imagesavealpha($image_d, true);
            } else {
                imagefill($image_d, 0, 0, imagecolorallocate($image_d, 255, 255, 255));
            }
        }

        // get original size
        $w = $this->orig_width;
        $h = $this->orig_height;

        // get proportions of resized
        $new_h = ceil($this->new_width * ($h/$w));
        $new_w = ceil($this->new_height * ($w/$h));

        // change image by cropping by height
        if ($new_h > $this->new_height) {
            $new_w = $this->new_width;
            $crop_height = ($new_h - $this->new_height) / 2;
            imagecopyresampled($image_d,$image_s,0,-$crop_height,0,0,$new_w,$new_h,$w,$h);

        // change image by cropping by width
        } else {
            $new_h = $this->new_height;
            $crop_width = ($new_w - $this->new_width) / 2;
            imagecopyresampled($image_d,$image_s,-$crop_width,0,0,0,$new_w,$new_h,$w,$h);
        }

        // check if we are outputting the new image directly
        $save_path = $this->full_dst_path;
        if ($this->dynamic_output) {

            // set no save path
            $save_path = NULL;

            // set headers
            header("Content-Disposition: filename={$this->dest_image};");
            header("Content-Type: {$dest_mime}");
            header('Content-Transfer-Encoding: binary');
            header('Last-Modified: '.gmdate('D, d M Y H:i:s', time()).' GMT');
        }

        // save or output file
        if ($dest_mime == "image/gif") {
            if ($this->dynamic_output) {
                imagegif($image_d);
            } else {
                imagegif($image_d, $save_path);
            }
        } elseif ($dest_mime == "image/jpeg") {
            imagejpeg($image_d, $save_path, $this->quality);
        } elseif ($dest_mime == "image/png") {
            imagepng($image_d, $save_path);
        }

        // destroy images
        imagedestroy($image_s);
        imagedestroy($image_d);

        return TRUE;
    }
}
// END MY_Image_lib class

/* End of file MY_Image_lib.php */
/* Location: ./application/libraries/MY_Image_lib.php */
