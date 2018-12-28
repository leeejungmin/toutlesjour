<?php
//$properties = array('serial', 'name', 'establishment', 'onboard', 'active', 'department', 'service', 'unit');
        $properties = array('name', 'establishment', 'onboard', 'active', 'department', 'service', 'unit');
        $data = $this->get('app.datatable')->jsonQuery($request->request, $properties);

        $jsonResult = array();
        if ($data) {
            $employees = $this->get('app.manager.user')->getManagedEmployeeByCompany($company);

            $repo = $this->getDoctrine()->getRepository('SageBundle:Employee');
            $employees = $repo->getEmployeeList($company, $data, $employees);
            //TODO: Display actions according to role
            if ($this->isGranted(UserAccessVoter::ACCESS_MANAGER_ONLY, $company)) {
                $links = array(
                    array(
                        'link_store' => 'actions',
                        'route' => 'app_company_employee_events', 'label' => 'Events',
                        'parameters' => array('company'=> $company->getId()), 'transform' => array()),
                );
            } else {
                $links = array(
                    array(
                        'link_store' => 'actions',
                        'route' => 'app_company_employee_show', 'label' => 'action.view',
                        'parameters' => array('company'=> $company->getId()), 'transform' => array()),
                    array(
                        'link_store' => 'actions',
                        'route' => 'app_company_employee_edit', 'label' => 'action.edit',
                        'parameters' => array('company'=> $company->getId()), 'transform' => array()),
                );
            }
            $links[] = array(
                'link_store' => 'avatar',
                'route' => 'app_downloader_avatar', 'label' => false,
                'parameters' => array('filter' => 'small'), 'transform' => array('id' => 'employee'));

            $jsonResult = $this->get('app.datatable')->jsonResult(
                $data['draw'],
                $employees,
                $links
            );
        }

        $response = new JsonResponse();
        $response->setData($jsonResult);

        return $response;
    }
