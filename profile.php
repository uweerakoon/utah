<?php
include 'checklogin.php';
$extra_js = "<script type=\"text/javascript\" src=\"../js/user.js\"></script>
            <script type=\"text/javascript\" src=\"../js/val.js\"></script>
            <script>
                User = new User();
                Validate = new Validate();
            </script>";
echo checklogin(array('title'=>'User Profile / Utah.gov','extra_js'=>$extra_js));

$user = new \Info\User($db);
$user_array = $user->get($_SESSION['user']['id']);
$blocks = $user->getBlocks($_SESSION['user']['id']);

$onclick = "User.profileForm({$_SESSION['user']['id']})";
?>
<div class="container" style="margin-bottom: 15px">
    <div class="row">
        <div class="col-sm-12">
            <div class="btn-group pull-right">
                <button class="btn btn-sm btn-default" onclick="<?php echo $onclick; ?>"><span class="glyphicon glyphicon-cog"></span> Edit User Info</button>
            </div>
            <h3><?php echo $user_array['full_name']; ?></h3>
        </div>
    </div>
    <br>
    <div id="interfaceForm" style="display:none">
        <div class="row">
            <div class="col-sm-12">
                <div class="form-block"></div>
            </div>
        </div>
    </div>
    <div id="interfaceMain">
        <div class="row">
            <div class="col-sm-4">
                <address>
                    <?php echo $blocks['email']; ?><br>
                    <?php echo $blocks['phone']; ?>
                </address>
                <address>
                    <a href="<?php echo $blocks['address_href']; ?>" style="color: #000">   
                        <strong><span class="glyphicon glyphicon-map-marker"></span> Your Address</strong><br>
                        <?php echo $blocks['address']; ?>
                    </a>
                </address>
                <address>
                    <?php echo $blocks['password_update']; ?>
                </address>
            </div>
            <div class="col-sm-4">
                <div class="panel panel-default" style="margin-top: 30px">
                    <div class="panel-heading">Agency</div>
                    <div class="panel-body">
                        <?php echo $blocks['agency']; ?>
                    </div>
                </div>
                <div class="panel panel-default" style="margin-top: 30px">
                    <div class="panel-heading"><?php echo $blocks['district_title']; ?></div>
                    <div class="panel-body">
                        <?php echo $blocks['districts']; ?>
                    </div>
                </div>
            </div>
            <!--<div class="col-sm-4">
                <div class="panel panel-default" style="margin-top: 30px">
                    <div class="panel-heading">Projects</div>
                    <div class="panel-body">
                        
                    </div>
                </div>
            </div>-->
        </div>
    </div>
</div>