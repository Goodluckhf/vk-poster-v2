<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <title>Выгрузка продуктов</title>
        <meta charset="UTF-8">
        <!--meta name="viewport" content="width=device-width, initial-scale=1.0"-->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <script src="/js/vendor/jquery.js"></script>
        <script src="/js/vendor/jquery-ui.js"></script>
        <!--script src="/js/vendor/slimscroll.js" type="text/javascript"></script-->
        <script src="/js/vendor/bootstrap.js"></script>

        <script src="//vk.com/js/api/openapi.js" type="text/javascript"></script>
        <script src="/js/vendor/bootbox.js" type="text/javascript"></script>
        <script src="/js/vendor/moment.js" type="text/javascript"></script>
        <script src="/js/vendor/ru.js" type="text/javascript"></script>
        <script src="/js/vendor/datepicker.min.js" type="text/javascript"></script>
        <script src="/js/vendor/toastr.js" type="text/javascript"></script>
        <script  type="text/javascript">
            $(function() {
                toastr.options = {
                "closeButton": false,
                "debug": false,
                "newestOnTop": false,
                "progressBar": false,
                "positionClass": "toast-bottom-left",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "2000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
              }
            });


        </script>

        <script src="/js/vendor/app.js"></script>

        <script src="/js/EventsContainer.js" type="text/javascript"></script>
        <script src="/js/Request.js" type="text/javascript"></script>
        <script src="/js/VKAuthService.js" type="text/javascript"></script>
        <script src="/js/AuthService.js" type="text/javascript"></script>
        <script src="/js/Posts.js" type="text/javascript"></script>
        <script src="/js/LoginForm.js" type="text/javascript"></script>
        <script src="/js/PostProvider.js" type="text/javascript"></script>
        <script src="/js/script.js" type="text/javascript"></script>

        <script type="text/javascript">
            App.setToken("{{csrf_token()}}");
//            VK.init({
//                apiId: 	5180832
//            });

        </script>
        <link rel='stylesheet' href="/css/style.css">
        <link rel='stylesheet' href="/css/bootstrap.css">
        <link rel='stylesheet' href="/css/AdminLTE.css">
        <link rel='stylesheet' href="/css/_all-skins.css">
        <link rel='stylesheet' href="/css/datepicker.min.css">
        <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
        <link rel='stylesheet' href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
        <link rel='stylesheet' href="/css/toastr.css">
    </head>
    <body class=" skin-green sidebar-mini fixed">

        <div class="wrapper">

            <header class="main-header">
                <!-- Logo -->
                <a href="#" class="logo">
                    <!-- mini logo for sidebar mini 50x50 pixels -->
                    <span class="logo-mini"><b>VK</b>P</span>
                    <!-- logo for regular state and mobile devices -->
                    <span class="logo-lg"><b>VK</b>Post</span>
                </a>
                <!-- Header Navbar: style can be found in header.less -->
                <nav class="navbar navbar-static-top" role="navigation">
                    <!-- Sidebar toggle button-->
                    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                        <span class="sr-only">Toggle navigation</span>
                    </a>
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                           <li class="dropdown messages-menu">
                                <a data-toggle="dropdown" class="dropdown-toggle next-post-date" href="#" aria-expanded="false">
                                    следущий: <span></span>
                                </a>
                            </li>
                           <li class="dropdown messages-menu">
                                <a data-toggle="dropdown" class="dropdown-toggle group-list-select" href="#" aria-expanded="false">
                                    Выбери группу
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <!-- inner menu: contains the actual data -->
                                        <div class="slimScrollDiv" style="position: relative; overflow: auto; width: auto; height: 200px;"><ul class="menu" style=" width: 100%; height: 200px;">


                                        </ul><div class="slimScrollBar" style="background: rgb(0, 0, 0) none repeat scroll 0% 0%; width: 3px; position: absolute; top: 54px; opacity: 0.4; display: none; border-radius: 7px; z-index: 99; right: 1px; height: 131.148px;"></div><div class="slimScrollRail" style="width: 3px; height: 100%; position: absolute; top: 0px; display: none; border-radius: 7px; background: rgb(51, 51, 51) none repeat scroll 0% 0%; opacity: 0.2; z-index: 90; right: 1px;"></div></div>
                                    </li>
                                    <li class="footer"></li>
                                </ul>
                            </li>
                            <li class="dropdown user user-menu">
                                <a aria-expanded="false" href="#" class="dropdown-toggle open-login-form" data-toggle="dropdown">

                                    <span class="hidden-xs">Войти</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <!-- User image -->
                                    <li class="user-header">
                                        <img src="" class="img-circle" alt="User Image">
                                        <p>
                                            Alexander Pierce - Web Developer
                                            <small>Member since Nov. 2012</small>
                                        </p>
                                    </li>
                                    <!-- Menu Body -->
                                    <li class="user-body">
                                        <div class="col-xs-4 text-center">
                                            <a href="#">Followers</a>
                                        </div>
                                        <div class="col-xs-4 text-center">
                                            <a href="#">Sales</a>
                                        </div>
                                        <div class="col-xs-4 text-center">
                                            <a href="#">Friends</a>
                                        </div>
                                    </li>
                                    <!-- Menu Footer-->
                                    <li class="user-footer">
                                        <div class="pull-left">
                                            <a href="#" class="btn btn-default btn-flat">Profile</a>
                                        </div>
                                        <div class="pull-right">
                                            <a href="#" class="btn btn-default btn-flat">Sign out</a>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                            <!-- Control Sidebar Toggle Button -->

                        </ul>
                    </div>
                </nav>
            </header>
            <!-- Left side column. contains the logo and sidebar -->
            <aside class="main-sidebar">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <!-- Sidebar user panel -->
                    <div class="user-panel">
                        <div class="pull-left image">
                            <img src="#" class="img-circle" alt=0>
                        </div>
                        <div class="pull-left info">
                            <p>Настройки</p>

                        </div>
                    </div>
                    <!-- search form -->
                    <div  class="sidebar-form">
                        <div class="input-group">
                            <input name="q" class="form-control group-search-inp" placeholder="Группа..." type="text">
                            <span class="input-group-btn">
                                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i></button>
                            </span>
                        </div>
                    </div>
                    <!-- /.search form -->
                    <!-- sidebar menu: : style can be found in sidebar.less -->
                    <ul class="sidebar-menu">
                        <li class="header">MAIN NAVIGATION</li>


                        <li>
                            <a class="sort-by-reposts" href="#">
                                <i class="fa fa-th"></i> <span>Сортировать</span>
                            </a>
                        </li>

                        <!--li>
                            <div class="group-inp-div">
                                <input type="text" class="form-control group-inp" placeholder="Ваш паблик">
                            </div>
                        </li-->







                        <li class="header">Дата и время</li>
                        <li>
                            <div class="date-picker-div">
                                <input type="text" placeholder="Дата поста" class="form-control date-picker">
                            </div>
                        </li>
                        <li>

                        <center><button  class="btn btn-primary saveConfig" >Сохранить</button></center>

                        </li>


                    </ul>
                </section>
                <!-- /.sidebar -->
            </aside>

            <!-- Content Wrapper. Contains page content -->
            <div style="min-height: 909px;" class="content-wrapper">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>
                        Постер

                    </h1>

                </section>

                <!-- Main content -->
                <section class="content">
                    <!-- Small boxes (Stat box) -->
                    <div class="row">
                        <!-- ./col -->
                        <!-- ./col -->
                        <!-- ./col -->
                        <!-- ./col -->
                    </div><!-- /.row -->
                    <!-- Main row -->
                    <div class="row">
                        <!-- Left col -->
                        <section class="col-lg-12 connectedSortable">
                            <!-- Custom tabs (Charts with tabs)-->
                            <div class="nav-tabs-custom">
                                <!-- Tabs within a box -->
                                <ul class="nav nav-tabs pull-right">
                                    <li class="pull-left header"><i class="fa fa-inbox"></i> Посты</li>
                                </ul>
                                <div class="tab-content no-padding">
                                    <!-- Morris chart - Sales -->

                                </div>
                            </div><!-- /.nav-tabs-custom -->

                            <!-- Chat box -->
                            <!-- /.box (chat box) -->

                            <!-- TO DO List -->
                            <!-- /.box -->

                            <!-- quick email widget -->


                        </section><!-- /.Left col -->
                        <!-- right col (We are only adding the ID to make the widgets sortable)-->
                        <!-- right col -->
                    </div><!-- /.row (main row) -->

                </section><!-- /.content -->
            </div><!-- /.content-wrapper -->
            <footer class="main-footer">
                <div class="pull-right hidden-xs">
                    <b>Version</b> 2.3.0
                </div>
                <strong>Copyright &copy; 2015-2016 <a href="#">Четкая студия</a>.</strong> Все права защищены.
            </footer>
        </div><!-- ./wrapper -->
        <!-- ./wrapper -->





    </body>
</html>
