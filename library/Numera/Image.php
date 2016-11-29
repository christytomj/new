<?php
/**
 * Singular - Academic Resource Planning
 *
 * LICENÇA
 *
 * Este arquivo-fonte é propriedade da Númera Soluções e Sistemas Ltda.,
 * empresa brasileira inscrita no CNPJ/MF sob nº 08.179.010/0001-48.
 * A reprodução parcial ou total do conteúdo é expressamente vedada, conforme
 * descrição detalhada da licença, disponível no documento "docs/license.txt".
 * Se o arquivo estiver ausente, por favor entre em contato pelo email
 * license@numera.com.br para que possamos enviar uma cópia imediatamente. 
 *
 * @copyright  Copyright (c) 2008 Númera Soluções e Sistemas Ltda. (http://www.numera.com.br)
 * @license    http://www.numera.com.br/license/singular     Singular 1.0 License
 * @version    $Id$
 */

class Numera_Image
{
	protected $_options = array(
	   'source'      => null,
       'destination' => null,
       'width'       => null,
       'height'      => null
	);
	
    public static function extension($mime)
    {
        $extensions = array(
           'image/jpg'    => '.jpg',
           'image/jpeg'   => '.jpg',
           'image/pjpeg'  => '.jpg',
           'image/png'    => '.png',
           'image/gif'    => '.gif',
           IMAGETYPE_GIF  => '.gif',
           IMAGETYPE_JPEG => '.jpg', 
           IMAGETYPE_PNG  => '.png'
        );      
        return (isset($extensions[$mime])) ? $extensions[$mime] : null;
    }
	
	public function __construct($data = null)
    {
        if (isset($data)) {
            $this->setOptions($data);
        }
    }

    public function setOptions($data)
    {
        if (!is_array($data)) {
            throw new Exception('Data must be an array');
        }

        foreach ($data as $key => $value) {
            $this->_options[$key] = $value;
        }
        return $this;
    }
	
	function save($forceResize = false) {
	    $imageInfo = getImageSize($this->_options['source']); // see EXIF for faster way
	    switch ($imageInfo['mime']) {
	        case 'image/gif':
	            if (imagetypes() & IMG_GIF) {
	                $source = imageCreateFromGIF($this->_options['source']);
	            } else {
	                throw new Exception('GIF images are not supported');
	            }
	            break;
	        case 'image/jpeg':
	            if (imagetypes() & IMG_JPG) {
	                $source = imageCreateFromJPEG($this->_options['source']) ;
	            } else {
	                throw new Exception('JPEG images are not supported');
	            }
	            break;
	        case 'image/png':
	            if (imagetypes() & IMG_PNG) {
	                $source = imageCreateFromPNG($this->_options['source']) ;
	            } else {
	                throw new Exception('PNG images are not supported');
	            }
	            break;
	        case 'image/wbmp':
	            if (imagetypes() & IMG_WBMP) {
	                $source = imageCreateFromWBMP($this->_options['source']) ;
	            } else {
	                throw new Exception('WBMP images are not supported');
	            }
	            break;
	        default:
	            throw new Exception($image_info['mime'].' images are not supported');
	    }
		    
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $destinationWidth = $this->_options['width'];
        $destinationHeight = $this->_options['height'];
        
        if ($forceResize == false && $sourceWidth <= $destinationWidth && $sourceHeight <= $destinationHeight) {
            $destinationWidth = $sourceWidth;
            $destinationHeight = $sourceHeight;
        } else if ($sourceWidth > $sourceHeight) {
            $destinationHeight = $sourceHeight * $destinationWidth / $sourceWidth;
        } else if ($sourceWidth < $sourceHeight) {
            $destinationWidth = $sourceWidth * $destinationHeight / $sourceHeight;
        }
	        
        $destination = imageCreateTrueColor($destinationWidth, $destinationHeight);
        imageCopyResampled($destination, $source, 0, 0, 0, 0, $destinationWidth, $destinationHeight, $sourceWidth, $sourceHeight);
        imageJPEG($destination, $this->_options['destination']);
        imageDestroy($source);
        imageDestroy($destination);
	}
}