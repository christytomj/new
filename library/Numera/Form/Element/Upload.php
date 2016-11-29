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

class HandsOn_Form_Element_Upload extends Zend_Form_Element_Xhtml
{    
    public $helper = 'formFile';
    
    protected $_count = null;
    protected $_destinationName = null;
    protected $_destinationPath = null;
    protected $_destinationExtension = null;
    protected $_mimetypes = null;
    protected $_size = null;
    
    public function hasFiles()
    {
    	return isset($_FILES[$this->getId()]);
    }
    
    public function setCount($count)
    {
        $this->_count = $count;
        return $this;
    }
    
    public function getCount()
    {
        return $this->_count;
    }

    public function setDestination($path, $name, $extension = null)
    {
        $this->_destinationPath = $path;
        $this->_destinationName = $name;
        $this->_destinationExtension = $extension;
        return $this;
    }
    
    public function getDestination()
    {
        return $this->_destination;
    }
    
    public function setMimetypes($mimetypes)
    {
    	$this->_mimetypes = $mimetypes;
    	return $this;
    }
    
    public function getMimetypes()
    {
    	return $this->_mimetypes;
    }
    
    public function setSize($size)
    {
    	$this->_size = (int)$size;
    	return $this;
    }
    
    public function getSize()
    {
    	return $this->_size;
    }
    
    protected function _extension($mime)
    {
    	$extensions = array(
    	   'image/jpg'   => '.jpg',
    	   'image/jpeg'  => '.jpg',
    	   'image/pjpeg' => '.jpg',
    	   'image/png'   => '.png',
    	   'image/gif'   => '.gif'
    	);    	
    	return (isset($extensions[$mime])) ? $extensions[$mime] : null;
    }
    
    public function receive($fileId = null)
    {
        $upload = new Zend_File_Transfer_Adapter_Http();
        if ($this->getCount() !== null) {
            $upload->addValidator('Count', true, $this->getCount());
        }
        if ($this->getSize() !== null) {
            $upload->addValidator('Size', true, $this->getSize());
        }
        if ($this->getMimetypes() !== null) {
        	$upload->addValidator('MimeType', true, $this->getMimetypes());
        }
        
        if (!$upload->isValid()) {
            $this->_messages = $upload->getMessages();
            $this->_errors = $upload->getErrors();
            return false;
        }
        $upload->receive();
        
        $files = $upload->getFileInfo();
        $destinations = array();
        foreach ($files as $file => $info) {
        	if (isset($this->_destinationPath)) {
        	   $destination = $this->_destinationPath . '/' . $this->_destinationName . $this->_extension($info['type']);
	           rename($upload->getFileName($file), $destination);
        	}
	        $destinations[] = isset($destination) ? $destination : $upload->getFileName($file);
        }
        return (count($destinations) == 1) ? $destinations[0] : $destinations;
        
        
        //$uploadedFile = $_FILES[$this->getId()];
        //$separator = strrpos($uploadedFile['name'], '.');
        //$extension = substr($uploadedFile['name'], $separator + 1);
    }
    

}
 