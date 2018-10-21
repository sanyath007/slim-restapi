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

    public function checkinList($req, $res) 
    {
	    try {
			$conn = $this->container->db;

			$sql = "SELECT c.emp_id, CONCAT(e.prefix, e.emp_fname, ' ', e.emp_lname) as emp_name,
					c.checkin_date, TIME(timein) as timein, timein_score, timein_img 
					FROM checkin c LEFT JOIN employees e ON (c.emp_id=e.emp_id)";

			$pre = $conn->prepare($sql, [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY]);
			
			$pre->execute();
			$result = $pre->fetchAll();

			if ($result) {
				return $res->withJson([
					'checkins' => $result
				], 200);
			} else {
				return $res->withJson([
					$result
				], 442);
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
				], 422);
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
}