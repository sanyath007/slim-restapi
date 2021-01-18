<?php

namespace App\Controllers;

use App\Controllers\Controller;

class UserController extends Controller
{
    public function home($request, $response, $args) 
    {
		// your code here
		// use $this->view to render the HTML
		return $response;
    }

    public function user($req, $res, $args)
    {
    	try {
			$cid = $args['cid'];
			$conn = $this->container->db;

			$sql = "SELECT e.*, p.position_name 
					FROM employees e 
					LEFT JOIN positions p ON (e.position_id=p.id) 
					WHERE (emp_id=:cid)";

			$pre = $conn->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
			$values = [':cid' => $cid];

			$pre->execute($values);
			$result = $pre->fetch();

			if ($result) {
				return $res->withJson([
					'employee' => [
						'cid' => $result['emp_id'],
						'fullName' => $result['prefix'] . $result['emp_fname']. ' ' .$result['emp_lname'],
						'position' => $result['position_name']
					]
				], 200);
			} else {
				return $res->withJson([
					$result
				]);
			}		
		} catch (Exception $e) {
			return $res->withJson([
				'error' => $e->getMessage()
			], 442);
		}
    }
}