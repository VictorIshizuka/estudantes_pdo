<?php


use Victor\Pdo\Domain\Model\Student;
use Victor\Pdo\Infrastructure\Repository\PdoStudentRepository;
use Victor\Pdo\Infrastructure\Persistence\ConnectionCreator;

require_once 'vendor/autoload.php';

$connection = ConnectionCreator::createConnection();
$studentRepository = new PdoStudentRepository($connection);


$connection->beginTransaction();

try{

  $student1 = new Student(
    null,
    'Paula',
    new DateTimeImmutable('1999-11-24')
  );
  
  $studentRepository->save($student1);
  
  $student2 = new Student(
    null,
    'Cynthia',
    new DateTimeImmutable('1975-06-30')
  );
  
  $studentRepository->save($student2);
  
  $connection->commit();
}catch(\PDOException $e){
  echo $e->getMessage();
  $connection->rollBack();

}
  