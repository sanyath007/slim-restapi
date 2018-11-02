<?php
namespace App\Controllers;

use Psr\Container\ContainerInterface;

class EmployeeController
{
	protected $container;
    protected $view;

    public function __construct(ContainerInterface $container) 
    {
        $this->container = $container;
    }

    public function info($req, $res, $args)
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
					'status' => 'success',
					'employee' => [
						'cid' => $result['emp_id'],
						'fullName' => $result['prefix'] . $result['emp_fname']. ' ' .$result['emp_lname'],
						'position' => $result['position_name'] . $result['position_level']
					]
				], 200);
			} else {
				return $res->withJson([
					'status' => 'error',
				]);
			}		
		} catch (Exception $e) {
			return $res->withJson([
				'error' => $e->getMessage()
			], 442);
		}
    }

    public function employee($req, $res, $args)
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
					'status' => 'success',
					'employee' => $result,
				], 200);
			} else {
				return $res->withJson([
					'status' => 'error',
				]);
			}		
		} catch (Exception $e) {
			return $res->withJson([
				'error' => $e->getMessage()
			], 442);
		}
    }

    public function employeeList($req, $res, $args)
    {
    	try {
			$conn = $this->container->db;

			$sql = "SELECT e.*, p.position_name FROM employees e LEFT JOIN positions p ON (e.position_id=p.id)";

			$pre = $conn->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);

			$pre->execute();
			$result = $pre->fetchAll();
			
			if ($result) {
				return $res->withJson([
					'status' =>'success',
					'employees' => $result
				], 200);
			} else {
				return $res->withJson([
					'status' => 'error',
					$result
				]);
			}		
		} catch (Exception $e) {
			return $res->withJson([
				'error' => $e->getMessage()
			], 442);
		}
    }

    public function positionList($req, $res, $args)
    {
    	try {
			$conn = $this->container->db;

			$sql = "SELECT * FROM positions";

			$pre = $conn->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);

			$pre->execute();
			$result = $pre->fetchAll();
			
			if ($result) {
				return $res->withJson([
					'status' =>'success',
					'positions' => $result
				], 200);
			} else {
				return $res->withJson([
					'status' => 'error',
					$result
				]);
			}		
		} catch (Exception $e) {
			return $res->withJson([
				'error' => $e->getMessage()
			], 442);
		}
    }

    public function employeeAdd($req, $res)
    {
    	try {
			$conn = $this->container->db;
			
			$sql = "INSERT INTO employees (emp_id, prefix, emp_fname, emp_lname, 
					birthdate, sex, position_id, position_level, created_at, updated_at)
					VALUES(?,?,?,?,?,?,?,?,?,?)";
			
			$pre = $conn->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
			$values = [
				$req->getParam('cid'), 
				$req->getParam('prefix'), 
				$req->getParam('fname'), 
				$req->getParam('lname'), 
				$req->getParam('birthdate'),
				$req->getParam('sex'),
				$req->getParam('position'),
				$req->getParam('level'),
				date('Y-m-d H:i:s'), 
				date('Y-m-d H:i:s'),
			];

			if ($pre->execute($values)) {
				return $res->withJson([
					'status' =>'success',
				], 200);
			} else {
				return $res->withJson([
					'status' => 'error'
				]);
			}
		} catch (Exception $e) {
			return $res->withJson([
				'status' => 'error',
				'message' => $e->getMessage()
			], 442);
		}
    }

    public function employeeUpdate($req, $res, $args)
    {
    	try {
			$conn = $this->container->db;

			$prefix = $req->getParam('prefix');
			$fname = $req->getParam('fname');
			$lname = $req->getParam('lname');
			$birthdate = $req->getParam('birthdate');
			$sex = $req->getParam('sex');
			$position = $req->getParam('position');
			$level = $req->getParam('level');
			$updated = date('Y-m-d H:i:s');

			$sql = "UPDATE employees SET ";
			if ($prefix != '') $sql .= "prefix=:prefix, ";
			if ($fname != '') $sql .= "emp_fname=:fname, ";
			if ($lname != '') $sql .= "emp_lname=:lname, ";
			if ($birthdate != '') $sql .= "birthdate=:birthdate, ";
			if ($sex != '') $sql .= "sex=:sex, ";
			if ($position != '') $sql .= "position_id=:position, ";
			if ($level != '') $sql .= "position_level=:level, ";
			$sql .= "updated_at=:updated ";
			$sql .= "WHERE (emp_id=:cid)";
			
			$pre = $conn->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
			$pre->bindParam(":cid", $args['cid']);
			// $pre->bindParam(":emp_id", $req->getParam('cid'));
			if ($prefix != '') { $pre->bindParam(":prefix", $prefix); }
			if ($fname != '') { $pre->bindParam(":fname", $fname); }
			if ($lname != '') $pre->bindParam(":lname", $lname);
			if ($birthdate != '') $pre->bindParam(":birthdate", $birthdate);
			if ($sex != '') $pre->bindParam(":sex", $sex);
			if ($position != '') $pre->bindParam(":position", $position);
			if ($level != '') $pre->bindParam(":level", $level);

			$pre->bindParam(":updated", $updated);

			if ($pre->execute()) {
				return $res->withJson([
					'status' => 'success'
				], 200);
			} else {
				return $res->withJson([
					'status' => 'error'
				], 200);
			}
		} catch (Exception $e) {
			return $res->withJson([
				'status' => 'error',
				'message' => $e->getMessage()
			], 442);
		}
    }

    public function employeeDel($req, $res, $args)
    {
    	try {
			$conn = $this->container->db;
			
			$sql = "DELETE FROM employees WHERE (emp_id=:cid)";
			
			$pre = $conn->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
			$values = [':cid' => $args['cid']];

			if ($pre->execute($values)) {
				return $res->withJson([
					'status' =>'success',
				], 200);
			} else {
				return $res->withJson([
					'status' => 'error',
				], 200);
			}
		} catch (Exception $e) {
			return $res->withJson([
				'status' => 'error',
				'message' => $e->getMessage()
			], 442);
		}
    }
}