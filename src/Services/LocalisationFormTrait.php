<?php


namespace App\Services;


use App\Entity\Department;
use App\Repository\DepartmentRepository;
use App\Repository\TownRepository;

trait LocalisationFormTrait
{
    protected $departmentRepository;
    protected $townRepository;

    public function __construct(DepartmentRepository $departmentRepository, TownRepository $townRepository)
    {
        $this->departmentRepository = $departmentRepository;
        $this->townRepository = $townRepository;
    }

    protected function getDepartmentByCountry(?string $country = null): array
    {
        return $this->departmentRepository->findBy(
            ['country' => $country ?? 'fr'],
            ['code' => 'ASC']
        );
    }

    protected function getTownsByDepartment(?Department $department = null): array
    {
        if ($department) {
            return $this->townRepository->findBy(['department' => $department]);
        } else {
            $department = $this->getDepartmentByCountry("fr");
            return $this->townRepository->findBy(['department' => $department[0]]);
        }
    }
}