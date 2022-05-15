<?php
require_once 'includes/config.php';
include_once 'includes/global.php';
$page = 'users';
$sub_page = 'contractors';
global $icons;
global $user_type;
if($user_type != 1){
    error404();
}
$msg = filter_input(INPUT_GET, 'msg', FILTER_SANITIZE_SPECIAL_CHARS);
$contractors = getAllContractors();
switch ($msg){
    case 'createOk':
        $msg = 'קבלן חדש נוצר בהצלחה';
        break;
    case 'editOk':
        $msg = 'הנתונים שמרו בהצלחה';
        break;
    case 'deleteOk':
        $msg = 'קבלן נמחק בהצחה';
        break;
    case 'error':
        $msg = 'משהו השתבש נסו שוב מאוחר יותר';
        break;
    default;
}
include_once 'includes/head.php';
include_once 'includes/header.php';
?>
<?= isset($msg) ? '<div class="msg" id="msg"><p>' . $msg .'</p></div>' : '' ?>
<div class="main_container">
    <div class="main_container__header">
        <div class="main_container__title">קבלני ביצוע</div>
        <a class="btn" href="contractorEditor.php">הוספת קבלן חדש</a>
    </div>

    <div class="line"></div>
    <table id="projects_table" class="table table-striped">
        <thead>
        <tr>
            <th>שם הקבלן</th>
            <th>מספר נייד</th>
            <th>דוא"ל</th>
            <th>תחומים</th>
            <th>פרויקטים פעילים</th>
            <th>סטטוס</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($contractors as $contractor) : ?>
            <tr <?= $contractor['i_active'] == '1' ? '' : 'class="disable_user"' ?>>
                <td><?= $contractor['st_user_name'] != '' ? $contractor['st_user_name'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td><?= $contractor['st_phone_first'] != '' ? $contractor['st_phone_first'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td><?= $contractor['st_mail'] != '' ? $contractor['st_mail'] : '<span class="td_empty">אין נתונים</span>' ?></td>
                <td class="td_link">
                    <?php
                        $count = count(getAllContractorsTypesById($contractor['id']));
                        $domains = getAllContractorsTypesById($contractor['id']);
                        if($count > 1){
                            echo '<div class="td_link_hover">' . $count . ' תחומים';
                            echo '<div class="td_hover_list">';
                            foreach ($domains as $domain){
                                echo '<p>- ' . $domain['st_contractor_type']  . '</p>';
                            }
                            echo '</div>';
                            echo '</div>';
                        }elseif ($count == 1){
                            echo $domains[0]['st_contractor_type'];
                        }else{
                            echo '<span class="td_empty">לא נבחר תחום</span>';
                        }
                    ?>
                </td>
                <td class="td_link">
                    <?php
                    $count = count(getAllProjectsByContractorId($contractor['id']));
                    $projects = getAllProjectsByContractorId($contractor['id']);
                    if($count > 1){
                        echo '<div class="td_link_hover">' . $count . ' פרויקטים';
                        echo '<div class="td_hover_list">';
                        foreach ($projects as $project){
                            echo '<p>- ' . $project['st_project_name']  . '</p>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }elseif ($count == 1){
                        echo $projects[0]['st_project_name'];
                    }else{
                        echo '<span class="td_empty">אין פרויקט פעיל</span>';
                    }
                    ?>
                </td>
                <td><?= $contractor['i_active'] == '1' ? 'פעיל' : 'מבוטל' ?></td>

                <td class="actions">
                    <a title="עריכת קבלן" href="contractorEditor.php?edit=<?= $contractor['id'] ?>"><?= $icons['edit'] ?></a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="js/contractors.js"></script>
<?php include_once 'includes/footer.php'; ?>
