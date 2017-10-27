<?php
    require_once __DIR__ . '/bootstrap.php';

    $demoInfo = getStatus();
    $serverName = preg_replace('/:\d+$/i', '', $_SERVER['HTTP_HOST']);

?><!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">

    <style>
        .card-img-top {
            padding-top: 30%;
            background-repeat: no-repeat;
            background-position: center center;
            background-size: 70%;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="page-header">Altinity Demo Appliance</h1>

    <p>
        <b>Altinity Data Platform</b> is a toolset which provides you with the easiest way to process your analytic data and get maximum information out of it in a second.
    </p>

    <h2>Engine Core</h2>
    <p><b>Altinity Data Platform</b> uses <b>ClickHouse</b> Database server to store and process analytics data.</p>

    <p>
        ClickHouse is an open source column-oriented database management system capable of real time generation of analytical data reports using SQL queries.
    </p>

    <p>
        ClickHouse streamlines all your data processing. It's easy to use: ingest all your structured data into the system, and it is instantly available for reports. New columns for new properties or dimensions can be easily added to the system at any time without slowing it down.
    </p>
    <p>
        ClickHouse is simple and works out-of-the-box. As well as performing on hundreds of node clusters, this system can be easily installed on a single server or even a virtual machine. No development experience or code-writing skills are required to install ClickHouse.
    </p>

    <h2>Demo Data <span class="badge bg-success demo-data-installed" style="display:none">Installed</span></h2>
    <p>
        Use demo data and see for yourself how fast you can play with tons of events generated by official air traffic service in US.
    </p>

    <div class="demo-data-installed" style="display:none">
        <h4>Demo Data Usage</h4>
        <p>
            There is a table named <b>ontime</b> created and populated in the <b>default</b> database at ClickHouse server. It has <b class="records-count"><?php echo $demoInfo['records'] ?> records</b>.
        </p>
        <p>
            There is also a dashboard in Grafana prepared for this particular dataset. You can view it <a href="//<?php echo $serverName ?>:3000/dashboard/db/ontime?orgId=1">here</a>.
        </p>
        <p>
            You can also try to play with the following queries by using preinstalled <a href="//<?php echo $serverName ?>:8123/">Tabix</a> or any other MySQL client connected to ProxySQL port (see <a href="#proxySQLcollapse" data-toggle="collapse" aria-expanded="false" aria-controls="proxySQLcollapse">here</a>).
        </p>

        <p>This one gets you a number of fligts per each year:</p>
        <pre class="font-weight-bold">> SELECT toYear(FlightDate) y, count() FROM ontime GROUP BY y ORDER BY y</pre>
        <p>This one gets you list of states ordered by number of flights that goes out:</p>
        <pre class="font-weight-bold">> SELECT OriginState, count() c FROM ontime GROUP BY OriginState ORDER BY c DESC</pre>
        <p>This one gets you a number of flights per month made from CA ordered from the most active to worst:</p>
        <pre class="font-weight-bold">> SELECT toMonth(FlightDate) t, count() c FROM ontime WHERE OriginState = 'CA' GROUP BY t ORDER BY c DESCt</pre>
    </div>

    <?php if (empty($demoInfo['status'])) : ?>
        <p>
            <a href="install.php?start" class="btn btn-success">Install Demo Data</a> <span class="text-muted">takes about 30 minutes</span>
        </p>
    <?php else : ?>

        <div class="row demo-data-progress">
            <div class="col-md-6">
                <div class="progress">
                    <div class="progress-bar bg-success <?php echo $demoInfo['progress'] < 100 ? 'progress-bar-striped progress-bar-animated' : '' ?>" role="progressbar" aria-valuenow="<?php echo $demoInfo['progress'] ?>"
                         aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $demoInfo['progress'] ?>%; height: auto; font-size: 1.5em; padding: 5px 0">
                        <?php echo $demoInfo['progress'] . '%' ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6 progress-bar-title text-muted">
                <?php echo $demoInfo['message'] ?>
            </div>
        </div>

        <br/>
    <?php endif ?>

    <h2>Logs Analytics Demo</h2>
    <p>
        Use ClickHouse for your logs analytics by uploading files into this page.
        It will be automatically parsed by Logstash and the results will be stored in ClickHouse.
        There are number of Grafana Dashboards prepared to look at graphical view of your logs.
    </p>
    <ul>
        <li><a href="//<?php echo $serverName ?>:3000/dashboard/db/web-logs?orgId=1">Web Logs</a></li>
        <li><a href="//<?php echo $serverName ?>:3000/dashboard/db/mysql-slow-logs?orgId=1">MySQL Slow Logs</a></li>
    </ul>
    <p>
        <form method="post" action="logs.php" class="form" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nginx Access Log or MySQL Slow Log</label>
                <input name="log" type="file" class="form-control"  accept=".log" />
            </div>

            <input type="submit" class="btn btn-success" name="type" value="Upload as Web Log" />
            <input type="submit" class="btn btn-success" name="type" value="Upload as MySQL Slow Log" />
        </form>
    </p>

    <h2>Tools to Try</h2>
    <div class="row">

        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-img-top" style="background-image: url('./logotabix.png')" alt="Tabix"></div>
                <div class="card-body">
                    <h4 class="card-title">Tabix</h4>
                    <p class="card-text">Graphical Web Interface + SQL editor for ClickHouse</p>
                    <a href="//<?php echo $serverName ?>:8123/" class="btn btn-primary trabbix-link"
                       data-toggle="tooltip" title="Usage: http://default@<?php echo $serverName?>:8123/default">Try it!</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-img-top"  style="background-image: url('./grafana_icon.svg'); background-size: 20%;" alt="Grafana"></div>
                <div class="card-body">
                    <h4 class="card-title">Grafana</h4>
                    <p class="card-text">The open platform for beautiful analytics and monitoring</p>
                    <a href="//<?php echo $serverName ?>:3000/" class="btn btn-primary"
                       data-toggle="tooltip" title="Login: admin, Password: admin">Try it!</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card">
                <div class="card-img-top"  style="background-image: url('./proxysql.png'); background-size: 20%;" alt="ProxySQL"></div>
                <div class="card-body">
                    <h4 class="card-title">ProxySQL</h4>
                    <p class="card-text">
                        High-performance MySQL proxy with a GPL license
                    </p>
                    <a href="#proxySQLcollapse" class="btn btn-info"  data-toggle="collapse" aria-expanded="false" aria-controls="proxySQLcollapse">More info...</a>
                </div>
            </div>
        </div>
    </div>

    <br/>

    <div class="row">
        <div class="col-md-12" id="accordion"  role="tablist"  data-children=".collapse">

            <div class="collapse" id="proxySQLcollapse"  role="tabpanel" data-parent="#accordion">
                <div class="card card-body">
                    <h3>ProxySQL</h3>
                    <p>
                        This tool allows you to connect your working application to a ClickHouse server without changing the code!

                    </p>

                    <p>
                        You can try it yourself. Use a mysql client and connect using standard credentials and endpoint.
                    </p>

                    <h3>Usage</h3>
                    <p>
                    <pre>demo@altinity:~$ mysql -u clicku -pclickp --protocol=tcp -h <?php echo $serverName ?> -P6090</pre>
                    </p>
                </div>
            </div>

        </div>
    </div>

    <hr/>

    <div class="text-muted text-center">
        &copy;&nbsp;2017&nbsp;<a href="https://www.altinity.com">Altinity</a>
    </div>
</div>


<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.2.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>

<script>
    $(document).ready(function() {
        var gIT = setInterval(function() {
            jQuery.get('install.php?status', function(data) {
                var $progress = $('.progress-bar'),
                    $progressTitle = $('.progress-bar-title');

                $progress.text(data.progress + '%');
                $progress.width(data.progress + '%');
                $('.records-count').text(data.records + ' records');

                $progressTitle.text(data.message);

                if (data.progress >= 100) {
                    $progress.removeClass('progress-bar-striped progress-bar-animated');
                    $('.demo-data-installed').show();
                    $('.demo-data-progress').hide();
                    clearInterval(gIT);
                }
            });
        }, 1000);

        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
    })
</script>
</body>
</html>