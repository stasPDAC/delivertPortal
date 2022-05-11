<?php global $page ?>
<?php global $sub_page ?>
<?php global $user_type ?>
<?php global $user_name_connect ?>
<header>
    <div class="header__nav">
        <nav>
            <?php if($user_type != 4) : ?>
                    <a <?= $page == 'projects' ? 'class="bold"' : ''?> href="../projects.php">פרוייקטים</a>
                    <span>|</span>
                <?php if($user_type == 1) : ?>
                    <div class="sub_bt <?= $page == 'users' ? 'bold' : ''?>">מורשי כניסה
                        <div class="sub_menu">
                            <a <?= $sub_page == 'admins' ? 'class="bold"' : ''?> href="../users.php">מנהלי מערכת</a>
                            <div class="sub_menu_line"></div>
                            <a <?= $sub_page == 'managers' ? 'class="bold"' : ''?> href="../managers.php">מנהלי פרויקטים</a>
                            <div class="sub_menu_line"></div>
                            <a <?= $sub_page == 'contractors' ? 'class="bold"' : ''?> href="../contractors.php">קבלני ביצוע</a>
<!--                            <div class="sub_menu_line"></div>-->
<!--                            <a --><?//= $sub_page == 'clients' ? 'class="bold"' : ''?><!-- href="../clients.php">דיירים</a>-->
                            <div class="sub_menu_line"></div>
                            <a <?= $sub_page == 'inspectors' ? 'class="bold"' : ''?> href="../inspectors.php">בקרת איכות</a>
                        </div>
                    </div>
                    <span>|</span>
                <?php endif ?>
<!--                --><?php //if($user_type == 1 || $user_type == 2 || $user_type == 5) : ?>
<!--                    <a --><?//= $page == 'reports' ? 'class="bold"' : ''?><!-- href="../reports.php">דוחות דיירים</a>-->
<!--                    <span>|</span>-->
<!--                --><?php //endif ?>
                <?php if($user_type == 3) : ?>
                    <a <?= $page == 'notes' ? 'class="bold"' : ''?> href="../notes.php">תקלות</a>
                    <span>|</span>
                <?php endif ?>
                    <a href="/?logOut=logOut">יציאה</a>




            <?php else : ?>
                <?php $report_serial = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS); ?>
                <a href="/?logOut=logOut&report=<?=$report_serial?>">יציאה</a>
            <?php endif ?>
        </nav>
    </div>
    <div class="header__name">
        <p>שלום: <span class="bold"><?=strtok($user_name_connect, " ");?></span></p>
    </div>
    <button aria-expanded="false" aria-label="לחץ לפתיחת תפריט" class="icon">
        <div class="burger"></div>
    </button>
        <div class="menu_mobile">
            <nav>
                <p>שלום: <span class="bold"><?=strtok($user_name_connect, " ");?></span></p>
                <div class="line"></div>
                <?php if($user_type != 4) : ?>
                        <a <?= $page == 'projects' ? 'class="bold"' : ''?> href="../projects.php">פרוייקטים</a>
                    <?php if($user_type == 1) : ?>
                        <a  class="sub_bt_mobile <?= $page == 'users' ? 'bold' : ''?>">מורשי כניסה</a>
                        <div class="sub_menu_mobile">
                            <a <?= $sub_page == 'admins' ? 'class="bold"' : ''?> href="../users.php">מנהלי מערכת</a>
                            <a <?= $sub_page == 'managers' ? 'class="bold"' : ''?> href="../managers.php">מנהלי פרויקטים</a>
                            <a <?= $sub_page == 'contractors' ? 'class="bold"' : ''?> href="../contractors.php">קבלני ביצוע</a>
<!--                            <a --><?//= $sub_page == 'clients' ? 'class="bold"' : ''?><!-- href="../clients.php">דיירים</a>-->
                            <a <?= $sub_page == 'inspectors' ? 'class="bold"' : ''?> href="../inspectors.php">בקרת איכות</a>
                        </div>
                    <?php endif ?>
<!--                    --><?php //if($user_type == 1 || $user_type == 2 || $user_type == 5) : ?>
<!--                        <a --><?//= $page == 'reports' ? 'class="bold"' : ''?><!-- href="../reports.php">דוחות דיירים</a>-->
<!--                    --><?php //endif ?>
                    <?php if($user_type == 3) : ?>
                        <a <?= $page == 'notes' ? 'class="bold"' : ''?> href="../notes.php">תקלות</a>
                    <?php endif ?>
                    <a href="/">יציאה</a>
                <?php else : ?>
                    <?php $report_serial = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS); ?>
                    <a href="/?report=<?=$report_serial?>">יציאה</a>
                <?php endif ?>
            </nav>
        </div>
    <div class="menu_mobile_bg"></div>
    <div class="header__logo"><img src="../svg/SolelBuilds_logo.svg" alt=""></div>
</header>
<script src="../js/header.js?var=3"></script>
<main>

