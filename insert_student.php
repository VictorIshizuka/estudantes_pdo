<?php

use Victor\Pdo\Domain\Model\Student;
use Victor\Pdo\Infrastructure\Persistence\ConnectionCreator;


require_once 'vendor/autoload.php';

$pdo = ConnectionCreator::createConnection();


$student = new Student(
  null, 
  'Victor',
  new \DateTimeImmutable('2003-03-23'));

$slqInsert= "INSERT INTO students (name, birth_date) VALUES (?,?)";
$statement = $pdo->prepare($slqInsert);

$statement->bindValue(1,$student->name());
$statement->bindValue(2,$student->birthDate()->format('Y-m-d'));

if($statement->execute()){
  echo 'estudante incluido!';
}

// var_dump($pdo->exec($slqInsert));

