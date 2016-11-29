<?php
/*
Lembre Fácil - Web Service Mobile Interface
Copyright (c) 2009 Lembre Fácil
Autor:  Pedro Moritz de Carvalho Neto <pmoritz@yahoo.com>
Versão: service.php, v1.01 20/07/2009
*/

error_reporting(E_ERROR | E_WARNING | E_PARSE);

/*
 *  Inclui biblioteca NuSOAP
*/
require_once('NuSOAP/nusoap.php');
/*
 *  Instancia objeto servidor do web service
*/
$soap = new soap_server;
/*
 *  Cria documento WSDL (opcional)
*/
$soap->configureWSDL('LembreFacilService', 'http://www.lembrefacil.com.br');
/*
 *  Define namespace do web service
*/
$soap->wsdl->schemaTargetNamespace = 'LembreFacil';
/*
 *  Registra serviço update
*/
$soap->register( 'update',
        array('code' => 'xsd:string'),
        array('schedule' => 'xsd:string'),
        'http://www.lembrefacil.com.br'
);
/*
 *  Implementa o serviço update
 *  Retorna programações não-vencidas e ainda não baixadas
*/
function update( $code ) {
    //$url = 'http://189.8.192.146/teste/lembrefacil/default/schedule/wsapp';
    $url = 'http://www.lembrefacil.com.br/panel/schedule/wsapp';
    $data = array(
            username =>    'wsapp',
            password =>    'wsapp123',
            code    =>    $code,
    );
    $data = http_build_query($data);
    $params = array('http' => array(
                    'method' => 'POST',
                    'content' => $data
    ));
    $context = stream_context_create($params);
    $file = @fopen($url, 'r', false, $context);
    $response = @stream_get_contents($file);

    return $response;
}
/*
 *  Retorna resultado do web service ao cliente
*/
$soap->service(isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '');
?>