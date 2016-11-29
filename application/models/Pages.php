<?php

class Pages extends HandsOn_BasicModel
{
     
    public function getSiteContentValues($data, $options = null){

         if (!is_array($data)) {
            throw new Exception('Initial data must be an array');
        }

        $order = '';
        if (!empty($options['sortColumn'])) {
            switch($options['sortColumn']) {
                case 'title':
                    $column = 'title';
                    break;
                case 'modification_date':
                    $column = 'modification_date';
                    break;
                default:
                    $column = $this->_dataSource[$options['sortColumn']];
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

        return self::$_db->fetchAll("SELECT * FROM site_content $order $limit");
    }

    public function getSiteContentByID($id){
        return self::$_db->fetchRow("SELECT * FROM site_content WHERE id = ".$id);
    }

    public function updateSiteContent($values){

        $data = array(
            'modification_date' => $values['date'],
            'title' => $values['title'],
            'content' => $values['text']
        );

        self::$_db->update('site_content', $data, 'id ='.$values['id']);

    }

    public function convertDate($date){
        $values = explode("-", $date);
        $dateConvert = $values[2].'/'.$values[1].'/'.$values[0];
        return $dateConvert;
    }

    public function countSite($filterColumn = null, $filter = null)
    {
        return self::$_db->fetchOne("SELECT count(*) FROM site_content");
    }
}