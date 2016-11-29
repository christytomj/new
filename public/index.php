<?php

include '../application/bootstrap.php';

// Pega a variável de ambiente com a configuração desejada
$bootstrap = new Bootstrap(getenv('HANDSON_CONFIG'));
$bootstrap->run();