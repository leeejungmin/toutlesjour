<?php
use SageBundle\Entity\Reference\CollectiveAgreement;
use SageBundle\Entity\Reference\JobType;
use SageBundle\Entity\Company\Establishment;
use SageBundle\Entity\Reference\ContractType;

public function createWorkflowAddExternForm($company)
   {
       $contract = new Employee\Contract();
       $job = new Employee\Job();
       $people = new People();
       $employee = new Employee();
       $reference =  new CollectiveAgreement();
       $jobType =  new JobType();
       $contractType =  new ContractType();
       $establishment =  new Establishment();
       $employee->setSerial($this->getEmployeeNextSerial($company));
       $account = new \AppBundle\Form\Model\EmployeeAccountModel();
       $employee->setPeople($people);
       $employee->setCompany($company);
       //$employee->setSerial(72);
       $people->setEmployee($employee);
       //$people->setBirthDistrict(75);

       $contract->setEmployee($employee);
       $contract->setCodeInsee($jobType);
       $contract->setEstablishment($establishment);
       $contract->setType($contractType);
       $contract->setReasonForEntry('this is for fake information');
       $job->setCollectiveAgreement($reference);
       $form = $this->formFactory->create(AddExternWorkflowType::class, array(
           'employee' => $employee,
           'people' => $people,
           'contract' => $contract,
           'job' => $job,
           'account' => $account
       ), array(
           'company' => $company
       ));
     }
