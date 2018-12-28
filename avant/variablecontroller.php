<?php
$properties = array('serial', 'name', 'department', 'service', 'unit');
        $data = $this->get('app.datatable')->jsonQuery($request->request, $properties);
        $interval = $this->get('app.helper')->getDateInterval($start, 'month');
        $start = $interval['start'];
        $end = $interval['end'];
        $jsonResult = array();
        if ($data) {
            $result = $this->getDoctrine()->getRepository('SageBundle:Employee')
                ->getVariableListActive($company, $data, false, $start->format('c'), $end->format('c'));
            $employees = $result['data'];
            //Filter only Active Employee
            $codes = $this->get('app.manager.variable')->getVariablesCodesByCompany(
                $company,
                $this->getParameter('sage_section_variable_list')
            );

            //Get all variables
            $variables = $this->getDoctrine()->getRepository('SageBundle:Employee\Variable')
                ->getVariablesByEmployeeByCodesByInterval($employees, $codes,
                    $start->format('c'), $end->format('c'));

            //Get the custom variables (Comment) from the table "employee_variable_custom"
            //99999 is the custom value for the Comment !
            $customCodes = Array(99999);
            $customVariables = $this->getDoctrine()->getRepository('SageBundle:Employee\Variable\Custom')
                ->getCustomVariablesByEmployeeByCodesByInterval($employees, $customCodes,
                    $start->format('c'), $end->format('c'));

            //We add the new values to the previous array to have a global one !
            $variables = array_merge($variables, $customVariables);

            //Hash for datatable
            $hashVariables = $this->get('app.hasher')->hashVariables($employees, $variables);
            $result['data'] = $hashVariables;

            //new changes for export
            if ($typeData == 'array') {
                return $hashVariables;
            }

            $links = array();
            $jsonResult = $this->get('app.datatable')->jsonResult(
                $data['draw'],
                $result,
                $links
            );
        }

        $response = new JsonResponse();
        $response->setData($jsonResult);

        return $response;
    }
