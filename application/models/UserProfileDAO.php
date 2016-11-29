<?php

/**
 * Contém métodos estáticos relativos à persistência do UserProfile.
 */
class UserProfileDAO extends UserProfile {

    private static $userProfiles = null;

    /**
     * Pega os perfis de usuário.
     * @return <array> de UserProfile()
     */
    public static function getUserProfiles() {
        if (self::$userProfiles === null) {
            self::$userProfiles = self::listUserProfiles();
        }
        return self::$userProfiles;
    }

    /**
     * Pega os perfis de usuário filtrado pelos labels.
     * 
     * @param <array> $except lista de labels de perfil pra excluir
     * @return <array> lista de UserProfile() sem os $exclude.
     */
    public static function getUserProfilesFiltered($exclude) {
        $ret = array();
        $profiles = self::getUserProfiles();
        foreach ($profiles as $profile) {
            if (array_search($profile->label, $exclude) !== false) {
                continue;
            }
            $ret[] = $profile;
        }
        return($ret);
    }

    /**
     * Pega o label do perfil
     * @param <int> $id o id do BD
     * @return <string> o label do perfil, false se não tem.
     */
    public static function getLabelFromId($id) {
        foreach (self::getUserProfiles() as $profile) {
            if ($profile->id == $id) {
                return $profile->label;
            }
        }
        return false;
    }

    /**
     *
     * @return <array> Lista todos os labels do BD.
     */
    public static function listAllLabels() {
        $ret = array();
        foreach (self::getUserProfiles() as $profile) {
            $ret[] = $profile->label;
        }
        return $ret;
    }

    protected static $_dataSource = array(
        'id'              => 'id',
        'title'    => 'title',
        'label'           => 'label',
    );
    public static function listUserProfiles($options=array()) {

        $from = 'profiles';

        $select = join(', ', array_values(self::$_dataSource));
        
        $where = '';
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
                case 'title':
                    $column = 'title';
                    break;
                case 'label':
                    $column = 'label';
                    break;
                default:
                    $column = self::$_dataSource[$options['sortColumn']];
                    break;
            }
            
            $order = 'ORDER BY ' . $column . ' ' . $options['sortOrder'];
        }
        
        $limit = '';
        if (!empty($options['rowCount'])) {
            $limit = 'LIMIT ' . (int)$options['rowCount'];
            if ($options['page'] > 1) {
                $limit .= ' OFFSET ' . (int)($options['rowCount'] * ($options['page'] - 1));
            }
        }

        $lista = array();

        $res = self::$_db->fetchAll(
                "SELECT $select FROM $from $where $order $limit");

        foreach ($res as $rs) {
            $lista[] = new UserProfile($rs);
        }

        return $lista;
    }
}
