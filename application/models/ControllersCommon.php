<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ControllersCommon
 *
 * @author mauro
 */
class ControllersCommon {
    //put your code here
    /**
     * Calcula o preco em creditos do array $alboxes, multiplicando a
     * quantidade de caixas pelo numro de itens do remedio.
     *
     * @param <type> $alboxes o array de remedios alocados naquele
     *      formato estranho
     * @return <int> total de credis necessarios para $alboxes
     */
    public static function calclaTotalCreditosBoxes($alboxes) {
        $totCred = 0;
        foreach ($alboxes as $eaal) {
            $remd = RemedyDAO::getById($eaal[0]);
            $qtRem = $eaal[1];
            $totCred += $remd->qty * $qtRem;
        }
        return $totCred;
    }

    /**
     *  Desconta labcreds do usuário.
     *
     * @param <object Users> $descUser o usuario a ser descontado
     * @param <int> $totCred a quantodade de creditos a tirar
     */
    public static function descontaDoLab($descUser, $totCred) {
        // tira o crédito do lab
        $descUser->labcredit -= $totCred;
        $descUser->saveLabCredit();
    }

    /**
     * Aloca caixas para o usuario.
     *
     * @param <type> $idDescUser o id do dono atual dos creditos
     * @param <type> $alboxes o arrai de boxes no formato estranho
     * @param <type> $idRecip o id do usuario que vai receber as caixas.
     */
    public static function alocaBoxes($idDescUser, $alboxes, $idRecip) {
        // aloca as caixas
        foreach ($alboxes as $eaal) {
            $alBox = new BoxAlloc(array(
                // 'id' => null,
                'id_allocated' => $idRecip,
                'qty' => $eaal[1],
                'id_remedy' => $eaal[0],
                'id_origin' => $idDescUser,
            ));
            $alBox->save();
        }
    }

    /**
     * Troca o dono dos remedios da lista.
     *
     * @param array $alboxes a lista de remedios pra trocar, no
     *      formato estranho.
     * @param array $myRemedy a lista de BoxAlloc do cUser
     * @param Users $cUser o usuario fornecedor dos remedios
     * @param User $account o usuario recebedor dos remedios
     * @return array lista de erros como objetos: 
     *          {id:<id do remedio>, qty:<quantidade que faltou>}
     */
    public static function changeRemedyOwn($alboxes, $myRemedy, $cUser, $account) {
        $erros = array();
        foreach ($alboxes as $eaal) { // percorre os remédios desejados
            $eaId = $eaal[0];
            $eaQty = $eaal[1];
            $rems = Util::findObjectByProperty(
                $myRemedy,
                'id_remedy', $eaId);
            foreach ($rems as $rem) { // percorre os remédios que o cedente já tem
                if ($rem->getCredit() == 0) {
                    continue;
                }
                if ( ($rem->getCredit()) < $eaQty ) {
                    $part = $rem->getCredit();
                    $eaQty -= $part;
                    $rem->used = $rem->qty;
                    $rem->save();
                    $alBox = new BoxAlloc(array(
                        'id_allocated' => $account->id,
                        'qty' => $part,
                        'id_remedy' => $eaId,
                        'id_origin' => $cUser->id,
                    ));
                    $alBox->save();
                } else {
                    // tem crédito neste remedio
                    $rem->used += $eaQty;
                    $rem->save();
                    $alBox = new BoxAlloc(array(
                        'id_allocated' => $account->id,
                        'qty' => $eaQty,
                        'id_remedy' => $eaId,
                        'id_origin' => $cUser->id,
                    ));
                    $alBox->save();
                    $eaQty = 0;
                    break; // sai da lista de remedios achados
                }
            }

            if ($eaQty > 0) {
                // não conseguiu alocar todos remédios
                $erro = new stdClass();
                $erro->id = $eaId;
                $erro->qty = $eaQty;
                $erros[] = $erro;
            }
        }
        
        return $erros;
    }

}
?>
