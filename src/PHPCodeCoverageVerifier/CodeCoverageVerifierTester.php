<?php

include(__DIR__.'/../../vendor/autoload.php');

$clover_xml = $argv[1];
$diff_file = $argv[2];

$codeCoverageVerifier = new PHPCodeCoverageVerifier\CodeCoverageVerifier();
$codeCoverageVerifier->run($clover_xml, $diff_file);