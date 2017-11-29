<?php
$signature = hash('sha256', $_GET['input'] . '5b812b04690463ca23d2113758ff8efa');
echo $signature;