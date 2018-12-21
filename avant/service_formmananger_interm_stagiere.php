<?php
public function createWorkflowAddInterimForm($company)
 {
     $contract = new Employee\Contract();
     $job = new Employee\Job();
     $people = new People();
     $employee = new Employee();
     $employee->setSerial($this->getEmployeeNextSerial($company));
     $account = new \AppBundle\Form\Model\EmployeeAccountModel();
     $employee->setPeople($people);
     $employee->setCompany($company);
     $people->setEmployee($employee);
     $contract->setEmployee($employee);
     $form = $this->formFactory->create(AddInterimWorkflowType::class, array(
         'employee' => $employee,
         'people' => $people,
         'contract' => $contract,
         'job' => $job,
         'account' => $account
     ), array(
         'company' => $company
     ));

     return $form;
 }
 public function createWorkflowAddStagiereForm($company)
 {
     $contract = new Employee\Contract();
     $job = new Employee\Job();
     $paySlip = new Employee\PaySlip();
     $people = new People();
     $employee = new Employee();
     $employee->setSerial($this->getEmployeeNextSerial($company));
     $account = new \AppBundle\Form\Model\EmployeeAccountModel();
     $employee->setPeople($people);
     $employee->setCompany($company);
     $people->setEmployee($employee);
     $contract->setEmployee($employee);
     $form = $this->formFactory->create(AddStagiereWorkflowType::class, array(
         'employee' => $employee,
         'people' => $people,
         'contract' => $contract,
         'job' => $job,
         'payslip' => $paySlip,
         'account' => $account
     ), array(
         'company' => $company
     ));

     return $form;
 }
