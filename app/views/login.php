
<!DOCTYPE html>

<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Frugal Kitchens v2.0 | Login</title>
<link href="/css/styles.css" rel="stylesheet" type="text/css">

<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
<script type="text/javascript" src="/js/vendors/horisontal/modernizr.custom.js"></script>
</head>

<body>
<div class="colorful-page-wrapper">
  <div class="center-block">
    <div class="login-block">
      <form action="/login" id="login-form" class="orb-form" method='post'>
        <header>
          <div class="image-block"><img src="/images/logo.png" alt="User" /></div>
          Frugal Kitchens and Cabinets</header>
        <?php
        if (Session::has('loginFailed'))
          echo BS::callout('danger', "<b>Login Failed</b> You have entered an incorrect username or password. Please try again.");
        ?>

        <fieldset>
          <section>
            <div class="row">
              <label class="label col col-4">E-mail</label>
              <div class="col col-8">
                <label class="input"> <i class="icon-append fa fa-user"></i>
                  <input type="email" name="email">
                </label>
              </div>
            </div>
          </section>
          <section>
            <div class="row">
              <label class="label col col-4">Password</label>
              <div class="col col-8">
                <label class="input"> <i class="icon-append fa fa-lock"></i>
                  <input type="password" name="password">
                </label>
                <div class="note"><a href="#">Forgot password?</a></div>
              </div>
            </div>
          </section>
          <section>
            <div class="row">
              <div class="col col-4"></div>
              <div class="col col-8">
                <label class="checkbox">
                  <input type="checkbox" name="remember" checked>
                  <i></i>Keep me logged in</label>
              </div>
            </div>
          </section>
        </fieldset>
        <footer>
          <button type="submit" class="btn btn-default">Log in</button>
        </footer>
      </form>
    </div>
    <div class="using-social-header">THIS IS A PRIVATE SYSTEM. UNAUTHORIZED ACCESS PROHIBITED</div>
    <div class="copyrights"> Frugal Kitchens Operational Dashboard v2.0 <br/>
      Created by <a href='http://www.vocalogic.com'>Vocalogic</a> <br/>Copyright &copy;2014 Frugal Kitchens and Cabinets.<br/>
      All Rights Reserved.</div>
  </div>
</div>

<!--Scripts-->
<!--JQuery-->
<script type="text/javascript" src="/js/vendors/jquery/jquery.min.js"></script>
<script type="text/javascript" src="/js/vendors/jquery/jquery-ui.min.js"></script>

<!--Forms-->
<script type="text/javascript" src="/js/vendors/forms/jquery.form.min.js"></script>
<script type="text/javascript" src="/js/vendors/forms/jquery.validate.min.js"></script>
<script type="text/javascript" src="/js/vendors/forms/jquery.maskedinput.min.js"></script>
<script type="text/javascript" src="/js/vendors/jquery-steps/jquery.steps.min.js"></script>

<!--NanoScroller-->
<script type="text/javascript" src="/js/vendors/nanoscroller/jquery.nanoscroller.min.js"></script>

<!--Sparkline-->
<script type="text/javascript" src="/js/vendors/sparkline/jquery.sparkline.min.js"></script>

<!--Main App-->
<script type="text/javascript" src="/js/scripts.js"></script>
<!--/Scripts-->

</body>
</html>
