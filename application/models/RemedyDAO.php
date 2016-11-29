<?php

/**
 * Contém métodos estáticos relativos à persistência do UserProfile.
 */
class RemedyDAO extends Remedy {
    /**
     * Lista os remédios.
     * @return <array> de Remedios
     * 
     */
    public static function listRemedies() {
        $rsRems = Remedy::get(array('*'));
        $ret = array();
        foreach ($rsRems as $rowRems) {
            $ret[] = new Remedy($rowRems);
        }
        return $ret;
    }

    public static function listApprovedRemedies() {
        $rsRems = Remedy::get(array('*'), array('approved'=>true));
        $ret = array();
        foreach ($rsRems as $rowRems) {
            $ret[] = new Remedy($rowRems);
        }
        return $ret;
    }

    public static function listApprovedRemediesForLab($idLab) {
        $rsRems = Remedy::get(
            array('*'),
            array(
                'approved'=>true,
            ),
            $idLab
        );
        $ret = array();
        foreach ($rsRems as $rowRems) {
            $ret[] = new Remedy($rowRems);
        }
        return $ret;
    }

    /**
     * Pega uma lista dos Remedy alocados para um user e que ainda tenham
     * crédito disponível.
     * @param <type> $userOuId user ou id de user
     * @return <array> um array() de Remedy
     */
    public static function listRemediesAvailableForUser($userOuId) {
//        $id = ($userOuId instanceof Users)
//            ? $userOuId->id
//            : $userOuId;
//        $allocs = BoxAlloc::listByAllocated($id); // caixas alocadas pro user
//        $remsIds = array(); // os ids de remedios pra puxar do BD
//        foreach ($allocs as $alloc) {
//            if ($alloc->qty > $alloc->used) {
//                $remsIds[] = $alloc->id_remedy;
//            }
//        }
//        
//        $user = Users::getUserById($id);
        
        $ret = array();
//        if (! empty($remsIds)) {
//            if($user->id_profile == 0 && $user->id_owner == NULL){
                $remsDoUser = Remedy::get(array('*'));
//            }
//            else{
//                $remsDoUser = Remedy::get(array('*'), array('listOfIds'=>$remsIds));
//            }
            
            foreach ($remsDoUser as $cadaRem) {
                $cadaRem = new Remedy($cadaRem);
                if($cadaRem->approval == 1)
                $ret[] = $cadaRem;
             }
//        }
        return $ret;
    }

    public static function getById($id) {
        $rem = new Remedy(array('id' => $id));
        $rem->read(array('*'));
        return $rem;
    }
}
