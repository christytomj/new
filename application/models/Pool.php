<?php

class Pool
{
    protected $_data = array();
    
    protected $_count;
    
    protected $_path;
    
    public function __construct()
    {
        $this->_path = UPLOAD_PATH . 'pool/';        
    }
    
    public function get($data, $options = null)
    {
        $files = scandir($this->_path);
        array_shift($files);
        array_shift($files);
        $files = array_values($files);
        
        foreach ($files as $key => $file) {
            if ($options['filterColumn'] == 'label' && !empty($options['filter'])) {
                if (strpos($file, $options['filter']) === false) {
                    continue;
                }
            }
            if ($options['filterColumn'] == 'type' && $options['filter'] == 'image') {
                $imageExtensions = array('jpg', 'jpeg', 'gif', 'png');
                $fileparts = explode('.', $file);
                $ext = strtolower(array_pop($fileparts));
                if (!in_array($ext, $imageExtensions)) {
                    continue;
                }
            }
            
            if (in_array('name', $data)) {
                $this->_data[$key]['name'] = $file;
            }
            if (in_array('link', $data)) {
                $this->_data[$key]['link'] = 'file/' . $file;
            }
            if (in_array('dateModification', $data)) {
                $this->_data[$key]['dateModification'] = filemtime($this->_path . $file);
            }
            if (in_array('size', $data)) {
                $this->_data[$key]['size'] = filesize($this->_path . $file);
            }            
        }
        
        $this->_count = count($this->_data);
        
        if (isset($options['sortOrder'])) {
            $files = array_map('strtolower', $files);
            if ($options['sortOrder'] == 'DESC') {
                array_multisort($files, SORT_DESC, SORT_STRING, $this->_data);
            } else {
                array_multisort($files, SORT_ASC, SORT_STRING, $this->_data);
            }
        }
        
        if (!empty($options['rowCount'])) {
            $this->_data = array_slice($this->_data, ($options['page'] - 1), ($options['rowCount'] * ($options['page'])));
        }
        return $this->_data; 
    }
    
    public function count()
    {
        return $this->_count;
    }
    
    public function delete($name)
    {
        if (!is_string($name)) {
            throw new Exception('Initial data must be an string');
        }
        if ($this->_validName($name) == false) {
            throw new Exception('Nome de arquivo inválido');
        }
        if (!file_exists($this->_path . $name)) {
            throw new Exception('Arquivo não existe');

        }
        unlink($this->_path . $name);        
    }
    
    public function save($filename)
    {
        $name = array_pop(explode('/', $filename));
        rename($filename, $this->_path . $name);
    }
    
    protected function _validName($name)
    {
        $invalidCharacters = '/\,*?<>"|' . "'\n\t";
        $invalidNames = array(
            'CON',
            'PRN',
            'CLOCK$',
            'LPT1',
            '.',
            '..',                        
        );
        $sanitizedName = str_replace($invalidCharacters, '', $name);
        if ($sanitizedName != $name) {
            return false;
        }
        
        foreach ($invalidNames as $invalid) {
            if ($invalid == $name) {
                return false;
            }
        }
        return true;
    }
}
