<?php

// header('Access-Control-Allow-Origin:  http://localhost:4200/');
// header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
// header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Origin, Authorization');

use Dingo\Api\Routing\Router;

/** @var Router $api */
$api = app(Router::class);

$api->version('v1', function (Router $api) {
    $api->get('file-aportacion-efectivo', 'App\Api\V1\Controllers\ContractController@getAportacionEfectivo');
    $api->get('file-aportacion-especie', 'App\Api\V1\Controllers\ContractController@getAportacionEspecie');
    $api->get('file-gasto-efectivo', 'App\Api\V1\Controllers\ContractController@getGastoEfectivo');
    $api->get('file-contrato-aportacion', 'App\Api\V1\Controllers\ContractController@getContratoAportacion');
    $api->get('file-aportacion-militantes', 'App\Api\V1\Controllers\ContractController@getAportacionMilitantes');
    $api->get('file-aportacion-militantes-precam', 'App\Api\V1\Controllers\ContractController@getAportacionMilitantesPrecam');
    $api->get('file-aportacion-militantes-coa', 'App\Api\V1\Controllers\ContractController@getAportacionMilitantesCoa');
    $api->get('file-aportacion-simpatizantes', 'App\Api\V1\Controllers\ContractController@getAportacionSimpatizantes');
    $api->get('file-aportacion-simpatizantes-coa', 'App\Api\V1\Controllers\ContractController@getAportacionSimpatizantesCoa');
    $api->get('file-simpatizantes-efect-especie', 'App\Api\V1\Controllers\ContractController@getAportacionSimEfecEspecie');
    $api->get('file-formulario-registro', 'App\Api\V1\Controllers\ContractController@getFormularioRegistro');

    $api->get('hello', function () {
        return response()->json([
            'message' => 'This is a simple example of item returned by your APIs. Everyone can see it.',
        ]);
    });
});
