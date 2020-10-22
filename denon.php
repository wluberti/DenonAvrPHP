<?php

require_once 'DenonAvr.php';

$denon = new DenonAvr('192.168.178.31');

#$denon->changeInput('CD');
$denon->startUp();
