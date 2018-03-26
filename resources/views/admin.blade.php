<!DOCTYPE html>
<html lang="en" data-ng-app="App">
<head>
    <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Restorer</title>
    <link rel="stylesheet" href="/assets/icons/style.css"/>
    <link rel="stylesheet" href="/assets/css/styleAdmin.css"/>

    <script>
        window.localeData = {
            'header_link_user_list': 'User List',
            'header_link_dashboard': 'Dashboard',
            'header_link_notification': 'Notification',
            //
            'admin_dashboard_users': 'Users in system',
            'admin_dashboard_projects_uploaded': 'Projects uploaded',
            'admin_dashboard_disk_space': 'Disk space used',
            //
            'admin_dashboard_general_title': 'General History',
            'admin_user_list_title': 'User List',
            'admin_user_history_title': 'User History'
            //
        };
    </script>
</head>
<body data-ng-class="{'scroll-freeze': $root.scrollFreeze}">
    <header></header>
    <ui-view></ui-view>
    <confirm-dialog></confirm-dialog>

    <script src="/appAdmin/build.js"></script>
</body>
</html>