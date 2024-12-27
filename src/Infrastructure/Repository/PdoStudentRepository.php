<?php

namespace Victor\Pdo\Infrastructure\Repository;

use Victor\Pdo\Domain\Model\Phone;
use Victor\Pdo\Domain\Model\Student;
use Victor\Pdo\Domain\Repository\StudentRepository;


class PdoStudentRepository implements StudentRepository
{
    private \PDO $connection;

    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    public function allStudents(): array
    {
        $statement = $this->connection->query("SELECT * FROM   students;");

        return $this->hydrateStudentList($statement);
    }

    public function studentsBirthAt(\DateTimeInterface $birthDate): array
    {
        $statement = $this->connection->prepare("SELECT * FROM   students WHERE birth_date = ?;");
        $statement->bindValue(1, $birthDate->format('Y-m-d'));
        $statement->execute();
        return  $this->hydrateStudentList($statement);
    }

    private function hydrateStudentList(\PDOStatement $stmt): array
    {
        $studentDataList = $stmt->fetchAll();
        $studentList = [];
        foreach ($studentDataList as $studentData) {
            $student = new Student(
                $studentData['id'],
                $studentData['name'],
                new \DateTimeImmutable($studentData['birth_date']),
            );
            $this->fillPhoneOf($student);
            $studentList[] = $student;
        }
        return $studentList;
    }

    private function fillPhoneOf(Student $student): void
    {
        $sqlQuery = 'SELECT id area_code, number FROM phones WHERE student_id = ?';
        $stmt = $this->connection->prepare($sqlQuery);
        $stmt->bindValue(1, $student->id(), \PDO::PARAM_INT);
        $stmt->execute();

        $phoneDataList = $stmt->fetchAll();
        foreach ($phoneDataList  as $phoneData) {
            $phone = new Phone(
                $phoneData['id'],
                $phoneData['area_code'],
                $phoneData['number']
            );
            $student->addPhone($phone);
        };
    }
    public function save(Student $student): bool
    {
        if ($student->id() === null) {
            return $this->insert($student);
        }
        return $this->update($student);
    }

    private function insert(Student $student)
    {
        $slqInsert = "INSERT INTO students (name, birth_date) VALUES (?,?);";
        $statement = $this->connection->prepare($slqInsert);
        $statement->bindValue(1, $student->name());
        $statement->bindValue(2, $student->birthDate()->format('Y-m-d'));

        $success = $statement->execute();
        if ($success) {
            $student->defineId($this->connection->lastInsertId());
        }
        return $success;
    }

    private function update(Student $student)
    {
        $sqlUpdate = "UPDATE students SET name = :name, birth_date =  :birth_date WHERE id= :id;";
        $stmt = $this->connection->prepare($sqlUpdate);
        $stmt->bindValue(':name', $student->name());
        $stmt->bindValue(':birth_date', $student->birthDate()->format('Y-m-d'));
        $stmt->bindValue(':id', $student->id(), \PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function remove(Student $student): bool
    {
        $preparedStatement = $this->connection->prepare("DELETE FROM students WHERE id= ?;");
        $preparedStatement->bindValue(1, $student->id(), \PDO::PARAM_INT);
        return $preparedStatement->execute();
    }

    public function studentsWithPhones(): array
    {
        $sqlQuery = 'SELECT students.id,
                            students.name,
                            students.birth_date,
                            phones.id AS phone_id,
                            phones.area_code,
                            phones.number
                     FROM students
                     JOIN phones ON students.id = phones.student_id;';
        $stmt = $this->connection->query($sqlQuery);
        $result = $stmt->fetchAll();
        $studentList = [];

        foreach ($result as $row) {
            if (!array_key_exists($row['id'], $studentList)) {
                $studentList[$row['id']] = new Student(
                    $row['id'],
                    $row['name'],
                    new \DateTimeImmutable($row['birth_date'])
                );
            }
            $phone = new Phone($row['phone_id'], $row['area_code'], $row['number']);
            $studentList[$row['id']]->addPhone($phone);
        }

        return $studentList;
    }
}
