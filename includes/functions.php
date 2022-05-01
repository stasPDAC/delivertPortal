<?php
$date = date("Y-m-d H:i:s");

function check($value){
    echo '<pre>';
    print_r($value);
    echo '</pre>';
    exit();
}

function getPercentage($all_finish_reports_count, $all_reports_count){
    if($all_reports_count != 0){
        $response = $all_finish_reports_count * 100 / $all_reports_count;
    }else{
        $response = 0;
    }
    $response = number_format($response, 1, '.', '');
    $response = $response . '%';
    return $response;
}


/////////////////////////////////// SELECT ///////////////////////////////////

function getAllPhonesUsersWithoutClients($phone)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT st_phone_first FROM tb_users WHERE st_phone_first = :st_phone_first AND i_type != 4');
    $stmt->bindParam(':st_phone_first', $phone);
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllMailUsersWithoutClients($mail)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT st_mail FROM tb_users WHERE st_mail = :st_mail AND i_type != 4');
    $stmt->bindParam(':st_mail', $mail);
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllContractorTypes()
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM tb_contractors_types');
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function checkKitchenOrBathroomContractorByProjectId($project_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM tb_projects_conn WHERE i_project_id = :i_project_id AND i_contractor_id = -1');
    $stmt->bindParam(':i_project_id', $project_id);
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function checkKitchenOrBathroomContractorBySerialNumber($serial_number)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT tb_projects_conn.i_contractor_type FROM tb_reports 
    LEFT JOIN tb_projects_conn ON tb_reports.i_project_id = tb_projects_conn.i_project_id
    WHERE tb_reports.i_serial_number = :i_serial_number AND i_contractor_id = -1');
    $stmt->bindParam(':i_serial_number', $serial_number);
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllProjectNames($project_name)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM tb_projects WHERE st_project_name LIKE :st_project_name');
    $stmt->bindParam('st_project_name', $project_name);
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllContractorByTypesId($type_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT *, tb_users.id AS user_id  FROM tb_users LEFT JOIN tb_contractors_conn ON tb_users.id = tb_contractors_conn.i_contractor_id WHERE i_contractor_type = :i_contractor_type AND i_active = :i_active');
    $stmt->bindParam(':i_contractor_type', $type_id);
    $stmt->bindValue(':i_active', 1);
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllContractorByProjectId($project_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM tb_projects_conn 
    LEFT JOIN tb_users 
    ON tb_projects_conn.i_contractor_id = tb_users.id 
    LEFT JOIN tb_contractors_types 
    ON tb_projects_conn.i_contractor_type = tb_contractors_types.id 
    WHERE tb_projects_conn.i_project_id = :i_project_id');
    $stmt->bindParam(':i_project_id', $project_id);
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllProjectsByContractorId($contractor_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM tb_projects 
    LEFT JOIN tb_projects_conn 
    ON tb_projects.id = tb_projects_conn.i_project_id 
    WHERE tb_projects_conn.i_contractor_id = :i_contractor_id AND i_active = :i_active GROUP BY tb_projects_conn.i_project_id');
    $stmt->bindParam(':i_contractor_id', $contractor_id);
    $stmt->bindValue(':i_active', 1);
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllContractorTypesByUserId($user_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT *,tb_contractors_types.id AS type_id FROM tb_contractors_types LEFT JOIN tb_contractors_conn 
    ON tb_contractors_types.id = tb_contractors_conn.i_contractor_type
    AND tb_contractors_conn.i_contractor_id = :i_contractor_id ORDER BY type_id');
    $stmt->bindParam('i_contractor_id', $user_id);
    $stmt->execute();
    $response = $stmt->fetchAll();


    return $response;
}

function getAllContractorTypesByContractorId($user_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT i_contractor_type FROM tb_contractors_conn WHERE i_contractor_id = :i_contractor_id');
    $stmt->bindParam(':i_contractor_id', $user_id);
    $stmt->execute();
    $response = $stmt->fetchAll();


    return $response;
}

function getAllContractorsByTypeId($contractor_types,$project_id)
{
    $sql = 'SELECT tb_contractors_types.id AS contractor_type, tb_users.id AS user_id, tb_users.st_user_name,
(
SELECT !ISNULL(tb_projects_conn.id) FROM tb_projects_conn

WHERE tb_projects_conn.i_contractor_id=  tb_users.id
AND tb_projects_conn.i_project_id = :i_project_id
AND tb_projects_conn.i_contractor_type = tb_contractors_types.id
)
AS IS_SELECTED


FROM tb_users

RIGHT JOIN tb_contractors_conn
ON tb_users.id = tb_contractors_conn.i_contractor_id

LEFT JOIN tb_contractors_types
ON tb_contractors_types.id = tb_contractors_conn.i_contractor_type

WHERE tb_users.i_active = 1 AND tb_contractors_types.id = :contractor_type
ORDER BY contractor_type';
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':i_project_id', $project_id);
    $stmt->bindParam(':contractor_type', $contractor_types);
    $stmt->execute();
    $response = $stmt->fetchAll();


    return $response;
}

function getAllAdmins()
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM tb_users WHERE i_type = 1');
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getSerialNumberByUserId($user_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT i_serial_number FROM tb_users WHERE id = :id');
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $response = $stmt->fetch();
    $stmt = null;

    return $response['i_serial_number'];
}

function getAllInspectors()
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM tb_users WHERE i_type = 5');
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllManageress()
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM tb_users WHERE i_type = :i_type');
    $stmt->bindValue(':i_type', '2');
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllManageressActivities()
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM tb_users WHERE i_type = :i_type AND i_active = 1 ORDER BY st_user_name');
    $stmt->bindValue(':i_type', '2');
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getLastSerialNumber()
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT i_serial_number FROM tb_users ORDER BY i_serial_number DESC LIMIT 1');
    $stmt->execute();
    $response = $stmt->fetch();
    $response = $response['i_serial_number'] +1;
    $stmt = null;

    return $response;
}

function getAllContractors()
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM tb_users WHERE i_type = :i_type AND id > 0');
    $stmt->bindValue(':i_type', '3');
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllContractorsTypesById($contractor_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM tb_contractors_types
        LEFT JOIN tb_contractors_conn
        ON tb_contractors_types.id = tb_contractors_conn.i_contractor_type
        WHERE i_contractor_id = :i_contractor_id');
    $stmt->bindParam(':i_contractor_id', $contractor_id);
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllProjects()
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT *, tb_projects.id AS project_id, tb_projects.i_active AS project_activity 
    FROM tb_projects LEFT JOIN tb_users ON tb_users.id = tb_projects.i_project_manager 
    ORDER BY tb_projects.i_active DESC');
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllProjectsByManagerId($manager_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT *, tb_projects.id AS project_id, tb_projects.i_active AS project_activity 
    FROM tb_projects LEFT JOIN tb_users ON tb_users.id = tb_projects.i_project_manager WHERE tb_projects.i_project_manager = :i_project_manager AND tb_projects.i_active = 1');
    $stmt->bindParam(':i_project_manager', $manager_id);
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllReports()
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT *, tb_users.i_active AS user_activity FROM tb_reports 
        LEFT JOIN tb_projects ON tb_reports.i_project_id = tb_projects.id
        LEFT JOIN tb_report_status ON tb_reports.i_status = tb_report_status.id
        LEFT JOIN tb_users ON tb_reports.i_serial_number = tb_users.i_serial_number WHERE tb_users.i_active = 1');
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllReportsByProjectId($project_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT *, tb_users.i_active AS user_activity, tb_reports.i_project_id AS project_id FROM tb_reports 
        LEFT JOIN tb_projects ON tb_reports.i_project_id = tb_projects.id
        LEFT JOIN tb_report_status ON tb_reports.i_status = tb_report_status.id
        LEFT JOIN tb_users ON tb_reports.i_serial_number = tb_users.i_serial_number
        WHERE tb_reports.i_project_id = :i_project_id AND tb_users.i_active = 1');
    $stmt->bindParam(':i_project_id',$project_id);
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllReportsByManagerId($manager_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT *, tb_users.i_active AS user_activity FROM tb_reports 
        LEFT JOIN tb_projects ON tb_reports.i_project_id = tb_projects.id
        LEFT JOIN tb_report_status ON tb_reports.i_status = tb_report_status.id
        LEFT JOIN tb_users ON tb_reports.i_serial_number = tb_users.i_serial_number
        WHERE i_project_manager = :i_project_manager AND tb_users.i_active = 1');
    $stmt->bindParam(':i_project_manager', $manager_id);
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getProjectByProjectId($project_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT tb_projects.*, tb_projects.id AS project_id, tb_users.st_user_name
    FROM tb_projects 
    LEFT JOIN tb_users ON tb_users.id = tb_projects.i_project_manager 
    WHERE tb_projects.id = :id');
    $stmt->bindParam(':id', $project_id);
    $stmt->execute();
    $response = $stmt->fetch();
    $stmt = null;

    return $response;
}

function getAllProjectsById($project_manager_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM tb_projects WHERE i_project_manager = :i_project_manager AND i_active = :i_active');
    $stmt->bindParam(':i_project_manager', $project_manager_id);
    $stmt->bindValue(':i_active', '1');
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllClientsByProjectId($project_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM tb_users WHERE i_project_id = :i_project_id AND i_type = 4');
    $stmt->bindParam(':i_project_id', $project_id);
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllReportsFinishedByProjectId($project_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT tb_reports.*, tb_users.i_active AS user_activity FROM tb_reports 
LEFT JOIN tb_users ON tb_users.i_serial_number = tb_reports.i_serial_number
WHERE tb_reports.i_project_id = :i_project_id AND tb_reports.i_status = 3 AND tb_users.i_active = 1');
    $stmt->bindParam(':i_project_id', $project_id);
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllReportsAtWorkByProjectId($project_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT tb_reports.*, tb_users.i_active AS user_activity FROM tb_reports 
LEFT JOIN tb_users ON tb_users.i_serial_number = tb_reports.i_serial_number
WHERE tb_reports.i_project_id = :i_project_id AND tb_reports.i_status = 2 AND tb_users.i_active = 1');
    $stmt->bindParam(':i_project_id', $project_id);
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllReportsDraftByProjectId($project_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT tb_reports.*, tb_users.i_active AS user_activity FROM tb_reports 
LEFT JOIN tb_users ON tb_users.i_serial_number = tb_reports.i_serial_number
WHERE tb_reports.i_project_id = :i_project_id AND tb_reports.i_status = 1 AND tb_users.i_active = 1');
    $stmt->bindParam(':i_project_id', $project_id);
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllClients()
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT *, tb_users.id AS client_id, tb_users.i_active AS user_activity 
    FROM tb_users 
    LEFT JOIN tb_projects ON tb_users.i_project_id = tb_projects.id
    WHERE i_type = 4');
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAdminById($client_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM tb_users WHERE id = :id AND i_type = 1');
    $stmt->bindParam(':id', $client_id);
    $stmt->execute();
    $response = $stmt->fetch();
    $stmt = null;

    return $response;
}

function getInspectorById($inspector_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM tb_users WHERE id = :id AND i_type = 5');
    $stmt->bindParam(':id', $inspector_id);
    $stmt->execute();
    $response = $stmt->fetch();
    $stmt = null;

    return $response;
}

function getClientById($client_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM tb_users WHERE id = :id');
    $stmt->bindParam(':id', $client_id);
    $stmt->execute();
    $response = $stmt->fetch();
    $stmt = null;

    return $response;
}

function getAllFaultStatuses()
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM tb_fault_status');
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllNotesByFaultId($fault_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT tb_fault_remarks.*, tb_fault_status.st_fault_status_name, tb_fault_status2.st_fault_status_name AS st_last_fault_status_name, tb_fault_status.st_color_status, tb_users_types.user_type, tb_users.st_user_name
FROM tb_fault_remarks 
LEFT JOIN tb_fault_status ON tb_fault_remarks.i_status = tb_fault_status.id
LEFT JOIN tb_fault_status AS tb_fault_status2 ON tb_fault_remarks.i_last_status= tb_fault_status2.id
LEFT JOIN tb_users_types ON tb_fault_remarks.i_user_type= tb_users_types.id
LEFT JOIN tb_users ON tb_fault_remarks.i_user_id = tb_users.id 
WHERE tb_fault_remarks.i_fault_id = :i_fault_id');
    $stmt->bindParam(':i_fault_id', $fault_id);
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getReportBySerialId($serial_number_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM tb_reports WHERE i_serial_number = :i_serial_number');
    $stmt->bindParam(':i_serial_number', $serial_number_id);
    $stmt->execute();
    $response = $stmt->fetch();
    $stmt = null;

    return $response;
}

function getPhoneCheckerBySerialId($serial_number_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT st_phone_checker FROM tb_users WHERE i_serial_number = :i_serial_number');
    $stmt->bindParam(':i_serial_number', $serial_number_id);
    $stmt->execute();
    $response = $stmt->fetch();
    $stmt = null;

    return $response;
}

function getClientBySerialId($serial_number_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT *, tb_users.i_project_id AS project_id FROM tb_users 
    LEFT JOIN tb_reports ON tb_users.i_serial_number = tb_reports.i_serial_number
    LEFT JOIN tb_report_status ON tb_reports.i_status = tb_report_status.id
    WHERE tb_users.i_serial_number = :i_serial_number AND i_type = 4');
    $stmt->bindParam(':i_serial_number', $serial_number_id);
    $stmt->execute();
    $response = $stmt->fetch();
    $stmt = null;

    return $response;
}

function getFaultBySerialId($serial_number_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM tb_fault WHERE i_serial_number = :i_serial_number');
    $stmt->bindParam(':i_serial_number', $serial_number_id);
    $stmt->execute();
    $response = $stmt->fetch();
    $stmt = null;

    return $response;
}

function getAllFaultsBySerialId($serial_number_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT tb_contractors_types.st_contractor_type, tb_fault.*, tb_fault.id AS fault_id, tb_fault_status.*, tb_fault.id AS fault_id
        FROM tb_contractors_types
        LEFT JOIN tb_fault ON tb_contractors_types.id = tb_fault.i_category
        LEFT JOIN tb_fault_status ON tb_fault.i_fault_status= tb_fault_status.id
        WHERE tb_fault.id IS NOT NULL AND tb_fault.i_serial_number = :i_serial_number
        ORDER BY tb_fault.i_category ASC');
    $stmt->bindParam(':i_serial_number', $serial_number_id);
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllReportsFaults()
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT tb_fault.id, tb_fault_status.st_fault_status_name, tb_fault_status.st_color_status, tb_projects.st_project_name, tb_contractors_types .st_contractor_type, tb_fault_status.id AS status_id
FROM tb_fault 
LEFT JOIN tb_fault_status ON tb_fault.i_fault_status = tb_fault_status.id
LEFT JOIN tb_reports ON tb_reports.i_serial_number = tb_fault.i_serial_number
LEFT JOIN tb_projects ON tb_reports.i_project_id = tb_projects.id
LEFT JOIN tb_contractors_types ON tb_fault.i_category= tb_contractors_types.id WHERE tb_fault.i_fault_status > 1');
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getAllReportsFaultsByContractorsId($сontractors_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT tb_fault.id, tb_fault_status.st_fault_status_name, tb_fault_status.st_color_status, tb_projects.st_project_name, tb_contractors_types .st_contractor_type, tb_fault_status.id AS status_id, tb_projects_conn.i_contractor_id, 
        tb_contractors_types.id AS type_id
        FROM tb_fault 
        LEFT JOIN tb_fault_status ON tb_fault.i_fault_status = tb_fault_status.id
        LEFT JOIN tb_reports ON tb_reports.i_serial_number = tb_fault.i_serial_number
        LEFT JOIN tb_projects ON tb_reports.i_project_id = tb_projects.id
        LEFT JOIN tb_contractors_types ON tb_fault.i_category = tb_contractors_types.id
        LEFT JOIN tb_projects_conn ON tb_projects_conn.i_project_id = tb_projects.id
        INNER JOIN tb_contractors_conn ON tb_contractors_conn.i_contractor_id = :i_contractor_id AND tb_contractors_types.id = tb_contractors_conn.i_contractor_type
        WHERE tb_projects_conn.i_contractor_id = :i_contractor_id2 AND tb_fault_status.id = 2
        GROUP BY tb_fault.id');
    $stmt->bindParam(':i_contractor_id', $сontractors_id);
    $stmt->bindParam(':i_contractor_id2', $сontractors_id);
    $stmt->execute();
    $response = $stmt->fetchAll();
    $stmt = null;

    return $response;
}

function getFaultById($fault_id)
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT tb_fault.*, tb_fault_status.st_fault_status_name, tb_contractors_types.st_contractor_type
FROM tb_fault
LEFT JOIN tb_fault_status ON tb_fault.i_fault_status = tb_fault_status.id 
LEFT JOIN tb_contractors_types ON tb_fault.i_category = tb_contractors_types.id 
WHERE tb_fault.id = :id');
    $stmt->bindParam(':id', $fault_id);
    $stmt->execute();
    $response = $stmt->fetch();
    $stmt = null;

    return $response;
}

/////////////////////////////////// CREATE ///////////////////////////////////

function createNewProject($project_name, $project_manager, $project_address, $date_occupancy, $selections)
{
    global $pdo;
    global $user_id;
    $stmt = $pdo->prepare("INSERT INTO tb_projects (
                st_project_name, 
                i_project_manager, 
                st_project_address,
                date_occupancy,
                i_created_by_user,     
                date_created
            ) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([
        $project_name,
        $project_manager,
        $project_address,
        $date_occupancy,
        $user_id
    ]);
    $project_id = $pdo->lastInsertId();

    if ($project_id) {
        foreach ($selections as $id=>$selected){
            if(isset($selected)){
                $sql = "INSERT INTO tb_projects_conn (i_project_id, i_contractor_id, i_contractor_type) VALUES (?, ?, ?)";
                $params = [$project_id, $selected, $id];
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            }
        }
        if ($stmt->rowCount() > 0) {
            header('Location: projects.php?msg=createOk');
            exit;
        }

    } else {
        header('Location: projects.php?msg=error');
        exit;
    }
}

function createNewClient($project_id, $user_name, $phone_first, $phone_second, $mail, $property_type, $property_number, $floor, $apartment, $type, $kitchen_name, $kitchen_number, $bathroom_name, $bathroom_number)
{
    global $pdo;
    global $user_id;
    $serial_number = getLastSerialNumber();
    $sql = "INSERT INTO tb_users (
                i_project_id,
                i_serial_number,
                st_user_name,
                st_phone_first,
                st_phone_second,
                st_mail,
                st_property_type,
                st_property_number,
                st_floor,
                st_apartment,
                i_type,
                i_created_by_user,
                date_created) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $params = [$project_id,
        $serial_number,
        $user_name,
        $phone_first,
        $phone_second,
        $mail,
        $property_type,
        $property_number,
        $floor,
        $apartment,
        $type,
        $user_id];

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    if ($stmt->rowCount() > 0) {
        $sql = "INSERT INTO tb_reports (
                i_serial_number,
                i_project_id,
                st_kitchen_name,
                st_kitchen_number,
                st_bathroom_name,
                st_bathroom_number,
                i_created_by_user,
                date_created) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $params = [$serial_number,
            $project_id,
            $kitchen_name,
            $kitchen_number,
            $bathroom_name,
            $bathroom_number,
            $user_id];

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if ($stmt->rowCount() > 0) {
            header('Location: project.php?id=' . $project_id . '&msg=createOk');
            exit;
        } else {
            header('Location: projects.php?msg=error');
            exit;
        }
    } else {
        header('Location: projects.php?msg=error');
        exit;
    }
}

function createNewAdmin($user_name, $phone_first, $mail, $type)
{
    global $pdo;
    global $user_id;

    $sql = "INSERT INTO tb_users (
            st_user_name,
            st_phone_first,
            st_mail,
            i_type,
            i_created_by_user,
            date_created) VALUES ( ?, ?, ?, ?, ?, NOW())";
    $params = [$user_name,
        $phone_first,
        $mail,
        $type,
        $user_id];
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        header('Location: users.php?msg=createOk');
        exit;
    } else {
        header('Location: users.php?msg=error');
        exit;
    }
}

function createNewInspector($user_name, $phone_first, $mail, $type)
{
    global $pdo;
    global $user_id;
    $sql = "INSERT INTO tb_users (
                st_user_name,
                st_phone_first,
                st_mail,
                i_type,
                i_created_by_user,
                date_created) VALUES (?, ?, ?, ?, ?, NOW())";
    $params = [$user_name,
        $phone_first,
        $mail,
        $type,
        $user_id];
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        header('Location: inspectors.php?msg=createOk');
        exit;
    } else {
        header('Location: inspectors.php?msg=error');
        exit;
    }
}

function createNewManager($user_name, $phone_first, $mail, $type)
{
    global $pdo;
    global $user_id;
    $sql = "INSERT INTO tb_users (
                st_user_name,
                st_phone_first,
                st_mail,
                i_type,
                i_created_by_user,
                date_created) VALUES (?, ?, ?, ?, ?, NOW())";
    $params = [$user_name,
        $phone_first,
        $mail,
        $type,
        $user_id];
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        header('Location: managers.php?msg=createOk');
        exit;
    } else {
        header('Location: managers.php?msg=error');
        exit;
    }
}

function createNewContractor($user_name, $phone_first, $mail, $type, $checkboxes)
{
    global $pdo;
    global $user_id;
    $sql = "INSERT INTO tb_users (
                st_user_name,
                st_phone_first,
                st_mail,
                i_type,
                i_created_by_user,
                date_created) VALUES (?, ?, ?, ?, ?, NOW())";
    $params = [$user_name,
        $phone_first,
        $mail,
        $type,
        $user_id];
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $contractor_id = $pdo->lastInsertId();


    if ($contractor_id) {
        foreach ($checkboxes as $id=>$checked){
            if($checked == 1){
                $sql = "INSERT INTO tb_contractors_conn (i_contractor_id, i_contractor_type) VALUES (?, ?)";
                $params = [$contractor_id, $id];
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            }
        }
        if ($stmt->rowCount() > 0) {
            header('Location: contractors.php?msg=createOk');
            exit;
        }
    } else {
        header('Location: contractors.php?msg=error');
        exit;
    }
}

function createNewFault($body_text, $category, $title, $report_serial)
{
    global $pdo;
    $fault_status = 0;
    $sql = "INSERT INTO tb_fault (i_serial_number, i_category, st_title, st_fault_content, i_fault_status) VALUES ( ?, ?, ?, ?, ?)";
    $params = [$report_serial, $category, $title, $body_text, $fault_status];
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        header('Location: report.php?id=' . $report_serial . '&msg=createOk');
        exit;
    } else {
        header('Location: report.php?id=' . $report_serial . '&msg=error');
        exit;
    }
}

function createNewNote($status, $report_serial, $fault_id, $note, $last_status)
{
    global $pdo;
    global $user_id;
    global $user_type;

    $sql = "INSERT INTO tb_fault_remarks (
              i_fault_id, 
              st_note, 
              i_status, 
              i_last_status, 
              i_user_id, 
              i_user_type, 
              st_note_date) VALUES ( ?, ?, ?, ?, ?, ?, NOW())";
    $params = [
        $fault_id,
        $note,
        $status,
        $last_status,
        $user_id,
        $user_type];
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare('UPDATE tb_fault SET i_fault_status = :i_fault_status WHERE id = :id');

        $stmt->bindParam(':i_fault_status', $status);
        $stmt->bindParam(':id', $fault_id);
        $stmt->execute();
        $stmt = null;
        if ($user_type == 3){
            header('Location: noteView.php?id=' . $fault_id . '&msg=createOk');
        }else{
            header('Location: report.php?id=' . $report_serial . '&msg=createOk');
        }
        exit;
    } else {
        if ($user_type == 3){
            header('Location: noteView.php?id=' . $fault_id . '&msg=error');
        }else{
            header('Location: report.php?id=' . $report_serial . '&msg=error');
        }

        exit;
    }
}



/////////////////////////////////// UPDATE ///////////////////////////////////

function updateClientById($user_name, $project_id, $client_id, $phone_first, $phone_second, $mail, $property_type, $property_number, $floor, $apartment, $active, $kitchen_name, $kitchen_number, $bathroom_name, $bathroom_number)
{
    global $pdo;
    global $user_id;
    try {
        $stmt = $pdo->prepare('UPDATE tb_users SET
        st_user_name = :st_user_name,
        st_phone_first = :st_phone_first,
        st_phone_second = :st_phone_second,
        st_mail = :st_mail,
        st_property_type = :st_property_type,
        st_property_number = :st_property_number,
        st_floor = :st_floor,
        i_active = :i_active,
        st_apartment = :st_apartment,
        i_change_user = :i_change_user,
        date_change = NOW()
        WHERE id = :id'
        );
        $stmt->bindParam(':st_user_name', $user_name);
        $stmt->bindParam(':st_phone_first', $phone_first);
        $stmt->bindParam(':st_phone_second', $phone_second);
        $stmt->bindParam(':st_mail', $mail);
        $stmt->bindParam(':st_property_type', $property_type);
        $stmt->bindParam(':st_property_number', $property_number);
        $stmt->bindParam(':st_floor', $floor);
        $stmt->bindParam(':i_active', $active);
        $stmt->bindParam(':st_apartment', $apartment);
        $stmt->bindParam(':i_change_user', $user_id);

        $stmt->bindParam(':id', $client_id);
        $stmt->execute();
        $stmt = null;

        $stmt = $pdo->prepare('UPDATE tb_reports SET
        st_kitchen_name = :st_kitchen_name,
        st_kitchen_number = :st_kitchen_number,
        st_bathroom_name = :st_bathroom_name,
        st_bathroom_number = :st_bathroom_number,
                      
        i_change_user = :i_change_user,
        date_change = NOW()
        WHERE i_project_id = :i_project_id'
        );
        $stmt->bindParam(':st_kitchen_name', $kitchen_name);
        $stmt->bindParam(':st_kitchen_number', $kitchen_number);
        $stmt->bindParam(':st_bathroom_name', $bathroom_name);
        $stmt->bindParam(':st_bathroom_number', $bathroom_number);

        $stmt->bindParam(':i_change_user', $user_id);

        $stmt->bindParam(':i_project_id', $project_id);
        $stmt->execute();
        $stmt = null;



        header('Location: project.php?id=' . $project_id . '&msg=editOk');
        exit;
    } catch (Exception $e) {
        header('Location: projects.php?msg=error');
        exit;
    }
}

function updateAdminById($user_name, $manager_id, $phone_first, $mail, $active)
{
    global $pdo;
    global $user_id;
    try {
        $stmt = $pdo->prepare('UPDATE tb_users SET
        st_user_name = :st_user_name,
        st_phone_first = :st_phone_first,
        st_mail = :st_mail,
        i_active = :i_active,
        i_change_user = :i_change_user,
        date_change = NOW()
        WHERE id = :id'
        );
        $stmt->bindParam(':st_user_name', $user_name);
        $stmt->bindParam(':st_phone_first', $phone_first);
        $stmt->bindParam(':st_mail', $mail);
        $stmt->bindParam(':i_active', $active);
        $stmt->bindParam(':i_change_user', $user_id);

        $stmt->bindParam(':id', $manager_id);
        $stmt->execute();
        $stmt = null;

        header('Location: users.php?msg=editOk');
        exit;
    } catch (Exception $e) {
        header('Location: users.php?msg=error');
        exit;
    }
}

function updateDateTermsConfirmed($serial_number)
{
    global $pdo;
    global $user_id;
    try {
        $stmt = $pdo->prepare('UPDATE tb_users SET
        date_terms_confirmed = NOW()
        WHERE id = :id'
        );

        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        $stmt = null;

        header('Location: report.php?id=' . $serial_number);
        exit;
    } catch (Exception $e) {
        header('Location: terms.php?id=' . $serial_number);
        exit;
    }
}

function updateInspectorById($user_name, $manager_id, $phone_first, $mail, $active)
{
    global $pdo;
    global $user_id;
    try {
        $stmt = $pdo->prepare('UPDATE tb_users SET
        st_user_name = :st_user_name,
        st_phone_first = :st_phone_first,
        st_mail = :st_mail,
        i_active = :i_active,
        i_change_user = :i_change_user,
        date_change = NOW()
        WHERE id = :id'
        );
        $stmt->bindParam(':st_user_name', $user_name);
        $stmt->bindParam(':st_phone_first', $phone_first);
        $stmt->bindParam(':st_mail', $mail);
        $stmt->bindParam(':i_active', $active);
        $stmt->bindParam(':i_change_user', $user_id);

        $stmt->bindParam(':id', $manager_id);
        $stmt->execute();
        $stmt = null;

        header('Location: inspectors.php?msg=editOk');
        exit;
    } catch (Exception $e) {
        header('Location: inspectors.php?msg=error');
        exit;
    }
}

function updateManagerById($user_name, $manager_id, $phone_first, $mail, $active)
{
    global $pdo;
    global $user_id;
    try {
        $stmt = $pdo->prepare('UPDATE tb_users SET
        st_user_name = :st_user_name,
        st_phone_first = :st_phone_first,
        st_mail = :st_mail,
        i_active = :i_active,
        i_change_user = :i_change_user,
        date_change = NOW()
        WHERE id = :id'
        );
        $stmt->bindParam(':st_user_name', $user_name);
        $stmt->bindParam(':st_phone_first', $phone_first);
        $stmt->bindParam(':st_mail', $mail);
        $stmt->bindParam(':i_active', $active);
        $stmt->bindParam(':i_change_user', $user_id);

        $stmt->bindParam(':id', $manager_id);
        $stmt->execute();
        $stmt = null;

        header('Location: managers.php?msg=editOk');
        exit;
    } catch (Exception $e) {
        header('Location: managers.php?msg=error');
        exit;
    }
}

function updateContractorById($user_name, $contractor_id, $phone_first, $mail, $checkboxes, $active)
{
    global $pdo;
    global $user_id;
    try {
        $stmt = $pdo->prepare('UPDATE tb_users SET
        st_user_name = :st_user_name,
        st_phone_first = :st_phone_first,
        st_mail = :st_mail,
        i_active = :i_active,
        i_change_user = :i_change_user,
        date_change = NOW()
        WHERE id = :id'
        );
        $stmt->bindParam(':st_user_name', $user_name);
        $stmt->bindParam(':st_phone_first', $phone_first);
        $stmt->bindParam(':st_mail', $mail);
        $stmt->bindParam(':i_active', $active);
        $stmt->bindParam(':i_change_user', $user_id);

        $stmt->bindParam(':id', $contractor_id);
        $stmt->execute();
        $stmt = null;

        $sql = "DELETE FROM tb_contractors_conn WHERE i_contractor_id = :i_contractor_id";
        $stmt= $pdo->prepare($sql);
        $stmt->bindParam('i_contractor_id',$contractor_id);
        $stmt->execute();

        foreach ($checkboxes as $id=>$checked){
            if($checked == 1){
                $sql = "INSERT INTO tb_contractors_conn (i_contractor_id, i_contractor_type) VALUES (?, ?)";
                $params = [$contractor_id, $id];
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            }
        }

        header('Location: contractors.php?msg=editOk');
        exit;
    } catch (Exception $e) {
        header('Location: contractors.php?msg=error');
        exit;
    }
}

function updateProjectById($project_name, $project_manager, $project_address, $date_occupancy, $idToEdit, $selections, $active)
{
    global $pdo;
    global $user_id;
    try {
        $stmt = $pdo->prepare('UPDATE tb_projects SET
                st_project_name = :st_project_name,
                st_project_address = :st_project_address,
                i_project_manager = :i_project_manager,
                date_occupancy = :date_occupancy,
                i_change_user = :i_change_user,
                i_active = :i_active,
                date_change = NOW()
                WHERE id = :id'
        );
        $stmt->bindParam(':st_project_name', $project_name);
        $stmt->bindParam(':st_project_address', $project_address);
        $stmt->bindParam(':i_project_manager', $project_manager);
        $stmt->bindParam(':date_occupancy', $date_occupancy);
        $stmt->bindParam(':i_change_user', $user_id);
        $stmt->bindParam(':i_active', $active);

        $stmt->bindParam(':id', $idToEdit);
        $stmt->execute();
        $stmt = null;

        $sql = "DELETE FROM tb_projects_conn WHERE i_project_id = :i_project_id";
        $stmt= $pdo->prepare($sql);
        $stmt->bindParam('i_project_id',$idToEdit);
        $stmt->execute();

        foreach ($selections as $id=>$selected){
            if(isset($selected)){
                $sql = "INSERT INTO tb_projects_conn (i_project_id, i_contractor_id, i_contractor_type) VALUES (?, ?, ?)";
                $params = [$idToEdit, $selected, $id];
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            }
        }

        header('Location: projects.php?msg=editOk');
        exit;
    } catch (Exception $e) {
        header('Location: projects.php?msg=error');
        exit;
    }
}

function updatePerformingTheTest($body_text, $name, $phone, $report_serial)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare('UPDATE tb_reports SET
        st_checker = :st_checker,
        st_name_checker = :st_name_checker
        WHERE i_serial_number = :i_serial_number'
        );
        $stmt->bindParam(':st_checker', $body_text);
        $stmt->bindParam(':st_name_checker', $name);

        $stmt->bindParam(':i_serial_number', $report_serial);
        $stmt->execute();
        $stmt = null;

        $stmt = $pdo->prepare('UPDATE tb_users SET
        st_phone_checker = :st_phone_checker
        WHERE i_serial_number = :i_serial_number'
        );
        $stmt->bindParam(':st_phone_checker', $phone);

        $stmt->bindParam(':i_serial_number', $report_serial);
        $stmt->execute();
        $stmt = null;

        header('Location: report.php?id=' . $report_serial . '&msg=editOk');
        exit;
    } catch (Exception $e) {
        header('Location: report.php?id=' . $report_serial . '&msg=error');
        exit;
    }
}

function updateReportBySerialNumber($report_serial, $status)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare('UPDATE tb_reports SET i_status = :i_status WHERE i_serial_number = :i_serial_number');
        $stmt->bindParam(':i_status', $status);
        $stmt->bindParam(':i_serial_number', $report_serial);
        $stmt->execute();
        $stmt = null;

        $faults = getAllFaultsBySerialId($report_serial);
        $fault_status = 1;
        foreach ($faults as $fault){
            $stmt = $pdo->prepare('UPDATE tb_fault SET i_fault_status = :i_fault_status WHERE id = :id');
            $stmt->bindParam(':i_fault_status', $fault_status);
            $stmt->bindParam(':id', $fault['fault_id']);
            $stmt->execute();
        }
        $stmt = null;


        header('Location: report.php?id=' . $report_serial . '&msg=editOk');
        exit;
    } catch (Exception $e) {
        header('Location: report.php?id=' . $report_serial . '&msg=error');
        exit;
    }
}

function updateReportStatusBySerialNumber($report_serial, $status)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare('UPDATE tb_reports SET i_status = :i_status WHERE i_serial_number = :i_serial_number');
        $stmt->bindParam(':i_status', $status);
        $stmt->bindParam(':i_serial_number', $report_serial);
        $stmt->execute();
        $stmt = null;

        header('Location: report.php?id=' . $report_serial . '&msg=editOk');
        exit;
    } catch (Exception $e) {
        header('Location: report.php?id=' . $report_serial . '&msg=error');
        exit;
    }
}

function updateFaultById($fault_id, $body_text, $category, $title, $report_serial)
{
    global $pdo;
    try {
        $stmt = $pdo->prepare('UPDATE tb_fault SET
        i_category = :i_category,
        st_title = :st_title,
        st_fault_content = :st_fault_content
        WHERE id = :id'
        );
        $stmt->bindParam(':i_category', $category);
        $stmt->bindParam(':st_title', $title);
        $stmt->bindParam(':st_fault_content', $body_text);

        $stmt->bindParam(':id', $fault_id);
        $stmt->execute();
        $stmt = null;

        header('Location: report.php?id=' . $report_serial . '&msg=editOk');
        exit;
    } catch (Exception $e) {
        header('Location: report.php?id=' . $report_serial . '&msg=error');
        exit;
    }
}

