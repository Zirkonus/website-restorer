
<!DOCTYPE html>
<html lang="en" data-ng-app="App">
<head>
    <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Restorer</title>
    <link rel="stylesheet" href="/assets/icons/style.css"/>
    <link rel="stylesheet" href="/assets/css/styleAdmin.css"/>
    <link rel="stylesheet" href="/assets/css/auth.css"/>

    <script>
        window.localeData = {};
    </script>
</head>
<body data-ng-class="{'scroll-freeze': $root.scrollFreeze}" class="auth">

    <label class="hidden"  id='error-email' value="{{ $errors->has('email') ? 'has-login-err' : '' }}"></label>
    <label class="hidden"  id='error-login' value="{{ $errors->has('password') ?  'has-error-pass' : '' }}"></label>

<header></header>
<ui-view></ui-view>
<footer-tpl></footer-tpl>

<script src="/auth/build.js"></script>
<script>
    angular.module("App").constant("CSRF_TOKEN", '{{ csrf_token() }}');
</script>
</body>
</html>
