<?php
$page_title = "Help Manager/Utah.gov";
include '../checklogin.php';
//$extra_js = "<script type=\"text/javascript\" src=\"../js/user.js\"></script>";
echo checklogin(array('title'=>$page_title,'extra_js'=>$extra_js));
$sys_admin = checkFunctionPermissions($_SESSION['user']['id'], array('system'), 'write');

$help = new \Info\Help($db);
?>
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <? echo $message; ?>
                <h1>Help Manager</h1>
                <hr>
            </div>
        </div>
        <div id="interfaceForm" style="display:none">
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-block">
                    </div>
                </div>
            </div>
        </div>
        <div id="interfaceMain">
            <script type="text/javascript">
                $('#helpTabs a').click(function (e) {
                    e.preventDefault()
                    $(this).tab('show')
                })
            </script>
            <div role="tabpanel">
                <!-- Nav tabs -->
                <ul id="helpTabs" class="nav nav-tabs" role="tablist">
                    <li role="presentation"><a href="#page_help" aria-controls="page_help" role="tab" data-toggle="tab">Page Help</a></li>
                    <li role="presentation"class="active"><a href="#form_help" aria-controls="form_help" role="tab" data-toggle="tab">Form Fields Help</a></li>
                    <?php if ($sys_admin['any']) { echo "<li role=\"presentation\"><a href=\"#advanced\" aria-controls=\"advanced\" role=\"tab\" data-toggle=\"tab\">Advanced Setup</a></li>"; } ?>
                </ul>
                <!-- Tab panes -->
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane" id="page_help">
                        <div class="row">
                            <div class="col-sm-12">
                                <br>
                                <?php echo $help->pageHelpTable(); ?>
                            </div>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane active" id="form_help">
                        <div class="row">
                            <div class="col-sm-12">
                                <br>
                                <?php echo $help->fieldHelpTable(); ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($sys_admin['any']) { echo "
                        <div role=\"tabpanel\" class=\"tab-pane\" id=\"advanced\"><div class=\"row\">
                                <div class=\"col-sm-12\">
                                    <br>
                                    <p class=\"small text-danger\">
                                        <span class=\"glyphicon glyphicon-warning-sign\"></span> Do not change advanced setup unless you are absolutely sure what you are doing. Information in these tables control the automatic forms, saves, updates, as well as help information linking. These tables are only available for easy correction of linking errors.
                                    </p>
                                    <br>
                                    <h5>Table Configuration <label class=\"label label-danger\">Advanced<label></h5>
                                    {$help->tableTable()}
                                </div>
                            </div>
                            <br>
                            <div class=\"row\">
                                <div class=\"col-sm-12\">
                                    <hr>
                                    <h5>Field Configuration <label class=\"label label-danger\">Advanced<label></h5>
                                    {$help->fieldTable()}
                                </div>
                            </div>
                        </div>";
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
