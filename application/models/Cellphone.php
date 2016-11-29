<?php

class Cellphone extends HandsOn_BasicModel {
    protected $_data = array(
        'id'              => null,
        'manufacturer'    => null,
        'model'           => null,
        'file'            => null,
        'filepath'        => null,
        'filepath_thumb'  => null
    );
    
    protected static $_allowedData = array(
        'id',
        'manufacturer',
        'model',
        'file',
        'filepath',
        'filepath_thumb'
    );
    
    protected static $_dataSource = array(
        'id'              => 'id',
        'manufacturer'    => 'manufacturer',
        'model'           => 'model',
        'file'            => 'file',
        'filepath'        => 'filepath',
        'filepath_thumb'  => 'filepath_thumb'
    );
    
    public function __construct($data = null) {
        $this->filepath = APPLICATION_PATH . '/../public/images/uploads/';
        parent::__construct($data);
    }
    
    public function __set($name, $value) {
        if (!array_key_exists($name, $this->_data)) {
            throw new Exception('Invalid property "' . $name . '"');
        }
        $this->_data[$name] = $value;
    }
    
    public function read($data) {
        if (!is_array($data)) {
            throw new Exception('Initial data must be an array or object');
        }
        $select = '';
        foreach ($data as $name) {
            if (!array_key_exists($name, $this->_data)) {
                throw new Exception('Invalid property "' . $name . '"');
            }
            $select .= empty($select) 
                    ? self::$_dataSource[$name]
                    : ',' . self::$_dataSource[$name];
        }
        
        if (!empty($select)) {
            if (empty($this->id)) {
                $key = 'name';
                $value = self::$_db->quote($this->name);
            } else {
                $key = 'id';
                $value = $this->id;
            }
            $results = self::$_db->fetchRow("SELECT $select FROM cellphone c
                                             WHERE c.$key = $value");
            
            foreach ($results as $key => $value) {
                $this->$key = $value;
            }
        }
    }
    
    public function save() {
        $data = array(
            'id'              => $this->id,
            'manufacturer'    => $this->manufacturer,
            'model'           => $this->model,
        );
        
        if (isset($this->id)) {
            $path = $this->filepath . $this->id;
            if (isset($this->file)) {
                $imageOriginal =  $path .'_original'
                        . HandsOn_Image::extension(exif_imagetype($this->file));
                                
                rename($this->file, $imageOriginal);
                $image = new HandsOn_Image();
                $image->setOptions(array(
                    'source'      => $imageOriginal,
                    'destination' => $path . '.jpg',
                    'width'       => 250,
                    'height'      => 250
                    ))->save();

                $image->setOptions(array(
                    'source'      => $imageOriginal,
                    'destination' => $path . '_thumb.jpg',
                    'width'       => 96,
                    'height'      => 96
                    ))->save();
                
                $explode = explode('/../public', $path);
                $generic_path = $explode[1];
                
                $data['filepath'] = $generic_path . '.jpg';
                $data['filepath_thumb'] = $generic_path . '_thumb.jpg';
                
                unlink($imageOriginal);

            }
             self::$_db->update('cellphone', $data, 'id='.$this->id);
        }
        else {
        // novo
            self::$_db->insert('cellphone', $data);
            
            $lastId = self::$_db->lastInsertId();
            $filepath =  $this->filepath . $lastId;
                        
            if (isset($this->file)) {
                $path = $filepath;
                $imageOriginal =  $path .'_original'. HandsOn_Image::extension(exif_imagetype($this->file));
                
                rename($this->file, $imageOriginal);
                
                $image = new HandsOn_Image();
                $image->setOptions(array(
                    'source'      => $imageOriginal,
                    'destination' => $path . '.jpg',
                    'width'       => 250,
                    'height'      => 250
                    ))->save();
                $image->setOptions(array(
                    'source'      => $imageOriginal,
                    'destination' => $path . '_thumb.jpg',
                    'width'       => 96,
                    'height'      => 96
                    ))->save();
                
                unlink($imageOriginal);

                $explode = explode('/../public', $path);
                $generic_path = $explode[1];

                $update = array(
                    'filepath'       => $generic_path . '.jpg',
                    'filepath_thumb' => $generic_path . '_thumb.jpg'
                );
                self::$_db->update('cellphone', $update, 'id='.$lastId);
            }
        }
    }
    
    public function delete() {
        if (empty($this->id)) {
            throw new Exception('Modelo não identificado para remoção');
        }
        $values = self::$_db->fetchRow('SELECT filepath, filepath_thumb FROM cellphone WHERE id='.$this->id);
        @unlink($values['filepath']);
        @unlink($values['filepath_thumb']);
        self::$_db->delete('cellphone', 'id='.$this->id);
    }
    
    public function getCellPhones($data, $options) {
        
        $from = 'cellphone';
        
        if (!is_array($data)) {
            throw new Exception('Initial data must be an array');
        }
        
        $select = '';
        foreach ($data as $name) {
            if (!in_array($name, self::$_allowedData)) {
                throw new Exception('Invalid property "' . $name . '"');
            }
            $select .= empty($select) 
                    ? self::$_dataSource[$name]
                    : ',' . self::$_dataSource[$name];
        }
        
        $where = null;
        if (!empty($options['filterColumn']) && !empty($options['filter'])) {
            $where .= sprintf(
                    "WHERE %s LIKE %s",
                    $options['filterColumn'],
                    self::$_db->quote('%%' . $options['filter'] . '%%')
            );
        }
        
        $order = '';
        if (!empty($options['sortColumn'])) {
            switch($options['sortColumn']) {
                case 'manufacturer':
                    $column = 'manufacturer';
                    break;
                case 'model':
                    $column = 'model';
                    break;
                default:
                    $column = self::$_dataSource[$options['sortColumn']];
                    break;
            }
            
            $order = 'ORDER BY ' . $column . $options['sortOrder'];
        }
        
        $limit = '';
        if (!empty($options['rowCount'])) {
            $limit = 'LIMIT ' . (int)$options['rowCount'];
            if ($options['page'] > 1) {
                $limit .= ' OFFSET ' . (int)($options['rowCount'] * ($options['page'] - 1));
            }
        }
        
        return self::$_db->fetchAll("SELECT $select FROM $from $where $order $limit");
    }
    
    public function getPhoneByID($id) {
        return self::$_db->fetchRow("SELECT * FROM cellphone WHERE id = ".$id);
    }
    
    public function count($filterColumn = null, $filter = null) {
        
        $from = 'cellphone';
        
        $where = null;
        if (!empty($filterColumn) && !empty($filter)) {
            $where = sprintf(
                    "WHERE %s LIKE %s",
                    $filterColumn,
                    self::$_db->quote('%%' . $filter . '%%')
            );
        }
        
        return self::$_db->fetchOne("SELECT count(*) FROM $from $where");
    }
}
