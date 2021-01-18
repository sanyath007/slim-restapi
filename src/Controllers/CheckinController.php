<?php
namespace App\Controllers;

use Psr\Container\ContainerInterface;

class CheckinController
{
	protected $container;
    protected $view;

    public function __construct(ContainerInterface $container) 
    {
        $this->container = $container;
    }

    public function checkinAll($req, $res, $args) 
    {
	    try {
	    	$month = $args['month'];
	    	$sdate = $month . '-01';
	     	$edate = date("Y-m-t", strtotime($sdate));

				$conn = $this->container->db;

				$sql = "SELECT c.emp_id, CONCAT(e.prefix, e.emp_fname, ' ', e.emp_lname) as emp_name,
								c.checkin_date, TIME(timein) as timein, timein_score, timein_img 
								FROM checkin c LEFT JOIN employees e ON (c.emp_id=e.emp_id) 
								WHERE (checkin_date BETWEEN :sdate AND :edate)
								ORDER By checkin_date ";

				$pre = $conn->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
				$values = [ ':sdate' => $sdate, ':edate' => $edate ];

				$pre->execute($values);
				$result = $pre->fetchAll();

				if ($result) {
					return $res->withJson([
						'checkins' => $result
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
		
		public function checkinScore($req, $res, $args) 
    {
	    try {
	    	$month = $args['month'];
	    	$sdate = $month . '-01';
				$edate = date("Y-m-t", strtotime($sdate));
				$endDateOfMonth = date("t", strtotime($sdate));

				$conn = $this->container->db;

				$sql = "SELECT c.emp_id, CONCAT(e.prefix, e.emp_fname, ' ', e.emp_lname) as emp_name 
								FROM checkin c LEFT JOIN employees e ON (c.emp_id=e.emp_id) 
								WHERE (checkin_date BETWEEN :sdate AND :edate)
								GROUP BY c.emp_id, CONCAT(e.prefix, e.emp_fname, ' ', e.emp_lname)
								ORDER By c.emp_id, CONCAT(e.prefix, e.emp_fname, ' ', e.emp_lname) ";

				$pre = $conn->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
				$values = [ ':sdate' => $sdate, ':edate' => $edate ];

				$pre->execute($values);
				$empQueries = $pre->fetchAll();
				
				$d = 1;
				$checkins = [];
				foreach($empQueries as $employee) {
					$scores = [];
					for($d = 1; $d <= 31; $d++) {	
						$pre = $conn->prepare("SELECT DAY(checkin_date) as _day, timein_score FROM checkin WHERE (checkin_date=:chkDate) AND (emp_id=:empId)", [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
						$values = [ ':chkDate' => $month . '-' .$d, ':empId' => $employee['emp_id'] ];
						
						$pre->execute($values);
						
						if($result = $pre->fetch()) {
							$scores[$d] = $result['timein_score'];
						} else {
							$scores[$d] = '';
						}

					}
					
					array_push($checkins, array_merge($employee, ['scores' => $scores]));
				}
				
				if ($checkins) {
					return $res->withJson([
						'checkins' => $checkins,
						'numdate' => $endDateOfMonth
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

		public function checkinTime($req, $res, $args) 
    {
	    try {
	    	$month = $args['month'];
	    	$sdate = $month . '-01';
				$edate = date("Y-m-t", strtotime($sdate));
				$endDateOfMonth = date("t", strtotime($sdate));

				$conn = $this->container->db;

				$sql = "SELECT c.emp_id, CONCAT(e.prefix, e.emp_fname, ' ', e.emp_lname) as emp_name 
								FROM checkin c LEFT JOIN employees e ON (c.emp_id=e.emp_id) 
								WHERE (checkin_date BETWEEN :sdate AND :edate)
								GROUP BY c.emp_id, CONCAT(e.prefix, e.emp_fname, ' ', e.emp_lname)
								ORDER By c.emp_id, CONCAT(e.prefix, e.emp_fname, ' ', e.emp_lname) ";

				$pre = $conn->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
				$values = [ ':sdate' => $sdate, ':edate' => $edate ];

				$pre->execute($values);
				$empQueries = $pre->fetchAll();
				
				$d = 1;
				$checkins = [];
				foreach($empQueries as $employee) {
					$timeins = [];
					for($d = 1; $d <= 31; $d++) {	
						$pre = $conn->prepare("SELECT DAY(checkin_date) as _day, timein FROM checkin WHERE (checkin_date=:chkDate) AND (emp_id=:empId)", [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
						$values = [ ':chkDate' => $month . '-' .$d, ':empId' => $employee['emp_id'] ];
						
						$pre->execute($values);
						
						if($result = $pre->fetch()) {
							$timeins[$d] = $result['timein'];
						} else {
							$timeins[$d] = '';
						}

					}
					
					array_push($checkins, array_merge($employee, ['timeins' => $timeins]));
				}
				
				if ($checkins) {
					return $res->withJson([
						'checkins' => $checkins,
						'numdate' => $endDateOfMonth
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

		public function checkinList($req, $res, $args) 
    {
	    try {
	    	$checkin_date = $args['date'];
			$conn = $this->container->db;

			$sql = "SELECT c.emp_id, CONCAT(e.prefix, e.emp_fname, ' ', e.emp_lname) as emp_name,
					c.checkin_date, TIME(timein) as timein, timein_score, timein_img 
					FROM checkin c LEFT JOIN employees e ON (c.emp_id=e.emp_id)
					WHERE (checkin_date=:checkin_date)";

			$pre = $conn->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
			$values = [ ':checkin_date' => $checkin_date ];

			$pre->execute($values);
			$result = $pre->fetchAll();

			if ($result) {
				return $res->withJson([
					'checkins' => $result
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

    public function checkin($req, $res)
    {
    	// $timein = date('H:i:s');
    	$checkin_date = $req->getParam('checkin_date');
    	$timein = $req->getParam('timein');
    	$timein_img = $req->getParam('timein_img') . '.png';
    	$timein_score = $this->timeScored($timein);

    	try {
				$conn = $this->container->db;
				
				$sql = "INSERT INTO checkin (emp_id, checkin_date, timein, timein_score, timein_img, created_at, updated_at)VALUES(?,?,?,?,?,?,?)";
				
				$pre = $conn->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
				$values = [
					$req->getParam('emp_id'), 
					$checkin_date, 
					$checkin_date .' '. $timein,
					$timein_score,
					$timein_img,
					date('Y-m-d H:i:s'), 
					date('Y-m-d H:i:s'),
				];

				if ($pre->execute($values)) {
					return $res->withJson([
						'status' =>'success',
						'timein' => $timein,
						'timeinScore' => $timein_score,
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

    public function upload($req, $res) 
    {
    	// Set path to upload file.
			$target_dir		= $_SERVER['DOCUMENT_ROOT'] . "/uploads/";

			if (file_exists($target_dir) && is_dir($target_dir)) {
				$target_file 	= $target_dir . basename($_FILES['file']['name']) . ".png";

				// Get image file type.
				$imageFileType 	= strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

				$target_file_hash 	= $target_dir . md5(uniqid(rand(), true)) . ".png";

				// Check file
				$check = getimagesize($_FILES['file']['tmp_name']);

				if(move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
					return $res->withJson([
						'status' => 'success',
						'message' => 'The file ' . basename($_FILES['file']['name']) . ' has been uploaded.',
					]);
				} else {
					return $res->withJson([
						'status' => 'error',
						'message' => 'Sorry, there was an error uploading your file.',
					]);
				}
			} else {
				return $res->withJson([
					'status' => 'error',
					'message' => $target_dir . ' directory not found.',
				]);
			}
    }

    private function timeScored ($time) 
    {
    	$score = 0;

    	if (strtotime($time) <= strtotime("07:45:59")) {
    		$score = 5;
    	} else if (strtotime($time) >= strtotime("07:46:00") && strtotime($time) <= strtotime("07:59:59")) {
    		$score = 4;
    	} else if (strtotime($time) >= strtotime("08:00:00") && strtotime($time) <= strtotime("08:15:59")) {
    		$score = 3;
    	} else if (strtotime($time) >= strtotime("08:16:00") && strtotime($time) <= strtotime("08:30:59")) {
    		$score = 2;
    	} else if (strtotime($time) > strtotime("08:30:00")) {
    		$score = 1;
    	}

    	return $score;
    }

    public function timeinImg ($req, $res, $args)
    {
    	$data = $args['data'];
    	$image = @file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/../uploads/" . $data);

    	$res->write($image);

    	return $res->withHeader('Content-Type', 'image/png');
    }

    public function checkinChart ($req, $res, $args)
    {
    	try {
	    	$checkin_date = $args['date'];
			$conn = $this->container->db;

			$sql = "SELECT CONCAT('คะแนน ', timein_score) AS score, COUNT(id) AS num 
					FROM checkin WHERE (checkin_date=:checkin_date) GROUP BY timein_score ";

			$pre = $conn->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
			$values = [ ':checkin_date' => $checkin_date ];

			$pre->execute($values);
			$result = $pre->fetchAll();

			if ($result) {
				return $res->withJson($result, 200);
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
}