<?php

class UserProfile extends HandsOn_BasicModel {
    protected $_data = array(
        'id'        => null,
        'title'     => null,
        'label'     => null,
    );
    
    protected static $_allowedData = array(
        'id',
        'title',
        'label',
    );
    
    protected static $_dataSource = array(
        'id'        => 'id',
        'title'     => 'title',
        'label'     => 'label',
    );

    protected static $_fromTable = 'profiles';

    /**
     * Converte lista de UserProfile pra array
     * @param <Array> $profiles de UserProfile
     * @return <hash> ->id => ->title
     */
    public static function convertObjectToIdTitle($profiles) {
        $ret = array();
        foreach ($profiles as $profile) {
            $ret[$profile->id] = $profile->title;
        }
        return $ret;
    }

    /**
     * Relê este objeto do banco de dados, tem que ter ID.
     * @return $this.
     */
    public function read($data) {
        if (empty($this->id)) {
            throw new Exception('No index found to read data.');
        }
        $select = array();
        foreach ($data as $name) {
            if (!array_key_exists($name, $this->_data)) {
                throw new Exception('Invalid property "' . $name . '"');
            }
            $select[] = self::$_dataSource[$name];
        }
        $select = join(', ', $select);
        
        if (empty($this->id)) {
            $key = 'label';
            $value = self::$_db->quote($this->label);
        } else {
            $key = 'id';
            $value = $this->id;
        }
        $result = self::$_db->fetchRow(sprintf(
                "SELECT %s FROM %s WHERE %s = %s",
                $select, self::$_fromTable, $key, $value
                ));
        if ($result) {
            $this->populate($results);
        }
        return $this;
    }

    /**
     * Salva este objeto no BD, update se tem id, ou insert.
     * @return $this.
     */
    public function save() {
        $data = $this->toArray();
        
        if (isset($this->id)) {
            self::$_db->update('profiles', $data, 'id='.$this->id);
        } else {
            // novo
            self::$_db->insert('profiles', $data);
            
        }
        return $this;
    }

    /**
     * Apaga este obj do BD, e limpa os campos desta instância.
     * @return $this.
     */
    public function delete() {
        if (empty($this->id)) {
            throw new Exception('Perfil não identificado para remoção');
        }
        self::$_db->delete(self::$_fromTable, 'id='.$this->id);
        $this->clear();
        return $this;
    }

}
