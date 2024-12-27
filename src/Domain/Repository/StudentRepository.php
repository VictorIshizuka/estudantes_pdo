<?php

namespace Victor\Pdo\Domain\Repository;

use Victor\Pdo\Domain\Model\Student;

interface StudentRepository
{
    public function allStudents(): array;
    public function studentsBirthAt(\DateTimeInterface $birthDate): array;
    public function save(Student $student): bool;
    public function remove(Student $student): bool;
    public function studentsWithPhones(): array;
}