<?php

class City extends HandsOn_BasicModel {

    protected $_data = array(
        'id'        => null,
        'name'      => null,
        'uf'        => null,
    );

    protected static $_allowedData = array(
        'id',
        'name',
        'uf',
    );

    protected static $_dataSource = array(
        'id'        => 'id',
        'name'      => 'name',
        'uf'        => 'uf',
    );

    public static function getById($id) {
        $city = new City();
        if (is_numeric($id)) {
            $sql = sprintf(
                'SELECT * FROM city WHERE id=%s',
                self::$_db->quote($id)
            );
            $rs = self::$_db->fetchRow($sql);
            $city->populate($rs);
        }
        return $city;
    }

    /**
     * Lista as cidades.
     *
     * @param <string> $uf a UF com 2 letras, opcional
     * @param <string> $part se presente, filtra o nome da cidade
     * @return <type> array() de city.
     */
    public static function listData($uf=null, $part=null) {
        $data = self::$_allowedData;

        $select = '*'; //join(', ', self::$_allowedData);

        $from = 'city';

        $where = array();
        if (!empty($uf)) {
            $where[] = sprintf(
                    'uf = %s',
                    self::$_db->quote($uf));
        }
        if (!empty($part)) {
            $where[] = sprintf(
                "name LIKE %s",
                self::$_db->quote('%%' . $part . '%%')
            );
        }
        $where = 'WHERE ' . join(' AND ', $where);

        $limit = ''; // LIMIT 15 ';

        $rs = self::$_db->fetchAll(
                "SELECT $select FROM $from $where $limit");
        foreach ($rs as $rec) {
            $ret[] = new City($rec);
        }
        return $ret;
    }

    /**
     * Lista id => nome as cidades.
     *
     * @param string $uf a UF com 2 letras, opcional
     * @param string $part se presente, filtra o nome da cidade
     * @return array de city->id => city->name.
     */
    public static function listHashIdName($uf=null, $part=null) {
        $list = City::listData($uf, $part);
        $rets = array();
        foreach ($list as $ea) {
            $rets[$ea->id] = $ea->name;
        }
        return $rets;
    }

    /**
     * @return array lista de 'siglas de estados'=>'nomes'
     */
    public static function getUFs() {
        $estados = array(
            "AC" => "Acre",
            "AL" => "Alagoas",
            "AP" => "Amapá",
            "AM" => "Amazonas",
            "BA" => "Bahia",
            "CE" => "Ceará",
            "DF" => "Distrito Federal",
            "ES" => "Espírito Santo",
            "GO" => "Goiás",
            "MA" => "Maranhão",
            "MG" =>  "Minas Gerais",
            "MT" =>  "Mato Grosso",
            "MS" =>  "Mato Grosso do Sul",
            "PA" =>  "Pará",
            "PR" =>  "Paraná",
            "PE" =>  "Pernambuco",
            "PI" =>  "Piauí",
            "RJ" =>  "Rio de Janeiro",
            "RN" =>  "Rio Grande do Norte",
            "RS" =>  "Rio Grande do Sul",
            "RO" =>  "Rondônia",
            "RR" =>  "Roraima",
            "SC" =>  "Santa Catarina",
            "SP" =>  "São Paulo",
            "SE" =>  "Sergipe",
            "TO" =>  "Tocantins");
        return $estados;
    }
}
