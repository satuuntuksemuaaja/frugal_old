<?php
if (!isset($title)) $title = null;
if (!isset($content)) $content = null;
?>
<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?=$title?></title>
<style type="text/css">
@import url("css/vendors/x-editable/address.css");
@import url("css/vendors/x-editable/select2.css");
 @import url("css/vendors/x-editable/typeahead.js-bootstrap.css");
@import url("css/vendors/x-editable/demo-bs3.css");
@import url("css/vendors/x-editable/select2-bootstrap.css");
@import url("css/vendors/x-editable/bootstrap-editable.css");
</style>

<link id="main-style" href="/css/styles.css" rel="stylesheet" type="text/css">
<link rel='stylesheet' type='text/css' href='/js/vendors/form-select2/select2.css' />
<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
<link rel='stylesheet' type='text/css' href='/js/datepicker/datepicker3.css'/>
<link rel='stylesheet' type='text/css' href='/js/bootstrap-3-timepicker-master/css/bootstrap-timepicker.css' />

<script type="text/javascript" src="/js/vendors/modernizr/modernizr.custom.js"></script>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
<script type="text/javascript" src="/js/vendors/jquery/jquery.min.js"></script>
<script type="text/javascript" src="/js/vendors/jquery/jquery-ui.min.js"></script>

<script type="text/javascript" src="/js/vendors/sigpad/jquery.signaturepad.min.js"></script>
<script type="text/javascript" src="/js/jQuery.pulsate.min.js"></script>
<script type='text/javascript' src='/js/datepicker/js/bootstrap-datepicker.js'></script>
<script type='text/javascript' src='/js/bootstrap-3-timepicker-master/js/bootstrap-timepicker.js'></script>
    <!--Datatables-->
    <script type="text/javascript" src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="/js/vendors/datatables/jquery.dataTables-bootstrap.js"></script>



    <style type="text/css">
.form-inline .form-control {
  height:auto;
}
</style>
</head>
<body>
<!--Smooth Scroll-->
<div class="smooth-overflow">
<!--Navigation-->
    <nav class="main-header clearfix" role="navigation"> <a class="navbar-brand" href="/"><span class="text-blue"><?=Config::get('crm.title')?></span></a>

      <!--Search-->
      <div class="site-search">
        <form action="#" id="inline-search">
          <i class="fa fa-search"></i>
          <input type="search" placeholder="Search">
        </form>
      </div>

      <!--Navigation Itself-->

      <div class="navbar-content">

        <!--Sidebar Toggler-->
        <a href="#" class="btn btn-default left-toggler"><i class="fa fa-bars"></i></a>
        <!--Fullscreen Trigger-->
        <button type="button" class="btn btn-default hidden-xs pull-right" id="toggle-fullscreen"> <i class=" entypo-popup"></i> </button>

        </div>
      </div>
    </nav>

    <!--/Navigation-->

    <!--MainWrapper-->
    <div class="main-wrap">


      <!-- /Offcanvas user menu-->

      <!--Main Menu-->
      <div class="responsive-admin-menu">
        <div class="responsive-menu"><?=Config::get('crm.title')?>
          <div class="menuicon"><i class="fa fa-angle-down"></i></div>
        </div>
      <ul id="menu">

          <?php
            if (Auth::check() && Auth::user()->superuser)
              echo '<li><a href="/admin" title="Admin"><i class="fa fa-edit"></i><span> Admin</span></a></li>
                    <li><a href="/reports" title="Reports"><i class="fa fa-money"></i><span> Reports</span></a></li>';
            ?>

          <?php
          if (Auth::check() && Auth::user()->level_id != 6)
          {
             if ((Auth::check() && Auth::user()->superuser) || (Auth::user()->level_id == 4 && Auth::user()->designation_id == 8) || Auth::user()->id == 32 || Auth::user()->level_id == 10)
            $customers =
            '<li><a href="/customers" title="Leads"><i class="entypo-user"></i><span> Customers</span></a></li>';
            else $customers = null;
            echo '
          <li><a href="/" title="Calendar"><i class="entypo-inbox"></i><span> Home</span></a></li>
          '.$customers.'
          <li><a href="/dashboard" title="Dashboard"><i class="entypo-list"></i><span> Dashboard</span></a></li>
          <li><a href="/leads" title="Leads"><i class="entypo-list"></i><span> Leads</span></a></li>';
          if (Auth::check() && Auth::user()->level_id != 10)
            echo '<li><a href="/quotes" title="Quotes"><i class="entypo-cc-share"></i><span> Quotes</span></a></li>';
          if (Auth::user()->level_id != 4 && Auth::user()->level_id != 7)
          echo '<li><a href="/jobs" title="Jobs"><i class="entypo-tools"></i><span> Jobs</span></a></li>
          <li><a href="/fft" title="FFT"><i class="fa fa-exclamation"></i><span> Final Touch</span></a></li>
          <li><a href="/warranties" title="FFT"><i class="fa fa-question"></i><span> Warranties</span></a></li>
          <li><a href="/service" title="FFT"><i class="fa fa-gears"></i><span> Service Work</span></a></li>
          
          <li><a href="/changes" title="FFT"><i class="fa fa-refresh"></i><span> Change Orders</span></a></li>
          <li><a href="/pos" title="POs"><i class="fa fa-truck"></i><span> Purchase Orders</span></a></li>
          ';
          echo '
          <li><a href="/tasks" title="Tasks"><i class="fa fa-envelope"></i><span> Tasks</span></a></li>
          <li><a href="/receiving" title="Receiving"><i class="fa fa-arrow-left"></i><span> Receiving</span></a></li>
          <li><a href="/buildup" title="Buildup"><i class="fa fa-arrow-up"></i><span> Buildup</span></a></li>

          ';


          }
          ?>
          <li><a href="/logout" title="Logout"><i class="fa fa-arrow-right"></i><span> Logout</span></a></li>
        </ul>
      </div>
      <!--/MainMenu-->

      <!--Content Wrapper-->
      <div class="content-wrapper">

      <?php
        if (isset($crumbs))
        {
          $data = "<!--Breadcrumb-->
              <div class='breadcrumb clearfix'>
          <ul>
            <li><a href='/'><i class='fa fa-home'></i></a></li>";
            foreach ($crumbs AS $crumb)
              $data .= "<li><a href='$crumb[url]'>$crumb[text]</a></li>";
          $data .= "</ul></div>";
          echo $data;
        }
        if (preg_match('/dev.fru/', App::make('request')->getUri()))
            echo "<div class='alert alert-info'>You are currently working on the DEV site!</div>";
      echo $content;
      ?>


      </div>
      <!-- / Content Wrapper -->

    </div>
    <!--/MainWrapper-->
  </div>
<!--/Smooth Scroll-->


<!-- scroll top -->
<div class="scroll-top-wrapper hidden-xs">
    <i class="fa fa-angle-up"></i>
</div>
<!-- /scroll top -->



<!--Scripts-->
<!--JQuery-->


<!--Fullscreen-->
<script type="text/javascript" src="/js/vendors/fullscreen/screenfull.min.js"></script>

<!--Forms-->
<script type="text/javascript" src="/js/vendors/forms/jquery.form.min.js"></script>
<script type="text/javascript" src="/js/vendors/forms/jquery.validate.min.js"></script>
<script type="text/javascript" src="/js/vendors/forms/jquery.maskedinput.min.js"></script>
<script type='text/javascript' src='/js/vendors/form-select2/select2.min.js'></script>
<script type="text/javascript" src="/js/vendors/jquery-steps/jquery.steps.min.js"></script>
<script type='text/javascript' src='/js/vendors/form-inputmask/jquery.inputmask.bundle.min.js'></script>



<!--Sparkline-->
<script type="text/javascript" src="/js/vendors/sparkline/jquery.sparkline.min.js"></script>

<!--Horizontal Dropdown-->
<script type="text/javascript" src="/js/vendors/horisontal/cbpHorizontalSlideOutMenu.js"></script>
<script type="text/javascript" src="/js/vendors/classie/classie.js"></script>


<!--PowerWidgets-->
<script type="text/javascript" src="/js/vendors/powerwidgets/powerwidgets.js"></script>

<!--Summernote-->
<script type="text/javascript" src="/js/vendors/summernote/summernote.min.js"></script>

<!--Morris Chart-->
<script type="text/javascript" src="/js/vendors/raphael/raphael-min.js"></script>
<script type="text/javascript" src="/js/vendors/morris/morris.min.js"></script>

<!--FlotChart-->
<script type="text/javascript" src="/js/vendors/flotchart/jquery.flot.min.js"></script>
<script type="text/javascript" src="/js/vendors/flotchart/jquery.flot.stack.min.js"></script>
<script type="text/javascript" src="/js/vendors/flotchart/jquery.flot.categories.min.js"></script>
<script type="text/javascript" src="/js/vendors/flotchart/jquery.flot.time.min.js"></script>
<script type="text/javascript" src="/js/vendors/flotchart/jquery.flot.resize.min.js"></script>
<script type="text/javascript" src="/js/vendors/flotchart/jquery.flot.axislabels.js"></script>
<script type="text/javascript" src="/js/vendors/flotchart/jquery.flot-tooltip.js"></script>
<script type="text/javascript" src="/js/vendors/flotchart/jquery.flot.pie.min.js"></script>



<!--Calendar-->
<script type="text/javascript" src="/js/vendors/fullcalendar/fullcalendar.min.js"></script>
<script type="text/javascript" src="/js/vendors/fullcalendar/gcal.js"></script>

<!--Bootstrap-->
<script type="text/javascript" src="/js/vendors/bootstrap/bootstrap.min.js"></script>

<!--Bootstrap Progress Bar-->
<script type="text/javascript" src="/js/vendors/bootstrap-progress-bar/bootstrap-progressbar.min.js"></script>
<script type="text/javascript" src="/js/bootstrap-confirmation.js"></script>
<!--iOnRangeSlider-->
<script type="text/javascript" src="/js/vendors/ionrangeslider/ion.rangeSlider.min.js"></script>
<!--X-Editable-->
<script type="text/javascript" src="/js/vendors/x-editable/bootstrap-editable.min.js"></script>
<script type="text/javascript" src="/js/vendors/x-editable/demo-mock.js"></script>
<script type="text/javascript" src="/js/vendors/x-editable/select2.js"></script>
<script type="text/javascript" src="/js/vendors/x-editable/address.js"></script>
<script type="text/javascript" src="/js/vendors/x-editable/jquery.mockjax.js"></script>
<script type="text/javascript" src="/js/vendors/x-editable/moment.min.js"></script>
<script type="text/javascript" src="/js/vendors/x-editable/select2-bootstrap.css"></script>
<script type="text/javascript" src="/js/vendors/x-editable/typeahead.js"></script>
<script type="text/javascript" src="/js/vendors/x-editable/typeaheadjs.js"></script>
<!--Vector Map-->
<script type="text/javascript" src="/js/vendors/vector-map/jquery.vmap.min.js"></script>
<script type="text/javascript" src="/js/vendors/vector-map/jquery.vmap.sampledata.js"></script>
<script type="text/javascript" src="/js/vendors/vector-map/jquery.vmap.world.js"></script>
<script type="text/javascript" src="/js/vendors/vector-map/jquery.vmap.usa.js"></script>
<script type="text/javascript" src="/js/vendors/vector-map/jquery.vmap.europe.js"></script>
<script type="text/javascript" src="/js/vendors/vector-map/jquery.vmap.russia.js"></script>

<!--ToDo-->
<script type="text/javascript" src="/js/vendors/todos/todos.js"></script>

<!--Nestable-->
<script type="text/javascript" src="/js/vendors/nestable-lists/jquery.nestable.js"></script>

<!--FitVids-->
<script type="text/javascript" src="/js/vendors/fitvids/jquery.fitvids.js"></script>

<!--Main App-->
<script type="text/javascript" src="/js/scripts.js"></script>
<script type='text/javascript' src='/js/c3.js'></script>







<!--/Scripts-->

</body>
</html>