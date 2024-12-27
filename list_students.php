<?php

use Victor\Pdo\Domain\Model\Student;
use Victor\Pdo\Infrastructure\Persistence\ConnectionCreator;
use Victor\Pdo\Infrastructure\Repository\PdoStudentRepository;

require_once 'vendor/autoload.php';

$pdo = ConnectionCreator::createConnection();
$repository = new PdoStudentRepository($pdo);
// $studentList = $repository->allStudents();
$studentList = $repository->studentsWithPhones();

var_dump($studentList);
