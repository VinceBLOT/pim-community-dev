<?php
$coverage->stop();

$writer = new PHP_CodeCoverage_Report_PHP;
$objectName = 'proc_'.getmypid().'_'.uniqid();
$writer->process($coverage, __DIR__. '/../../../build/coverage/'.$objectName.'cov');

$writer = new PHP_CodeCoverage_Report_HTML;
$writer->process($coverage, '/tmp/test');
